<?php
/**
 * Reporter - Data Endpoints
 */

$db = Database::getInstance()->getConnection();
$user = Auth::getCurrentUser();

if (!$user || $user['role'] !== 'reporter') {
    Response::forbidden('Reporter access required');
}

// Get all data needed for report generation
$data = [];

// Get all awards
$stmt = $db->query("SELECT id, category, award_number FROM awards WHERE status = 'active' ORDER BY category ASC");
$data['awards'] = $stmt->fetchAll();

// Get all institutions
$stmt = $db->query("SELECT id, name, type FROM institutions WHERE status = 'active' ORDER BY name ASC");
$data['institutions'] = $stmt->fetchAll();

// Get evaluation statistics
$stmt = $db->query("
    SELECT 
        COUNT(*) as total_evaluations,
        COUNT(CASE WHEN status = 'submitted' THEN 1 END) as submitted_evaluations,
        COUNT(CASE WHEN status = 'draft' THEN 1 END) as draft_evaluations,
        AVG(percentage) as average_percentage
    FROM evaluations
");
$data['statistics'] = $stmt->fetch();

Response::success('Report data retrieved', $data);

