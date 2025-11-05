<?php
/**
 * Script to fix admin password hash
 * Run this after creating the database to set a proper password hash
 */

require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../core/Auth.php';

$password = 'admin123'; // Change this to your desired password
$hashedPassword = Auth::hashPassword($password);

try {
    $db = Database::getInstance()->getConnection();
    $stmt = $db->prepare("UPDATE users SET password = ? WHERE username = 'admin'");
    $stmt->execute([$hashedPassword]);
    
    echo "Admin password updated successfully!\n";
    echo "Username: admin\n";
    echo "Password: $password\n";
    echo "Hash: $hashedPassword\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

