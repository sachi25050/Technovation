<?php
/**
 * Serve uploaded files
 * This endpoint serves uploaded images and other files
 * URL format: /api/uploads/{subfolder}/{filename}
 */

// No authentication required for serving uploaded files
// These are public resources

// Get the file path from URL parameters
$subfolder = isset($_GET['_params'][0]) ? $_GET['_params'][0] : (isset($_GET['params'][0]) ? $_GET['params'][0] : '');
$filename = isset($_GET['_params'][1]) ? $_GET['_params'][1] : (isset($_GET['params'][1]) ? $_GET['params'][1] : '');

// Build file path
$filePath = $subfolder . '/' . $filename;

if (empty($subfolder) || empty($filename)) {
    http_response_code(400);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'File path is required']);
    exit;
}

// Security: Prevent directory traversal
$filePath = str_replace('..', '', $filePath);
$filePath = str_replace('//', '/', $filePath);
$filePath = ltrim($filePath, '/');

// Get the full path to the file
$config = require __DIR__ . '/../../config/config.php';
$uploadPath = rtrim($config['upload_path'], '/') . '/';

// Build full path without using realpath first (file might not exist yet for realpath)
$fullPath = $uploadPath . $filePath;

// Check if file exists first
if (!file_exists($fullPath) || !is_file($fullPath)) {
    http_response_code(404);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'File not found', 'path' => $filePath]);
    exit;
}

// Now verify the resolved path is within the upload directory (security check)
$resolvedPath = realpath($fullPath);
$uploadPathReal = realpath($uploadPath);

if (!$resolvedPath || !$uploadPathReal || strpos($resolvedPath, $uploadPathReal) !== 0) {
    http_response_code(403);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Access denied']);
    exit;
}

// Get file mime type
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mimeType = finfo_file($finfo, $resolvedPath);
finfo_close($finfo);

// Set headers for image serving
header('Content-Type: ' . $mimeType);
header('Content-Length: ' . filesize($resolvedPath));
header('Cache-Control: public, max-age=86400'); // Cache for 24 hours
header('Access-Control-Allow-Origin: *');

// Output the file
readfile($resolvedPath);
exit;

