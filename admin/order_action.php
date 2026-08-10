<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();
require_once "../config/database.php";

/* ============================
   ADMIN GUARD
============================ */
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit;
}

/* ============================
   VALIDATE REQUEST
============================ */
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: orders.php");
    exit;
}

$action  = $_POST['action'] ?? '';
$orderId = $_POST['order_id'] ?? '';

if (!$action || !$orderId) {
    header("Location: orders.php");
    exit;
}

/* ============================
   FETCH ORDER (SAFE)
============================ */
$stmt = $db->prepare("
    SELECT 
        o.*,
        s.provider_id,
        s.provider_service_id,
        p.api_url,
        p.api_key
    FROM orders o
    JOIN services s ON s.id = o.service_id
    JOIN providers p ON p.id = s.provider_id
    WHERE o.order_id = ?
    LIMIT 1
");
$stmt->execute([$orderId]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    die("Order not found");
}

/* ============================
   CANCEL ORDER
============================ */
if ($action === 'cancel') {

    if (in_array($order['status'], ['failed','cancelled'])) {
        header("Location: orders.php");
        exit;
    }

    $db->prepare("
        UPDATE orders
        SET status = 'cancelled'
        WHERE order_id = ?
    ")->execute([$orderId]);

    header("Location: orders.php");
    exit;
}

/* ============================
   RESEND ORDER TO PROVIDER
============================ */
if ($action === 'resend') {

    if (
        !empty($order['provider_order_id']) ||
        !in_array($order['status'], ['paid','processing'])
    ) {
        header("Location: orders.php");
        exit;
    }

    $payload = [
        'key'      => $order['api_key'],
        'action'   => 'add',
        'service'  => $order['provider_service_id'],
        'link'     => $order['link'],
        'quantity' => $order['quantity']
    ];

    $ch = curl_init($order['api_url']);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => http_build_query($payload),
        CURLOPT_TIMEOUT        => 30
    ]);

    $response = curl_exec($ch);
    curl_close($ch);

    $result = json_decode($response, true);

    if (isset($result['order'])) {

        $db->prepare("
            UPDATE orders
            SET 
                provider_order_id = ?,
                provider_response = ?,
                status = 'processing'
            WHERE order_id = ?
        ")->execute([
            $result['order'],
            json_encode($result),
            $orderId
        ]);
    }

    header("Location: orders.php");
    exit;
}

/* ============================
   FALLBACK
============================ */
header("Location: orders.php");
exit;