<?php
/**
 * Database Configuration
 * Technovation e-Judging System
 */

// Load environment variables if .env file exists
if (file_exists(__DIR__ . '/../.env')) {
    $envFile = file(__DIR__ . '/../.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($envFile as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        if (strpos($line, '=') === false) continue;
        list($name, $value) = explode('=', $line, 2);
        $_ENV[trim($name)] = trim($value);
    }
}

return [
    'host' => $_ENV['DB_HOST'] ?? '127.0.0.1:3310', // Can be 'host' or 'host:port'
    'database' => $_ENV['DB_NAME'] ?? $_ENV['DB_DATABASE'] ?? 'technovation_judging',
    'username' => $_ENV['DB_USER'] ?? $_ENV['DB_USERNAME'] ?? 'root',
    'password' => $_ENV['DB_PASS'] ?? $_ENV['DB_PASSWORD'] ?? 'root', // Set via .env or update here
    'charset' => 'utf8mb4',
    'collation' => 'utf8mb4_unicode_ci',
    'options' => [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,                   
        PDO::ATTR_EMULATE_PREPARES => false,
    ]
];

