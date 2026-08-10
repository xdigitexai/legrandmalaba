<?php

if (!defined('BASEPATH')) {
    die('Direct access to the script is not allowed');
}

if ($admin["access"]["services"] != 1):
    header("Location:" . site_url("admin"));
    exit();
endif;

if ($_SESSION["client"]["data"]):
    $data = $_SESSION["client"]["data"];
    foreach ($data as $key => $value) {
        $$key = $value;
    }
    unset($_SESSION["client"]);
endif;

if (!route(2)):
    $page = 1;
elseif (is_numeric(route(2))):
    $page = route(2);
elseif (!is_numeric(route(2))):
    $action = route(2);
endif;

if (empty($action)):

    // --- Load categories ---
    $categoriesStmt = $conn->prepare("SELECT * FROM categories ORDER BY category_name ASC");
    $categoriesStmt->execute();
    $categories = $categoriesStmt->fetchAll(PDO::FETCH_ASSOC);

    // --- Selected category ---
    $selected_category_id   = isset($_GET['category_id']) ? intval($_GET['category_id']) : null;
    $services               = [];
    $selected_category_name = '';

    if ($selected_category_id) {
        // Fetch services in category
        $servicesStmt = $conn->prepare("SELECT * FROM services WHERE category_id = ?");
        $servicesStmt->execute([$selected_category_id]);
        $services = $servicesStmt->fetchAll(PDO::FETCH_ASSOC);

        // Fetch category name
        $catStmt = $conn->prepare("SELECT category_name FROM categories WHERE category_id = ?");
        $catStmt->execute([$selected_category_id]);
        $selected_category_name = $catStmt->fetchColumn();
    }

    // --- Pass data to view ---
    $data = [
        'categories'             => $categories,
        'selected_category_id'   => $selected_category_id,
        'services'               => $services,
        'selected_category_name' => $selected_category_name,
    ];

    // --- Load view ---
    require __DIR__ . "/../views/description_service.php";

endif;