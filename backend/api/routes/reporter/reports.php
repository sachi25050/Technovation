<?php
/**
 * Reporter - Reports Management
 */

$db = Database::getInstance()->getConnection();
$user = Auth::getCurrentUser();

if (!$user || $user['role'] !== 'reporter') {
    Response::forbidden('Reporter access required');
}

$method = $_SERVER['REQUEST_METHOD'];
$input = json_decode(file_get_contents('php://input'), true);
$id = isset($_GET['_params'][0]) ? (int)$_GET['_params'][0] : (isset($_GET['params'][0]) ? (int)$_GET['params'][0] : null);
$action = isset($_GET['_params'][1]) ? $_GET['_params'][1] : (isset($_GET['params'][1]) ? $_GET['params'][1] : null);

// Handle download
if ($action === 'download' && $method === 'GET') {
    if (!$id) {
        Response::error('Report ID is required');
    }
    
    $stmt = $db->prepare("SELECT * FROM reports WHERE id = ? AND reporter_id = ?");
    $stmt->execute([$id, $user['user_id']]);
    $report = $stmt->fetch();
    
    if (!$report) {
        Response::notFound('Report not found');
    }
    
    if ($report['status'] !== 'completed' || !file_exists($report['file_path'])) {
        Response::error('Report file not available', null, 404);
    }
    
    // Send file
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . basename($report['file_path']) . '"');
    header('Content-Length: ' . filesize($report['file_path']));
    readfile($report['file_path']);
    exit;
}

