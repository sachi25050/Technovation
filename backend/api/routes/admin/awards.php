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
            // Get single award
            $stmt = $db->prepare("SELECT * FROM awards WHERE id = ?");
            $stmt->execute([$id]);
            $award = $stmt->fetch();
            
            if (!$award) {
                Response::notFound('Award not found');
            }
            
            // Get criteria for this award
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
            
            // Get criteria for each award
            foreach ($awards as &$award) {
                $criteriaStmt = $db->prepare("SELECT * FROM award_criteria WHERE award_id = ? ORDER BY display_order ASC");
                $criteriaStmt->execute([$award['id']]);
                $award['criteria'] = $criteriaStmt->fetchAll();
            }
            
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
        $presentationWeightage = isset($input['presentationWeightage']) ? (float)$input['presentationWeightage'] : null;
        $preliminaryWeightage = isset($input['preliminaryWeightage']) ? (float)$input['preliminaryWeightage'] : null;
        $criteria = $input['criteria'] ?? [];
        
        // Validation
        if (empty($category)) {
            Response::validationError(['awardCategory' => 'Award category is required']);
        }
        
        if (empty($description)) {
            Response::validationError(['awardDescription' => 'Award description is required']);
        }
        
        if ($presentationWeightage === null || $presentationWeightage < 0) {
            Response::validationError(['presentationWeightage' => 'Presentation weightage is required and must be a positive number']);
        }
        
        if ($preliminaryWeightage === null || $preliminaryWeightage < 0) {
            Response::validationError(['preliminaryWeightage' => 'Preliminary weightage is required and must be a positive number']);
        }
        
        // Validate criteria
        if (empty($criteria) || !is_array($criteria)) {
            Response::validationError(['criteria' => 'At least one evaluation criterion is required']);
        }
        
        // Validate each criterion
        $validCriteria = [];
        foreach ($criteria as $index => $criterion) {
            if (empty($criterion['name']) || trim($criterion['name']) === '') {
                Response::validationError(['criteria' => "Criterion #" . ($index + 1) . " name is required"]);
            }
            
            $marks = isset($criterion['allocated_marks']) ? (float)$criterion['allocated_marks'] : (isset($criterion['marks']) ? (float)$criterion['marks'] : null);
            if ($marks === null || $marks <= 0) {
                Response::validationError(['criteria' => "Criterion #" . ($index + 1) . " allocated marks must be a positive number"]);
            }
            
            $validCriteria[] = [
                'name' => trim($criterion['name']),
                'allocated_marks' => $marks,
                'description' => isset($criterion['description']) ? trim($criterion['description']) : null
            ];
        }
       
        
        // Start transaction
        $db->beginTransaction();
        
        try {
            // Generate award number
            $awardNumber = 'Award-' . time();
            
            // Insert award
            $stmt = $db->prepare("INSERT INTO awards (award_number, category, description, presentation_weightage, preliminary_weightage) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$awardNumber, $category, $description, $presentationWeightage, $preliminaryWeightage]);
            $awardId = $db->lastInsertId();
            
            // Insert criteria
            $criteriaStmt = $db->prepare("INSERT INTO award_criteria (award_id, name, allocated_marks, description, display_order) VALUES (?, ?, ?, ?, ?)");
            foreach ($validCriteria as $index => $criterion) {
                $displayOrder = $index + 1;
                $criteriaStmt->execute([
                    $awardId,
                    $criterion['name'],
                    $criterion['allocated_marks'],
                    $criterion['description'],
                    $displayOrder
                ]);
            }
            
            // Commit transaction
            $db->commit();
            
            // Get created award with criteria
            $stmt = $db->prepare("SELECT * FROM awards WHERE id = ?");
            $stmt->execute([$awardId]);
            $award = $stmt->fetch();
            
            // Get criteria
            $criteriaStmt = $db->prepare("SELECT * FROM award_criteria WHERE award_id = ? ORDER BY display_order ASC");
            $criteriaStmt->execute([$awardId]);
            $award['criteria'] = $criteriaStmt->fetchAll();
            
            Response::success('Award created successfully', $award, 201);
        } catch (Exception $e) {
            // Rollback transaction on error
            $db->rollBack();
            Response::error('Failed to create award: ' . $e->getMessage(), null, 500);
        }
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
        
        $category = $input['awardCategory'] ?? $input['category'] ?? null;
        $description = $input['awardDescription'] ?? $input['description'] ?? null;
        $presentationWeightage = isset($input['presentationWeightage']) ? (float)$input['presentationWeightage'] : null;
        $preliminaryWeightage = isset($input['preliminaryWeightage']) ? (float)$input['preliminaryWeightage'] : null;
        
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
        if ($presentationWeightage !== null) {
            $updates[] = "presentation_weightage = ?";
            $params[] = $presentationWeightage;
        }
        if ($preliminaryWeightage !== null) {
            $updates[] = "preliminary_weightage = ?";
            $params[] = $preliminaryWeightage;
        }
        
        // Validate weightages if both are being updated
        if ($presentationWeightage !== null && $preliminaryWeightage !== null) {
            $totalWeightage = $presentationWeightage + $preliminaryWeightage;
            if (abs($totalWeightage - 100) > 0.01) {
                Response::validationError(['weightage' => 'Presentation and Preliminary weightage must sum to 100']);
            }
        } elseif ($presentationWeightage !== null || $preliminaryWeightage !== null) {
            // If only one is being updated, check against existing value
            $existingStmt = $db->prepare("SELECT presentation_weightage, preliminary_weightage FROM awards WHERE id = ?");
            $existingStmt->execute([$id]);
            $existing = $existingStmt->fetch();
            
            $presWeight = $presentationWeightage !== null ? $presentationWeightage : $existing['presentation_weightage'];
            $prelimWeight = $preliminaryWeightage !== null ? $preliminaryWeightage : $existing['preliminary_weightage'];
            
            $totalWeightage = $presWeight + $prelimWeight;
            if (abs($totalWeightage - 100) > 0.01) {
                Response::validationError(['weightage' => 'Presentation and Preliminary weightage must sum to 100']);
            }
        }
        
        if (!empty($updates)) {
            $params[] = $id;
            $sql = "UPDATE awards SET " . implode(', ', $updates) . " WHERE id = ?";
            $stmt = $db->prepare($sql);
            $stmt->execute($params);
        }
        
        // Get updated award
        $stmt = $db->prepare("SELECT * FROM awards WHERE id = ?");
        $stmt->execute([$id]);
        $award = $stmt->fetch();
        
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

