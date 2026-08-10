<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

$logFile = __DIR__ . "/retry_orders.log";

function logMsg($msg) {
    global $logFile;
    file_put_contents($logFile, "[".date("Y-m-d H:i:s")."] ".$msg.PHP_EOL, FILE_APPEND);
}

logMsg("Retry cron started");

require_once __DIR__ . "/../config/database.php";

// Get API details from service_api table (id=3 for cdvrsbooster)
$apiStmt = $db->prepare("SELECT api_url, api_key FROM service_api WHERE id = 3");
$apiStmt->execute();
$api = $apiStmt->fetch(PDO::FETCH_ASSOC);

if (!$api) {
    logMsg("No API configuration found");
    exit;
}

$api_url = $api['api_url'];
$api_key = $api['api_key'];

logMsg("Using API: {$api_url}");

// Find stuck orders (api_orderid=0, pending)
$stmt = $db->prepare("
    SELECT o.order_id, o.service_id, o.order_url, o.order_quantity, o.order_charge,
           s.api_service
    FROM orders o
    JOIN services s ON o.service_id = s.service_id
    WHERE o.api_orderid = 0
    AND o.order_status = 'pending'
    AND o.order_url IS NOT NULL
    AND o.order_url != ''
    LIMIT 20
");
$stmt->execute();
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (!$orders) {
    logMsg("No stuck orders found");
    exit;
}

logMsg("Found " . count($orders) . " stuck orders to retry");

$submitted = 0;
$failed = 0;

foreach ($orders as $order) {
    $api_service = $order['api_service'];
    $link = $order['order_url'];
    $quantity = (int)$order['order_quantity'];
    $order_id = $order['order_id'];
    
    logMsg("Processing order #{$order_id}: service={$api_service}, link={$link}, qty={$quantity}");
    
    // Submit to API
    $postData = http_build_query([
        'key' => $api_key,
        'action' => 'add',
        'service' => $api_service,
        'link' => $link,
        'quantity' => $quantity
    ]);
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $api_url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    $result = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);
    
    if (!$result) {
        logMsg("Order #{$order_id}: CURL error - {$curlError}");
        $failed++;
        continue;
    }
    
    $response = json_decode($result, true);
    
    if (!$response) {
        logMsg("Order #{$order_id}: Invalid JSON response - " . substr($result, 0, 200));
        $failed++;
        continue;
    }
    
    // Check for API order ID
    $api_orderid = 0;
    if (isset($response['order'])) {
        $api_orderid = (int)$response['order'];
    } elseif (isset($response['id'])) {
        $api_orderid = (int)$response['id'];
    }
    
    if ($api_orderid > 0) {
        // Update order with API order ID
        $update = $db->prepare("UPDATE orders SET api_orderid = :api_id, order_status = 'processing', last_check = NOW() WHERE order_id = :oid");
        $update->execute(['api_id' => $api_orderid, 'oid' => $order_id]);
        logMsg("Order #{$order_id}: SUBMITTED - API order ID: {$api_orderid}");
        $submitted++;
    } else {
        $errorMsg = isset($response['error']) ? $response['error'] : json_encode($response);
        logMsg("Order #{$order_id}: API ERROR - {$errorMsg}");
        $failed++;
    }
}

logMsg("Retry complete: {$submitted} submitted, {$failed} failed");
