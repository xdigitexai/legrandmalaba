<?php
require_once __DIR__ . "/config/database.php";

/* =========================
   HEADERS (VERY IMPORTANT)
========================= */
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

/* =========================
   READ INPUT
========================= */
$key    = $_REQUEST['key'] ?? '';
$action = $_REQUEST['action'] ?? '';

if ($key === '' || $action === '') {
    echo json_encode([
        "status" => "error",
        "message" => "Missing API key or action"
    ]);
    exit;
}

/* =========================
   VALIDATE API KEY
========================= */
$stmt = $db->prepare("
    SELECT id 
    FROM api_keys 
    WHERE api_key = ? AND active = 1 
    LIMIT 1
");
$stmt->execute([$key]);

if (!$stmt->fetch()) {
    echo json_encode([
        "status" => "error",
        "message" => "Invalid API key"
    ]);
    exit;
}

/* =========================
   ACTION ROUTER
========================= */
switch ($action) {

    case 'services':
        $services = $db->query("
            SELECT 
                id AS service,
                name,
                cost_usd AS rate,
                min,
                max
            FROM services
            WHERE active = 1
        ")->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode($services);
        break;

    case 'balance':
        echo json_encode([
            "status" => "success",
            "balance" => 0
        ]);
        break;

    default:
        echo json_encode([
            "status" => "error",
            "message" => "Invalid action"
        ]);
}