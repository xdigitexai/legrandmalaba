<?php
chdir('/home/tipmrnhl/panel.mkboost.site');

// Mock session and environment
$_SERVER['HTTP_HOST'] = 'panel.mkboost.site';
$_SERVER['REQUEST_URI'] = '/ajax_data';
$_GET['path'] = 'ajax_data';
$_POST['action'] = 'services_list';
$_POST['category'] = '1';

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// We will capture output to see what it prints
ob_start();

// Include the main entry point which will load everything
require 'index.php';

$output = ob_get_clean();

echo "--- Output ---\n";
echo substr($output, 0, 1000);
if(strlen($output) > 1000) echo "\n... (truncated)";
echo "\n";

echo "--- Session ---\n";
print_r($_SESSION);
