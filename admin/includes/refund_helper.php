<?php
function autoRefund(PDO $db, array $order)
{
    // Uses correct tipmrnhl_ssdboost schema
    $status = $order['order_status'] ?? $order['status'] ?? '';
    if (!in_array(strtolower($status), ['failed','cancelled','canceled'])) return false;

    $db->beginTransaction();
    try {
        // Refund wallet balance
        $clientId = $order['client_id'] ?? $order['user_id'] ?? null;
        $amount   = $order['order_charge'] ?? $order['price'] ?? 0;
        $orderId  = $order['order_id'] ?? $order['id'] ?? null;
        if (!$clientId || !$orderId) { $db->rollBack(); return false; }

        $db->prepare("UPDATE clients SET balance = balance + ? WHERE client_id = ?")
           ->execute([$amount, $clientId]);

        $db->commit();
        return true;
    } catch (Exception $e) {
        $db->rollBack();
        return false;
    }
}
