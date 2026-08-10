<?php
if (!defined('BASEPATH')) {
    die('Direct access to the script is not allowed');
}

include("./admin_security.php");

/* Force admin array safety */
if (empty($admin) || !is_array($admin)) {
    header("Location: " . site_url('admin/login'));
    exit();
}

/* Normalize access value safely */
$hasAccess = isset($admin['access']['admin_access']) 
    ? (int)$admin['access']['admin_access'] 
    : 0;

/* Debug fallback (optional) */
// error_log(print_r($admin, true));

if ($hasAccess !== 1) {
    header("Location: " . site_url('admin/no_access'));
    exit();
}

require admin_view('access');
?>