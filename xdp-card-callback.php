<?php
/**
 * xdp-card-callback.php — cdvrsbooster.site
 * Called by pay.xdigitex.space after user completes card/mpesa/airtel on Pesapal.
 * xdigitex appends ?reference=TX-xxx to the callback_url.
 */
ini_set('display_errors', 0);
session_start();
require_once __DIR__ . "/config/database.php";

$logDir  = __DIR__ . '/logs';
if (!is_dir($logDir)) @mkdir($logDir, 0755, true);
$logFile = "$logDir/xdp_card.log";

function cLog(string $msg): void {
    global $logFile;
    file_put_contents($logFile, "[" . date("Y-m-d H:i:s") . "] " . $msg . "\n", FILE_APPEND | LOCK_EX);
}

$reference = trim(
    $_GET['reference'] ??
    $_GET['ref'] ??
    $_GET['OrderMerchantReference'] ??
    $_SESSION['xdp_card_ref'] ?? ''
);

cLog("HIT GET=" . json_encode($_GET) . " session_ref=" . ($_SESSION['xdp_card_ref'] ?? '') . " resolved=$reference");

if (!empty($_SESSION['xdp_card_ref'])) {
    unset($_SESSION['xdp_card_ref']);
}

if (!$reference) {
    cLog("No reference — redirect to add-funds");
    header("Location: /add-funds.php");
    exit;
}

// Find deposit record
$stmt = $db->prepare("SELECT * FROM deposits WHERE checkout_id = ? LIMIT 1");
$stmt->execute([$reference]);
$deposit = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$deposit) {
    cLog("No deposit record found for ref=$reference");
    header("Location: /add-funds.php");
    exit;
}

$userId = (int)$deposit['user_id'];

if ($deposit['status'] === 'completed') {
    cLog("Already completed ref=$reference");
    header("Location: /add-funds.php?pay=completed");
    exit;
}

// Poll xdigitex for status
$apiKey   = 'pg_L6P7S05sXNANM2rxVL5qdWRbik2OtqSH';
$gwStatus = null;
$gwData   = null;

$ch = curl_init("https://pay.xdigitex.space/api/payments/{$reference}/status");
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT        => 20,
    CURLOPT_HTTPHEADER     => ["X-API-Key: $apiKey"],
    CURLOPT_SSL_VERIFYPEER => false,
]);
$resp = curl_exec($ch);
$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

cLog("Status poll HTTP=$code ref=$reference body=" . substr((string)$resp, 0, 300));

if ($code === 200 && $resp) {
    $gwData   = json_decode($resp, true);
    $gwStatus = $gwData['status'] ?? null;
}

cLog("Gateway status='$gwStatus' ref=$reference user=$userId");

$defaultRates = [
    'KES'=>130,'UGX'=>3800,'CDF'=>2350,'XAF'=>600,'XOF'=>600,
    'RWF'=>1350,'ZMW'=>27,'SLE'=>22,'TZS'=>2600,'GHS'=>15,'NGN'=>1600,
];

if ($gwStatus === 'completed') {
    $rawAmount  = (float)($gwData['amount'] ?? $deposit['amount']);
    $gwCurrency = strtoupper($gwData['currency'] ?? 'USD');

    $credAmount = $rawAmount;
    if ($gwCurrency !== 'USD') {
        $row  = $db->query("SELECT currency_rate FROM currencies WHERE currency_code='$gwCurrency' LIMIT 1")->fetch(PDO::FETCH_ASSOC);
        $rate = (float)($row['currency_rate'] ?? $defaultRates[$gwCurrency] ?? 0);
        if ($rate > 0) $credAmount = round($rawAmount / $rate, 4);
    }

    try {
        $db->beginTransaction();

        $upd = $db->prepare(
            "UPDATE deposits
             SET status='completed', amount=?, completed_at=NOW()
             WHERE id=? AND status='pending'"
        );
        $upd->execute([$credAmount, $deposit['id']]);

        if ($upd->rowCount() > 0) {
            $db->prepare("UPDATE users SET balance = balance + ? WHERE id = ?")
               ->execute([$credAmount, $userId]);
            $db->commit();
            cLog("CREDITED $credAmount USD (from $rawAmount $gwCurrency) user=$userId ref=$reference");
            header("Location: /add-funds.php?pay=completed&amount=" . urlencode(number_format($credAmount, 2)));
            exit;
        }

        $db->rollBack();
        cLog("rowCount=0 — webhook already credited ref=$reference");
        header("Location: /add-funds.php?pay=completed");
        exit;

    } catch (Exception $e) {
        $db->rollBack();
        cLog("DB ERROR: " . $e->getMessage());
        header("Location: /add-funds.php?pay=processing");
        exit;
    }
}

if ($gwStatus === 'failed') {
    $db->prepare("UPDATE deposits SET status='failed' WHERE id=? AND status='pending'")->execute([$deposit['id']]);
    cLog("FAILED ref=$reference user=$userId");
    header("Location: /add-funds.php?pay=failed");
    exit;
}

// Still pending — restore session so add-funds shows check-status view
cLog("PENDING — restoring session ref=$reference user=$userId");
$_SESSION['xdp_card_ref'] = $reference;
header("Location: /add-funds.php?tab=card");
exit;
