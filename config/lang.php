<?php
session_start();

/**
 * Detect browser language once
 */
if (!isset($_SESSION['lang'])) {

    $browserLang = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? 'en', 0, 2);

    if (in_array($browserLang, ['fr', 'en'])) {
        $_SESSION['lang'] = $browserLang;
    } else {
        $_SESSION['lang'] = 'en';
    }
}

/**
 * Manual override via ?lang=
 */
if (isset($_GET['lang']) && in_array($_GET['lang'], ['en','fr'])) {
    $_SESSION['lang'] = $_GET['lang'];
}

$lang = $_SESSION['lang'];

$L = require __DIR__ . "/../lang/$lang.php";