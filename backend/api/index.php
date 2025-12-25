<?php
/**
 * API Entry Point
 * Technovation e-Judging System
 */

// Set timezone
date_default_timezone_set('Asia/Colombo');

// Error reporting (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Load core files
require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../core/Response.php';
require_once __DIR__ . '/../core/CORS.php';
require_once __DIR__ . '/../core/FileUpload.php';

// Handle CORS
CORS::handle();

// Get request method and URI
$method = $_SERVER['REQUEST_METHOD'];
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
// Normalize URI so routing works both when served under /backend/api (apache/nginx)
// and when using PHP's built-in server (where requests may be /api/.. or /auth/..).
// Remove common prefixes if present.
$uri = preg_replace('#^(/backend/api|/api|/backend)#', '', $uri);
$uri = trim($uri, '/');

// Get request body
$input = json_decode(file_get_contents('php://input'), true);
if ($input === null) {
    $input = [];
}

// Route handling
$routes = [
    // Authentication
    'POST /auth/login' => 'auth/login.php',
    'POST /auth/logout' => 'auth/logout.php',
    
    // Admin - Accounts
    'GET /admin/accounts' => 'admin/accounts.php',
    'GET /admin/accounts/{id}' => 'admin/accounts.php',
    'POST /admin/accounts' => 'admin/accounts.php',
    'POST /admin/accounts/{id}' => 'admin/accounts.php',  // For updates with file upload (_method=PUT)
    'PUT /admin/accounts/{id}' => 'admin/accounts.php',
    'DELETE /admin/accounts/{id}' => 'admin/accounts.php',
    
    // Admin - Institutions
    'GET /admin/institutions' => 'admin/institutions.php',
    'POST /admin/institutions' => 'admin/institutions.php',
    'POST /admin/institutions/{id}' => 'admin/institutions.php',  // For updates with file upload (_method=PUT)
    'PUT /admin/institutions/{id}' => 'admin/institutions.php',
    'DELETE /admin/institutions/{id}' => 'admin/institutions.php',
    'GET /admin/institutions/{id}' => 'admin/institutions.php',
    
    // Admin - Awards
    'GET /admin/awards' => 'admin/awards.php',
    'POST /admin/awards' => 'admin/awards.php',
    'PUT /admin/awards/{id}' => 'admin/awards.php',
    'DELETE /admin/awards/{id}' => 'admin/awards.php',
    'GET /admin/awards/{id}' => 'admin/awards.php',
    
    // Admin - Dashboard
    'GET /admin/dashboard' => 'admin/dashboard.php',
    
    // Judger - Evaluations
    'GET /judger/evaluations' => 'judger/evaluations.php',
    'POST /judger/evaluations' => 'judger/evaluations.php',
    'PUT /judger/evaluations/{id}' => 'judger/evaluations.php',
    'DELETE /judger/evaluations/{id}' => 'judger/evaluations.php',
    'GET /judger/evaluations/{id}' => 'judger/evaluations.php',
    'POST /judger/evaluations/{id}/submit' => 'judger/evaluations.php',
    
    // Judger - Data
    'GET /judger/institutions' => 'judger/data.php',
    'GET /judger/awards' => 'judger/data.php',
    'GET /judger/criteria/{award_id}' => 'judger/data.php',
    
    // Reporter - Reports
    'GET /reporter/reports' => 'reporter/reports.php',
    'POST /reporter/reports' => 'reporter/reports.php',
    'GET /reporter/reports/{id}' => 'reporter/reports.php',
    'GET /reporter/reports/{id}/download' => 'reporter/reports.php',
    
    // Reporter - Data
    'GET /reporter/data' => 'reporter/data.php',
    
    // Uploads - Serve uploaded files (handles /uploads/subfolder/filename)
    'GET /uploads/{subfolder}/{filename}' => 'uploads.php',
];

// Simple router
$routeKey = $method . ' /' . $uri;
$found = false;
$params = [];

// Try exact match first
if (isset($routes[$routeKey])) {
    $found = true;
    $file = __DIR__ . '/routes/' . $routes[$routeKey];
} else {
    // Try pattern matching for dynamic routes
    foreach ($routes as $pattern => $file) {
        $patternParts = explode(' ', $pattern);
        $patternMethod = $patternParts[0];
        $patternUri = trim($patternParts[1], '/');
        
        if ($patternMethod !== $method) continue;
        
        // Convert {id} to regex
        $patternRegex = '#^' . preg_replace('/\{[^}]+\}/', '([^/]+)', $patternUri) . '$#';
        
        if (preg_match($patternRegex, $uri, $matches)) {
            $found = true;
            $file = __DIR__ . '/routes/' . $file;
            // Store matched parameters
            $params = array_slice($matches, 1);
            break;
        }
    }
}

// Store params in a way that route files can access
if (!empty($params)) {
    $_GET['_params'] = $params;
    // Also allow access via numeric indices for backward compatibility
    foreach ($params as $index => $param) {
        $_GET['params'][$index] = $param;
    }
}

if (!$found) {
    Response::notFound('Route not found');
}

// Check if file exists
if (!file_exists($file)) {
    Response::error('Endpoint file not found', null, 500);
}

// Include the route file
require_once $file;

## API Endpoints

### Authentication
// - `POST /api/auth/login` - Login
// - `POST /api/auth/logout` - Logout

### Admin (requires admin role)
// - `GET /api/admin/accounts` - List accounts
// - `POST /api/admin/accounts` - Create account
// - `PUT /api/admin/accounts/{id}` - Update account
// - `DELETE /api/admin/accounts/{id}` - Delete account
// - `GET /api/admin/institutions` - List institutions
// - `POST /api/admin/institutions` - Create institution
// - `GET /api/admin/institutions/{id}` - Get institution
// - `PUT /api/admin/institutions/{id}` - Update institution
// - `DELETE /api/admin/institutions/{id}` - Delete institution
// - `GET /api/admin/awards` - List awards
// - `POST /api/admin/awards` - Create award
// - `GET /api/admin/awards/{id}` - Get award
// - `PUT /api/admin/awards/{id}` - Update award
// - `DELETE /api/admin/awards/{id}` - Delete award
// - `GET /api/admin/dashboard` - Dashboard stats

### Judger (requires judger role)
// - `GET /api/judger/institutions` - List institutions
// - `GET /api/judger/awards` - List awards
// - `GET /api/judger/criteria/{award_id}` - Get criteria
// - `GET /api/judger/evaluations` - List evaluations
// - `POST /api/judger/evaluations` - Create evaluation
// - `GET /api/judger/evaluations/{id}` - Get evaluation
// - `PUT /api/judger/evaluations/{id}` - Update evaluation
// - `DELETE /api/judger/evaluations/{id}` - Delete evaluation
// - `POST /api/judger/evaluations/{id}/submit` - Submit evaluation

### Reporter (requires reporter role)
// - `GET /api/reporter/data` - Get report data
// - `GET /api/reporter/reports` - List reports
// - `POST /api/reporter/reports` - Generate report
// - `GET /api/reporter/reports/{id}` - Get report
// - `GET /api/reporter/reports/{id}/download` - Download report
