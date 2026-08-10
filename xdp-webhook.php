<?php
/**
 * xdp-webhook.php — cdvrsbooster.site
 * Receives xdigitex payment webhooks (PawaPay + card).
 * Payload: {"event":"payment.completed","reference":"TX-xxx","amount":...,"currency":"KES","status":"completed"}
 */
ini_set('display_errors', 0);
require_once __DIR__ . "/config/database.php";

$logDir  = __DIR__ . '/logs';
if (!is_dir($logDir)) @mkdir($logDir, 0755, true);
$logFile = "$logDir/xdp_webhook.log";

function wLog(string $msg): void {
    global $logFile;
    file_put_contents($logFile, "[" . date("Y-m-d H:i:s") . "] " . $msg . "\n", FILE_APPEND | LOCK_EX);
}

$rawBody   = file_get_contents('php://input');
$payload   = json_decode($rawBody, true) ?: [];
$event     = $payload['event']     ?? '';
$reference = trim($payload['reference'] ?? '');
$amount    = (float)($payload['amount'] ?? 0);
$currency  = strtoupper($payload['currency'] ?? '');
$status    = $payload['status']    ?? '';

$ip = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? 'unknown';
$ua = $_SERVER['HTTP_USER_AGENT'] ?? '(no-ua)';
wLog("REQUEST ip=$ip method={$_SERVER['REQUEST_METHOD']}");
wLog("BODY=" . substr($rawBody, 0, 500));
wLog("HIT event=$event ref=$reference amount=$amount $currency status=$status");

if ($event !== 'payment.completed' || $status !== 'completed' || !$reference) {
    http_response_code(200); echo 'OK'; exit;
}

// Find deposit by checkout_id (xdigitex TX ref) or reference (XDP- internal ref)
$stmt = $db->prepare(
    "SELECT id, user_id, amount, api_response FROM deposits
     WHERE (checkout_id = ? OR reference = ?)
       AND status = 'pending'
     LIMIT 1"
);
$stmt->execute([$reference, $reference]);
$deposit = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$deposit) {
    wLog("SKIP — no pending deposit found for ref=$reference");
    http_response_code(200); echo 'OK'; exit;
}

$userId    = (int)$deposit['user_id'];
$rawAmount = $amount > 0 ? $amount : (float)$deposit['amount'];
$gwCurrency = $currency ?: 'USD';

// Currency conversion → USD
$defaultRates = [
    'KES'=>130,'UGX'=>3800,'CDF'=>2350,'XAF'=>600,'XOF'=>600,
    'RWF'=>1350,'ZMW'=>27,'SLE'=>22,'TZS'=>2600,'GHS'=>15,'NGN'=>1600,
];
$credAmount = $rawAmount;
if ($gwCurrency !== 'USD' && $gwCurrency !== '') {
    $rateRow = $db->prepare("SELECT currency_rate FROM currencies WHERE currency_code = ? LIMIT 1");
    $rateRow->execute([$gwCurrency]);
    $rateData = $rateRow->fetch(PDO::FETCH_ASSOC);
    $rate = (float)($rateData['currency_rate'] ?? 0);
    if ($rate <= 0) $rate = (float)($defaultRates[$gwCurrency] ?? 0);
    if ($rate > 0) $credAmount = round($rawAmount / $rate, 4);
}

// Update api_response note with local amount for history
$oldNote  = $deposit['api_response'] ?? '';
$baseNote = strtok($oldNote, '|');
$localInfo = ($gwCurrency && $gwCurrency !== 'USD') ? '|' . number_format($rawAmount, 2, '.', '') . ' ' . $gwCurrency : '';
$newNote   = trim($baseNote) . $localInfo;

wLog("Crediting user=$userId amount=$credAmount USD (from $rawAmount $gwCurrency) for deposit_id={$deposit['id']}");

try {
    $db->beginTransaction();

    $upd = $db->prepare(
        "UPDATE deposits
         SET status='completed', amount=?, api_response=?, completed_at=NOW()
         WHERE id=? AND status='pending'"
    );
    $upd->execute([$credAmount, $newNote, $deposit['id']]);

    if ($upd->rowCount() > 0) {
        $db->prepare("UPDATE users SET balance = balance + ? WHERE id = ?")
           ->execute([$credAmount, $userId]);
        $db->commit();
        wLog("CREDITED $credAmount USD to user=$userId ref=$reference");
    } else {
        $db->rollBack();
        wLog("SKIP (already processed) ref=$reference");
    }
} catch (Exception $e) {
    $db->rollBack();
    wLog("DB ERROR: " . $e->getMessage());
    http_response_code(500); echo 'ERROR'; exit;
}

http_response_code(200); echo 'OK';
