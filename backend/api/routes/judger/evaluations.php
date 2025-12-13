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
        // Create/Update evaluation with all criteria and calculated scores
        $institutionId = isset($input['institution_id']) ? (int)$input['institution_id'] : null;
        $awardId = isset($input['award_id']) ? (int)$input['award_id'] : null;
        $criteriaMarks = $input['criteria_marks'] ?? [];
        $comments = $input['comments'] ?? '';
        
        // Get scores from input
        $presentationScore = isset($input['presentation_score']) ? (float)$input['presentation_score'] : 0;
        $preliminaryScore = isset($input['preliminary_score']) ? (float)$input['preliminary_score'] : 0;
        $aggregatedScore = isset($input['aggregated_score']) ? (float)$input['aggregated_score'] : 0;
        $presentationWeightage = isset($input['presentation_weightage']) ? (float)$input['presentation_weightage'] : 0;
        $preliminaryWeightage = isset($input['preliminary_weightage']) ? (float)$input['preliminary_weightage'] : 0;
        $totalAchievedMarks = isset($input['total_achieved_marks']) ? (float)$input['total_achieved_marks'] : 0;
        $totalAllocatedMarks = isset($input['total_allocated_marks']) ? (float)$input['total_allocated_marks'] : 0;
        
        // Get status (default to 'submitted' for submit action)
        $status = isset($input['status']) ? $input['status'] : 'submitted';
        
        if (!$institutionId || !$awardId) {
            Response::validationError([
                'institution_id' => !$institutionId ? 'Institution ID is required' : null,
                'award_id' => !$awardId ? 'Award ID is required' : null
            ]);
        }
        
        // Verify institution exists
        $checkStmt = $db->prepare("SELECT id FROM institutions WHERE id = ?");
        $checkStmt->execute([$institutionId]);
        if (!$checkStmt->fetch()) {
            Response::error('Invalid institution', null, 400);
        }
        
        // Verify award exists and get weightages
        $checkStmt = $db->prepare("SELECT id, presentation_weightage, preliminary_weightage FROM awards WHERE id = ?");
        $checkStmt->execute([$awardId]);
        $award = $checkStmt->fetch();
        if (!$award) {
            Response::error('Invalid award', null, 400);
        }
        
        // Use award weightages if not provided in input
        if ($presentationWeightage == 0 && isset($award['presentation_weightage'])) {
            $presentationWeightage = (float)$award['presentation_weightage'];
        }
        if ($preliminaryWeightage == 0 && isset($award['preliminary_weightage'])) {
            $preliminaryWeightage = (float)$award['preliminary_weightage'];
        }
        
        // Prepare criteria columns (up to 10)
        $criteriaColumns = [];
        $criteriaValues = [];
        for ($i = 1; $i <= 10; $i++) {
            $criteriaColumns[] = "criteria_{$i}_marks";
            // Find matching criterion marks from input
            $marks = null;
            if (isset($criteriaMarks[$i - 1])) {
                $marks = (float)($criteriaMarks[$i - 1]['achieved_marks'] ?? $criteriaMarks[$i - 1]['marks'] ?? null);
            }
            $criteriaValues[] = $marks;
        }
        
        // Calculate totals from criteria if not provided
        if ($totalAchievedMarks == 0) {
            foreach ($criteriaMarks as $mark) {
                $totalAchievedMarks += (float)($mark['achieved_marks'] ?? $mark['marks'] ?? 0);
            }
        }
        
        // Calculate percentage
        $percentage = $totalAllocatedMarks > 0 ? ($totalAchievedMarks / $totalAllocatedMarks) * 100 : 0;
        $percentage = round($percentage, 2);
        
        // Check if evaluation already exists
        $checkStmt = $db->prepare("SELECT id, status FROM evaluations WHERE judge_id = ? AND institution_id = ? AND award_id = ?");
        $checkStmt->execute([$user['user_id'], $institutionId, $awardId]);
        $existing = $checkStmt->fetch();
        
        if ($existing) {
            // Check if already submitted
            if ($existing['status'] === 'submitted' && $status === 'submitted') {
                Response::error('Evaluation has already been submitted for this institution and award', null, 400);
            }
            
            // Update existing evaluation
            $evalId = $existing['id'];
            $sql = "UPDATE evaluations SET 
                criteria_1_marks = ?, criteria_2_marks = ?, criteria_3_marks = ?, criteria_4_marks = ?, criteria_5_marks = ?,
                criteria_6_marks = ?, criteria_7_marks = ?, criteria_8_marks = ?, criteria_9_marks = ?, criteria_10_marks = ?,
                total_achieved_marks = ?, total_allocated_marks = ?, total_marks = ?, percentage = ?,
                presentation_score = ?, preliminary_score = ?, aggregated_score = ?,
                presentation_weightage = ?, preliminary_weightage = ?,
                comments = ?, status = ?, submitted_at = " . ($status === 'submitted' ? 'NOW()' : 'submitted_at') . ",
                updated_at = NOW()
                WHERE id = ?";
            
            $params = array_merge(
                $criteriaValues,
                [
                    $totalAchievedMarks, $totalAllocatedMarks, $totalAchievedMarks, $percentage,
                    $presentationScore, $preliminaryScore, $aggregatedScore,
                    $presentationWeightage, $preliminaryWeightage,
                    $comments, $status, $evalId
                ]
            );
            
            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            
            // Delete old criteria marks from normalized table
            $deleteStmt = $db->prepare("DELETE FROM evaluation_criteria_marks WHERE evaluation_id = ?");
            $deleteStmt->execute([$evalId]);
        } else {
            // Create new evaluation
            $sql = "INSERT INTO evaluations (
                judge_id, institution_id, award_id,
                criteria_1_marks, criteria_2_marks, criteria_3_marks, criteria_4_marks, criteria_5_marks,
                criteria_6_marks, criteria_7_marks, criteria_8_marks, criteria_9_marks, criteria_10_marks,
                total_achieved_marks, total_allocated_marks, total_marks, percentage,
                presentation_score, preliminary_score, aggregated_score,
                presentation_weightage, preliminary_weightage,
                comments, status, submitted_at, created_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, " . 
            ($status === 'submitted' ? 'NOW()' : 'NULL') . ", NOW())";
            
            $params = array_merge(
                [$user['user_id'], $institutionId, $awardId],
                $criteriaValues,
                [
                    $totalAchievedMarks, $totalAllocatedMarks, $totalAchievedMarks, $percentage,
                    $presentationScore, $preliminaryScore, $aggregatedScore,
                    $presentationWeightage, $preliminaryWeightage,
                    $comments, $status
                ]
            );
            
            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            $evalId = $db->lastInsertId();
        }
        
        // Insert criteria marks into normalized table (for detailed reporting)
        $marksStmt = $db->prepare("INSERT INTO evaluation_criteria_marks 
            (evaluation_id, criterion_id, criterion_name, display_order, allocated_marks, achieved_marks) 
            VALUES (?, ?, ?, ?, ?, ?)");
        
        foreach ($criteriaMarks as $index => $mark) {
            $criterionId = isset($mark['criterion_id']) ? (int)$mark['criterion_id'] : ($index + 1);
            $criterionName = $mark['name'] ?? $mark['criterion_name'] ?? "Criterion " . ($index + 1);
            $displayOrder = $index + 1;
            $allocatedMarks = (float)($mark['allocated_marks'] ?? $mark['allocated'] ?? 0);
            $achievedMarks = (float)($mark['achieved_marks'] ?? $mark['marks'] ?? 0);
            
            $marksStmt->execute([
                $evalId, $criterionId, $criterionName, $displayOrder, $allocatedMarks, $achievedMarks
            ]);
        }
        
        // Get created/updated evaluation with related data
        $stmt = $db->prepare("
            SELECT e.*, 
                   i.name as institution_name,
                   a.category as award_category,
                   CONCAT(COALESCE(u.title, ''), ' ', COALESCE(u.first_name, ''), ' ', COALESCE(u.last_name, '')) as judge_name
            FROM evaluations e
            JOIN institutions i ON e.institution_id = i.id
            JOIN awards a ON e.award_id = a.id
            JOIN users u ON e.judge_id = u.id
            WHERE e.id = ?
        ");
        $stmt->execute([$evalId]);
        $evaluation = $stmt->fetch();
        
        $message = $existing ? 'Evaluation updated successfully' : 'Evaluation submitted successfully';
        Response::success($message, $evaluation, $existing ? 200 : 201);
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

