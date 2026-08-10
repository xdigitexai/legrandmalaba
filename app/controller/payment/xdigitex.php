<?php
/**
 * payment/xdigitex.php — Legrand panel
 * Called via GET after card/Pesapal checkout redirect.
 * Also handles manual status checks.
 * xdigitex appends ?reference=TX-xxx to redirect_url.
 */
if (!defined('PAYMENT')) {
    http_response_code(404);
    die();
}

$apiKey = $methodExtras["apiKey"] ?? '';

$logDir = PATH . '/logs';
if (!is_dir($logDir)) {
    @mkdir($logDir, 0755, true);
}
function cbLog(string $msg): void
{
    global $logDir;
    @file_put_contents("$logDir/xdp.log", "[" . date('Y-m-d H:i:s') . "] CB: $msg\n", FILE_APPEND | LOCK_EX);
}

if ($_SERVER["REQUEST_METHOD"] !== "GET") {
    http_response_code(405);
    die();
}

// xdigitex appends ?reference=TX-xxx; our callback_url has ?ref=XDP-internal
$xdgRef  = trim($_GET["reference"] ?? '');
$localRef = trim($_GET["ref"] ?? '');

cbLog("HIT GET=" . json_encode($_GET) . " user={$user['client_id']}");

// Resolve payment record
$pd = null;
foreach (array_filter([$xdgRef, $localRef]) as $candidate) {
    $s = $conn->prepare("SELECT * FROM payments WHERE payment_extra = :ref LIMIT 1");
    $s->execute(["ref" => $candidate]);
    if ($s->rowCount()) {
        $pd = $s->fetch(PDO::FETCH_ASSOC);
        break;
    }
}

if (!$pd) {
    cbLog("No payment found GET=" . json_encode($_GET));
    header("Location: " . site_url("addfunds"));
    exit;
}

// Already fully processed — just redirect
if ((int)$pd["payment_status"] === 3 && (int)$pd["payment_delivery"] === 2) {
    cbLog("Already completed payment_id={$pd['payment_id']}");
    header("Location: " . site_url("addfunds"));
    exit;
}

// Verify payment via xdigitex status API
$ref = $pd["payment_extra"];
$ch  = curl_init("https://pay.xdigitex.space/api/payments/" . urlencode($ref) . "/status");
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER     => ["X-API-Key: " . $apiKey],
    CURLOPT_TIMEOUT        => 20,
]);
$raw      = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$gwData   = json_decode($raw, true) ?? [];
$gwStatus = $gwData['status'] ?? null;

cbLog("STATUS_POLL ref=$ref http=$httpCode gwStatus=$gwStatus");

if ($gwStatus === 'completed') {
    $paidAmountUsd = (float)$pd["payment_amount"];

    try {
        $conn->beginTransaction();

        $clientRow = $conn->prepare("SELECT balance FROM clients WHERE client_id = :id FOR UPDATE");
        $clientRow->execute(["id" => $pd["client_id"]]);
        $clientData     = $clientRow->fetch(PDO::FETCH_ASSOC);
        $currentBalance = (float)($clientData["balance"] ?? 0);

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
                 ->execute(["amt" => $paidAmountUsd, "id" => $pd["client_id"]]);
            $conn->commit();
            cbLog("CREDITED \${$paidAmountUsd} to client={$pd['client_id']} ref=$ref");
        } else {
            $conn->rollBack();
            cbLog("SKIP_CREDIT (already done) ref=$ref");
        }
    } catch (Throwable $e) {
        $conn->rollBack();
        cbLog("DB_ERROR: " . $e->getMessage());
    }
} elseif ($gwStatus === 'failed') {
    $conn->prepare("UPDATE payments SET payment_status=3, payment_delivery=1 WHERE payment_id=:id AND payment_status != 3")
         ->execute(["id" => $pd["payment_id"]]);
    cbLog("FAILED ref=$ref");
}

header("Location: " . site_url("addfunds"));
exit;
