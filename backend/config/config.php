<?php
/**
 * Application Configuration
 * Technovation e-Judging System
 */

// Load environment variables
if (file_exists(__DIR__ . '/../.env')) {
    $envFile = file(__DIR__ . '/../.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($envFile as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        list($name, $value) = explode('=', $line, 2);
        $_ENV[trim($name)] = trim($value);
    }
}

return [
    'app_name' => 'Technovation e-Judging System',
    'app_version' => '1.0.0',
    'app_url' => $_ENV['APP_URL'] ?? 'http://localhost',
    
    // JWT Configuration
    'jwt_secret' => $_ENV['JWT_SECRET'] ?? 'your-secret-key-change-in-production',
    'jwt_expire' => $_ENV['JWT_EXPIRE'] ?? 3600, // 1 hour
    
    // File Upload Configuration
    'upload_path' => __DIR__ . '/../uploads/',
    'upload_url' => ($_ENV['APP_URL'] ?? 'http://localhost:8000') . '/backend/uploads/',
    'max_file_size' => 2048 * 1024, // 2MB
    'allowed_image_types' => ['image/jpeg', 'image/png', 'image/jpg'],
    
    // CORS Configuration
    'cors' => [
        'allowed_origins' => ['*'],
        'allowed_methods' => ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS'],
        'allowed_headers' => ['Content-Type', 'Authorization', 'X-Requested-With'],
        'max_age' => 3600
    ],
    
    // Timezone
    'timezone' => 'Asia/Colombo',
];

