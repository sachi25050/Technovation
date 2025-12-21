<?php
/**
 * Reporter - Reports Management
 * Generates Award-wise and Bank-wise Excel reports matching the specification format
 */

$db = Database::getInstance()->getConnection();
$user = Auth::getCurrentUser();

if (!$user || $user['role'] !== 'reporter') {
    Response::forbidden('Reporter access required');
}

// Include the Excel Report Generator
require_once __DIR__ . '/../../../core/ExcelReportGenerator.php';

$method = $_SERVER['REQUEST_METHOD'];
$input = json_decode(file_get_contents('php://input'), true);
$id = isset($_GET['_params'][0]) ? (int)$_GET['_params'][0] : (isset($_GET['params'][0]) ? (int)$_GET['params'][0] : null);
$action = isset($_GET['_params'][1]) ? $_GET['_params'][1] : (isset($_GET['params'][1]) ? $_GET['params'][1] : null);

// Handle download action
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
    
    // Determine content type based on format
    $contentType = 'application/octet-stream';
    if ($report['report_format'] === 'excel') {
        $contentType = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
    } elseif ($report['report_format'] === 'pdf') {
        $contentType = 'application/pdf';
    } elseif ($report['report_format'] === 'csv') {
        $contentType = 'text/csv';
    }
    
    // Send file
    header('Content-Type: ' . $contentType);
    header('Content-Disposition: attachment; filename="' . basename($report['file_path']) . '"');
    header('Content-Length: ' . filesize($report['file_path']));
    header('Cache-Control: no-cache, must-revalidate');
    header('Pragma: public');
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
        
        // Update status to generating
        $stmt = $db->prepare("UPDATE reports SET status = 'generating' WHERE id = ?");
        $stmt->execute([$reportId]);
        
        try {
            $config = require __DIR__ . '/../../../config/config.php';
            $reportsDir = __DIR__ . '/../../../uploads/reports';
            
            if (!is_dir($reportsDir)) {
                mkdir($reportsDir, 0755, true);
            }
            
            $fileResult = null;
            
            // Generate report based on format and type
            if ($reportFormat === 'excel') {
                // Use the Excel Report Generator
                $generator = new ExcelReportGenerator($db);
                
                if ($reportType === 'award-marks') {
                    $fileResult = $generator->generateAwardWiseReport([
                        'award_id' => !empty($selectedAwards) ? $selectedAwards[0] : null
                    ]);
                } elseif ($reportType === 'institution-performance') {
                    $fileResult = $generator->generateBankWiseReport($filters);
                } else {
                    // Default to award-wise
                    $fileResult = $generator->generateAwardWiseReport($filters);
                }
                
                $filename = $fileResult['filename'];
                $filePath = $fileResult['filepath'];
            } else {
                // For CSV and PDF, generate simplified content
                $filename = 'report_' . $reportId . '_' . time() . '.' . $reportFormat;
                $filePath = $reportsDir . '/' . $filename;
                
                if ($reportFormat === 'csv') {
                    $content = generateCSVContent($reportType, $filters, $selectedAwards, $db);
                    file_put_contents($filePath, $content);
                } else {
                    // PDF - for now generate a text placeholder
                    // In production, use TCPDF or FPDF
                    $content = generateTextContent($reportType, $filters, $selectedAwards, $db);
                    file_put_contents($filePath, $content);
                }
            }
            
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
            
        } catch (Exception $e) {
            // Update report status to failed
            $stmt = $db->prepare("UPDATE reports SET status = 'failed' WHERE id = ?");
            $stmt->execute([$reportId]);
            
            Response::error('Failed to generate report: ' . $e->getMessage(), null, 500);
        }
        break;
        
    default:
        Response::error('Method not allowed', null, 405);
}

/**
 * Generate CSV content for reports
 */
