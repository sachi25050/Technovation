<?php
/**
 * Serve uploaded files
 * This endpoint serves uploaded images and other files
 */

// Get the file path from the request
$filePath = isset($_GET['path']) ? $_GET['path'] : '';

if (empty($filePath)) {
    http_response_code(400);
    die('File path is required');
}

// Security: Prevent directory traversal
$filePath = str_replace('..', '', $filePath);
$filePath = ltrim($filePath, '/');

// Get the full path to the file
$config = require __DIR__ . '/../../config/config.php';
$uploadPath = $config['upload_path'];
$fullPath = realpath($uploadPath . $filePath);

// Verify the file is within the upload directory
$uploadPathReal = realpath($uploadPath);
if (!$fullPath || strpos($fullPath, $uploadPathReal) !== 0) {
    http_response_code(403);
    die('Access denied');
}

// Check if file exists
if (!file_exists($fullPath) || !is_file($fullPath)) {
    http_response_code(404);
    die('File not found');
}

// Get file mime type
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mimeType = finfo_file($finfo, $fullPath);
finfo_close($finfo);

// Set headers
header('Content-Type: ' . $mimeType);
header('Content-Length: ' . filesize($fullPath));
header('Cache-Control: public, max-age=3600');
header('Access-Control-Allow-Origin: *');

// Output the file
readfile($fullPath);
exit;