switch ($method) {
    case 'GET':
        if ($id) {
            // Get single report
            $stmt = $db->prepare("SELECT * FROM reports WHERE id = ? AND reporter_id = ?");
            $stmt->execute([$id, $user['user_id']]);
            $report = $stmt->fetch();
            
            if (!$report) {
                Response::notFound('Report not found');
            }
            
            // Decode filters JSON
            if ($report['filters']) {
                $report['filters'] = json_decode($report['filters'], true);
            }
            
            Response::success('Report retrieved', $report);
        } else {
            // Get all reports for current reporter
            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
            $offset = ($page - 1) * $limit;
            
            $countStmt = $db->prepare("SELECT COUNT(*) as total FROM reports WHERE reporter_id = ?");
            $countStmt->execute([$user['user_id']]);
            $total = $countStmt->fetch()['total'];
            
            $stmt = $db->prepare("SELECT * FROM reports WHERE reporter_id = ? ORDER BY created_at DESC LIMIT ? OFFSET ?");
            $stmt->execute([$user['user_id'], $limit, $offset]);
            $reports = $stmt->fetchAll();
            
            // Decode filters for each report
            foreach ($reports as &$report) {
                if ($report['filters']) {
                    $report['filters'] = json_decode($report['filters'], true);
                }
            }
            
            Response::success('Reports retrieved', [
                'data' => $reports,
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
        // Generate report
        $reportType = $input['reportType'] ?? $input['report_type'] ?? '';
        $reportFormat = $input['reportFormat'] ?? $input['report_format'] ?? '';
        $title = $input['reportTitle'] ?? $input['title'] ?? '';
        $filters = $input['filters'] ?? [];
        $selectedAwards = $input['selectedAwards'] ?? $input['selected_awards'] ?? [];
        
        if (empty($reportType) || empty($reportFormat)) {
            Response::validationError([
                'report_type' => empty($reportType) ? 'Report type is required' : null,
                'report_format' => empty($reportFormat) ? 'Report format is required' : null
            ]);
        }
        
        if (!in_array($reportFormat, ['pdf', 'excel', 'csv'])) {
            Response::validationError(['report_format' => 'Invalid report format']);
        }
        
        // Create report record
        $stmt = $db->prepare("INSERT INTO reports (reporter_id, report_type, report_format, title, filters, status) VALUES (?, ?, ?, ?, ?, 'pending')");
        $stmt->execute([
            $user['user_id'],
            $reportType,
            $reportFormat,
            $title ?: 'Report ' . date('Y-m-d H:i:s'),
            json_encode($filters)
        ]);
        $reportId = $db->lastInsertId();
        
        // For now, we'll just mark it as generating
        // In a real application, you'd have a background job/queue system to generate the actual file
        $stmt = $db->prepare("UPDATE reports SET status = 'generating' WHERE id = ?");
        $stmt->execute([$reportId]);
        
        // Simulate report generation (in production, this would be done asynchronously)
        // For demonstration, we'll create a simple text file
        $reportsDir = __DIR__ . '/../../../uploads/reports';
        if (!is_dir($reportsDir)) {
            mkdir($reportsDir, 0755, true);
        }
        
        $filename = 'report_' . $reportId . '_' . time() . '.' . $reportFormat;
        $filePath = $reportsDir . '/' . $filename;
        
        // Generate report content based on type
        $content = generateReportContent($reportType, $filters, $selectedAwards, $db);
        
        // Write file based on format
        switch ($reportFormat) {
            case 'csv':
                file_put_contents($filePath, $content);
                break;
            case 'excel':
                // In production, use a library like PhpSpreadsheet
                file_put_contents($filePath, $content);
                break;
            case 'pdf':
                // In production, use a library like TCPDF or FPDF
                file_put_contents($filePath, $content);
                break;
        }
        
        $config = require __DIR__ . '/../../../config/config.php';
        $fileUrl = $config['upload_url'] . 'reports/' . $filename;
        
        // Update report with file info
        $stmt = $db->prepare("UPDATE reports SET file_path = ?, file_url = ?, status = 'completed', generated_at = NOW() WHERE id = ?");
        $stmt->execute([$filePath, $fileUrl, $reportId]);
        
        // Get created report
        $stmt = $db->prepare("SELECT * FROM reports WHERE id = ?");
        $stmt->execute([$reportId]);
        $report = $stmt->fetch();
        
        if ($report['filters']) {
            $report['filters'] = json_decode($report['filters'], true);
        }
        
        Response::success('Report generated successfully', $report, 201);
        break;
        
    default:
        Response::error('Method not allowed', null, 405);
}

/**
 * Generate report content (simplified version)
 */
function generateReportContent($reportType, $filters, $selectedAwards, $db) {
    $content = "Technovation e-Judging System Report\n";
    $content .= "Report Type: $reportType\n";
    $content .= "Generated: " . date('Y-m-d H:i:s') . "\n\n";
    
    switch ($reportType) {
        case 'award-marks':
            $content .= "Award Marks Report\n";
            $content .= "==================\n\n";
            
            // Get evaluation data
            $where = "e.status = 'submitted'";
            $params = [];
            
            if (!empty($filters['institution_id'])) {
                $where .= " AND e.institution_id = ?";
                $params[] = $filters['institution_id'];
            }
            
            if (!empty($selectedAwards)) {
                $placeholders = implode(',', array_fill(0, count($selectedAwards), '?'));
                $where .= " AND e.award_id IN ($placeholders)";
                $params = array_merge($params, $selectedAwards);
            }
            
            $stmt = $db->prepare("
                SELECT e.*, 
                       i.name as institution_name,
                       a.category as award_category
                FROM evaluations e
                JOIN institutions i ON e.institution_id = i.id
                JOIN awards a ON e.award_id = a.id
                WHERE $where
                ORDER BY e.total_marks DESC
            ");
            $stmt->execute($params);
            $evaluations = $stmt->fetchAll();
            
            foreach ($evaluations as $eval) {
                $content .= sprintf(
                    "%s - %s: %d marks (%.2f%%)\n",
                    $eval['institution_name'],
                    $eval['award_category'],
                    $eval['total_marks'],
                    $eval['percentage']
                );
            }
            break;
            
        case 'institution-performance':
            $content .= "Institution Performance Report\n";
            $content .= "==============================\n\n";
            
            $stmt = $db->query("
                SELECT i.name, 
                       COUNT(e.id) as total_evaluations,
                       AVG(e.percentage) as avg_percentage,
                       MAX(e.total_marks) as max_marks
                FROM institutions i
                LEFT JOIN evaluations e ON i.id = e.institution_id AND e.status = 'submitted'
                GROUP BY i.id
                ORDER BY avg_percentage DESC
            ");
            $performance = $stmt->fetchAll();
            
            foreach ($performance as $perf) {
                $content .= sprintf(
                    "%s: %.2f%% average (%.0f evaluations)\n",
                    $perf['name'],
                    $perf['avg_percentage'] ?? 0,
                    $perf['total_evaluations'] ?? 0
                );
            }
            break;
            
        default:
            $content .= "Report content for: $reportType\n";
    }
    
    return $content;
}

