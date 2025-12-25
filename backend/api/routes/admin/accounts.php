<?php
/**
 * Admin - Accounts Management
 */

$db = Database::getInstance()->getConnection();
$user = Auth::getCurrentUser();

// Check authentication
if (!$user) {
    Response::unauthorized('Authentication required');
}

// Check admin role
if ($user['role'] !== 'admin') {
    Response::forbidden('Admin access required');
}

$method = $_SERVER['REQUEST_METHOD'];

// Handle method override for file uploads (PHP doesn't populate $_FILES for PUT requests)
// Check if this is a POST request with _method=PUT (used for updates with file uploads)
if ($method === 'POST' && isset($_POST['_method']) && strtoupper($_POST['_method']) === 'PUT') {
    $method = 'PUT';
}

// Read raw input once and store it (php://input can only be read once)
$rawInput = file_get_contents('php://input');

// Handle both JSON and FormData requests
$input = json_decode($rawInput, true);

// If JSON decode failed or input is empty, try reading from $_POST (FormData)
if (json_last_error() !== JSON_ERROR_NONE || empty($input)) {
    $input = $_POST;
}

// For PUT/PATCH requests with multipart/form-data, PHP doesn't populate $_POST
// We need to manually parse it
if (($method === 'PUT' || $method === 'PATCH') && empty($input)) {
    $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
    if (strpos($contentType, 'multipart/form-data') !== false) {
        // Parse multipart form data for PUT requests
        $putData = [];
        $putFiles = [];
        
        // Get boundary from content type - handle quoted boundaries and additional parameters
        preg_match('/boundary=([^;]+)/i', $contentType, $matches);
        if (isset($matches[1])) {
            // Strip quotes if present (boundary may be quoted in Content-Type header)
            $boundary = trim($matches[1], '"\'');
            
            // Use the already-read raw input (don't read php://input again)
            $rawData = $rawInput;
            
            // Split by boundary
            $parts = preg_split('/-+' . preg_quote($boundary) . '/', $rawData);
            
            foreach ($parts as $part) {
                if (empty(trim($part)) || $part === '--') continue;
                
                // Separate headers from body
                $segments = preg_split('/\r\n\r\n/', $part, 2);
                if (count($segments) < 2) continue;
                
                $headers = $segments[0];
                $body = $segments[1];
                
                // Remove exactly the trailing \r\n that's part of multipart format
                // (don't use rtrim as it could remove valid bytes from binary files)
                if (substr($body, -2) === "\r\n") {
                    $body = substr($body, 0, -2);
                } elseif (substr($body, -1) === "\n") {
                    $body = substr($body, 0, -1);
                }
                
                // Parse Content-Disposition header
                if (preg_match('/Content-Disposition:.*name="([^"]+)"(?:;\s*filename="([^"]+)")?/i', $headers, $matches)) {
                    $fieldName = $matches[1];
                    $filename = $matches[2] ?? null;
                    
                    if ($filename) {
                        // This is a file upload
                        $tmpName = tempnam(sys_get_temp_dir(), 'put_');
                        file_put_contents($tmpName, $body);
                        
                        // Get content type
                        $fileContentType = 'application/octet-stream';
                        if (preg_match('/Content-Type:\s*([^\r\n]+)/i', $headers, $ctMatches)) {
                            $fileContentType = trim($ctMatches[1]);
                        }
                        
                        $_FILES[$fieldName] = [
                            'name' => $filename,
                            'type' => $fileContentType,
                            'tmp_name' => $tmpName,
                            'error' => UPLOAD_ERR_OK,
                            'size' => strlen($body)
                        ];
                    } else {
                        // Regular field
                        $putData[$fieldName] = $body;
                    }
                }
            }
        }
        
        $input = $putData;
    }
}

// Get ID from URI if present
$id = isset($_GET['_params'][0]) ? (int)$_GET['_params'][0] : (isset($_GET['params'][0]) ? (int)$_GET['params'][0] : null);

