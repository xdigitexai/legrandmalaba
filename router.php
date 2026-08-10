<?php
// Router for PHP built-in server — replaces .htaccess mod_rewrite
$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

// Serve real files (css, js, images, etc.) directly
if ($uri !== '/' && file_exists(__DIR__ . $uri)) {
    return false;
}

// Everything else → index.php
require_once __DIR__ . '/index.php';
