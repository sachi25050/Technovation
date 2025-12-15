<?php
/**
 * Admin Dashboard Statistics
 */

$db = Database::getInstance()->getConnection();
$user = Auth::getCurrentUser();

if (!$user || $user['role'] !== 'admin') {
    Response::forbidden('Admin access required');
}

// Get statistics
$stats = [];

// Total users
$stmt = $db->query("SELECT COUNT(*) as total FROM users");
$stats['total_users'] = (int)$stmt->fetch()['total'];

// Active judges
$stmt = $db->query("SELECT COUNT(*) as total FROM users WHERE role = 'judger' AND status = 'active'");
$stats['active_judges'] = (int)$stmt->fetch()['total'];

// Total institutions
$stmt = $db->query("SELECT COUNT(*) as total FROM institutions");
$stats['institutions'] = (int)$stmt->fetch()['total'];

// Awards created
$stmt = $db->query("SELECT COUNT(*) as total FROM awards");
$stats['awards_created'] = (int)$stmt->fetch()['total'];

// Institution breakdown by type
$stmt = $db->query("SELECT type, COUNT(*) as count FROM institutions GROUP BY type");
$stats['institutions_by_type'] = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

// Recent activity (last 10 activities)
$recentActivities = [];

// Recent accounts created
$stmt = $db->query("SELECT 'account' as type, username, created_at FROM users ORDER BY created_at DESC LIMIT 5");
$recentActivities['accounts'] = $stmt->fetchAll();

// Recent institutions
$stmt = $db->query("SELECT 'institution' as type, name, created_at FROM institutions ORDER BY created_at DESC LIMIT 5");
$recentActivities['institutions'] = $stmt->fetchAll();

// Recent awards
$stmt = $db->query("SELECT 'award' as type, category, created_at FROM awards ORDER BY created_at DESC LIMIT 5");
$recentActivities['awards'] = $stmt->fetchAll();

// Institution Performance - aggregate marks from institution_awards and evaluations
$institutionPerformance = [];
try {
    // Combine data from institution_awards (preliminary marks) and evaluations (presentation scores)
    // institution_awards.marks = preliminary marks entered when creating institution
    // evaluations.presentation_score = presentation scores from judge evaluations
    $stmt = $db->query("
        SELECT 
            i.id,
            i.name,
            i.image_url as image,
            COALESCE((SELECT SUM(ia.marks) FROM institution_awards ia WHERE ia.institution_id = i.id), 0) as preliminary_marks,
            COALESCE((SELECT SUM(e.presentation_score) FROM evaluations e WHERE e.institution_id = i.id), 0) as presentation_marks
        FROM institutions i
        ORDER BY preliminary_marks DESC
        LIMIT 10
    ");
    $institutionPerformance = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // If all presentation marks are 0, also try getting total_achieved_marks from evaluations as fallback
    $hasPresentation = false;
    foreach ($institutionPerformance as $inst) {
        if ((float)$inst['presentation_marks'] > 0) {
            $hasPresentation = true;
            break;
        }
    }
    
    if (!$hasPresentation) {
        // Try using aggregated_score or total_achieved_marks from evaluations as presentation marks
        $stmt = $db->query("
            SELECT 
                i.id,
                i.name,
                i.image_url as image,
                COALESCE((SELECT SUM(ia.marks) FROM institution_awards ia WHERE ia.institution_id = i.id), 0) as preliminary_marks,
                COALESCE((SELECT SUM(e.aggregated_score) FROM evaluations e WHERE e.institution_id = i.id), 0) as presentation_marks
            FROM institutions i
            ORDER BY preliminary_marks DESC
            LIMIT 10
        ");
        $institutionPerformance = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (Exception $e) {
    // Fallback: get institutions with marks from institution_awards only
    try {
        $stmt = $db->query("
            SELECT 
                i.id,
                i.name,
                i.image_url as image,
                COALESCE(SUM(ia.marks), 0) as preliminary_marks,
                0 as presentation_marks
            FROM institutions i
            LEFT JOIN institution_awards ia ON i.id = ia.institution_id
            GROUP BY i.id, i.name, i.image_url
            ORDER BY preliminary_marks DESC
            LIMIT 10
        ");
        $institutionPerformance = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e2) {
        // Final fallback: get institutions with zero marks
        $stmt = $db->query("
            SELECT id, name, image_url as image, 0 as preliminary_marks, 0 as presentation_marks
            FROM institutions
            ORDER BY name
            LIMIT 10
        ");
        $institutionPerformance = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

// Fix image URLs - convert old /backend/uploads/ to /api/uploads/
foreach ($institutionPerformance as &$inst) {
    if (!empty($inst['image'])) {
        $inst['image'] = str_replace('/backend/uploads/', '/api/uploads/', $inst['image']);
    }
}

Response::success('Dashboard data retrieved', [
    'stats' => $stats,
    'recent_activities' => $recentActivities,
    'institution_performance' => $institutionPerformance
]);

