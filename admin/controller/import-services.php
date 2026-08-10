<?php
if (!defined('BASEPATH')) {
    die('Direct access to the script is not allowed');
}

$FASTERSMM_URL = 'https://fastersmm.com/api/v2';
$FASTERSMM_KEY = 'bd2abc1898a9959829e64c7422233f18';

/* ── Ensure FasterSMM provider exists ── */
try {
    $chk = $conn->prepare("SELECT id FROM service_api WHERE api_url LIKE '%fastersmm%' LIMIT 1");
    $chk->execute();
    if (!$chk->fetch()) {
        $conn->prepare("INSERT INTO service_api (api_name,api_url,api_key,api_type,status,api_sync) VALUES ('FasterSMM',?,?,1,1,1)")
             ->execute([$FASTERSMM_URL, $FASTERSMM_KEY]);
    }
} catch (Exception $e) {}

/* ────────────────────────────────────────────────────────────
   AJAX ONE-CLICK IMPORT  (returns JSON)
   ─────────────────────────────────────────────────────────── */
if (isset($_POST['action']) && $_POST['action'] === 'import_all') {
    /* Clean any buffered output (PHP warnings from admin controller) */
    while (ob_get_level()) ob_end_clean();
    error_reporting(0);
    @ini_set('display_errors', '0');
    set_time_limit(180);
    ignore_user_abort(true);
    header('Content-Type: application/json');

    $markup  = max(0, min(500, (float)($_POST['markup'] ?? 30)));
    $doClear = !empty($_POST['clear_first']);
    $log     = [];
    $deleted = 0;
    $inserted = 0;
    $skipped  = 0;

    try {
        /* Disable strict mode so ENUM/int columns use their schema defaults */
        $conn->exec("SET SESSION sql_mode = ''");

        /* Step 1 — optional clear */
        if ($doClear) {
            $deleted = (int)$conn->query("SELECT COUNT(*) FROM services")->fetchColumn();
            $conn->exec("DELETE FROM services");
            $conn->exec("DELETE FROM categories WHERE category_id > 0");
            $log[] = "Cleared $deleted existing services and all categories.";
        }

        /* Step 2 — fetch from FasterSMM API */
        $ch = curl_init($FASTERSMM_URL);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_TIMEOUT        => 60,
            CURLOPT_POSTFIELDS     => http_build_query(['key' => $FASTERSMM_KEY, 'action' => 'services']),
        ]);
        $raw     = curl_exec($ch);
        $curlErr = curl_error($ch);
        curl_close($ch);

        if ($curlErr) {
            echo json_encode(['ok' => false, 'error' => "Connection error: $curlErr"]);
            exit;
        }

        $apiServices = json_decode($raw, true);
        if (!is_array($apiServices)) {
            echo json_encode(['ok' => false, 'error' => 'Invalid API response: ' . htmlspecialchars(substr($raw, 0, 200))]);
            exit;
        }

        $log[] = 'Fetched ' . count($apiServices) . ' services from FasterSMM.';

        /* Get provider ID */
        $provRow = $conn->prepare("SELECT id FROM service_api WHERE api_url LIKE '%fastersmm%' LIMIT 1");
        $provRow->execute();
        $providerId = (int)($provRow->fetchColumn() ?: 0);

        $factor   = 1 + ($markup / 100);
        $catCache = [];

        /* Step 3 — insert categories + services in one transaction */
        $conn->beginTransaction();
        foreach ($apiServices as $svc) {
            $svcId   = (int)($svc['service'] ?? 0);
            $name    = substr(trim($svc['name'] ?? ''), 0, 250);
            $type    = strtolower(trim($svc['type'] ?? 'Default'));
            $minQ    = max(1, (int)($svc['min'] ?? 1));
            $maxQ    = max($minQ, (int)($svc['max'] ?? 99999));
            $rate    = (float)($svc['rate'] ?? 0);
            $catName = trim($svc['category'] ?? 'General');

            if (!$svcId || !$name) { $skipped++; continue; }

            /* Category — in-memory cache avoids repeated DB hits */
            if (!isset($catCache[$catName])) {
                $cStmt = $conn->prepare("SELECT category_id FROM categories WHERE category_name=? AND category_deleted='0' LIMIT 1");
                $cStmt->execute([$catName]);
                $catId = $cStmt->fetchColumn();
                if (!$catId) {
                    $conn->prepare("INSERT INTO categories (category_name,category_icon,category_line,category_deleted) VALUES (?,?,999,'0')")->execute([$catName, '']);
                    $catId = $conn->lastInsertId();
                }
                $catCache[$catName] = $catId;
            }
            $catId = $catCache[$catName];

            $sellPrice = round($rate * $factor, 4);
            $apiDetail = json_encode([
                'type'     => 'api',
                'provider' => $providerId,
                'api_id'   => $svcId,
                'rate'     => $rate,
                'currency' => 'USD',
            ]);

            /* Skip duplicates only when NOT clearing first */
            if (!$doClear) {
                $exists = $conn->prepare("SELECT service_id FROM services WHERE service_name=? AND service_deleted='0' LIMIT 1");
                $exists->execute([$name]);
                if ($exists->fetchColumn()) { $skipped++; continue; }
            }

            $conn->prepare("INSERT INTO services
                (service_name,category_id,service_price,service_min,service_max,
                 service_type,api_detail,service_deleted,service_line)
                VALUES (?,?,?,?,?,?,?,0,999)")
                 ->execute([$name, $catId, $sellPrice, $minQ, $maxQ, $type, $apiDetail]);
            $inserted++;
        }
        $conn->commit();

        $catCount = count($catCache);
        $log[]    = "Created/reused $catCount categories.";
        $log[]    = "Imported $inserted services with {$markup}% markup.";
        if ($skipped) $log[] = "Skipped $skipped (duplicates or invalid entries).";

        echo json_encode([
            'ok'       => true,
            'inserted' => $inserted,
            'skipped'  => $skipped,
            'cats'     => $catCount,
            'markup'   => $markup,
            'log'      => $log,
        ]);
    } catch (Exception $e) {
        if ($conn->inTransaction()) $conn->rollBack();
        echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
    }
    exit;
}

/* ── Page load stats ── */
$totalServices   = (int)$conn->query("SELECT COUNT(*) FROM services WHERE service_deleted='0'")->fetchColumn();
$totalCategories = (int)$conn->query("SELECT COUNT(*) FROM categories WHERE category_deleted='0'")->fetchColumn();
$providers       = $conn->query("SELECT id, api_name, api_url, status FROM service_api ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);

require admin_view('import-services');
