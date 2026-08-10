<?php
/**
 * Cheap Panel - Auto Sync Services (HYBRID MODEL)
 * Run via CRON
 */

require_once __DIR__ . '/../config/database.php';

$logFile = __DIR__ . '/../logs/cron.log';

function logMsg($msg) {
    global $logFile;
    file_put_contents(
        $logFile,
        '[' . date('Y-m-d H:i:s') . '] ' . $msg . PHP_EOL,
        FILE_APPEND
    );
}

// Fetch active providers
$providers = $db->query("
    SELECT * FROM providers 
    WHERE active = 1
")->fetchAll();

if (!$providers) {
    logMsg("No active providers found");
    exit;
}

foreach ($providers as $provider) {

    logMsg("Syncing services for provider: {$provider['name']}");

    try {
        $apiUrl = rtrim($provider['api_url'], '/');
        $requestUrl = $apiUrl . "?key=" . urlencode($provider['api_key']) . "&action=services";

        $response = @file_get_contents($requestUrl);

        if ($response === false) {
            throw new Exception("Unable to reach provider API");
        }

        $services = json_decode($response, true);

        if (!is_array($services)) {
            throw new Exception("Invalid API response");
        }

        $inserted = 0;
        $updated  = 0;

        foreach ($services as $srv) {

            if (!isset($srv['service'])) {
                continue;
            }

            // Check if service exists
            $check = $db->prepare("
                SELECT id FROM services
                WHERE provider_id = ?
                AND provider_service_id = ?
                LIMIT 1
            ");
            $check->execute([$provider['id'], $srv['service']]);

            if ($check->fetch()) {
                // Update meta only (never price or active)
                $update = $db->prepare("
                    UPDATE services SET
                        name = ?,
                        category = ?,
                        min = ?,
                        max = ?
                    WHERE provider_id = ?
                    AND provider_service_id = ?
                ");
                $update->execute([
                    $srv['name'] ?? '',
                    $srv['category'] ?? '',
                    $srv['min'] ?? 0,
                    $srv['max'] ?? 0,
                    $provider['id'],
                    $srv['service']
                ]);
                $updated++;
            } else {
                // Insert new service (inactive)
                $insert = $db->prepare("
                    INSERT INTO services
                    (provider_id, provider_service_id, name, category, min, max, price_per_1000, active)
                    VALUES (?, ?, ?, ?, ?, ?, 0, 0)
                ");
                $insert->execute([
                    $provider['id'],
                    $srv['service'],
                    $srv['name'] ?? '',
                    $srv['category'] ?? '',
                    $srv['min'] ?? 0,
                    $srv['max'] ?? 0
                ]);
                $inserted++;
            }
        }

        logMsg("Provider {$provider['name']}: {$inserted} new, {$updated} updated");

    } catch (Exception $e) {
        logMsg("Provider {$provider['name']} failed: " . $e->getMessage());
    }
}

logMsg("Service sync completed");
