<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once "config/database.php";

/* Allow only POST */
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: signup.php");
    exit;
}

/* Collect & sanitize */
$username = trim($_POST['username'] ?? '');
$email    = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$confirm  = $_POST['password_confirm'] ?? '';

/* Basic validation */
if ($username === '' || $email === '' || $password === '' || $confirm === '') {
    $_SESSION['signup_error'] = "All fields are required.";
    header("Location: signup.php");
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['signup_error'] = "Please enter a valid email address.";
    header("Location: signup.php");
    exit;
}

if ($password !== $confirm) {
    $_SESSION['signup_error'] = "Passwords do not match.";
    header("Location: signup.php");
    exit;
}

/* =========================
   CHECK USERNAME EXISTS
========================= */
$stmt = $db->prepare("SELECT id FROM users WHERE username = ? LIMIT 1");
$stmt->execute([$username]);

if ($stmt->fetch()) {
    $_SESSION['signup_error'] =
        "Username already exists in our data. Please login or sign up using a different username.";
    header("Location: signup.php");
    exit;
}

/* =========================
   CHECK EMAIL EXISTS
========================= */
$stmt = $db->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
$stmt->execute([$email]);

if ($stmt->fetch()) {
    $_SESSION['signup_error'] =
        "Email already exists in our data. Please use a different email or login.";
    header("Location: signup.php");
    exit;
}

/* =========================
   CREATE USER
========================= */
$hash = password_hash($password, PASSWORD_DEFAULT);

$stmt = $db->prepare("
    INSERT INTO users (username, email, password, balance)
    VALUES (?, ?, ?, 0)
");

try {
    $stmt->execute([$username, $email, $hash]);
} catch (PDOException $e) {
    $_SESSION['signup_error'] = "Registration failed. Please try again.";
    header("Location: signup.php");
    exit;
}

/* =========================
   AUTO LOGIN
========================= */
$_SESSION['user_id']  = $db->lastInsertId();
$_SESSION['username'] = $username;

/* Redirect */
header("Location: dashboard.php");
exit;