<?php
require_once __DIR__ . "/../../config/database.php";

/* =========================
   API HEADERS
========================= */
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

/* =========================
   INPUT
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

$apiUser = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$apiUser) {
    echo json_encode([
        "status" => "error",
        "message" => "Invalid API key"
    ]);
    exit;
}

/* =========================
   ROUTER
========================= */
switch ($action) {

    /* =========================
       SERVICES
    ========================= */
    case 'services':

        $services = $db->query("
            SELECT 
                id              AS service,
                name,
                cost_usd        AS rate,
                min,
                max,
                category,
                platform
            FROM services
            WHERE active = 1
            ORDER BY platform, category, name
        ")->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode($services);
        break;

    /* =========================
       ADD ORDER
    ========================= */
    case 'add':

        $service  = (int)($_POST['service'] ?? 0);
        $link     = trim($_POST['link'] ?? '');
        $quantity = (int)($_POST['quantity'] ?? 0);

        if ($service <= 0 || $link === '' || $quantity <= 0) {
            echo json_encode([
                "status" => "error",
                "message" => "Missing required parameters"
            ]);
            exit;
        }

        /* Fetch service */
        $stmt = $db->prepare("
            SELECT cost_usd, min, max 
            FROM services 
            WHERE id = ? AND active = 1 
            LIMIT 1
        ");
        $stmt->execute([$service]);
        $srv = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$srv) {
            echo json_encode([
                "status" => "error",
                "message" => "Service not found"
            ]);
            exit;
        }

        if ($quantity < $srv['min'] || $quantity > $srv['max']) {
            echo json_encode([
                "status" => "error",
                "message" => "Quantity out of range"
            ]);
            exit;
        }

        $price = ($quantity / 1000) * $srv['cost_usd'];

        $stmt = $db->prepare("
            INSERT INTO orders
            (service_id, link, quantity, price, status, created_at)
            VALUES (?, ?, ?, ?, 'pending', NOW())
        ");
        $stmt->execute([$service, $link, $quantity, $price]);

        echo json_encode([
            "status"   => "success",
            "order"    => $db->lastInsertId()
        ]);
        break;

    /* =========================
       ORDER STATUS
    ========================= */
    case 'status':

        $orderId = (int)($_REQUEST['order'] ?? 0);

        if ($orderId <= 0) {
            echo json_encode([
                "status" => "error",
                "message" => "Invalid order ID"
            ]);
            exit;
        }

        $stmt = $db->prepare("
            SELECT status 
            FROM orders 
            WHERE id = ?
            LIMIT 1
        ");
        $stmt->execute([$orderId]);

        $order = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$order) {
            echo json_encode([
                "status" => "error",
                "message" => "Order not found"
            ]);
            exit;
        }

        echo json_encode([
            "status" => "success",
            "order_status" => $order['status']
        ]);
        break;

    /* =========================
       BALANCE
    ========================= */
    case 'balance':
        echo json_encode([
            "status" => "success",
            "balance" => 0
        ]);
        break;

    /* =========================
       DEFAULT
    ========================= */
    default:
        echo json_encode([
            "status" => "error",
            "message" => "Invalid action"
        ]);
}