function generateCSVContent($reportType, $filters, $selectedAwards, $db) {
    $output = fopen('php://temp', 'r+');
    
    // Header row
    fputcsv($output, ['LANKAPAY TECHNNOVATION AWARDS 2025']);
    fputcsv($output, ['Generated: ' . date('Y-m-d H:i:s')]);
    fputcsv($output, []);
    
    if ($reportType === 'award-marks') {
        fputcsv($output, ['Award', 'Category', 'Institution', 'Quantitative (70%)', 'Qualitative (30%)', 'Total (100%)']);
        
        // Get evaluation data
        $stmt = $db->query("
            SELECT 
                a.award_number,
                a.category as award_name,
                i.name as institution_name,
                ia.marks as quantitative_score,
                AVG(e.presentation_score) as avg_presentation,
                (ia.marks * 0.7 + AVG(e.presentation_score) * 0.3) as total_score
            FROM institutions i
            JOIN institution_awards ia ON i.id = ia.institution_id
            JOIN awards a ON ia.award_id = a.id
            LEFT JOIN evaluations e ON i.id = e.institution_id AND e.award_id = a.id AND e.status = 'submitted'
            GROUP BY a.id, i.id
            ORDER BY a.award_number, total_score DESC
        ");
        
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            fputcsv($output, [
                'Award No. ' . $row['award_number'],
                $row['award_name'],
                $row['institution_name'],
                round($row['quantitative_score'] * 0.7, 2),
                round($row['avg_presentation'] * 0.3, 2),
                round($row['total_score'], 2)
            ]);
        }
    } else {
        // Bank-wise report
        fputcsv($output, ['Bank Name', 'Awards Participated', 'Average Score', 'Highest Score']);
        
        $stmt = $db->query("
            SELECT 
                i.name,
                COUNT(DISTINCT e.award_id) as awards_count,
                AVG(e.aggregated_score) as avg_score,
                MAX(e.aggregated_score) as max_score
            FROM institutions i
            LEFT JOIN evaluations e ON i.id = e.institution_id AND e.status = 'submitted'
            WHERE i.status = 'active'
            GROUP BY i.id
            ORDER BY avg_score DESC
        ");
        
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            fputcsv($output, [
                $row['name'],
                $row['awards_count'] ?? 0,
                round($row['avg_score'] ?? 0, 2),
                round($row['max_score'] ?? 0, 2)
            ]);
        }
    }
    
    rewind($output);
    $content = stream_get_contents($output);
    fclose($output);
    
    return $content;
}

/**
 * Generate text content for PDF placeholder
 */
function generateTextContent($reportType, $filters, $selectedAwards, $db) {
    $content = "LANKAPAY TECHNNOVATION AWARDS 2025\n";
    $content .= "================================\n\n";
    $content .= "Report Type: " . ucfirst(str_replace('-', ' ', $reportType)) . "\n";
    $content .= "Generated: " . date('Y-m-d H:i:s') . "\n\n";
    
    if ($reportType === 'award-marks') {
        $content .= "AWARD-WISE MARKING SCHEME\n";
        $content .= "--------------------------\n\n";
        
        $stmt = $db->query("
            SELECT 
                a.award_number,
                a.category as award_name,
                i.name as institution_name,
                e.aggregated_score
            FROM evaluations e
            JOIN institutions i ON e.institution_id = i.id
            JOIN awards a ON e.award_id = a.id
            WHERE e.status = 'submitted'
            ORDER BY a.award_number, e.aggregated_score DESC
        ");
        
        $currentAward = '';
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            if ($currentAward !== $row['award_number']) {
                $content .= "\nAward No. " . $row['award_number'] . " - " . $row['award_name'] . "\n";
                $currentAward = $row['award_number'];
            }
            $content .= "  - " . $row['institution_name'] . ": " . round($row['aggregated_score'], 2) . "\n";
        }
    } else {
        $content .= "BANK-WISE PERFORMANCE REPORT\n";
        $content .= "-----------------------------\n\n";
        
        $stmt = $db->query("
            SELECT 
                i.name,
                COUNT(DISTINCT e.award_id) as awards_count,
                AVG(e.aggregated_score) as avg_score
            FROM institutions i
            LEFT JOIN evaluations e ON i.id = e.institution_id AND e.status = 'submitted'
            WHERE i.status = 'active'
            GROUP BY i.id
            ORDER BY avg_score DESC
        ");
        
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $content .= $row['name'] . "\n";
            $content .= "  Awards: " . ($row['awards_count'] ?? 0) . ", Average: " . round($row['avg_score'] ?? 0, 2) . "\n\n";
        }
    }
    
    return $content;
}
