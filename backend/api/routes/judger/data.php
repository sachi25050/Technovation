<?php
/**
 * Judger - Data Endpoints (Institutions, Awards, Criteria)
 */

$db = Database::getInstance()->getConnection();
$user = Auth::getCurrentUser();

if (!$user || $user['role'] !== 'judger') {
    Response::forbidden('Judger access required');
}

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = trim($uri, '/');

if (strpos($uri, 'institutions') !== false) {
    // Get all active institutions
    $stmt = $db->query("SELECT id, name, type, image_url FROM institutions WHERE status = 'active' ORDER BY name ASC");
    $institutions = $stmt->fetchAll();
    
    $config = require __DIR__ . '/../../../config/config.php';
    $uploadBaseUrl = rtrim($config['upload_url'], '/');
    
    // Fix image URLs - ensure they are proper full URLs
    foreach ($institutions as &$inst) {
        if (!empty($inst['image_url'])) {
            $url = $inst['image_url'];
            
            // If it's already a full URL with the correct /api/uploads/ path, use it
            if (strpos($url, '/api/uploads/') !== false) {
                // Already correct format, just ensure full URL
                if (strpos($url, 'http') !== 0) {
                    // Extract path after /api/uploads/
                    $pathStart = strpos($url, '/api/uploads/') + 13;
                    $url = $uploadBaseUrl . '/' . substr($url, $pathStart);
                }
            } elseif (strpos($url, 'http') === 0) {
                // Full URL but old format - extract filename and rebuild
                // Convert old /backend/uploads/ to new format
                if (strpos($url, '/backend/uploads/') !== false) {
                    $pathStart = strpos($url, '/backend/uploads/') + 17;
                    $url = $uploadBaseUrl . '/' . substr($url, $pathStart);
                } elseif (strpos($url, '/uploads/') !== false) {
                    $pathStart = strpos($url, '/uploads/') + 9;
                    $url = $uploadBaseUrl . '/' . substr($url, $pathStart);
                }
            } else {
                // It's a relative path, construct full URL
                $url = ltrim($url, '/');
                // Remove 'uploads/' prefix if present
                if (strpos($url, 'uploads/') === 0) {
                    $url = substr($url, 8);
                }
                // Build full API URL
                $url = $uploadBaseUrl . '/' . $url;
            }
            
            $inst['image_url'] = $url;
        }
    }
    
    Response::success('Institutions retrieved', $institutions);
    
} elseif (strpos($uri, 'awards') !== false) {
    // Check if institution_id is provided
    $institutionId = isset($_GET['institution_id']) ? (int)$_GET['institution_id'] : null;
    
    if ($institutionId) {
        // Get awards for a specific institution
        $stmt = $db->prepare("
            SELECT DISTINCT a.id, 
                   a.award_number,
                   a.category,
                   a.description,
                   a.presentation_weightage,
                   a.preliminary_weightage,
                   a.status,
                   a.created_at,
                   a.updated_at,
                   ia.marks as institution_marks,
                   COUNT(ac.id) as criteria_count,
                   SUM(ac.allocated_marks) as total_allocated
            FROM awards a
            INNER JOIN institution_awards ia ON a.id = ia.award_id
            LEFT JOIN award_criteria ac ON a.id = ac.award_id
            WHERE ia.institution_id = ? AND (a.status = 'active' OR a.status IS NULL)
            GROUP BY a.id, a.award_number, a.category, a.description, a.presentation_weightage, a.preliminary_weightage, a.status, a.created_at, a.updated_at, ia.marks
            ORDER BY a.created_at DESC
        ");
        $stmt->execute([$institutionId]);
        $awards = $stmt->fetchAll();
    } else {
        // Get all active awards with criteria summary
        $stmt = $db->query("
            SELECT a.*, 
                   COUNT(ac.id) as criteria_count,
                   SUM(ac.allocated_marks) as total_allocated
            FROM awards a
            LEFT JOIN award_criteria ac ON a.id = ac.award_id
            WHERE a.status = 'active' OR a.status IS NULL
            GROUP BY a.id
            ORDER BY a.created_at DESC
        ");
        $awards = $stmt->fetchAll();
    }
    
    Response::success('Awards retrieved', $awards);
    
} elseif (strpos($uri, 'criteria') !== false) {
    // Get criteria for a specific award
    $awardId = isset($_GET['_params'][0]) ? (int)$_GET['_params'][0] : (isset($_GET['params'][0]) ? (int)$_GET['params'][0] : null);
    
    if (!$awardId) {
        Response::error('Award ID is required');
    }
    
    // Get award
    $stmt = $db->prepare("SELECT * FROM awards WHERE id = ? AND status = 'active'");
    $stmt->execute([$awardId]);
    $award = $stmt->fetch();
    
    if (!$award) {
        Response::notFound('Award not found');
    }
    
    // Get criteria
    $stmt = $db->prepare("SELECT * FROM award_criteria WHERE award_id = ? ORDER BY display_order ASC");
    $stmt->execute([$awardId]);
    $criteria = $stmt->fetchAll();
    
    Response::success('Criteria retrieved', [
        'award' => $award,
        'criteria' => $criteria
    ]);
    
} else {
    Response::notFound('Endpoint not found');
}

