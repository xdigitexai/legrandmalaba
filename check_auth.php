<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
define('BASEPATH', true);
require 'app/init.php';
echo 'Loaded init.php' . PHP_EOL;
if (isset($user)) {
    echo 'Auth: ' . ($user['auth'] ?? 'N/A') . PHP_EOL;
    echo 'Client ID: ' . ($user['client_id'] ?? 'N/A') . PHP_EOL;
    echo 'Username: ' . ($user['username'] ?? 'N/A') . PHP_EOL;
} else {
    echo 'User variable not set' . PHP_EOL;
}
