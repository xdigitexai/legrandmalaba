<?php
if (!defined('BASEPATH')) {
    die('Direct access to the script is not allowed');
}

function fetchData($db, $query, $params = []) {
    if (!$db) return [];
    try {
        $stmt = $db->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        return [];
    }
}

function fetchSingleValue($db, $query, $params = []) {
    if (!$db) return 0;
    try {
        $stmt = $db->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchColumn();
    } catch (PDOException $e) {
        return 0;
    }
}

$total_profit = fetchSingleValue($conn, "SELECT SUM(order_profit) FROM orders");
$profit_today = fetchSingleValue($conn, "SELECT SUM(order_profit) FROM orders WHERE DATE(order_create) = CURDATE()");
$profit_month = fetchSingleValue($conn, "SELECT SUM(order_profit) FROM orders WHERE MONTH(order_create) = MONTH(CURDATE()) AND YEAR(order_create) = YEAR(CURDATE())");
$total_payments_value = fetchSingleValue($conn, "SELECT SUM(payment_amount) FROM payments WHERE payment_status = 3");
$total_users = fetchSingleValue($conn, "SELECT COUNT(client_id) FROM clients");
$new_users_today = fetchSingleValue($conn, "SELECT COUNT(client_id) FROM clients WHERE DATE(register_date) = CURDATE()");
$new_users_week = fetchSingleValue($conn, "SELECT COUNT(client_id) FROM clients WHERE register_date >= NOW() - INTERVAL 7 DAY");
$banned_users = fetchSingleValue($conn, "SELECT COUNT(client_id) FROM clients WHERE client_type = 'banned'");
$total_telegram_users = fetchSingleValue($conn, "
    SELECT COUNT(client_id) 
    FROM clients 
    WHERE telegram_id IS NOT NULL AND telegram_id != ''
");

$total_orders      = fetchSingleValue($conn, "SELECT COUNT(order_id) FROM orders");
$completed_orders  = fetchSingleValue($conn, "SELECT COUNT(order_id) FROM orders WHERE order_status = 'completed'");
$pending_orders    = fetchSingleValue($conn, "SELECT COUNT(order_id) FROM orders WHERE order_status = 'pending'");
$inprogress_orders = fetchSingleValue($conn, "SELECT COUNT(order_id) FROM orders WHERE order_status = 'inprogress'");
$processing_orders = fetchSingleValue($conn, "SELECT COUNT(order_id) FROM orders WHERE order_status = 'processing'");
$partial_orders    = fetchSingleValue($conn, "SELECT COUNT(order_id) FROM orders WHERE order_status = 'partial'");
$failed_orders     = fetchSingleValue($conn, "SELECT COUNT(order_id) FROM orders WHERE order_status IN ('fail','failed')");
$cancelled_orders  = fetchSingleValue($conn, "SELECT COUNT(order_id) FROM orders WHERE order_status IN ('cancelled','canceled')");
$active_services   = fetchSingleValue($conn, "SELECT COUNT(service_id) FROM services WHERE service_type = 2");
$total_providers   = fetchSingleValue($conn, "SELECT COUNT(api_id) FROM apis");


$recent_orders = fetchData($conn, "
    SELECT o.order_charge, o.order_status, o.order_create, c.username, s.service_name 
    FROM orders AS o 
    LEFT JOIN clients AS c ON c.client_id = o.client_id 
    LEFT JOIN services AS s ON s.service_id = o.service_id 
    ORDER BY o.order_id DESC 
    LIMIT 5
");

$recent_payments = fetchData($conn, "
    SELECT p.payment_amount, p.payment_create_date, c.username, pm.methodVisibleName 
    FROM payments AS p 
    LEFT JOIN clients AS c ON c.client_id = p.client_id 
    LEFT JOIN payment_methods AS pm ON pm.methodId = p.payment_method 
    WHERE p.payment_status = 3 
    ORDER BY p.payment_id DESC 
    LIMIT 5
");

$last_pending_order = fetchData($conn, "
    SELECT o.order_id, o.order_charge, o.order_create, 
           c.username, s.service_name
    FROM orders AS o
    INNER JOIN clients AS c ON c.client_id = o.client_id
    INNER JOIN services AS s ON s.service_id = o.service_id
    WHERE o.order_status = 'Pending'
    ORDER BY o.order_id DESC
    LIMIT 1
");

$last_completed_order = fetchData($conn, "
    SELECT o.order_id,
           o.order_charge,
           o.order_finish,
           c.username,
           s.service_name
    FROM orders AS o
    INNER JOIN clients AS c ON c.client_id = o.client_id
    INNER JOIN services AS s ON s.service_id = o.service_id
    WHERE o.order_status = 'Completed'
    ORDER BY o.order_id DESC
    LIMIT 1
");

require admin_view('analytics');
?>