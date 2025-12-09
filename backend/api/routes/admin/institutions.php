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
                           CONCAT(ia.award_id, ':', ia.marks, ':', a.category, ':', a.award_number)
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
            
            // Parse awards - ensure it's always an array
            $awards = [];
            
            // Check if awards data exists and is not null/empty
            if (!empty($institution['awards']) && $institution['awards'] !== null) {
                $awardStrings = explode('||', $institution['awards']);
                foreach ($awardStrings as $awardStr) {
                    // Skip empty strings
                    if (empty(trim($awardStr))) {
                        continue;
                    }
                    
                    $parts = explode(':', $awardStr);
                    if (count($parts) >= 4) {
                        $awards[] = [
                            'id' => (int)$parts[0],
                            'marks' => (float)$parts[1],
                            'category' => $parts[2],
                            'award_number' => $parts[3]
                        ];
                    } elseif (count($parts) >= 3) {
                        // Backward compatibility for old format
                        $awards[] = [
                            'id' => (int)$parts[0],
                            'marks' => (float)$parts[1],
                            'category' => $parts[2],
                            'award_number' => null
                        ];
                    }
                }
            }
            
            // Always set awards as an array (even if empty)
            $institution['awards'] = $awards;
            
            Response::success('Institution retrieved', $institution);
        } else {
            // Get all institutions with awards
            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
            $offset = ($page - 1) * $limit;
            
            $countStmt = $db->query("SELECT COUNT(*) as total FROM institutions");
            $total = $countStmt->fetch()['total'];
            
            // Get institutions with their awards
            $stmt = $db->prepare("
                SELECT i.*, 
                       GROUP_CONCAT(
                           CONCAT(ia.award_id, ':', ia.marks, ':', a.category, ':', a.award_number)
                           SEPARATOR '||'
                       ) as awards
                FROM institutions i
                LEFT JOIN institution_awards ia ON i.id = ia.institution_id
                LEFT JOIN awards a ON ia.award_id = a.id
                GROUP BY i.id
                ORDER BY i.created_at DESC
                LIMIT ? OFFSET ?
            ");
            $stmt->execute([$limit, $offset]);
            $institutions = $stmt->fetchAll();
            
            // Parse awards for each institution
            foreach ($institutions as &$institution) {
                // Ensure awards is always an array
                $awards = [];
                
                // Check if awards data exists and is not null/empty
                if (!empty($institution['awards']) && $institution['awards'] !== null) {
                    $awardStrings = explode('||', $institution['awards']);
                    foreach ($awardStrings as $awardStr) {
                        // Skip empty strings
                        if (empty(trim($awardStr))) {
                            continue;
                        }
                        
                        $parts = explode(':', $awardStr);
                        if (count($parts) >= 4) {
                            $awards[] = [
                                'id' => (int)$parts[0],
                                'marks' => (float)$parts[1],
                                'category' => $parts[2],
                                'award_number' => $parts[3]
                            ];
                        } elseif (count($parts) >= 3) {
                            // Backward compatibility for old format
                            $awards[] = [
                                'id' => (int)$parts[0],
                                'marks' => (float)$parts[1],
                                'category' => $parts[2],
                                'award_number' => null
                            ];
                        }
                    }
                }
                
                // Always set awards as an array (even if empty)
                $institution['awards'] = $awards;
            }
            unset($institution); // Break reference
            
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
        
        // Debug logging
        error_log("POST /institutions - Received data:");
        error_log("  institutionName: " . ($input['institutionName'] ?? 'N/A'));
        error_log("  email: " . ($input['email'] ?? 'N/A'));
        error_log("  awardCategories type: " . gettype($awardCategories));
        error_log("  awardCategories content: " . json_encode($awardCategories));
        
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
            error_log("Inserting " . count($awardCategories) . " awards for institution ID " . $institutionId);
            $insertStmt = $db->prepare("INSERT INTO institution_awards (institution_id, award_id, marks) VALUES (?, ?, ?)");
            
            foreach ($awardCategories as $award) {
                try {
                    // Extract award information from different formats
                    $awardValue = is_array($award) ? ($award['value'] ?? null) : $award;
                    
                    // Extract and validate marks - ensure it's a numeric value
                    $marksRaw = is_array($award) ? ($award['marks'] ?? null) : null;
                    $marks = 0;
                    if ($marksRaw !== null && $marksRaw !== '') {
                        // Convert to float, default to 0 if invalid
                        $marks = is_numeric($marksRaw) ? (float)$marksRaw : 0;
                        // Ensure marks is non-negative
                        $marks = max(0, $marks);
                    }
                    
                    error_log("  Processing award: value=" . $awardValue . ", marks=" . $marks);
                    
                    if (empty($awardValue)) {
                        error_log("  Skipping empty award value");
                        continue; // Skip empty awards
                    }
                    
                    // Extract award ID from value format "award-{id}"
                    $awardId = null;
                    if (preg_match('/award-(\d+)/i', $awardValue, $matches)) {
                        $awardId = (int)$matches[1];
                        error_log("    Extracted award_id=" . $awardId . " from value");
                    }
                    
                    // Verify the award exists in the database
                    if ($awardId !== null) {
                        $checkStmt = $db->prepare("SELECT id FROM awards WHERE id = ?");
                        $checkStmt->execute([$awardId]);
                        $awardRecord = $checkStmt->fetch();
                        
                        if ($awardRecord) {
                            error_log("    Found award in database: award_id=" . $awardId . ", marks=" . $marks);
                            
                            // Insert into institution_awards table with award_id and marks
                            $insertStmt->execute([$institutionId, $awardId, $marks]);
                            error_log("    Successfully inserted into institution_awards: institution_id=" . $institutionId . ", award_id=" . $awardId . ", marks=" . $marks);
                        } else {
                            error_log("    ERROR: Award ID '{$awardId}' (from '{$awardValue}') not found in database");
                        }
                    } else {
                        error_log("    ERROR: Could not extract award ID from '{$awardValue}'");
                    }
                } catch (PDOException $e) {
                    // Log error but continue with other awards
                    error_log("    ERROR inserting award into institution_awards: " . $e->getMessage());
                }
            }
        } else {
            error_log("No awards to insert or awardCategories is not an array");
        }
        
        // Get created institution with awards
        $stmt = $db->prepare("
            SELECT i.*, 
                   GROUP_CONCAT(
                       CONCAT(ia.award_id, ':', ia.marks, ':', a.category, ':', a.award_number)
                       SEPARATOR '||'
                   ) as awards
            FROM institutions i
            LEFT JOIN institution_awards ia ON i.id = ia.institution_id
            LEFT JOIN awards a ON ia.award_id = a.id
            WHERE i.id = ?
            GROUP BY i.id
        ");
        $stmt->execute([$institutionId]);
        $institution = $stmt->fetch();
        
        if (!$institution) {
            Response::error('Failed to retrieve created institution');
        }
        
        // Parse awards - ensure it's always an array
        $awards = [];
        
        // Check if awards data exists and is not null/empty
        if (!empty($institution['awards']) && $institution['awards'] !== null) {
            $awardStrings = explode('||', $institution['awards']);
            foreach ($awardStrings as $awardStr) {
                // Skip empty strings
                if (empty(trim($awardStr))) {
                    continue;
                }
                
                $parts = explode(':', $awardStr);
                if (count($parts) >= 4) {
                    $awards[] = [
                        'id' => (int)$parts[0],
                        'marks' => (float)$parts[1],
                        'category' => $parts[2],
                        'award_number' => $parts[3]
                    ];
                }
            }
        }
        
        // Always set awards as an array (even if empty)
        $institution['awards'] = $awards;
        
        Response::success('Institution created successfully', $institution, 201);
        break;
        
    case 'PUT':
        if (!$id) {
            Response::error('Institution ID is required');
        }
        
        $name = $input['name'] ?? $input['institutionName'] ?? null;
        $type = $input['type'] ?? null;
        $contactPerson = $input['contact_person'] ?? null;
        $contactEmail = $input['email'] ?? $input['contact_email'] ?? null;
        $contactPhone = $input['contact_phone'] ?? null;
        $awardCategories = $input['awardCategories'] ?? [];
        if (is_string($awardCategories)) {
            $awardCategories = json_decode($awardCategories, true) ?? [];
        }
        
        // Check if exists
        $checkStmt = $db->prepare("SELECT id FROM institutions WHERE id = ?");
        $checkStmt->execute([$id]);
        if (!$checkStmt->fetch()) {
            Response::notFound('Institution not found');
        }
        
        // Handle image upload if new image provided
        // Check for both 'image' and 'instituteImage' field names for compatibility
        $imageFile = null;
        if (isset($_FILES['instituteImage']) && $_FILES['instituteImage']['error'] === UPLOAD_ERR_OK) {
            $imageFile = $_FILES['instituteImage'];
        } elseif (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $imageFile = $_FILES['image'];
        }
        
        if ($imageFile) {
            // Delete old image
            $oldStmt = $db->prepare("SELECT image_path FROM institutions WHERE id = ?");
            $oldStmt->execute([$id]);
            $oldInstitution = $oldStmt->fetch();
            if ($oldInstitution && $oldInstitution['image_path']) {
                FileUpload::deleteFile(basename($oldInstitution['image_path']), 'institutions');
            }
            
            try {
                $imageInfo = FileUpload::uploadImage($imageFile, 'institutions');
                $imagePath = $imageInfo['path'];
                $imageUrl = $imageInfo['url'];
                
                $stmt = $db->prepare("UPDATE institutions SET image_path = ?, image_url = ? WHERE id = ?");
                $stmt->execute([$imagePath, $imageUrl, $id]);
            } catch (Exception $e) {
                Response::error('Image upload failed: ' . $e->getMessage());
            }
        }
        
        // Build update query for institution fields
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
        
        // Update awards if provided
        if (!empty($awardCategories) && is_array($awardCategories)) {
            // Delete old awards
            $deleteStmt = $db->prepare("DELETE FROM institution_awards WHERE institution_id = ?");
            $deleteStmt->execute([$id]);
            
            // Insert new awards
            $insertStmt = $db->prepare("INSERT INTO institution_awards (institution_id, award_id, marks) VALUES (?, ?, ?)");
            
            foreach ($awardCategories as $award) {
                try {
                    $awardValue = is_array($award) ? ($award['value'] ?? null) : $award;
                    
                    // Extract and validate marks - ensure it's a numeric value
                    $marksRaw = is_array($award) ? ($award['marks'] ?? null) : null;
                    $marks = 0;
                    if ($marksRaw !== null && $marksRaw !== '') {
                        // Convert to float, default to 0 if invalid
                        $marks = is_numeric($marksRaw) ? (float)$marksRaw : 0;
                        // Ensure marks is non-negative
                        $marks = max(0, $marks);
                    }
                    
                    if (empty($awardValue)) {
                        continue;
                    }
                    
                    // Extract award ID from value format "award-{id}"
                    $awardId = null;
                    if (preg_match('/award-(\d+)/i', $awardValue, $matches)) {
                        $awardId = (int)$matches[1];
                    }
                    
                    // Verify the award exists in the database
                    if ($awardId !== null) {
                        $checkStmt = $db->prepare("SELECT id FROM awards WHERE id = ?");
                        $checkStmt->execute([$awardId]);
                        $awardRecord = $checkStmt->fetch();
                        
                        if ($awardRecord) {
                            // Insert into institution_awards table with award_id and marks
                            $insertStmt->execute([$id, $awardId, $marks]);
                            error_log("Updated institution_awards: institution_id={$id}, award_id={$awardId}, marks={$marks}");
                        } else {
                            error_log("Warning: Award ID '{$awardId}' (from '{$awardValue}') not found in database during update");
                        }
                    } else {
                        error_log("Warning: Could not extract award ID from '{$awardValue}' during update");
                    }
                } catch (PDOException $e) {
                    error_log("Warning: Failed to link award to institution during update: " . $e->getMessage());
                }
            }
        }
        
        // Get updated institution with awards
        $stmt = $db->prepare("
            SELECT i.*, 
                   GROUP_CONCAT(
                       CONCAT(ia.award_id, ':', ia.marks, ':', a.category, ':', a.award_number)
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
            Response::error('Failed to retrieve updated institution');
        }
        
        // Parse awards - ensure it's always an array
        $awards = [];
        
        // Check if awards data exists and is not null/empty
        if (!empty($institution['awards']) && $institution['awards'] !== null) {
            $awardStrings = explode('||', $institution['awards']);
            foreach ($awardStrings as $awardStr) {
                // Skip empty strings
                if (empty(trim($awardStr))) {
                    continue;
                }
                
                $parts = explode(':', $awardStr);
                if (count($parts) >= 4) {
                    $awards[] = [
                        'id' => (int)$parts[0],
                        'marks' => (float)$parts[1],
                        'category' => $parts[2],
                        'award_number' => $parts[3]
                    ];
                }
            }
        }
        
        // Always set awards as an array (even if empty)
        $institution['awards'] = $awards;
        
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

