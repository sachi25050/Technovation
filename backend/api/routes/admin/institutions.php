<?php
/**
 * Admin - Institutions Management
 */

$db = Database::getInstance()->getConnection();
$user = Auth::getCurrentUser();

if (!$user || $user['role'] !== 'admin') {
    Response::forbidden('Admin access required');
}

$method = $_SERVER['REQUEST_METHOD'];
// Handle both JSON and FormData requests
$rawInput = file_get_contents('php://input');
$input = json_decode($rawInput, true);
// If JSON decode failed or input is empty, try reading from $_POST (FormData)
if (json_last_error() !== JSON_ERROR_NONE || empty($input)) {
    $input = $_POST;
}
$id = isset($_GET['_params'][0]) ? (int)$_GET['_params'][0] : (isset($_GET['params'][0]) ? (int)$_GET['params'][0] : null);

switch ($method) {
    case 'GET':
        if ($id) {
            // Get single institution with awards
            $stmt = $db->prepare("
                SELECT i.*, 
                       GROUP_CONCAT(
                           CONCAT(ia.award_id, ':', ia.marks, ':', a.category)
                           SEPARATOR '||'
                       ) as awards
                FROM institutions i
                LEFT JOIN institution_awards ia ON i.id = ia.institution_id
                LEFT JOIN awards a ON ia.award_id = a.id
                WHERE i.id = ?
                GROUP BY i.id
            ");
            $stmt->execute([$id]);
            $institution = $stmt->fetch();
            
            if (!$institution) {
                Response::notFound('Institution not found');
            }
            
            // Parse awards
            if ($institution['awards']) {
                $awards = [];
                foreach (explode('||', $institution['awards']) as $awardStr) {
                    list($awardId, $marks, $category) = explode(':', $awardStr);
                    $awards[] = [
                        'id' => $awardId,
                        'marks' => $marks,
                        'category' => $category
                    ];
                }
                $institution['awards'] = $awards;
            } else {
                $institution['awards'] = [];
            }
            
            Response::success('Institution retrieved', $institution);
        } else {
            // Get all institutions
            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
            $offset = ($page - 1) * $limit;
            
            $countStmt = $db->query("SELECT COUNT(*) as total FROM institutions");
            $total = $countStmt->fetch()['total'];
            
            $stmt = $db->prepare("SELECT * FROM institutions ORDER BY created_at DESC LIMIT ? OFFSET ?");
            $stmt->execute([$limit, $offset]);
            $institutions = $stmt->fetchAll();
            
            Response::success('Institutions retrieved', [
                'data' => $institutions,
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
        // Create institution
        $name = $input['institutionName'] ?? $input['name'] ?? '';
        $type = $input['type'] ?? 'other';
        $contactPerson = $input['contact_person'] ?? '';
        $contactEmail = $input['contact_email'] ?? $input['email'] ?? '';
        $contactPhone = $input['contact_phone'] ?? '';
        // Handle awardCategories - can be JSON string from FormData or array from JSON
        $awardCategories = $input['awardCategories'] ?? [];
        if (is_string($awardCategories)) {
            $awardCategories = json_decode($awardCategories, true) ?? [];
        }
        
        if (empty($name)) {
            Response::validationError(['name' => 'Institution name is required']);
        }
        
        // Handle image upload
        $imagePath = null;
        $imageUrl = null;
        if (isset($_FILES['instituteImage']) && $_FILES['instituteImage']['error'] === UPLOAD_ERR_OK) {
            try {
                $imageInfo = FileUpload::uploadImage($_FILES['instituteImage'], 'institutions');
                $imagePath = $imageInfo['path'];
                $imageUrl = $imageInfo['url'];
            } catch (Exception $e) {
                Response::error('Image upload failed: ' . $e->getMessage());
            }
        }
        
        // Insert institution - only fields that exist in database
        $stmt = $db->prepare("INSERT INTO institutions (name, image_path, image_url, type, contact_person, contact_email, contact_phone) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$name, $imagePath, $imageUrl, $type, $contactPerson, $contactEmail, $contactPhone]);
        $institutionId = $db->lastInsertId();
        
        // Insert institution awards
        if (!empty($awardCategories) && is_array($awardCategories)) {
            $stmt = $db->prepare("INSERT INTO institution_awards (institution_id, award_id, marks) VALUES (?, ?, ?)");
            foreach ($awardCategories as $award) {
                try {
                    $awardValue = is_array($award) ? $award['value'] : $award;
                    $marks = is_array($award) ? ($award['marks'] ?? 0) : 0;
                    
                    // Try to extract numeric ID from formats like "award-14", "award-6a", etc.
                    $awardId = preg_replace('/[^0-9]/', '', $awardValue);
                    
                    // If we got a number, validate it exists in the database
                    if (!empty($awardId) && is_numeric($awardId)) {
                        $awardId = (int)$awardId;
                        
                        // Verify the award exists in the database
                        $checkStmt = $db->prepare("SELECT id FROM awards WHERE id = ?");
                        $checkStmt->execute([$awardId]);
                        $awardExists = $checkStmt->fetch();
                        
                        if ($awardExists) {
                            // Award exists, insert the relationship
                            $stmt->execute([$institutionId, $awardId, $marks]);
                        } else {
                            // Award doesn't exist, try to find by award_number or category
                            $searchStmt = $db->prepare("SELECT id FROM awards WHERE award_number LIKE ? OR category LIKE ? LIMIT 1");
                            $searchPattern = '%' . str_replace('award-', '', $awardValue) . '%';
                            $searchStmt->execute([$searchPattern, $searchPattern]);
                            $foundAward = $searchStmt->fetch();
                            
                            if ($foundAward) {
                                $stmt->execute([$institutionId, $foundAward['id'], $marks]);
                            } else {
                                // Skip this award if not found - don't fail the entire operation
                                error_log("Warning: Award with value '{$awardValue}' not found in database. Skipping.");
                            }
                        }
                    } else {
                        // If no numeric ID found, try to find award by category or award_number
                        $searchStmt = $db->prepare("SELECT id FROM awards WHERE award_number LIKE ? OR category LIKE ? LIMIT 1");
                        $searchPattern = '%' . $awardValue . '%';
                        $searchStmt->execute([$searchPattern, $searchPattern]);
                        $foundAward = $searchStmt->fetch();
                        
                        if ($foundAward) {
                            $stmt->execute([$institutionId, $foundAward['id'], $marks]);
                        } else {
                            // Skip this award if not found
                            error_log("Warning: Award with value '{$awardValue}' not found in database. Skipping.");
                        }
                    }
                } catch (PDOException $e) {
                    // If foreign key constraint fails, log and continue
                    // Don't fail the entire institution creation
                    error_log("Warning: Failed to link award '{$awardValue}' to institution: " . $e->getMessage());
                    // Continue with next award
                }
            }
        }
        
        // Get created institution
        $stmt = $db->prepare("SELECT * FROM institutions WHERE id = ?");
        $stmt->execute([$institutionId]);
        $institution = $stmt->fetch();
        
        Response::success('Institution created successfully', $institution, 201);
        break;
        
    case 'PUT':
        if (!$id) {
            Response::error('Institution ID is required');
        }
        
        $name = $input['name'] ?? null;
        $type = $input['type'] ?? null;
        $contactPerson = $input['contact_person'] ?? null;
        $contactEmail = $input['contact_email'] ?? null;
        $contactPhone = $input['contact_phone'] ?? null;
        
        // Check if exists
        $checkStmt = $db->prepare("SELECT id FROM institutions WHERE id = ?");
        $checkStmt->execute([$id]);
        if (!$checkStmt->fetch()) {
            Response::notFound('Institution not found');
        }
        
        // Handle image upload if new image provided
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            // Delete old image
            $oldStmt = $db->prepare("SELECT image_path FROM institutions WHERE id = ?");
            $oldStmt->execute([$id]);
            $oldInstitution = $oldStmt->fetch();
            if ($oldInstitution['image_path']) {
                FileUpload::deleteFile(basename($oldInstitution['image_path']), 'institutions');
            }
            
            try {
                $imageInfo = FileUpload::uploadImage($_FILES['image'], 'institutions');
                $imagePath = $imageInfo['path'];
                $imageUrl = $imageInfo['url'];
                
                $stmt = $db->prepare("UPDATE institutions SET image_path = ?, image_url = ? WHERE id = ?");
                $stmt->execute([$imagePath, $imageUrl, $id]);
            } catch (Exception $e) {
                Response::error('Image upload failed: ' . $e->getMessage());
            }
        }
        
        // Build update query
        $updates = [];
        $params = [];
        
        if ($name !== null) {
            $updates[] = "name = ?";
            $params[] = $name;
        }
        if ($type !== null) {
            $updates[] = "type = ?";
            $params[] = $type;
        }
        if ($contactPerson !== null) {
            $updates[] = "contact_person = ?";
            $params[] = $contactPerson;
        }
        if ($contactEmail !== null) {
            $updates[] = "contact_email = ?";
            $params[] = $contactEmail;
        }
        if ($contactPhone !== null) {
            $updates[] = "contact_phone = ?";
            $params[] = $contactPhone;
        }
        
        if (!empty($updates)) {
            $params[] = $id;
            $sql = "UPDATE institutions SET " . implode(', ', $updates) . " WHERE id = ?";
            $stmt = $db->prepare($sql);
            $stmt->execute($params);
        }
        
        // Get updated institution
        $stmt = $db->prepare("SELECT * FROM institutions WHERE id = ?");
        $stmt->execute([$id]);
        $institution = $stmt->fetch();
        
        Response::success('Institution updated successfully', $institution);
        break;
        
    case 'DELETE':
        if (!$id) {
            Response::error('Institution ID is required');
        }
        
        // Check if exists
        $checkStmt = $db->prepare("SELECT image_path FROM institutions WHERE id = ?");
        $checkStmt->execute([$id]);
        $institution = $checkStmt->fetch();
        
        if (!$institution) {
            Response::notFound('Institution not found');
        }
        
        // Delete image if exists
        if ($institution['image_path']) {
            FileUpload::deleteFile(basename($institution['image_path']), 'institutions');
        }
        
        // Delete institution (cascade will handle related records)
        $stmt = $db->prepare("DELETE FROM institutions WHERE id = ?");
        $stmt->execute([$id]);
        
        Response::success('Institution deleted successfully');
        break;
        
    default:
        Response::error('Method not allowed', null, 405);
}

