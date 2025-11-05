<?php
/**
 * Admin - Awards Management
 */

$db = Database::getInstance()->getConnection();
$user = Auth::getCurrentUser();

if (!$user || $user['role'] !== 'admin') {
    Response::forbidden('Admin access required');
}





$method = $_SERVER['REQUEST_METHOD'];
$input = json_decode(file_get_contents('php://input'), true);
$id = isset($_GET['_params'][0]) ? (int)$_GET['_params'][0] : (isset($_GET['params'][0]) ? (int)$_GET['params'][0] : null);

switch ($method) {
    case 'GET':
        if ($id) {
            // Get single award with criteria
            $stmt = $db->prepare("SELECT * FROM awards WHERE id = ?");
            $stmt->execute([$id]);
            $award = $stmt->fetch();
            
            if (!$award) {
                Response::notFound('Award not found');
            }
            
            // Get criteria
            $criteriaStmt = $db->prepare("SELECT * FROM award_criteria WHERE award_id = ? ORDER BY display_order ASC");
            $criteriaStmt->execute([$id]);
            $award['criteria'] = $criteriaStmt->fetchAll();
            
            Response::success('Award retrieved', $award);
        } else {
            // Get all awards
            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
            $offset = ($page - 1) * $limit;
            
            $countStmt = $db->query("SELECT COUNT(*) as total FROM awards");
            $total = $countStmt->fetch()['total'];
            
            $stmt = $db->prepare("SELECT * FROM awards ORDER BY created_at DESC LIMIT ? OFFSET ?");
            $stmt->execute([$limit, $offset]);
            $awards = $stmt->fetchAll();
            
            Response::success('Awards retrieved', [
                'data' => $awards,
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
        // Create award
        $category = $input['awardCategory'] ?? $input['category'] ?? '';
        $description = $input['awardDescription'] ?? $input['description'] ?? '';
        $totalMarks = isset($input['totalMarks']) ? (int)$input['totalMarks'] : 100;
        $criteria = $input['criteria'] ?? [];
        
        if (empty($category)) {
            Response::validationError(['category' => 'Award category is required']);
        }
        
        if (empty($criteria) || !is_array($criteria)) {
            Response::validationError(['criteria' => 'At least one criterion is required']);
        }
        
        // Validate criteria marks sum
        $totalAllocated = 0;
        foreach ($criteria as $criterion) {
            if (empty($criterion['name']) || !isset($criterion['marks'])) {
                Response::validationError(['criteria' => 'All criteria must have a name and marks']);
            }
            $totalAllocated += (int)$criterion['marks'];
        }
        
        if ($totalAllocated != $totalMarks) {
            Response::validationError(['criteria' => 'Total allocated marks must equal award total marks']);
        }
        
        // Generate award number
        $awardNumber = 'Award-' . time();
        
        // Insert award
        $stmt = $db->prepare("INSERT INTO awards (award_number, category, description, total_marks) VALUES (?, ?, ?, ?)");
        $stmt->execute([$awardNumber, $category, $description, $totalMarks]);
        $awardId = $db->lastInsertId();
        
        // Insert criteria
        $criteriaStmt = $db->prepare("INSERT INTO award_criteria (award_id, name, allocated_marks, description, display_order) VALUES (?, ?, ?, ?, ?)");
        foreach ($criteria as $index => $criterion) {
            $criteriaStmt->execute([
                $awardId,
                $criterion['name'],
                (int)$criterion['marks'],
                $criterion['description'] ?? '',
                $index
            ]);
        }
        
        // Get created award with criteria
        $stmt = $db->prepare("SELECT * FROM awards WHERE id = ?");
        $stmt->execute([$awardId]);
        $award = $stmt->fetch();
        
        $criteriaStmt = $db->prepare("SELECT * FROM award_criteria WHERE award_id = ? ORDER BY display_order ASC");
        $criteriaStmt->execute([$awardId]);
        $award['criteria'] = $criteriaStmt->fetchAll();
        
        Response::success('Award created successfully', $award, 201);
        break;
        
    case 'PUT':
        if (!$id) {
            Response::error('Award ID is required');
        }
        
        // Check if exists
        $checkStmt = $db->prepare("SELECT id FROM awards WHERE id = ?");
        $checkStmt->execute([$id]);
        if (!$checkStmt->fetch()) {
            Response::notFound('Award not found');
        }
        
        $category = $input['category'] ?? null;
        $description = $input['description'] ?? null;
        $totalMarks = isset($input['totalMarks']) ? (int)$input['totalMarks'] : null;
        $criteria = $input['criteria'] ?? null;
        
        // Build update query
        $updates = [];
        $params = [];
        
        if ($category !== null) {
            $updates[] = "category = ?";
            $params[] = $category;
        }
        if ($description !== null) {
            $updates[] = "description = ?";
            $params[] = $description;
        }
        if ($totalMarks !== null) {
            $updates[] = "total_marks = ?";
            $params[] = $totalMarks;
        }
        
        if (!empty($updates)) {
            $params[] = $id;
            $sql = "UPDATE awards SET " . implode(', ', $updates) . " WHERE id = ?";
            $stmt = $db->prepare($sql);
            $stmt->execute($params);
        }
        
        // Update criteria if provided
        if ($criteria !== null && is_array($criteria)) {
            // Delete old criteria
            $deleteStmt = $db->prepare("DELETE FROM award_criteria WHERE award_id = ?");
            $deleteStmt->execute([$id]);
            
            // Validate and insert new criteria
            if (!empty($criteria)) {
                $totalAllocated = 0;
                foreach ($criteria as $criterion) {
                    $totalAllocated += (int)($criterion['marks'] ?? 0);
                }
                
                $awardStmt = $db->prepare("SELECT total_marks FROM awards WHERE id = ?");
                $awardStmt->execute([$id]);
                $awardData = $awardStmt->fetch();
                
                if ($totalAllocated != $awardData['total_marks']) {
                    Response::validationError(['criteria' => 'Total allocated marks must equal award total marks']);
                }
                
                // Insert new criteria
                $criteriaStmt = $db->prepare("INSERT INTO award_criteria (award_id, name, allocated_marks, description, display_order) VALUES (?, ?, ?, ?, ?)");
                foreach ($criteria as $index => $criterion) {
                    $criteriaStmt->execute([
                        $id,
                        $criterion['name'],
                        (int)$criterion['marks'],
                        $criterion['description'] ?? '',
                        $index
                    ]);
                }
            }
        }
        
        // Get updated award
        $stmt = $db->prepare("SELECT * FROM awards WHERE id = ?");
        $stmt->execute([$id]);
        $award = $stmt->fetch();
        
        $criteriaStmt = $db->prepare("SELECT * FROM award_criteria WHERE award_id = ? ORDER BY display_order ASC");
        $criteriaStmt->execute([$id]);
        $award['criteria'] = $criteriaStmt->fetchAll();
        
        Response::success('Award updated successfully', $award);
        break;
        
    case 'DELETE':
        if (!$id) {
            Response::error('Award ID is required');
        }
        
        // Check if exists
        $checkStmt = $db->prepare("SELECT id FROM awards WHERE id = ?");
        $checkStmt->execute([$id]);
        if (!$checkStmt->fetch()) {
            Response::notFound('Award not found');
        }
        
        // Delete award (cascade will handle criteria)
        $stmt = $db->prepare("DELETE FROM awards WHERE id = ?");
        $stmt->execute([$id]);
        
        Response::success('Award deleted successfully');
        break;
        
    default:
        Response::error('Method not allowed', null, 405);
}