switch ($method) {
    case 'GET':
        if ($id) {
            // Get single account
            $stmt = $db->prepare("SELECT id, username, email, title, first_name, last_name, role, status, profile_image, created_at FROM users WHERE id = ?");
            $stmt->execute([$id]);
            $account = $stmt->fetch();
            
            if (!$account) {
                Response::notFound('Account not found');
            }
            
            Response::success('Account retrieved', $account);
        } else {
            // Get all accounts with pagination
            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
            $offset = ($page - 1) * $limit;
            $role = $_GET['role'] ?? '';
            
            $where = "1=1";
            $params = [];
            
            if ($role) {
                $where .= " AND role = ?";
                $params[] = $role;
            }
            
            // Get total count
            $countStmt = $db->prepare("SELECT COUNT(*) as total FROM users WHERE $where");
            $countStmt->execute($params);
            $total = $countStmt->fetch()['total'];
            
            // Get accounts
            $stmt = $db->prepare("SELECT id, username, email, title, first_name, last_name, role, status, profile_image, created_at FROM users WHERE $where ORDER BY created_at DESC LIMIT ? OFFSET ?");
            $params[] = $limit;
            $params[] = $offset;
            $stmt->execute($params);
            $accounts = $stmt->fetchAll();
            
            Response::success('Accounts retrieved', [
                'data' => $accounts,
                'pagination' => [
                    'page' => $page,
                    'limit' => $limit,
                    'total' => $total,
                    'pages' => ceil($total / $limit)
                ]
            ]);
        }
        break;
        
    case 'POST':
        // Create new account
        $username = $input['username'] ?? '';
        $email = $input['email'] ?? '';
        $password = $input['password'] ?? '';
        $title = $input['title'] ?? '';
        $firstName = $input['first_name'] ?? '';
        $lastName = $input['last_name'] ?? '';
        $role = $input['role'] ?? '';
        
        // Validation
        $errors = [];
        if (empty($username)) $errors['username'] = 'Username is required';
        if (empty($email)) $errors['email'] = 'Email is required';
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors['email'] = 'Invalid email format';
        if (empty($password)) $errors['password'] = 'Password is required';
        if (strlen($password) < 6) $errors['password'] = 'Password must be at least 6 characters';
        if (!in_array($role, ['admin', 'judger', 'reporter'])) $errors['role'] = 'Invalid role';
        
        if (!empty($errors)) {
            Response::validationError($errors);
        }
        
        // Check if username or email already exists
        $checkStmt = $db->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $checkStmt->execute([$username, $email]);
        if ($checkStmt->fetch()) {
            Response::error('Username or email already exists', null, 409);
        }
        
        // Hash password
        $hashedPassword = Auth::hashPassword($password);
        
        // Handle image upload
        $profileImage = null;
        $profileImageUrl = null;
        if (isset($_FILES['profileImage']) && $_FILES['profileImage']['error'] === UPLOAD_ERR_OK) {
            try {
                $imageInfo = FileUpload::uploadImage($_FILES['profileImage'], 'users');
                $profileImage = $imageInfo['path'];
                $profileImageUrl = $imageInfo['url'];
            } catch (Exception $e) {
                Response::error('Image upload failed: ' . $e->getMessage());
            }
        }
        
        // Insert user - include profile_image and title (title is nullable)
        if ($profileImage) {
            $stmt = $db->prepare("INSERT INTO users (username, email, password, title, first_name, last_name, role, profile_image) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$username, $email, $hashedPassword, $title ?: null, $firstName, $lastName, $role, $profileImageUrl]);
        } else {
            $stmt = $db->prepare("INSERT INTO users (username, email, password, title, first_name, last_name, role) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$username, $email, $hashedPassword, $title ?: null, $firstName, $lastName, $role]);
        }
        $userId = $db->lastInsertId();
        
        // Get created user
        $stmt = $db->prepare("SELECT id, username, email, title, first_name, last_name, role, status, profile_image, created_at FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $account = $stmt->fetch();
        
        Response::success('Account created successfully', $account, 201);
        break;
        
    case 'PUT':
        // Update account
        if (!$id) {
            Response::error('Account ID is required', null, 400);
        }
        
        $username = $input['username'] ?? null;
        $email = $input['email'] ?? null;
        $password = $input['password'] ?? null;
        $title = $input['title'] ?? null;
        $firstName = $input['first_name'] ?? null;
        $lastName = $input['last_name'] ?? null;
        $role = $input['role'] ?? null;
        $status = $input['status'] ?? null;
        
        // Check if account exists
        $checkStmt = $db->prepare("SELECT id FROM users WHERE id = ?");
        $checkStmt->execute([$id]);
        if (!$checkStmt->fetch()) {
            Response::notFound('Account not found');
        }
        
        // Build update query
        $updates = [];
        $params = [];
        
        if ($username !== null) {
            // Check if username already taken
            $checkStmt = $db->prepare("SELECT id FROM users WHERE username = ? AND id != ?");
            $checkStmt->execute([$username, $id]);
            if ($checkStmt->fetch()) {
                Response::error('Username already taken', null, 409);
            }
            $updates[] = "username = ?";
            $params[] = $username;
        }
        
        if ($email !== null) {
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                Response::validationError(['email' => 'Invalid email format']);
            }
            // Check if email already taken
            $checkStmt = $db->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
            $checkStmt->execute([$email, $id]);
            if ($checkStmt->fetch()) {
                Response::error('Email already taken', null, 409);
            }
            $updates[] = "email = ?";
            $params[] = $email;
        }
        
        if ($password !== null) {
            if (strlen($password) < 6) {
                Response::validationError(['password' => 'Password must be at least 6 characters']);
            }
            $updates[] = "password = ?";
            $params[] = Auth::hashPassword($password);
        }
        
        if ($title !== null) {
            $updates[] = "title = ?";
            $params[] = $title;
        }
        
        if ($firstName !== null) {
            $updates[] = "first_name = ?";
            $params[] = $firstName;
        }
        
        if ($lastName !== null) {
            $updates[] = "last_name = ?";
            $params[] = $lastName;
        }
        
        if ($role !== null) {
            if (!in_array($role, ['admin', 'judger', 'reporter'])) {
                Response::validationError(['role' => 'Invalid role']);
            }
            $updates[] = "role = ?";
            $params[] = $role;
        }
        
        if ($status !== null) {
            if (!in_array($status, ['active', 'inactive'])) {
                Response::validationError(['status' => 'Invalid status']);
            }
            $updates[] = "status = ?";
            $params[] = $status;
        }
        
        // Handle image upload
        if (isset($_FILES['profileImage']) && $_FILES['profileImage']['error'] === UPLOAD_ERR_OK) {
            // Delete old image if exists
            $oldStmt = $db->prepare("SELECT profile_image FROM users WHERE id = ?");
            $oldStmt->execute([$id]);
            $oldUser = $oldStmt->fetch();
            if ($oldUser && $oldUser['profile_image']) {
                // Extract filename from URL or path
                $oldImagePath = $oldUser['profile_image'];
                if (strpos($oldImagePath, '/backend/uploads/') !== false) {
                    $pathMatch = preg_match('/\/backend\/uploads\/users\/(.+)$/', $oldImagePath, $matches);
                    if ($pathMatch && isset($matches[1])) {
                        FileUpload::deleteFile($matches[1], 'users');
                    }
                }
            }
            
            try {
                $imageInfo = FileUpload::uploadImage($_FILES['profileImage'], 'users');
                $profileImageUrl = $imageInfo['url'];
                $updates[] = "profile_image = ?";
                $params[] = $profileImageUrl;
            } catch (Exception $e) {
                Response::error('Image upload failed: ' . $e->getMessage());
            }
        }
        
        if (empty($updates)) {
            Response::error('No fields to update', null, 400);
        }
        
        $params[] = $id;
        $sql = "UPDATE users SET " . implode(', ', $updates) . " WHERE id = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        
        // Get updated user
        $stmt = $db->prepare("SELECT id, username, email, title, first_name, last_name, role, status, profile_image, created_at FROM users WHERE id = ?");
        $stmt->execute([$id]);
        $account = $stmt->fetch();
        
        Response::success('Account updated successfully', $account);
        break;
        
    case 'DELETE':
        // Delete account
        if (!$id) {
            Response::error('Account ID is required', null, 400);
        }
        
        // Prevent deleting own account
        $currentUserId = $user['user_id'] ?? $user['id'] ?? null;
        if ($currentUserId && $id == $currentUserId) {
            Response::error('You cannot delete your own account', null, 403);
        }
        
        // Check if account exists
        $checkStmt = $db->prepare("SELECT id, role FROM users WHERE id = ?");
        $checkStmt->execute([$id]);
        $accountToDelete = $checkStmt->fetch();
        
        if (!$accountToDelete) {
            Response::notFound('Account not found');
        }
        
        // Prevent deleting the last admin account
        if ($accountToDelete['role'] === 'admin') {
            $adminCountStmt = $db->query("SELECT COUNT(*) as count FROM users WHERE role = 'admin' AND status = 'active'");
            $adminCount = $adminCountStmt->fetch()['count'];
            if ($adminCount <= 1) {
                Response::error('Cannot delete the last admin account', null, 403);
            }
        }
        
        // Hard delete - actually remove the record from database
        $stmt = $db->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$id]);
        
        // Check if deletion was successful
        $verifyStmt = $db->prepare("SELECT id FROM users WHERE id = ?");
        $verifyStmt->execute([$id]);
        if ($verifyStmt->fetch()) {
            Response::error('Failed to delete account', null, 500);
        }
        
        Response::success('Account deleted successfully');
        break;
        
    default:
        Response::error('Method not allowed', null, 405);
}

