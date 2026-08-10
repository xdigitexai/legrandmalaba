<?php
chdir('/home/tipmrnhl/panel.mkboost.site');
$_SERVER['HTTP_HOST'] = 'panel.mkboost.site';
$_SERVER['REQUEST_URI'] = '/ajax_data';
$_GET['path'] = 'ajax_data';
$_POST['action'] = 'services_list';
$_POST['category'] = '1';

// Simulate a logged-in user
session_start();
$_SESSION["msmbilisim_userlogin"] = 1;
$_SESSION["msmbilisim_userid"] = 1; // Assuming user ID 1 exists

error_reporting(E_ALL);
ini_set('display_errors', 1);

ob_start();
require 'index.php';
$output = ob_get_clean();

echo "--- User Object ---\n";
global $user;
print_r($user);

echo "\n--- Output ---\n";
echo substr($output, 0, 1000);
