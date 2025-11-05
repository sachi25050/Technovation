<?php
/**
 * File Upload Handler
 */

class FileUpload {
    
    /**
     * Upload image file
     */
    public static function uploadImage($file, $subfolder = '') {
        $config = require __DIR__ . '/../config/config.php';
        
        // Validate file
        if (!isset($file['error']) || is_array($file['error'])) {
            throw new Exception('Invalid file upload.');
        }
        
        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new Exception('File upload error: ' . $file['error']);
        }
        
        // Check file size
        if ($file['size'] > $config['max_file_size']) {
            throw new Exception('File size exceeds maximum allowed size.');
        }
        
        // Check file type
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($file['tmp_name']);
        
        if (!in_array($mimeType, $config['allowed_image_types'])) {
            throw new Exception('Invalid file type. Only JPG and PNG are allowed.');
        }
        
        // Generate unique filename
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = uniqid() . '_' . time() . '.' . $extension;
        
        // Create upload directory if it doesn't exist
        $uploadDir = $config['upload_path'] . $subfolder;
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        // Move uploaded file
        $destination = $uploadDir . '/' . $filename;
        if (!move_uploaded_file($file['tmp_name'], $destination)) {
            throw new Exception('Failed to move uploaded file.');
        }
        
        // Return file information
        return [
            'filename' => $filename,
            'path' => $destination,
            'url' => $config['upload_url'] . $subfolder . '/' . $filename,
            'size' => $file['size'],
            'mime_type' => $mimeType
        ];
    }
    
    /**
     * Delete file
     */
    public static function deleteFile($filename, $subfolder = '') {
        $config = require __DIR__ . '/../config/config.php';
        $filePath = $config['upload_path'] . $subfolder . '/' . $filename;
        
        if (file_exists($filePath)) {
            return unlink($filePath);
        }
        
        return false;
    }
}

