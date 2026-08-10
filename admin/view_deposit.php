<?php
session_start();

/* 🔐 Admin protection */
if (!isset($_SESSION['admin_logged_in'])) {
    http_response_code(403);
    exit('Unauthorized');
}

/* ======================
   VALIDATE FILE PARAM
====================== */
if (empty($_GET['file'])) {
    http_response_code(404);
    exit('File not specified');
}

/* Clean filename (security) */
$file = basename($_GET['file']); // removes ../ etc

$baseDir = realpath(__DIR__ . '/../uploads/deposits/');
$filePath = $baseDir . '/' . $file;

/* ======================
   CHECK FILE EXISTS
====================== */
if (!$baseDir || !file_exists($filePath)) {
    http_response_code(404);
    exit('File not found');
}

/* ======================
   DETECT MIME TYPE
====================== */
$mime = mime_content_type($filePath);

/* ======================
   OUTPUT FILE
====================== */
header('Content-Type: ' . $mime);
header('Content-Length: ' . filesize($filePath));
header('Content-Disposition: inline; filename="' . $file . '"');
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Pragma: no-cache');

readfile($filePath);
exit;