<?php
// Cheap Panel - M-Pesa Payment Callback Handler

require_once "../config/database.php";

// Allow only POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Read JSON input
$data = json_decode(file_get_contents("php://input"), true);

$order_id = $data['order_id'] ?? null;
$checkoutRequestId = $data['checkoutRequestId'] ?? null;

// Basic validation
if (!$order_id || !$checkoutRequestId) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}

try {
    // Fetch order
    $stmt = $db->prepare("
        SELECT id, status 
        FROM orders 
        WHERE order_id = ? 
        LIMIT 1
    ");
    $stmt->execute([$order_id]);
    $order = $stmt->fetch();

    if (!$order) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Order not found']);
        exit;
    }

    // Prevent double processing
    if ($order['status'] === 'paid' || $order['status'] === 'processing') {
        echo json_encode(['success' => true, 'message' => 'Order already processed']);
        exit;
    }

    // Update order as PAID
    $update = $db->prepare("
        UPDATE orders 
        SET status = 'paid',
            transaction_ref = ?
        WHERE order_id = ?
    ");
    $update->execute([$checkoutRequestId, $order_id]);

    echo json_encode([
        'success' => true,
        'message' => 'Payment confirmed and order updated'
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Server error'
    ]);
}
