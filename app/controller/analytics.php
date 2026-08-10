<?php
if (!defined('BASEPATH')) {
    die('Direct access to the script is not allowed');
}

$title .= $languageArray["orders.title"];
$smmapi = new SMMApi();

// Define status list and validate input
$status_list = ['all', 'pending', 'inprogress', 'completed', 'partial', 'processing', 'canceled'];
$search_statu = in_array(route(1), $status_list) ? route(1) : 'completed';

// Pagination setup
$page = max(1, (int)(route(2) ?: 1));
$to = 25;

// Initialize parameters and base query
$params = [];
$search = "";
$join = "INNER JOIN services ON services.service_id = orders.service_id";

// Status filter
if ($search_statu !== 'all') {
    $search .= " WHERE order_status = :status";
    $params[':status'] = $search_statu;
}

// Search filter
$searchQuery = isset($_GET['search']) ? urldecode(strip_tags($_GET['search'])) : '';
if (!empty($searchQuery)) {
    $searchClause = "(orders.order_id LIKE :search 
                    OR orders.order_url LIKE :search 
                    OR services.service_id LIKE :search 
                    OR services.service_name LIKE :search)";
    
    $search .= empty($search) ? " WHERE $searchClause" : " AND $searchClause";
    $params[':search'] = "%$searchQuery%";
}

// Count total orders
$countSql = "SELECT COUNT(*) FROM orders $join $search";
$countStmt = $conn->prepare($countSql);
foreach ($params as $key => $value) {
    $countStmt->bindValue($key, $value);
}
$countStmt->execute();
$count = $countStmt->fetchColumn();

// Calculate pagination
$pageCount = max(1, ceil($count / $to));
$page = min($page, $pageCount);
$where = ($page - 1) * $to;

// Fetch orders
// Change SQL to get category from categories table
$sql = "SELECT orders.*, 
               services.service_name,
               categories.category_id 
        FROM orders 
        INNER JOIN services ON services.service_id = orders.service_id
        INNER JOIN categories ON services.category_id = categories.category_id
        $search 
        ORDER BY orders.order_id DESC 
        LIMIT :offset, :limit";

$ordersStmt = $conn->prepare($sql);

// Bind parameters
foreach ($params as $key => $value) {
    $ordersStmt->bindValue($key, $value);
}
$ordersStmt->bindValue(':offset', $where, PDO::PARAM_INT);
$ordersStmt->bindValue(':limit', $to, PDO::PARAM_INT);
$ordersStmt->execute();
$orders = $ordersStmt->fetchAll(PDO::FETCH_ASSOC);

// Process orders
$ordersList = [];
foreach ($orders as $order) {
    $duration = strtotime($order['completion_time']) - strtotime($order['order_create']);
    
    $ordersList[] = [
        'id' => $order['order_id'],
        'date' => date('Y-m-d H:i:s', strtotime($order['order_create'])),
        'charge' => format_amount_string(
            $user["currency_type"],
            from_to(
                get_currencies_array("enabled"),
                $settings["site_base_currency"],
                $user["currency_type"],
                $order['order_charge']
            )
        ),
        'quantity' => $order['order_quantity'],
        'service' => $order['service_name'],
        'category_id' => $order['category_id'], // Added category ID
        'service_id' => $order['service_id'],
        'status' => $languageArray['orders.status.' . $order['order_status']],
        'duration' => format_duration($duration)
    ];
}

// Duration formatting function
function format_duration($seconds) {
    $units = [
        'year' => 31536000,
        'month' => 2592000,
        'week' => 604800,
        'day' => 86400,
        'hour' => 3600,
        'minute' => 60,
        'second' => 1,
    ];

    $result = [];
    foreach ($units as $unit => $value) {
        if ($seconds >= $value) {
            $count = floor($seconds / $value);
            $result[] = "$count $unit" . ($count > 1 ? 's' : '');
            $seconds %= $value;
        }
    }
    return implode(', ', $result);
}

// Build query string for pagination
function build_query_string($params) {
    $filtered = array_filter($params, function($value) {
        return !is_null($value);
    });
    return $filtered ? '?' . http_build_query($filtered) : '';
}

$queryParams = [
    'search' => !empty($searchQuery) ? $searchQuery : null,
];
$query_string = build_query_string($queryParams);

// Pagination data
$paginationArr = [
    'count' => $pageCount,
    'current' => $page,
    'next' => $page < $pageCount ? $page + 1 : $pageCount,
    'previous' => $page > 1 ? $page - 1 : 1,
];

// Pass to Twig
$twig->addGlobal('search', $searchQuery);
$twig->addGlobal('search_statu', $search_statu);
$twig->addGlobal('orders', $ordersList);
$twig->addGlobal('pagination', $paginationArr);
$twig->addGlobal('query_string', $query_string);