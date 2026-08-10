<?php
require_once "config/auth.php";
require_once "config/database.php";

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'unauthorized']);
    exit;
}

$userId = $_SESSION['user_id'];

$data = json_decode(file_get_contents("php://input"), true);
$checkoutId = $data['checkoutRequestId'] ?? null;

if (!$checkoutId) {
    echo json_encode(['status' => 'invalid']);
    exit;
}

/* =========================
   CHECK DEPOSIT
========================= */
$stmt = $db->prepare("
    SELECT * FROM deposits
    WHERE checkout_id = ?
      AND user_id = ?
      AND status = 'pending'
    LIMIT 1
");
$stmt->execute([$checkoutId, $userId]);
$deposit = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$deposit) {
    echo json_encode(['status' => 'already_processed']);
    exit;
}

/* =========================
   VERIFY WITH GIFTEDTECH
========================= */
$ch = curl_init("https://mpesapi.giftedtech.co.ke/api/verify-transaction.php");
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST           => true,
    CURLOPT_HTTPHEADER     => ["Content-Type: application/json"],
    CURLOPT_POSTFIELDS     => json_encode([
        "checkoutRequestId" => $checkoutId
    ]),
    CURLOPT_TIMEOUT => 30
]);

$response = curl_exec($ch);
curl_close($ch);

$verify = json_decode($response, true);

if (!is_array($verify) || $verify['status'] !== 'completed') {
    echo json_encode(['status' => 'pending']);
    exit;
}

/* =========================
   CREDIT WALLET (ATOMIC)
========================= */
try {
    $db->beginTransaction();

    // Credit user wallet
    $stmt = $db->prepare("
        UPDATE users
        SET balance = balance + ?
        WHERE id = ?
    ");
    $stmt->execute([$deposit['amount'], $userId]);

    // Mark deposit completed
    $stmt = $db->prepare("
        UPDATE deposits
        SET status = 'completed'
        WHERE id = ?
    ");
    $stmt->execute([$deposit['id']]);

    $db->commit();

    echo json_encode(['status' => 'completed']);

} catch (Exception $e) {
    $db->rollBack();
    echo json_encode(['status' => 'error']);
}