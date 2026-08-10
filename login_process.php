<?php
session_start();
require_once "config/database.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: login.php");
    exit;
}

$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';
$remember = isset($_POST['remember']);

if ($username === '' || $password === '') {
    header("Location: login.php?error=empty");
    exit;
}

$stmt = $db->prepare("SELECT * FROM clients WHERE username=:username && password=:password LIMIT 1");
$stmt->execute(["username" => $username, "password" => md5($password)]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    header("Location: login.php?error=invalid");
    exit;
}

if ($user['client_type'] == 1) {
    header("Location: login.php?error=invalid");
    exit;
}

$_SESSION["msmbilisim_userlogin"] = 1;
$_SESSION["msmbilisim_userid"] = $user["client_id"];
$_SESSION["msmbilisim_userpass"] = md5($password);

$access = json_decode($user["access"], true);
if ($access["admin_access"]) {
    $_SESSION["msmbilisim_adminlogin"] = 1;
}

if ($remember) {
    setcookie("u_id", $user["client_id"], strtotime('+7 days'), '/', null, null, true);
    setcookie("u_password", md5($password), strtotime('+7 days'), '/', null, null, true);
}

header("Location: index.php");
exit;
