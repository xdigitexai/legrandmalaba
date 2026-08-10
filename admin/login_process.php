<?php
session_start();
require_once __DIR__ . "/../config/database.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: /admin/login.php");
    exit;
}

$username = trim($_POST['username'] ?? $_POST['email'] ?? '');
$password = trim($_POST['password'] ?? '');

if ($username === '' || $password === '') {
    $_SESSION['login_error'] = "Username and password are required.";
    header("Location: /admin/login.php");
    exit;
}

try {
    $stmt = $db->prepare("SELECT * FROM admins WHERE username = ? LIMIT 1");
    $stmt->execute([$username]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);

    // Plain-text comparison (as stored in DB)
    if (!$admin || $admin['password'] !== $password) {
        $_SESSION['login_error'] = "Invalid username or password.";
        header("Location: /admin/login.php");
        exit;
    }

    $access = json_decode($admin['access'] ?? '{}', true);
    if (!($access['admin_access'] ?? false)) {
        $_SESSION['login_error'] = "Access denied.";
        header("Location: /admin/login.php");
        exit;
    }

    // Set both session systems so both standalone and main app work
    $_SESSION['admin_logged_in']        = true;
    $_SESSION['admin_id']               = $admin['admin_id'];
    $_SESSION['admin_username']         = $admin['username'];
    $_SESSION['msmbilisim_adminlogin']  = 1;
    $_SESSION['msmbilisim_adminid']     = $admin['admin_id'];
    $_SESSION['msmbilisim_adminpass']   = $admin['password'];

    // Set cookies for main app cookie-based auth
    setcookie("a_login",    'ok',                time()+(60*60*24*7), '/', null, null, true);
    setcookie("a_id",       $admin['admin_id'],  time()+(60*60*24*7), '/', null, null, true);
    setcookie("a_password", $admin['password'],  time()+(60*60*24*7), '/', null, null, true);

    $db->prepare("UPDATE admins SET login_date=:d, login_ip=:ip WHERE admin_id=:id")
       ->execute(['d' => date('Y-m-d H:i:s'), 'ip' => $_SERVER['REMOTE_ADDR'] ?? '', 'id' => $admin['admin_id']]);

    header("Location: /admin/");
    exit;

} catch (Exception $e) {
    $_SESSION['login_error'] = "Login failed. Please try again.";
    header("Location: /admin/login.php");
    exit;
}
