<?php
/**
 * Database Connection Class
 */

class Database {
    private static $instance = null;
    private $connection;
    
    private function __construct() {
        $config = require __DIR__ . '/../config/database.php';
        
        try {
            // Parse host:port if port is specified
            $host = $config['host'];
            $port = null;
            if (strpos($host, ':') !== false) {
                list($host, $port) = explode(':', $host, 2);
            }
            
            // Build DSN with optional port
            $dsn = "mysql:host={$host}";
            if ($port !== null) {
                $dsn .= ";port={$port}";
            }
            $dsn .= ";dbname={$config['database']};charset={$config['charset']}";
            
            $this->connection = new PDO($dsn, $config['username'], $config['password'], $config['options']);
        } catch (PDOException $e) {
            throw new Exception("Database connection failed: " . $e->getMessage());
        }
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function getConnection() {
        return $this->connection;
    }
    
    // Prevent cloning
    private function __clone() {}
    
    // Prevent unserialization
    public function __wakeup() {
        throw new Exception("Cannot unserialize singleton");
    }
}

