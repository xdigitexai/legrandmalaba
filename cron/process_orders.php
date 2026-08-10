<?php
// Cheap Panel - Order Processing Cron

require_once __DIR__ . '/../config/database.php';
$provider = require __DIR__ . '/../config/provider.php';

// Log helper
function logMsg($msg){
    $logFile = __DIR__ . '/../logs/cron.log';
    file_put_contents(
        $logFile,
        "[" . date("Y-m-d H:i:s") . "] " . $msg . PHP_EOL,
        FILE_APPEND
    );
}

// Fetch PAID orders (limit to avoid overload)
$stmt = $db->prepare("
    SELECT o.*, s.provider_service_id
    FROM orders o
    JOIN services s ON s.id = o.service_id
    WHERE o.status = 'paid'
    ORDER BY o.id ASC
    LIMIT 5
");
$stmt->execute();
$orders = $stmt->fetchAll();

if (!$orders) {
    logMsg("No paid orders to process");
    exit;
}

foreach ($orders as $order) {

    try {
        logMsg("Processing order {$order['order_id']}");

        // Build provider request
        $payload = [
            'key'     => $provider['api_key'],
            'action'  => 'add',
            'service' => $order['provider_service_id'],
            'link'    => $order['link'],
            'quantity'=> $order['quantity']
        ];

        // Send to provider
        $ch = curl_init($provider['api_url']);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query($payload),
            CURLOPT_TIMEOUT => 30
        ]);

        $response = curl_exec($ch);
        curl_close($ch);

        $result = json_decode($response, true);

        if (!isset($result['order'])) {
            throw new Exception("Provider rejected order");
        }

        // Update order → PROCESSING
        $update = $db->prepare("
            UPDATE orders
            SET status = 'processing',
                provider_order_id = ?
            WHERE id = ?
        ");
        $update->execute([$result['order'], $order['id']]);

        logMsg("Order {$order['order_id']} sent to provider (ID {$result['order']})");

    } catch (Exception $e) {

        // Mark as FAILED
        $fail = $db->prepare("
            UPDATE orders
            SET status = 'failed'
            WHERE id = ?
        ");
        $fail->execute([$order['id']]);

        logMsg("Order {$order['order_id']} failed: " . $e->getMessage());
    }
}
