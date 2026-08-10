<?php
/**
 * check_processing_deposits.php — Legrand Panel
 * Cron: checks pending deposits in cdvrs DB and processes completed ones.
 * Runs every 5 minutes.
 */
ini_set('max_execution_time', '300');
ini_set('display_errors', 0);

$logDir = dirname(__DIR__) . '/logs';
if (!is_dir($logDir)) @mkdir($logDir, 0755, true);
$logFile = "$logDir/check_deposits.log";

function dLog(string $msg): void {
    global $logFile;
    file_put_contents($logFile, "[" . date("Y-m-d H:i:s") . "] $msg
", FILE_APPEND | LOCK_EX);
}

// Connect to cdvrs DB
$DB_HOST = "localhost";
$DB_NAME = "tipmrnhl_cdvrs";
$DB_USER = "tipmrnhl_cdvrs";
$DB_PASS = "tipmrnhl_cdvrs";

try {
    $db = new PDO(
        "mysql:host={$DB_HOST};dbname={$DB_NAME};charset=utf8mb4",
        $DB_USER,
        $DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
} catch (Exception $e) {
    dLog("DB connection failed: " . $e->getMessage());
    die("DB error");
}

// Find pending deposits
$stmt = $db->prepare(
    "SELECT d.*, u.balance as user_balance
     FROM deposits d
     INNER JOIN users u ON u.id = d.user_id
     WHERE d.status = 'pending'
       AND (d.checkout_id IS NOT NULL AND d.checkout_id != '')
     LIMIT 20"
);
$stmt->execute();
$pending = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($pending)) {
    dLog("No pending deposits to process");
    exit;
}

dLog("Found " . count($pending) . " pending deposits to check");

foreach ($pending as $dep) {
    $ref = trim($dep["checkout_id"]);
    $depositId = (int)$dep["id"];
    $userId = (int)$dep["user_id"];
    $amount = (float)$dep["amount"];
    
    dLog("Checking deposit_id=$depositId ref=$ref amount=$amount");
    
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
        dLog("API check failed for ref=$ref (HTTP $httpCode)");
        continue;
    }
    
    $data = json_decode($response, true);
    if (!$data || !isset($data['status'])) {
        dLog("Invalid API response for ref=$ref");
        continue;
    }
    
    if ($data['status'] === 'completed' || $data['status'] === 'success') {
        dLog("Deposit completed for ref=$ref — crediting user=$userId amount=$amount");
        
        try {
            $db->beginTransaction();
            
            // Get current balance with lock
            $uRow = $db->prepare("SELECT balance FROM users WHERE id = :id FOR UPDATE");
            $uRow->execute(['id' => $userId]);
            $currentBalance = (float)($uRow->fetchColumn() ?: 0);
            
            // Mark deposit completed
            $upd = $db->prepare(
                "UPDATE deposits SET
                     status = 'completed',
                     updated_at = NOW()
                 WHERE id = :id
                   AND status = 'pending'"
            );
            $upd->execute(['id' => $depositId]);
            
            if ($upd->rowCount() > 0) {
                // Credit user
                $db->prepare("UPDATE users SET balance = balance + :amt WHERE id = :id")
                     ->execute(['amt' => $amount, 'id' => $userId]);
                
                $db->commit();
                dLog("CREDITED $" . number_format($amount, 2) . " to user=$userId (balance was $currentBalance)");
            } else {
                $db->rollBack();
                dLog("SKIP — deposit_id=$depositId already processed");
            }
        } catch (Exception $e) {
            $db->rollBack();
            dLog("DB_ERROR for deposit_id=$depositId: " . $e->getMessage());
        }
    } elseif ($data['status'] === 'failed' || $data['status'] === 'expired') {
        dLog("Deposit failed/expired for ref=$ref — marking as failed");
        $db->prepare(
            "UPDATE deposits SET status = 'failed', updated_at = NOW() WHERE id = :id"
        )->execute(['id' => $depositId]);
    } else {
        dLog("Deposit ref=$ref still pending (status: " . $data['status'] . ")");
    }
}

dLog("Cron completed");
