<?php
/**
 * CORS Handler
 */

class CORS {
    
    public static function handle() {
        $config = require __DIR__ . '/../config/config.php';
        $corsConfig = $config['cors'];
        
        // Allow credentials
        header('Access-Control-Allow-Credentials: true');
        
        // Set allowed origins
        $origin = $_SERVER['HTTP_ORIGIN'] ?? '*';
        if (in_array('*', $corsConfig['allowed_origins']) || in_array($origin, $corsConfig['allowed_origins'])) {
            header("Access-Control-Allow-Origin: $origin");
        }
        
        // Handle preflight requests
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            header('Access-Control-Allow-Methods: ' . implode(', ', $corsConfig['allowed_methods']));
            header('Access-Control-Allow-Headers: ' . implode(', ', $corsConfig['allowed_headers']));
            header('Access-Control-Max-Age: ' . $corsConfig['max_age']);
            http_response_code(200);
            exit;
        }
        
        // Set allowed methods and headers for actual requests
        header('Access-Control-Allow-Methods: ' . implode(', ', $corsConfig['allowed_methods']));
        header('Access-Control-Allow-Headers: ' . implode(', ', $corsConfig['allowed_headers']));
        
        // Expose headers that are needed for file downloads
        header('Access-Control-Expose-Headers: Content-Disposition, Content-Length, Content-Type');
    }
}

