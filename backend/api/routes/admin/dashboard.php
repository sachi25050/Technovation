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

Response::success('Dashboard data retrieved', [
    'stats' => $stats,
    'recent_activities' => $recentActivities
]);

