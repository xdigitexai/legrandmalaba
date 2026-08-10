<?php
header("Content-Type: application/json");

require_once __DIR__ . "/../../config/database.php";

/* ==========================
   GET PARAMS
========================== */
$key    = $_REQUEST['key'] ?? '';
$action = $_REQUEST['action'] ?? '';

if ($key === '' || $action === '') {
    http_response_code(403);
    echo json_encode(["error" => "Missing key or action"]);
    exit;
}

/* ==========================
   VERIFY API KEY
========================== */
$stmt = $db->prepare("
    SELECT id 
    FROM api_keys 
    WHERE api_key = ? AND active = 1
    LIMIT 1
");
$stmt->execute([$key]);
$apiUser = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$apiUser) {
    http_response_code(403);
    echo json_encode(["error" => "Invalid API key"]);
    exit;
}

/* ==========================
   ACTION HANDLER
========================== */
switch ($action) {

    /* ---------- SERVICES ---------- */
    case 'services':

        $services = $db->query("
            SELECT
                id              AS service,
                name,
                cost_usd        AS rate,
                min,
                max,
                category
            FROM services
            WHERE active = 1
            ORDER BY category, name
        ")->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode($services);
        exit;

    /* ---------- BALANCE ---------- */
    case 'balance':

        echo json_encode([
            "balance" => "0.00",
            "currency" => "USD"
        ]);
        exit;

    /* ---------- ORDER (OPTIONAL) ---------- */
    case 'add':
        echo json_encode([
            "order" => rand(100000,999999)
        ]);
        exit;

    default:
        echo json_encode(["error" => "Invalid action"]);
        exit;
}