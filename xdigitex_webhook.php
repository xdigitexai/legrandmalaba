<?php
/**
 * xdigitex_webhook.php — Legrand panel
 * Standalone server-to-server POST webhook from pay.xdigitex.space.
 * No session required. Credited amount is always the stored USD value.
 */

$config = require __DIR__ . '/app/config.php';

$logDir = __DIR__ . '/logs';
if (!is_dir($logDir)) {
    @mkdir($logDir, 0755, true);
}

function wLog(string $msg): void
{
    global $logDir;
    @file_put_contents("$logDir/xdp.log", "[" . date('Y-m-d H:i:s') . "] WH: $msg\n", FILE_APPEND | LOCK_EX);
}

try {
    $port = isset($config["db"]["port"]) ? ";port=" . $config["db"]["port"] : "";
    $conn = new PDO(
        "mysql:host=" . $config["db"]["host"] . $port
            . ";dbname=" . $config["db"]["name"]
            . ";charset=" . $config["db"]["charset"],
        $config["db"]["user"],
        $config["db"]["pass"]
    );
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    wLog("DB_CONNECT_ERROR: " . $e->getMessage());
    http_response_code(500);
    die("DB error");
}

$rawBody = file_get_contents('php://input');
$ip      = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? 'unknown';
wLog("REQUEST ip=$ip raw=" . substr($rawBody, 0, 500));

$payload   = json_decode($rawBody, true) ?? [];
    if (json_last_error() !== JSON_ERROR_NONE) {
        wLog("JSON_DECODE_ERROR: " . json_last_error_msg() . ", raw=" . substr($rawBody, 0, 200));
    }
$event     = $payload['event']     ?? '';
$reference = trim($payload['reference'] ?? '');
$status    = $payload['status']    ?? '';
$amount    = (float)($payload['amount']   ?? 0);
$currency  = strtoupper(trim($payload['currency'] ?? ''));

wLog("PARSED event=$event ref=$reference status=$status amount=$amount $currency");

// Only process confirmed completions
if ($status !== 'completed' || empty($reference)) {
    wLog("SKIP — not completed or no reference");
    http_response_code(200);
    echo 'OK';
    exit;
}

// Find pending payment by xdigitex reference
$stmt = $conn->prepare(
    "SELECT * FROM payments
     WHERE (payment_extra = :ref OR t_id = :ref)
       AND payment_status != 3
     LIMIT 1"
);
$stmt->execute(["ref" => $reference]);

if (!$stmt->rowCount()) {
    wLog("SKIP — no pending payment found for ref=$reference");
    http_response_code(200);
    echo 'OK';
    exit;
}

$pd             = $stmt->fetch(PDO::FETCH_ASSOC);
$clientId       = (int)$pd["client_id"];
$paidAmountUsd  = (float)$pd["payment_amount"]; // always stored in USD

wLog("PROCESSING payment_id={$pd['payment_id']} client=$clientId paidUSD=$paidAmountUsd");

try {
    $conn->beginTransaction();

    // Get current balance
    $clientRow = $conn->prepare("SELECT balance FROM clients WHERE client_id = :id FOR UPDATE");
    $clientRow->execute(["id" => $clientId]);
    $clientData     = $clientRow->fetch(PDO::FETCH_ASSOC);
    $currentBalance = (float)($clientData["balance"] ?? 0);

    // Mark payment completed (only if still pending — prevents double-credit)
    $upd = $conn->prepare(
        "UPDATE payments SET
             payment_status   = 3,
             payment_delivery = 2,
             client_balance   = :bal
         WHERE payment_id     = :pid
           AND payment_status != 3"
    );
    $upd->execute(["bal" => $currentBalance, "pid" => $pd["payment_id"]]);

    if ($upd->rowCount() > 0) {
        $conn->prepare("UPDATE clients SET balance = balance + :amt WHERE client_id = :id")
             ->execute(["amt" => $paidAmountUsd, "id" => $clientId]);
        $conn->commit();
        wLog("CREDITED \${$paidAmountUsd} to client=$clientId (new balance=" . ($currentBalance + $paidAmountUsd) . ") ref=$reference");
    } else {
        $conn->rollBack();
        wLog("SKIP_CREDIT — already processed ref=$reference");
    }
} catch (Throwable $e) {
    $conn->rollBack();
    wLog("DB_ERROR: " . $e->getMessage() . " ref=$reference");
    http_response_code(500);
    echo 'ERROR';
    exit;
}

http_response_code(200);
echo 'OK';
