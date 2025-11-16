<?php
/**
 * Login Endpoint
 */

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Response::error('Method not allowed', null, 405);
}

$input = json_decode(file_get_contents('php://input'), true);
$username = $input['username'] ?? '';
$password = $input['password'] ?? '';



// Validation
if (empty($username) || empty($password)) {
    Response::validationError([
        'username' => empty($username) ? 'Username is required' : null,
        'password' => empty($password) ? 'Password is required' : null
    ]);
}

// Get database connection
$db = Database::getInstance()->getConnection();

// Find user
$stmt = $db->prepare("SELECT id, username, email, password, first_name, last_name, role, status, profile_image FROM users WHERE username = ? AND status = 'active'");
$stmt->execute([$username]);
$user = $stmt->fetch();

if (!$user || !Auth::verifyPassword($password, $user['password'])) {
    Response::error('Invalid username or password', null, 401);
}

// Generate token
$token = Auth::generateToken($user['id'], $user['role'], $user['username']);

// Return response
Response::success('Login successful', [
    'token' => $token,
    'user' => [
        'id' => $user['id'],
        'username' => $user['username'],
        'email' => $user['email'],
        'role' => $user['role'],
        'first_name' => $user['first_name'],
        'last_name' => $user['last_name'],
        'profile_image' => $user['profile_image'] ?? null
    ]
]);

