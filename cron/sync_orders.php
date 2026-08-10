<?php

/* =========================
   ERROR + LOG SETTINGS
========================= */

ini_set('display_errors', 1);
error_reporting(E_ALL);

$logFile = "/home/tipmrnhl/cdvrsbooster.site/cron/status_cron.log";

function logMsg($msg){
    global $logFile;
    file_put_contents($logFile,"[".date("Y-m-d H:i:s")."] ".$msg.PHP_EOL, FILE_APPEND);
}

logMsg("Cron started");


/* =========================
   LOAD DATABASE
========================= */

require_once __DIR__ . "/../config/database.php";


/* =========================
   FETCH ACTIVE ORDERS
========================= */

$stmt = $db->prepare("
SELECT 
    o.order_id,
    o.provider_order_id,
    o.status,
    p.api_url,
    p.api_key
FROM orders o
JOIN services s ON o.service_id = s.id
JOIN providers p ON s.provider_id = p.id
WHERE o.status IN ('processing','partial')
AND o.provider_order_id IS NOT NULL
LIMIT 50
");

$stmt->execute();
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

if(!$orders){
    logMsg("No processing orders found");
    exit;
}

logMsg("Found ".count($orders)." orders");


/* =========================
   LOOP ORDERS
========================= */

foreach($orders as $order){

    logMsg("Checking order ".$order['id']." provider order ".$order['provider_order_id']);

    $payload = [
        'key'    => $order['api_key'],
        'action' => 'status',
        'order'  => $order['provider_order_id']
    ];

    $ch = curl_init($order['api_url']);

    curl_setopt_array($ch,[
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => http_build_query($payload),
        CURLOPT_TIMEOUT => 20
    ]);

    $response = curl_exec($ch);

    if(curl_errno($ch)){
        logMsg("cURL error: ".curl_error($ch));
    }

    curl_close($ch);

    if(!$response){
        logMsg("Empty provider response");
        continue;
    }

    logMsg("Provider response: ".$response);

    $data = json_decode($response,true);

    if(!is_array($data)){
        logMsg("Invalid JSON response");
        continue;
    }


    /* =========================
       HANDLE PROVIDER ERROR
    ========================= */

    if(isset($data['error'])){

        logMsg("Provider error for order ".$order['id']." : ".$data['error']);

        // Old service or deleted order → mark completed
        $stmt = $db->prepare("
            UPDATE orders SET
                status = 'completed',
                provider_response = ?,
                last_synced_at = NOW(),
                completed_at = NOW()
            WHERE id = ?
        ");

        $stmt->execute([
            $response,
            $order['id']
        ]);

        logMsg("Order ".$order['id']." marked COMPLETED (service deleted / order not found)");

        continue;
    }


    /* =========================
       EXTRACT STATUS DATA
    ========================= */

    $providerStatus = strtolower(trim($data['status'] ?? ''));

    $startCount = $data['start_count'] ?? null;
    $remains    = $data['remains'] ?? null;


    /* =========================
       MAP PROVIDER STATUS
    ========================= */

    $newStatus = 'processing';

    if(in_array($providerStatus,['completed','complete'])){
        $newStatus = 'completed';
    }
    elseif($providerStatus == 'partial'){
        $newStatus = 'partial';
    }
    elseif(in_array($providerStatus,['cancelled','canceled','failed'])){
        $newStatus = 'failed';
    }
    elseif(in_array($providerStatus,['pending','processing','in progress'])){
        $newStatus = 'processing';
    }

    logMsg("Mapped status: ".$providerStatus." -> ".$newStatus);


    /* =========================
       UPDATE ORDER
    ========================= */

    $stmt = $db->prepare("
        UPDATE orders SET
            start_count = COALESCE(?, start_count),
            remains = COALESCE(?, remains),
            status = ?,
            provider_response = ?,
            last_synced_at = NOW(),
            completed_at = IF(?='completed',NOW(),completed_at)
        WHERE id = ?
    ");

    $stmt->execute([
        $startCount,
        $remains,
        $newStatus,
        $response,
        $newStatus,
        $order['id']
    ]);

    logMsg("Order ".$order['id']." updated to ".$newStatus);
}

logMsg("Cron finished");