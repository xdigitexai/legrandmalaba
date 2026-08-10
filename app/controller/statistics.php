<?php
if (!defined('BASEPATH')) {
    die('Direct access to the script is not allowed');
}
$smmapi = new SMMApi();

if ($_SESSION["msmbilisim_userlogin"] != 1 || $user["client_type"] == 1) {
    Header("Location:".site_url('logout'));
}

if ($settings["email_confirmation"] == 1 && $user["email_type"] == 1) {
    Header("Location:".site_url('confirm_email'));
}

// Define the status list for filtering
$status_list = [
    'all',
    'pending',
    'inprogress',
    'completed',
    'partial',
    'processing',
    'canceled',
];

// Default to 'completed' if no status is provided
$search_statu = route(1) ?: 'completed';

if (!in_array($search_statu, $status_list)) {
    $search_statu = 'completed';
}

// Pagination setup
$page = route(2) ?: 1; // Get the current page from the URL
$to = 12; // Number of items per page

// Adjust search query to fetch completed orders for all users
$search = $search_statu !== 'all' ? "WHERE order_status='" . $search_statu . "'" : '';

// Handle search parameters
if (!empty(urldecode(strip_tags($_GET['search'])))) {
    $search .= " AND (order_url LIKE '%" . urldecode(strip_tags($_GET['search'])) . "%' || order_id LIKE '%" . urldecode(strip_tags($_GET['search'])) . "%')";
}

// Handle date filter
if (!empty($_GET['date'])) {
    $date = htmlspecialchars(strip_tags($_GET['date'])); // Sanitize the date input
    $search .= " AND DATE(order_create) = '$date'"; // Add date condition
}

// Count total completed orders for pagination
$countQuery = "SELECT COUNT(*) FROM orders $search";
$countStmt = $conn->prepare($countQuery);
$countStmt->execute();
$count = $countStmt->fetchColumn(); // Get the total count of orders
$pageCount = ceil($count / $to); // Calculate total pages

// Ensure the current page is within bounds
$page = max(1, min($pageCount, $page));
$offset = ($page - 1) * $to; // Calculate the offset for the SQL query

// Fetch completed orders for all users
$ordersQuery = "SELECT orders.*, services.*, categories.category_id 
                FROM orders 
                INNER JOIN services ON services.service_id = orders.service_id 
                INNER JOIN categories ON services.category_id = categories.category_id
                $search 
                ORDER BY orders.order_id DESC 
                LIMIT :offset, :limit";

$ordersStmt = $conn->prepare($ordersQuery);
$ordersStmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$ordersStmt->bindValue(':limit', $to, PDO::PARAM_INT);
$ordersStmt->execute();
$orders = $ordersStmt->fetchAll(PDO::FETCH_ASSOC);

$ordersList = [];

// Process each order
foreach ($orders as $order) {
    $o = []; // Initialize order array
    $o['completion_time'] = date("Y-m-d H:i:s", strtotime($order['completion_time']));
    
    // Calculate how long it took to complete the order
    $order_created_time = strtotime($order['order_create']); // Fetch order creation time
    $completion_time = strtotime($o['completion_time']); // Fetch completion time
    $duration = $completion_time - $order_created_time; // Calculate duration in seconds

    // Format the duration into a human-readable format
    $o['duration'] = format_duration($duration);
    // Populate order details
    $o['id'] = $order['order_id'];
    $o['date'] = date('Y-m-d H:i:s', strtotime($order['order_create']));
    $o['link'] = $order['order_url'];
    $o['charge'] = format_amount_string($user["currency_type"], from_to(get_currencies_array("enabled"), $settings["site_base_currency"], $user["currency_type"], $order['order_charge']));
    $o['start_count'] = $order['order_start'];
    $o['quantity'] = $order['order_quantity'];
    $o['service'] = $order['service_name'];
    $o['service_id'] = $order['service_id']; // Service ID
    $o['category_id'] = $order['category_id']; // Category ID
    $o['status'] = $languageArray['orders.status.' . $order['order_status']];
    
    // Add the order to the list
    array_push($ordersList, $o);
}

// Function to format duration into a human-readable format
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
            $seconds %= $value; // Get the remaining seconds
        }
    }

    return implode(', ', $result);
}

// Pass the necessary data to the view
$data['orders'] = $ordersList;
$data['page'] = $page;
$data['pageCount'] = $pageCount;
$data['query_string'] = build_query_string([
    'search' => $_GET['search'] ?? null,
    'date' => $_GET['date'] ?? null,
    'page' => $page // Include the current page in the query string
]);

function build_query_string($params) {
    $query = http_build_query($params);
    return $query ? '?' . $query : '';
}

// After fetching orders and calculating pagination
$paginationArr = [
    'count' => $pageCount,
    'current' => $page,
    'next' => $page < $pageCount ? $page + 1 : $pageCount,
    'previous' => $page > 1 ? $page - 1 : 1,
];

$data['pagination'] = $paginationArr; // Pass to view