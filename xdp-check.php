<?php
/**
 * xdp-check.php — Legrand Panel
 * AJAX endpoint: polls xdigitex status for card/mobile payments.
 * Adapted for "payments" table.
 */
ini_set("display_errors", 0);
session_start();

header("Content-Type: application/json");

$config = require __DIR__ . "/app/config.php";
$logDir = __DIR__ . "/logs";
if (!is_dir($logDir)) @mkdir($logDir, 0755, true);
$logFile = "$logDir/xdp_check.log";

function cLog(string $msg): void {
    global $logFile;
    file_put_contents($logFile, "[" . date("Y-m-d H:i:s") . "] $msg\n", FILE_APPEND | LOCK_EX);
}

if (empty($_SESSION["msmbilisim_userid"])) {
    echo json_encode(["status" => "error", "msg" => "Not authenticated"]);
    exit;
}

try {
    $port = isset($config["db"]["port"]) ? ";port=" . $config["db"]["port"] : "";
    $db = new PDO(
        "mysql:host=" . $config["db"]["host"] . $port . ";dbname=" . $config["db"]["name"] . ";charset=utf8mb4",
        $config["db"]["user"],
        $config["db"]["pass"],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
} catch (Exception $e) {
    echo json_encode(["status" => "error", "msg" => "DB connection failed"]);
    exit;
}

$ref    = trim($_GET["ref"] ?? "");
$clientId = (int)$_SESSION["msmbilisim_userid"];

if (!$ref) {
    echo json_encode(["status" => "error", "msg" => "No reference"]);
    exit;
}

// Find payment
$stmt = $db->prepare("SELECT payment_id, payment_status, payment_amount, payment_extra FROM payments WHERE t_id = ? AND client_id = ? LIMIT 1");
$stmt->execute([$ref, $clientId]);
$pd = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$pd) {
    echo json_encode(["status" => "not_found"]);
    exit;
}

if ($pd["payment_status"] == 3) {
    echo json_encode(["status" => "completed", "amount" => $pd["payment_amount"]]);
    exit;
}

// Get API Key
$pmStmt = $db->prepare("SELECT methodExtras FROM paymentmethods WHERE methodId=200 LIMIT 1");
$pmStmt->execute();
$pmRow = $pmStmt->fetch(PDO::FETCH_ASSOC);
$extras = json_decode($pmRow["methodExtras"] ?? "{}", true);
$apiKey = trim($extras["apiKey"] ?? "");

if (!$apiKey) {
    echo json_encode(["status" => "error", "msg" => "API Key missing"]);
    exit;
}

// Poll XDigitex Gateway
$ch = curl_init("https://pay.xdigitex.space/api/payments/{$ref}/status");
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 10,
    CURLOPT_HTTPHEADER => ["X-API-Key: $apiKey"],
    CURLOPT_SSL_VERIFYPEER => false
]);
$resp = curl_exec($ch);
$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

cLog("POLL ref=$ref http=$code resp=" . substr($resp, 0, 300));

$gwStatus = null;
$gwData = null;
if ($code === 200 && $resp) {
    $gwData = json_decode($resp, true);
    $gwStatus = $gwData["status"] ?? null;
}

if ($gwStatus === "completed") {
    $paidAmountUsd = (float)$pd["payment_amount"];
    try {
        $db->beginTransaction();
        
        // Get current balance
        $clientRow = $db->prepare("SELECT balance FROM clients WHERE client_id = :id FOR UPDATE");
        $clientRow->execute(["id" => $clientId]);
        $currentBalance = (float)($clientRow->fetchColumn() ?: 0);

        // Update payment status
        $upd = $db->prepare("UPDATE payments SET payment_status = 3, payment_delivery = 2, client_balance = :bal WHERE payment_id = :pid AND payment_status != 3");
        $upd->execute(["bal" => $currentBalance, "pid" => $pd["payment_id"]]);

        if ($upd->rowCount() > 0) {
            $db->prepare("UPDATE clients SET balance = balance + :amt WHERE client_id = :id")->execute(["amt" => $paidAmountUsd, "id" => $clientId]);
            $db->commit();
            cLog("CREDITED $paidAmountUsd USD to client=$clientId ref=$ref via POLL");
            echo json_encode(["status" => "completed", "amount" => $paidAmountUsd]);
        } else {
            $db->rollBack();
            echo json_encode(["status" => "completed", "amount" => $paidAmountUsd]);
        }
    } catch (Exception $e) {
        $db->rollBack();
        cLog("DB ERROR: " . $e->getMessage());
        echo json_encode(["status" => "error"]);
    }
    exit;
}

if ($gwStatus === "failed") {
    $db->prepare("UPDATE payments SET payment_status = 2 WHERE payment_id = ?")->execute([$pd["payment_id"]]);
    echo json_encode(["status" => "failed"]);
    exit;
}

echo json_encode(["status" => "pending", "gateway_status" => $gwStatus]);
