<?php
/**
 * ORDER SYNC WORKER
 * -----------------
 * Syncs order status from provider.
 * CLI only.
 */

if (php_sapi_name() !== 'cli') {
    die("CLI only");
}

require_once __DIR__ . "/../config/database.php";

/* =========================
   LOG FUNCTION
========================= */
$logFile = __DIR__ . '/order_sync.log';

function logMessage($msg)
{
    global $logFile;
    file_put_contents($logFile, "[" . date("Y-m-d H:i:s") . "] " . $msg . PHP_EOL, FILE_APPEND);
}

logMessage("Worker started");

/* =========================
   LOCK (Prevent overlap)
========================= */
$lockFile = __DIR__ . '/order_sync.lock';

if (file_exists($lockFile) && (time() - filemtime($lockFile)) < 50) {
    logMessage("Worker stopped: lock file active");
    exit;
}

file_put_contents($lockFile, time());

/* =========================
   FETCH ACTIVE ORDERS
========================= */
$stmt = $db->prepare("
    SELECT 
        o.id,
        o.status,
        o.provider_order_id,
        s.provider_service_id,
        p.api_url,
        p.api_key
    FROM orders o
    JOIN services s ON o.service_id = s.id
    JOIN providers p ON s.provider_id = p.id
    WHERE o.status IN ('processing','partial')
      AND o.provider_order_id IS NOT NULL
    ORDER BY o.id ASC
    LIMIT 50
");

$stmt->execute();
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

logMessage("Orders fetched: " . count($orders));

if (!$orders) {
    unlink($lockFile);
    logMessage("No active orders found");
    exit;
}

/* =========================
   LOOP ORDERS
========================= */
foreach ($orders as $order) {

    logMessage("Checking Order ID: {$order['id']} | Provider Order: {$order['provider_order_id']}");

    $payload = [
        'key'    => $order['api_key'],
        'action' => 'status',
        'order'  => $order['provider_order_id']
    ];

    logMessage("Sending request to provider: " . $order['api_url']);

    $ch = curl_init($order['api_url']);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => http_build_query($payload),
        CURLOPT_TIMEOUT        => 20
    ]);

    $response = curl_exec($ch);
    $error    = curl_error($ch);
    curl_close($ch);

    if ($error) {
        logMessage("cURL error: " . $error);
        continue;
    }

    if (!$response) {
        logMessage("Empty response from provider");
        continue;
    }

    logMessage("Provider response: " . $response);

    $data = json_decode($response, true);

    if (!is_array($data)) {
        logMessage("Invalid JSON response");
        continue;
    }

    if (isset($data['error'])) {
        logMessage("Provider returned error: " . $data['error']);
        continue;
    }

    $providerStatus = strtolower(trim($data['status'] ?? ''));
    $startCount     = $data['start_count'] ?? null;
    $remains        = $data['remains'] ?? null;

    logMessage("Provider status: " . $providerStatus);

    /* =========================
       MAP STATUS
    ========================= */
    switch ($providerStatus) {

        case 'completed':
        case 'complete':
            $newStatus = 'completed';
            break;

        case 'partial':
            $newStatus = 'partial';
            break;

        case 'canceled':
        case 'cancelled':
        case 'failed':
            $newStatus = 'failed';
            break;

        default:
            $newStatus = 'processing';
            break;
    }

    logMessage("Mapped status: " . $newStatus);

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
            updated_at = NOW(),
            completed_at = IF(? = 'completed', NOW(), completed_at)
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

    logMessage("Order {$order['id']} updated to {$newStatus}");
}

unlink($lockFile);

logMessage("Worker finished");