<?php
/**
 * Judger - Evaluations Management
 */

$db = Database::getInstance()->getConnection();
$user = Auth::getCurrentUser();

if (!$user || $user['role'] !== 'judger') {
    Response::forbidden('Judger access required');
}

$method = $_SERVER['REQUEST_METHOD'];
$input = json_decode(file_get_contents('php://input'), true);
$id = isset($_GET['_params'][0]) ? (int)$_GET['_params'][0] : (isset($_GET['params'][0]) ? (int)$_GET['params'][0] : null);
$action = isset($_GET['_params'][1]) ? $_GET['_params'][1] : (isset($_GET['params'][1]) ? $_GET['params'][1] : null);

// Check if this is a submit action
if ($action === 'submit' && $method === 'POST') {
    if (!$id) {
        Response::error('Evaluation ID is required');
    }
    
    // Update evaluation status to submitted
    $stmt = $db->prepare("UPDATE evaluations SET status = 'submitted', submitted_at = NOW() WHERE id = ? AND judge_id = ?");
    $stmt->execute([$id, $user['user_id']]);
    
    if ($stmt->rowCount() === 0) {
        Response::notFound('Evaluation not found');
    }
    
    Response::success('Evaluation submitted successfully');
    exit;
}

switch ($method) {
    case 'GET':
        if ($id) {
            // Get single evaluation with criteria marks
            $stmt = $db->prepare("
                SELECT e.*, 
                       i.name as institution_name,
                       a.category as award_category,
                       u.username as judge_username
                FROM evaluations e
                JOIN institutions i ON e.institution_id = i.id
                JOIN awards a ON e.award_id = a.id
                JOIN users u ON e.judge_id = u.id
                WHERE e.id = ? AND e.judge_id = ?
            ");
            $stmt->execute([$id, $user['user_id']]);
            $evaluation = $stmt->fetch();
            
            if (!$evaluation) {
                Response::notFound('Evaluation not found');
            }
            
            // Get criteria marks
            $stmt = $db->prepare("
                SELECT ecm.*, ac.name as criterion_name, ac.allocated_marks
                FROM evaluation_criteria_marks ecm
                JOIN award_criteria ac ON ecm.criterion_id = ac.id
                WHERE ecm.evaluation_id = ?
                ORDER BY ac.display_order ASC
            ");
            $stmt->execute([$id]);
            $evaluation['criteria_marks'] = $stmt->fetchAll();
            
            Response::success('Evaluation retrieved', $evaluation);
        } else {
            // Get all evaluations for current judge
            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
            $offset = ($page - 1) * $limit;
            
            $countStmt = $db->prepare("SELECT COUNT(*) as total FROM evaluations WHERE judge_id = ?");
            $countStmt->execute([$user['user_id']]);
            $total = $countStmt->fetch()['total'];
            
            $stmt = $db->prepare("
                SELECT e.*, 
                       i.name as institution_name,
                       a.category as award_category
                FROM evaluations e
                JOIN institutions i ON e.institution_id = i.id
                JOIN awards a ON e.award_id = a.id
                WHERE e.judge_id = ?
                ORDER BY e.created_at DESC
                LIMIT ? OFFSET ?
            ");
            $stmt->execute([$user['user_id'], $limit, $offset]);
            $evaluations = $stmt->fetchAll();
            
            Response::success('Evaluations retrieved', [
                'data' => $evaluations,
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
        // Create new evaluation
        $institutionId = isset($input['institution_id']) ? (int)$input['institution_id'] : null;
        $awardId = isset($input['award_id']) ? (int)$input['award_id'] : null;
        $criteriaMarks = $input['criteria_marks'] ?? [];
        $comments = $input['comments'] ?? '';
        
        if (!$institutionId || !$awardId) {
            Response::validationError([
                'institution_id' => !$institutionId ? 'Institution ID is required' : null,
                'award_id' => !$awardId ? 'Award ID is required' : null
            ]);
        }
        
        // Verify institution and award exist
        $checkStmt = $db->prepare("SELECT id FROM institutions WHERE id = ? AND status = 'active'");
        $checkStmt->execute([$institutionId]);
        if (!$checkStmt->fetch()) {
            Response::error('Invalid institution', null, 400);
        }
        
        $checkStmt = $db->prepare("SELECT id, total_marks FROM awards WHERE id = ? AND status = 'active'");
        $checkStmt->execute([$awardId]);
        $award = $checkStmt->fetch();
        if (!$award) {
            Response::error('Invalid award', null, 400);
        }
        
        // Get criteria for this award
        $stmt = $db->prepare("SELECT * FROM award_criteria WHERE award_id = ? ORDER BY display_order ASC");
        $stmt->execute([$awardId]);
        $criteria = $stmt->fetchAll();
        
        if (empty($criteria)) {
            Response::error('No criteria defined for this award', null, 400);
        }
        
        // Validate and calculate marks
        $totalAchieved = 0;
        $totalAllocated = 0;
        $marksData = [];
        
        foreach ($criteria as $criterion) {
            $criterionId = $criterion['id'];
            $allocated = (int)$criterion['allocated_marks'];
            $achieved = 0;
            
            // Find matching mark from input
            foreach ($criteriaMarks as $mark) {
                if (isset($mark['criterion_id']) && $mark['criterion_id'] == $criterionId) {
                    $achieved = (int)($mark['achieved_marks'] ?? 0);
                    break;
                }
            }
            
            if ($achieved > $allocated) {
                Response::validationError(['criteria_marks' => "Achieved marks cannot exceed allocated marks for criterion: {$criterion['name']}"]);
            }
            
            $totalAchieved += $achieved;
            $totalAllocated += $allocated;
            $marksData[] = [
                'criterion_id' => $criterionId,
                'allocated_marks' => $allocated,
                'achieved_marks' => $achieved
            ];
        }
        
        // Calculate percentage
        $percentage = $totalAllocated > 0 ? ($totalAchieved / $totalAllocated) * 100 : 0;
        $percentage = round($percentage, 2);
        
        // Check if evaluation already exists
        $checkStmt = $db->prepare("SELECT id FROM evaluations WHERE judge_id = ? AND institution_id = ? AND award_id = ?");
        $checkStmt->execute([$user['user_id'], $institutionId, $awardId]);
        $existing = $checkStmt->fetch();
        
        if ($existing) {
            // Update existing evaluation
            $evalId = $existing['id'];
            $stmt = $db->prepare("UPDATE evaluations SET total_marks = ?, percentage = ?, comments = ?, updated_at = NOW() WHERE id = ?");
            $stmt->execute([$totalAchieved, $percentage, $comments, $evalId]);
            
            // Delete old criteria marks
            $deleteStmt = $db->prepare("DELETE FROM evaluation_criteria_marks WHERE evaluation_id = ?");
            $deleteStmt->execute([$evalId]);
        } else {
            // Create new evaluation
            $stmt = $db->prepare("INSERT INTO evaluations (judge_id, institution_id, award_id, total_marks, percentage, comments, status) VALUES (?, ?, ?, ?, ?, ?, 'draft')");
            $stmt->execute([$user['user_id'], $institutionId, $awardId, $totalAchieved, $percentage, $comments]);
            $evalId = $db->lastInsertId();
        }
        
        // Insert/update criteria marks
        $marksStmt = $db->prepare("INSERT INTO evaluation_criteria_marks (evaluation_id, criterion_id, allocated_marks, achieved_marks) VALUES (?, ?, ?, ?)");
        foreach ($marksData as $mark) {
            $marksStmt->execute([$evalId, $mark['criterion_id'], $mark['allocated_marks'], $mark['achieved_marks']]);
        }
        
        // Get created evaluation
        $stmt = $db->prepare("SELECT * FROM evaluations WHERE id = ?");
        $stmt->execute([$evalId]);
        $evaluation = $stmt->fetch();
        
        Response::success('Evaluation saved successfully', $evaluation, 201);
        break;
        
    case 'PUT':
        // Update evaluation
        if (!$id) {
            Response::error('Evaluation ID is required');
        }
        
        // Check if evaluation belongs to this judge
        $checkStmt = $db->prepare("SELECT id, status FROM evaluations WHERE id = ? AND judge_id = ?");
        $checkStmt->execute([$id, $user['user_id']]);
        $evaluation = $checkStmt->fetch();
        
        if (!$evaluation) {
            Response::notFound('Evaluation not found');
        }
        
        if ($evaluation['status'] === 'submitted') {
            Response::error('Cannot modify submitted evaluation', null, 400);
        }
        
        $criteriaMarks = $input['criteria_marks'] ?? [];
        $comments = $input['comments'] ?? null;
        
        // Get award and criteria
        $stmt = $db->prepare("
            SELECT e.*, a.total_marks as award_total_marks
            FROM evaluations e
            JOIN awards a ON e.award_id = a.id
            WHERE e.id = ?
        ");
        $stmt->execute([$id]);
        $evalData = $stmt->fetch();
        
        $stmt = $db->prepare("SELECT * FROM award_criteria WHERE award_id = ? ORDER BY display_order ASC");
        $stmt->execute([$evalData['award_id']]);
        $criteria = $stmt->fetchAll();
        
        // Validate and calculate marks
        $totalAchieved = 0;
        $totalAllocated = 0;
        $marksData = [];
        
        foreach ($criteria as $criterion) {
            $criterionId = $criterion['id'];
            $allocated = (int)$criterion['allocated_marks'];
            $achieved = 0;
            
            foreach ($criteriaMarks as $mark) {
                if (isset($mark['criterion_id']) && $mark['criterion_id'] == $criterionId) {
                    $achieved = (int)($mark['achieved_marks'] ?? 0);
                    break;
                }
            }
            
            if ($achieved > $allocated) {
                Response::validationError(['criteria_marks' => "Achieved marks cannot exceed allocated marks for criterion: {$criterion['name']}"]);
            }
            
            $totalAchieved += $achieved;
            $totalAllocated += $allocated;
            $marksData[] = [
                'criterion_id' => $criterionId,
                'allocated_marks' => $allocated,
                'achieved_marks' => $achieved
            ];
        }
        
        $percentage = $totalAllocated > 0 ? ($totalAchieved / $totalAllocated) * 100 : 0;
        $percentage = round($percentage, 2);
        
        // Update evaluation
        $updates = ["total_marks = ?", "percentage = ?"];
        $params = [$totalAchieved, $percentage];
        
        if ($comments !== null) {
            $updates[] = "comments = ?";
            $params[] = $comments;
        }
        
        $params[] = $id;
        $sql = "UPDATE evaluations SET " . implode(', ', $updates) . " WHERE id = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        
        // Update criteria marks
        $deleteStmt = $db->prepare("DELETE FROM evaluation_criteria_marks WHERE evaluation_id = ?");
        $deleteStmt->execute([$id]);
        
        $marksStmt = $db->prepare("INSERT INTO evaluation_criteria_marks (evaluation_id, criterion_id, allocated_marks, achieved_marks) VALUES (?, ?, ?, ?)");
        foreach ($marksData as $mark) {
            $marksStmt->execute([$id, $mark['criterion_id'], $mark['allocated_marks'], $mark['achieved_marks']]);
        }
        
        // Get updated evaluation
        $stmt = $db->prepare("SELECT * FROM evaluations WHERE id = ?");
        $stmt->execute([$id]);
        $updatedEvaluation = $stmt->fetch();
        
        Response::success('Evaluation updated successfully', $updatedEvaluation);
        break;
        
    default:
        Response::error('Method not allowed', null, 405);
}

