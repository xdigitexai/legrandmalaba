<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once "config/database.php";

echo "<h2>🔧 Recovering Lost Revenue...</h2>";

try {

    $db->beginTransaction();

    // Get markup
    $pricing = $db->query("SELECT markup_percent FROM pricing_rules WHERE id = 1")
                  ->fetch(PDO::FETCH_ASSOC);

    if (!$pricing) {
        throw new Exception("Pricing rule missing.");
    }

    $markup = (float)$pricing['markup_percent'];

    // Get all affected orders
    $stmt = $db->prepare("
        SELECT o.id, o.order_id, o.user_id, o.quantity,
               s.cost_usd,
               u.balance
        FROM orders o
        JOIN services s ON s.id = o.service_id
        JOIN users u ON u.id = o.user_id
        WHERE (o.price = 0 OR o.price IS NULL)
        AND o.status IN ('completed','processing','paid')
    ");
    $stmt->execute();

    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!$orders) {
        echo "No affected orders found.";
        $db->rollBack();
        exit;
    }

    $totalRecovered = 0;
    $count = 0;

    foreach ($orders as $order) {

        $costUsd = (float)$order['cost_usd'];
        if ($costUsd <= 0) continue;

        // Calculate correct sell price
        $sellPer1000 = $costUsd * (1 + $markup / 100);
        $correctPrice = round(($order['quantity'] / 1000) * $sellPer1000, 4);

        if ($correctPrice <= 0) continue;

        // Check user balance
        if ($order['balance'] < $correctPrice) {
            echo "⚠ Skipped Order {$order['order_id']} (User insufficient balance)<br>";
            continue;
        }

        // Deduct user
        $db->prepare("UPDATE users SET balance = balance - ? WHERE id = ?")
           ->execute([$correctPrice, $order['user_id']]);

        // Update order price
        $db->prepare("UPDATE orders SET price = ? WHERE id = ?")
           ->execute([$correctPrice, $order['id']]);

        $totalRecovered += $correctPrice;
        $count++;

        echo "✅ Fixed {$order['order_id']} | Deducted: $correctPrice USD<br>";
    }

    $db->commit();

    echo "<hr>";
    echo "Total Orders Fixed: $count<br>";
    echo "Total Revenue Recovered: $totalRecovered USD<br>";

} catch (Exception $e) {
    $db->rollBack();
    echo "Error: " . $e->getMessage();
}
