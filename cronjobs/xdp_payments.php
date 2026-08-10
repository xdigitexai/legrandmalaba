<?php
/**
 * xdp_payments.php — Legrand Panel
 * Cron: checks pending xdigitex payments and processes completed ones.
 * Runs every 5 minutes.
 */
ini_set('max_execution_time', '300');
ini_set('display_errors', 0);

$config = require dirname(__DIR__) . '/app/config.php';
$logDir = dirname(__DIR__) . '/logs';
if (!is_dir($logDir)) @mkdir($logDir, 0755, true);
$logFile = "$logDir/xdp_cron.log";

function xLog(string $msg): void {
    global $logFile;
    file_put_contents($logFile, "[" . date("Y-m-d H:i:s") . "] $msg
", FILE_APPEND | LOCK_EX);
}

try {
    $port = isset($config["db"]["port"]) ? ";port=" . $config["db"]["port"] : "";
    $conn = new PDO(
        "mysql:host=" . $config["db"]["host"] . $port . ";dbname=" . $config["db"]["name"] . ";charset=utf8mb4",
        $config["db"]["user"],
        $config["db"]["pass"],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
} catch (Exception $e) {
    die("DB error: " . $e->getMessage());
}

// Find pending payments (status=2, delivery=1)
$stmt = $conn->prepare(
    "SELECT p.*, c.balance as client_balance
     FROM payments p
     INNER JOIN clients c ON c.client_id = p.client_id
     WHERE p.payment_status = 2
       AND p.payment_delivery = 1
       AND p.payment_extra IS NOT NULL
       AND p.payment_extra != ''
     LIMIT 20"
);
$stmt->execute();
$pending = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($pending)) {
    xLog("No pending payments to process");
    exit;
}

xLog("Found " . count($pending) . " pending payments to check");

foreach ($pending as $p) {
    $ref = trim($p["payment_extra"]);
    $paymentId = (int)$p["payment_id"];
    $clientId = (int)$p["client_id"];
    $amount = (float)$p["payment_amount"];
    
    xLog("Checking payment_id=$paymentId ref=$ref amount=$amount");
    
    // Call xdigitex verify API
    $apiKey = defined('XDIGITEX_API_KEY') ? XDIGITEX_API_KEY : '';
    $ch = curl_init('https://pay.xdigitex.space/api/checkout/verify');
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode(['reference' => $ref]),
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $apiKey,
        ],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 15,
        CURLOPT_SSL_VERIFYPEER => false,
    ]);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode !== 200 || !$response) {
        xLog("API check failed for ref=$ref (HTTP $httpCode)");
        continue;
    }
    
    $data = json_decode($response, true);
    if (!$data || !isset($data['status'])) {
        xLog("Invalid API response for ref=$ref");
        continue;
    }
    
    if ($data['status'] === 'completed' || $data['status'] === 'success') {
        xLog("Payment completed for ref=$ref — crediting client=$clientId amount=$amount");
        
        try {
            $conn->beginTransaction();
            
            // Get current balance with lock
            $cRow = $conn->prepare("SELECT balance FROM clients WHERE client_id = :id FOR UPDATE");
            $cRow->execute(['id' => $clientId]);
            $currentBalance = (float)($cRow->fetchColumn() ?: 0);
            
            // Mark payment completed
            $upd = $conn->prepare(
                "UPDATE payments SET
                     payment_status = 3,
                     payment_delivery = 2,
                     client_balance = :bal
                 WHERE payment_id = :pid
                   AND payment_status = 2"
            );
            $upd->execute(['bal' => $currentBalance, 'pid' => $paymentId]);
            
            if ($upd->rowCount() > 0) {
                // Credit client
                $conn->prepare("UPDATE clients SET balance = balance + :amt WHERE client_id = :id")
                     ->execute(['amt' => $amount, 'id' => $clientId]);
                
                // Log
                $conn->prepare(
                    "INSERT INTO client_report SET
                         client_id = :cid,
                         action = :action,
                         report_ip = :ip,
                         report_date = NOW()"
                )->execute([
                    'cid' => $clientId,
                    'action' => 'Payment of $' . number_format($amount, 2) . ' completed via xdigitex (cron)',
                    'ip' => '127.0.0.1',
                ]);
                
                $conn->commit();
                xLog("CREDITED $" . number_format($amount, 2) . " to client=$clientId (balance was $currentBalance)");
            } else {
                $conn->rollBack();
                xLog("SKIP — payment_id=$paymentId already processed");
            }
        } catch (Exception $e) {
            $conn->rollBack();
            xLog("DB_ERROR for payment_id=$paymentId: " . $e->getMessage());
        }
    } elseif ($data['status'] === 'failed' || $data['status'] === 'expired') {
        xLog("Payment failed/expired for ref=$ref — marking as failed");
        $conn->prepare(
            "UPDATE payments SET payment_status = 4, payment_delivery = 2 WHERE payment_id = :pid"
        )->execute(['pid' => $paymentId]);
    } else {
        xLog("Payment ref=$ref still pending (status: " . $data['status'] . ")");
    }
}

xLog("Cron completed");
