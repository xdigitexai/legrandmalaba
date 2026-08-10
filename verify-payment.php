<?php
require_once "config/auth.php";
require_once "config/database.php";

$userId = $_SESSION['user_id'];

$checkoutId = $_GET['checkout_id'] ?? '';
$amount     = (int)($_GET['amount'] ?? 0);

if (!$checkoutId || $amount <= 0) {
    die("Missing checkout ID");
}

$status = "pending";
$message = "Waiting for M-Pesa confirmation...";

/* =========================
   VERIFY PAYMENT (POLL)
========================= */
for ($i = 0; $i < 8; $i++) { // ~40 seconds
    sleep(5);

    $payload = json_encode([
        "checkoutRequestId" => $checkoutId
    ]);

    $ch = curl_init("https://mpesapi.giftedtech.co.ke/api/verify-transaction.php");
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_HTTPHEADER     => ["Content-Type: application/json"],
        CURLOPT_POSTFIELDS     => $payload,
        CURLOPT_TIMEOUT        => 30
    ]);

    $response = curl_exec($ch);
    curl_close($ch);

    $data = json_decode($response, true);

    if (($data['status'] ?? '') === 'completed') {
        $status = "completed";
        break;
    }

    if (($data['status'] ?? '') === 'failed') {
        $status = "failed";
        break;
    }
}

/* =========================
   CREDIT WALLET
========================= */
if ($status === 'completed') {

    $stmt = $db->prepare("
        UPDATE users 
        SET balance = balance + ? 
        WHERE id = ?
    ");
    $stmt->execute([$amount, $userId]);

    header("Location: dashboard.php?funded=1");
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Verifying Payment</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<style>
body{font-family:Arial;background:#f5f7fb;display:flex;justify-content:center;align-items:center;height:100vh}
.box{background:#fff;padding:30px;border-radius:14px;text-align:center}
</style>
</head>
<body>

<div class="box">
<h3>Payment <?= strtoupper($status) ?></h3>
<p><?= htmlspecialchars($message) ?></p>

<?php if ($status === 'failed'): ?>
<p>Please try again.</p>
<a href="add-funds.php">Retry</a>
<?php endif; ?>
</div>

</body>
</html>