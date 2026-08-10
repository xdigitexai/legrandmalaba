<?php
define("ADMIN", true);

require_once $_SERVER['DOCUMENT_ROOT'] . '/app/init.php';

if (!isset($_SESSION["msmbilisim_adminlogin"]) || $_SESSION["msmbilisim_adminlogin"] !== 1) {
    header("Location: /login.php");
    exit;
}

$pageTitle = "GCash Payments Management";

include __DIR__ . '/../views/OrdersController.php';