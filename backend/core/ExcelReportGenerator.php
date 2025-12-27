<?php
/**
 * Excel Report Generator for LankaPay Technnovation Awards
 * Generates Award-wise Marking Scheme reports matching the specification format
 */

require_once __DIR__ . '/../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Font;
use PhpOffice\PhpSpreadsheet\Style\Color;

class ExcelReportGenerator
{
    private $spreadsheet;
    private $sheet;
    private $db;
    private $currentRow = 1;
    
    // Column colors for judges (light pastel colors with black text)
    private $judgeColors = [
        'FFCDD2', // Light Red
        'FFE0B2', // Light Orange
        'BBDEFB', // Light Blue
        'FFF9C4', // Light Yellow
        'C8E6C9', // Light Green
        'B2DFDB', // Light Teal
        'F8BBD9', // Light Pink
        'E1BEE7', // Light Purple
    ];
    
    public function __construct($db)
    {
        $this->db = $db;
        $this->spreadsheet = new Spreadsheet();
        $this->sheet = $this->spreadsheet->getActiveSheet();
    }
    
    /**
     * Generate Award-wise Report
     * @param array $filters Optional filters (award_id, institution_id, etc.)
     * @return string Path to generated file
     */
    public function generateAwardWiseReport($filters = [])
    {
        // Set document properties
        $this->spreadsheet->getProperties()
            ->setCreator('LankaPay Technnovation Awards')
            ->setTitle('Award-wise Marking Scheme')
            ->setSubject('LANKAPAY TECHNNOVATION AWARDS 2025')
            ->setDescription('Award-wise marking scheme report');
        
        // Get judges list
        $judges = $this->getJudges();
        
        // Get all awards with evaluations
        $awards = $this->getAwardsWithEvaluations($filters);
        
        // Create header section
        $this->createReportHeader($judges);
        
        // Process each award
        foreach ($awards as $award) {
            $this->createAwardSection($award, $judges);
        }
        
        // Auto-size columns
        $this->autoSizeColumns();
        
        // Generate file
        $filename = 'Award_Wise_Report_' . date('Ymd_His') . '.xlsx';
        $filepath = __DIR__ . '/../uploads/reports/' . $filename;
        
        // Ensure directory exists
        if (!is_dir(dirname($filepath))) {
            mkdir(dirname($filepath), 0755, true);
        }
        
        $writer = new Xlsx($this->spreadsheet);
        $writer->save($filepath);
        
        return [
            'filename' => $filename,
            'filepath' => $filepath
        ];
    }
    
    /**
     * Get all judges from the system
     */
    private function getJudges()
    {
        $stmt = $this->db->query("
            SELECT id, 
                   CONCAT(COALESCE(title, ''), ' ', COALESCE(first_name, ''), ' ', COALESCE(last_name, '')) as name,
                   username
            FROM users 
            WHERE role = 'judger' AND status = 'active'
            ORDER BY first_name, last_name
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get awards with all evaluations
     */
    private function getAwardsWithEvaluations($filters = [])
    {
        $where = "1=1";
        $params = [];
        
        if (!empty($filters['award_id'])) {
            $where .= " AND a.id = ?";
            $params[] = $filters['award_id'];
        }
        
        $stmt = $this->db->prepare("
            SELECT a.id, a.award_number, a.category, a.description,
                   a.presentation_weightage, a.preliminary_weightage
            FROM awards a
            WHERE a.status = 'active' AND $where
            ORDER BY a.award_number
        ");
        $stmt->execute($params);
        $awards = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Get evaluations for each award
        foreach ($awards as &$award) {
            $award['institutions'] = $this->getInstitutionScoresForAward($award['id']);
        }
        
        return $awards;
    }
    
    /**
     * Get institution scores for a specific award
     * Includes ALL institutions linked to the award, even without judge evaluations
     */
    private function getInstitutionScoresForAward($awardId)
    {
        // Get ALL institutions linked to this award (whether evaluated or not)
        $stmt = $this->db->prepare("
            SELECT DISTINCT 
                i.id,
                i.name,
                ia.marks as quantitative_score,
                COALESCE(ia.category, 'A') as category
            FROM institutions i
            INNER JOIN institution_awards ia ON i.id = ia.institution_id AND ia.award_id = ?
            WHERE i.status = 'active'
            ORDER BY category, i.name
        ");
        $stmt->execute([$awardId]);
        $institutions = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Get total number of judges in the system (for average calculation)
        $totalJudges = $this->getTotalJudgesCount();
        
        // Get judge scores for each institution (may be empty if not evaluated)
        foreach ($institutions as &$inst) {
            $inst['judge_scores'] = $this->getJudgeScoresForInstitution($inst['id'], $awardId);
            $inst['calculated'] = $this->calculateScores($inst, $awardId, $totalJudges);
        }
        
        return $institutions;
    }
    
    /**
     * Get total number of active judges in the system
     */
    private function getTotalJudgesCount()
    {
        $stmt = $this->db->query("SELECT COUNT(*) as count FROM users WHERE role = 'judger' AND status = 'active'");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return intval($result['count'] ?? 1);
    }
    
    /**
     * Get individual judge scores for an institution
     * Also calculates actual percentage from criteria marks if needed
     */
    private function getJudgeScoresForInstitution($institutionId, $awardId)
    {
        $stmt = $this->db->prepare("
            SELECT 
                e.id as evaluation_id,
                e.judge_id,
                u.id as user_id,
                CONCAT(COALESCE(u.title, ''), ' ', COALESCE(u.first_name, ''), ' ', COALESCE(u.last_name, '')) as judge_name,
                e.presentation_score,
                e.total_achieved_marks,
                e.total_allocated_marks,
                e.percentage,
                e.status
            FROM evaluations e
            JOIN users u ON e.judge_id = u.id
            WHERE e.institution_id = ? AND e.award_id = ? AND e.status = 'submitted'
            ORDER BY u.first_name
        ");
        $stmt->execute([$institutionId, $awardId]);
        $scores = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // For each score, ensure we have a valid percentage
        // If percentage is 0 or invalid, calculate from criteria marks
        foreach ($scores as &$score) {
            $percentage = floatval($score['percentage'] ?? 0);
            $achieved = floatval($score['total_achieved_marks'] ?? 0);
            $allocated = floatval($score['total_allocated_marks'] ?? 0);
            
            // If percentage is 0 but we have marks, calculate it
            if ($percentage == 0 && $achieved > 0) {
                if ($allocated > 0) {
                    $percentage = ($achieved / $allocated) * 100;
                } else {
                    // Try to get allocated from criteria marks table
                    $criteriaStmt = $this->db->prepare("
                        SELECT SUM(allocated_marks) as total_allocated, SUM(achieved_marks) as total_achieved
                        FROM evaluation_criteria_marks 
                        WHERE evaluation_id = ?
                    ");
                    $criteriaStmt->execute([$score['evaluation_id']]);
                    $criteria = $criteriaStmt->fetch(PDO::FETCH_ASSOC);
                    
                    if ($criteria && floatval($criteria['total_allocated']) > 0) {
                        $allocated = floatval($criteria['total_allocated']);
                        $achieved = floatval($criteria['total_achieved']);
                        $percentage = ($achieved / $allocated) * 100;
                    } else {
                        // Last resort: assume achieved marks ARE the percentage
                        $percentage = $achieved;
                    }
                }
            }
            
            // Store the calculated percentage
            $score['calculated_percentage'] = round($percentage, 2);
        }
        
        return $scores;
    }
    
    /**
     * Calculate scores following the specification formula
     * - Preliminary marks are STATIC (already the final weighted value entered by admin)
     * - Average Judge Score = Sum(All Judge Percentages) / TOTAL Number of Judges
     *   IMPORTANT: Absent judges (Ab) count as 0 and are INCLUDED in the divisor
     * - Qualitative Weight = Average Percentage × presentation_weightage%
     * - Total = Preliminary (static) + Qualitative (weighted)
     * 
     * @param array $institution Institution data with judge_scores
     * @param int $awardId Award ID
     * @param int $totalJudges Total number of judges in the system (including absent)
     */
    private function calculateScores($institution, $awardId, $totalJudges = 1)
    {
        $judgeScores = $institution['judge_scores'];
        // Preliminary marks are STATIC - use as entered (already the 70% weighted value)
        $preliminaryMarks = floatval($institution['quantitative_score'] ?? 0);
        
        // Get award weightages
        $stmt = $this->db->prepare("SELECT presentation_weightage, preliminary_weightage FROM awards WHERE id = ?");
        $stmt->execute([$awardId]);
        $award = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $presentationWeight = floatval($award['presentation_weightage'] ?? 30) / 100;
        
        // Sum up all judge percentages (judges who submitted)
        // Absent judges are treated as 0
        $sumOfPercentages = 0;
        $submittedCount = 0;
        foreach ($judgeScores as $score) {
            // Check if judge was present (has a valid score)
            if ($score['status'] === 'submitted' && $score['total_achieved_marks'] !== null) {
                // Use the calculated percentage (handles all edge cases)
                $judgePercentage = floatval($score['calculated_percentage'] ?? $score['percentage'] ?? 0);
                $sumOfPercentages += $judgePercentage;
                $submittedCount++;
            }
            // Absent judges contribute 0 to the sum (implicitly)
        }
        
        // Calculate average percentage across ALL judges (including absent as 0)
        // Divide by TOTAL number of judges, not just those who submitted
        $averageJudgePercentage = 0;
        if ($totalJudges > 0) {
            $averageJudgePercentage = $sumOfPercentages / $totalJudges;
        }
        
        // Calculate weighted qualitative score (presentation)
        // Average judge percentage × presentation weightage (e.g., 30%)
        // Formula: (Sum / TotalJudges) × weight%
        // Example: (94 / 5) × 30% = 18.8 × 0.30 = 5.64
        $qualitativeWeighted = $averageJudgePercentage * $presentationWeight;
        
        // Preliminary marks are STATIC - do NOT multiply by weight again
        // They're already the final 70% value as entered by admin
        $quantitativeWeighted = $preliminaryMarks;
        
        // Total score = Static Preliminary + Weighted Presentation
        $totalScore = $quantitativeWeighted + $qualitativeWeighted;
        
        return [
            'quantitative_raw' => $preliminaryMarks,
            'quantitative_weighted' => $quantitativeWeighted,  // Same as raw (static)
            'average_judge_score' => $averageJudgePercentage,  // Average percentage (sum / total judges)
            'qualitative_weighted' => $qualitativeWeighted,
            'total_score' => $totalScore,
            'submitted_judges_count' => $submittedCount,
            'total_judges_count' => $totalJudges,
            'presentation_weight' => $presentationWeight
        ];
    }
    
    /**
     * Create the main report header
     */
    private function createReportHeader($judges)
    {
        // Title row - Light Blue theme
        $this->sheet->setCellValue('A1', 'LANKAPAY TECHNNOVATION AWARDS 2025');
        $this->sheet->mergeCells('A1:' . $this->getColumnLetter(5 + count($judges)) . '1');
        $this->sheet->getStyle('A1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 14, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1565C0']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
        ]);
        
        // Subtitle row - Light Blue theme
        $this->sheet->setCellValue('A2', 'AWARD-WISE MARKING SCHEME');
        $this->sheet->mergeCells('A2:' . $this->getColumnLetter(5 + count($judges)) . '2');
        $this->sheet->getStyle('A2')->applyFromArray([
            'font' => ['bold' => true, 'size' => 12, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1976D2']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
        ]);
        
        $this->currentRow = 4;
    }
    
    /**
     * Create a section for each award
     */
    private function createAwardSection($award, $judges)
    {
        // Get the actual weightages for this award
        $preliminaryWeightage = intval($award['preliminary_weightage'] ?? 70);
        $presentationWeightage = intval($award['presentation_weightage'] ?? 30);
        
        // Award title row - Light Blue theme
        $awardTitle = "Award No. " . $award['award_number'] . " - " . $award['category'];
        $this->sheet->setCellValue('A' . $this->currentRow, $awardTitle);
        $this->sheet->mergeCells('A' . $this->currentRow . ':' . $this->getColumnLetter(5 + count($judges)) . $this->currentRow);
        $this->sheet->getStyle('A' . $this->currentRow)->applyFromArray([
            'font' => ['bold' => true, 'size' => 11],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E3F2FD']],
        ]);
        $this->currentRow++;
        
        // Column headers row - Use DYNAMIC weightages from the award
        $headerRow = $this->currentRow;
        $this->sheet->setCellValue('A' . $headerRow, 'Awards Category');
        $this->sheet->setCellValue('B' . $headerRow, 'Name');
        $this->sheet->setCellValue('C' . $headerRow, $preliminaryWeightage . '%');
        $this->sheet->setCellValue('D' . $headerRow, $presentationWeightage . '%');
        $this->sheet->setCellValue('E' . $headerRow, '100%');
        
        // Judge columns
        $col = 6;
        foreach ($judges as $index => $judge) {
            $colLetter = $this->getColumnLetter($col);
            $judgeName = trim($judge['name']) ?: $judge['username'];
            $this->sheet->setCellValue($colLetter . $headerRow, $judgeName);
            
            // Apply light color to judge column header with black text (horizontal)
            $colorIndex = $index % count($this->judgeColors);
            $this->sheet->getStyle($colLetter . $headerRow)->applyFromArray([
                'font' => ['bold' => true, 'size' => 9, 'color' => ['rgb' => '000000']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $this->judgeColors[$colorIndex]]],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
            ]);
            $col++;
        }
        
        // Style header row - Light Blue theme
        $this->sheet->getStyle('A' . $headerRow . ':E' . $headerRow)->applyFromArray([
            'font' => ['bold' => true, 'size' => 10],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'BBDEFB']],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
        ]);
        
        $this->currentRow++;
        
        // Group institutions by category
        $categories = [];
        foreach ($award['institutions'] as $inst) {
            $cat = $inst['category'] ?? 'A';
            if (!isset($categories[$cat])) {
                $categories[$cat] = [];
            }
            $categories[$cat][] = $inst;
        }
        
        // Sort categories
        ksort($categories);
        
        // Output institutions by category
        foreach ($categories as $categoryName => $institutions) {
            // Sort institutions by total score descending within category
            usort($institutions, function($a, $b) {
                return ($b['calculated']['total_score'] ?? 0) <=> ($a['calculated']['total_score'] ?? 0);
            });
            
            foreach ($institutions as $inst) {
                $this->createInstitutionRow($inst, $judges, $categoryName);
            }
        }
        
        // Add empty row after award section
        $this->currentRow++;
    }
    
    /**
     * Create a row for an institution
     */
    private function createInstitutionRow($institution, $judges, $category)
    {
        $row = $this->currentRow;
        $calc = $institution['calculated'];
        
        // Category
        $this->sheet->setCellValue('A' . $row, $category);
        
        // Institution name
        $this->sheet->setCellValue('B' . $row, $institution['name']);
        
        // Quantitative score (70% - Preliminary)
        $this->sheet->setCellValue('C' . $row, round($calc['quantitative_weighted'], 1));
        
        // Qualitative score (30% - Presentation average)
        $this->sheet->setCellValue('D' . $row, round($calc['qualitative_weighted'], 1));
        
        // Total score (100%)
        $this->sheet->setCellValue('E' . $row, round($calc['total_score'], 1));
        
        // Individual judge scores - display percentage for each judge
        $judgeScoresMap = [];
        foreach ($institution['judge_scores'] as $score) {
            $judgeScoresMap[$score['judge_id']] = $score;
        }
        
        $col = 6;
        foreach ($judges as $index => $judge) {
            $colLetter = $this->getColumnLetter($col);
            
            if (isset($judgeScoresMap[$judge['id']])) {
                $score = $judgeScoresMap[$judge['id']];
                // Use the calculated percentage (computed in getJudgeScoresForInstitution)
                $judgePercentage = floatval($score['calculated_percentage'] ?? $score['percentage'] ?? 0);
                
                $this->sheet->setCellValue($colLetter . $row, round($judgePercentage, 0));
                
                // Light GREEN background for submitted marks
                $this->sheet->getStyle($colLetter . $row)->applyFromArray([
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'C8E6C9']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
                ]);
            } else {
                // Judge was absent/pending
                $this->sheet->setCellValue($colLetter . $row, 'Ab');
                $this->sheet->getStyle($colLetter . $row)->getFont()->setItalic(true);
                
                // Light ORANGE background for pending/absent
                $this->sheet->getStyle($colLetter . $row)->applyFromArray([
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFE0B2']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
                ]);
            }
            
            $col++;
        }
        
        // Style data row
        $this->sheet->getStyle('A' . $row . ':E' . $row)->applyFromArray([
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
        ]);
        
        // Left align institution name
        $this->sheet->getStyle('B' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
        
        $this->currentRow++;
    }
    
    /**
     * Convert column number to letter (1=A, 2=B, etc.)
     */
    private function getColumnLetter($num)
    {
        $letter = '';
        while ($num > 0) {
            $num--;
            $letter = chr(65 + ($num % 26)) . $letter;
            $num = intval($num / 26);
        }
        return $letter;
    }
    
    /**
     * Get a lighter version of a color for cell backgrounds
     */
    private function getLightColor($hexColor)
    {
        $r = hexdec(substr($hexColor, 0, 2));
        $g = hexdec(substr($hexColor, 2, 2));
        $b = hexdec(substr($hexColor, 4, 2));
        
        // Mix with white (80% white, 20% original)
        $r = intval($r * 0.2 + 255 * 0.8);
        $g = intval($g * 0.2 + 255 * 0.8);
        $b = intval($b * 0.2 + 255 * 0.8);
        
        return sprintf('%02X%02X%02X', $r, $g, $b);
    }
    
    /**
     * Auto-size columns for better readability
     */
    private function autoSizeColumns()
    {
        // Set specific column widths
        $this->sheet->getColumnDimension('A')->setWidth(15);
        $this->sheet->getColumnDimension('B')->setWidth(35);
        $this->sheet->getColumnDimension('C')->setWidth(10);
        $this->sheet->getColumnDimension('D')->setWidth(10);
        $this->sheet->getColumnDimension('E')->setWidth(10);
        
        // Judge columns - wider to fit horizontal names
        $highestColumn = $this->sheet->getHighestColumn();
        $col = 'F';
        while ($col <= $highestColumn) {
            $this->sheet->getColumnDimension($col)->setWidth(18);
            $col++;
        }
    }
    
    /**
     * Generate Bank-wise Report
     * Lists each bank with all their awards and total (100%) scores
     * Format: Bank Name | Award Description | Total Score (100%) | Rank
     */
    public function generateBankWiseReport($filters = [])
    {
        // Set document properties
        $this->spreadsheet->getProperties()
            ->setCreator('LankaPay Technnovation Awards')
            ->setTitle('Bank-wise Performance Report')
            ->setSubject('LANKAPAY TECHNNOVATION AWARDS 2025');
        
        // Get all institutions with their awards and scores
        $bankData = $this->getBankWiseData();
        
        // Get total number of judges for average calculation
        $totalJudges = $this->getTotalJudgesCount();
        
        // ========== PRE-CALCULATE ALL SCORES AND RANKINGS ==========
        // First, calculate all scores for all bank-award combinations
        $awardScores = []; // [award_id => [bank_id => score, ...], ...]
        foreach ($bankData as $bank) {
            foreach ($bank['awards'] as $award) {
                $awardId = $award['id'];
                if (!isset($awardScores[$awardId])) {
                    $awardScores[$awardId] = [];
                }
                $score = $this->calculateBankAwardScore($bank['id'], $awardId, $totalJudges);
                $awardScores[$awardId][$bank['id']] = $score;
            }
        }
        
        // Calculate rankings for each award
        $awardRankings = []; // [award_id => [bank_id => rank, ...], ...]
        foreach ($awardScores as $awardId => $bankScoresForAward) {
            // Sort by score descending
            arsort($bankScoresForAward);
            $rank = 1;
            $awardRankings[$awardId] = [];
            foreach ($bankScoresForAward as $bankId => $score) {
                $awardRankings[$awardId][$bankId] = ($score > 0) ? $rank++ : '-';
            }
        }
        
        // ========== CREATE HEADER SECTION ==========
        // Title row - Blue theme (matching Award-wise report)
        $this->sheet->setCellValue('A1', 'LANKAPAY TECHNNOVATION AWARDS 2025');
        $this->sheet->mergeCells('A1:E1');
        $this->sheet->getStyle('A1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 16, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1565C0']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER]
        ]);
        $this->sheet->getRowDimension(1)->setRowHeight(30);
        
        // Subtitle row - Blue theme
        $this->sheet->setCellValue('A2', 'BANK-WISE MARKING SCHEME');
        $this->sheet->mergeCells('A2:E2');
        $this->sheet->getStyle('A2')->applyFromArray([
            'font' => ['bold' => true, 'size' => 12, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '42A5F5']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER]
        ]);
        $this->sheet->getRowDimension(2)->setRowHeight(24);
        
        // Column headers row (Row 4)
        $this->sheet->setCellValue('B4', 'Institution Name');
        $this->sheet->setCellValue('C4', 'Award Category');
        $this->sheet->setCellValue('D4', 'Aggregate Mark');
        $this->sheet->setCellValue('E4', 'Rank');
        
        // Style column headers
        $this->sheet->getStyle('B4:E4')->applyFromArray([
            'font' => ['bold' => true, 'size' => 11, 'color' => ['rgb' => '000000']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFFFFF']],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER]
        ]);
        $this->sheet->getRowDimension(4)->setRowHeight(22);
        
        // Start data from row 5
        $this->currentRow = 5;
        
        // Light pastel colors for each bank (rotating through)
        $bankColors = [
            'FFCDD2', // Light Red/Pink
            'FFF9C4', // Light Yellow
            'C8E6C9', // Light Green
            'BBDEFB', // Light Blue
            'E1BEE7', // Light Purple
            'FFE0B2', // Light Orange
            'B2DFDB', // Light Teal
            'F8BBD0', // Light Pink
            'D7CCC8', // Light Brown
            'CFD8DC', // Light Blue Grey
            'DCEDC8', // Light Lime
            'B3E5FC', // Light Cyan
        ];
        
        // ========== PROCESS EACH BANK ==========
        $bankIndex = 0;
        foreach ($bankData as $bank) {
            $awardCount = count($bank['awards']);
            
            if ($awardCount === 0) {
                continue; // Skip banks with no awards
            }
            
            // Get color for this bank (cycle through colors)
            $bankColor = $bankColors[$bankIndex % count($bankColors)];
            $bankIndex++;
            
            // Output each award row for this bank
            $firstAwardRow = $this->currentRow;
            foreach ($bank['awards'] as $awardIndex => $award) {
                $row = $this->currentRow;
                $awardId = $award['id'];
                
                // Bank name only in first row (will be merged later)
                if ($awardIndex === 0) {
                    $this->sheet->setCellValue('B' . $row, $bank['name']);
                    
                    // Style bank name cell with light color background
                    $this->sheet->getStyle('B' . $row)->applyFromArray([
                        'font' => ['bold' => false, 'size' => 10, 'underline' => true, 'color' => ['rgb' => '0000FF']],
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $bankColor]],
                        'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
                        'alignment' => ['vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true]
                    ]);
                }
                
                // Award description - use category directly (it should contain full award name)
                $awardDescription = $award['category'];
                $this->sheet->setCellValue('C' . $row, $awardDescription);
                
                // Get pre-calculated total score
                $totalScore = $awardScores[$awardId][$bank['id']] ?? 0;
                
                // Total score (100%)
                if ($totalScore !== null && $totalScore > 0) {
                    $this->sheet->setCellValue('D' . $row, round($totalScore, 2));
                    
                    // Style score cell - yellow highlight for high scores (70+), white otherwise
                    $scoreColor = $totalScore >= 70 ? 'FFFF00' : 'FFFFFF';
                    $this->sheet->getStyle('D' . $row)->applyFromArray([
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $scoreColor]],
                        'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
                        'alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT],
                        'numberFormat' => ['formatCode' => '0.00']
                    ]);
                } else {
                    // Empty cell with light blue background for missing scores
                    $this->sheet->getStyle('D' . $row)->applyFromArray([
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'B8CCE4']],
                        'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
                    ]);
                }
                
                // Rank column - show this bank's rank for this award
                $rank = $awardRankings[$awardId][$bank['id']] ?? '-';
                $this->sheet->setCellValue('E' . $row, $rank);
                
                // Style rank cell - yellow highlight for rank 1, white for others
                $rankColor = 'FFFFFF';
                $fontColor = '000000';
                if ($rank === 1) {
                    $rankColor = 'FFFF00'; // Bright Yellow for 1st (matching image)
                    $fontColor = 'FF0000'; // Red text for rank 1
                }
                $this->sheet->getStyle('E' . $row)->applyFromArray([
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $rankColor]],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                    'font' => ['bold' => ($rank === 1), 'color' => ['rgb' => $fontColor]]
                ]);
                
                // Style award description cell
                $this->sheet->getStyle('C' . $row)->applyFromArray([
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'vertical' => Alignment::VERTICAL_CENTER]
                ]);
                
                $this->currentRow++;
            }
            
            // Merge bank name cells if multiple awards
            if ($awardCount > 1) {
                $this->sheet->mergeCells('B' . $firstAwardRow . ':B' . ($this->currentRow - 1));
                $this->sheet->getStyle('B' . $firstAwardRow)->getAlignment()->setVertical(Alignment::VERTICAL_TOP);
                
                // Apply bank color to merged bank name area
                $this->sheet->getStyle('B' . $firstAwardRow . ':B' . ($this->currentRow - 1))->applyFromArray([
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $bankColor]],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
                ]);
            }
            
            // Add empty row between banks for visual separation
            $this->currentRow++;
        }
        
        // ========== SET COLUMN WIDTHS ==========
        $this->sheet->getColumnDimension('A')->setWidth(5);
        $this->sheet->getColumnDimension('B')->setWidth(35);
        $this->sheet->getColumnDimension('C')->setWidth(75);
        $this->sheet->getColumnDimension('D')->setWidth(12);
        $this->sheet->getColumnDimension('E')->setWidth(8);
        
        // ========== GENERATE FILE ==========
        $filename = 'Bank_Wise_Report_' . date('Ymd_His') . '.xlsx';
        $filepath = __DIR__ . '/../uploads/reports/' . $filename;
        
        if (!is_dir(dirname($filepath))) {
            mkdir(dirname($filepath), 0755, true);
        }
        
        $writer = new Xlsx($this->spreadsheet);
        $writer->save($filepath);
        
        return [
            'filename' => $filename,
            'filepath' => $filepath
        ];
    }
    
    /**
     * Get all banks with their associated awards
     */
    private function getBankWiseData()
    {
        // Get all active institutions
        $stmt = $this->db->query("
            SELECT id, name 
            FROM institutions 
            WHERE status = 'active'
            ORDER BY name
        ");
        $institutions = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Get awards for each institution
        foreach ($institutions as &$inst) {
            $stmt = $this->db->prepare("
                SELECT 
                    a.id,
                    a.award_number,
                    a.category,
                    a.presentation_weightage,
                    a.preliminary_weightage,
                    ia.marks as quantitative_score,
                    ia.category as award_category
                FROM awards a
                INNER JOIN institution_awards ia ON a.id = ia.award_id AND ia.institution_id = ?
                WHERE a.status = 'active'
                ORDER BY CAST(a.award_number AS UNSIGNED), a.award_number
            ");
            $stmt->execute([$inst['id']]);
            $inst['awards'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        
        return $institutions;
    }
    
    /**
     * Calculate total score for a specific bank-award combination
     * Uses same logic as award-wise report
     */
    private function calculateBankAwardScore($institutionId, $awardId, $totalJudges)
    {
        // Get quantitative score (preliminary marks) from institution_awards
        $stmt = $this->db->prepare("
            SELECT marks as quantitative_score
            FROM institution_awards 
            WHERE institution_id = ? AND award_id = ?
        ");
        $stmt->execute([$institutionId, $awardId]);
        $iaData = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $preliminaryMarks = floatval($iaData['quantitative_score'] ?? 0);
        
        // Get award weightages
        $stmt = $this->db->prepare("SELECT presentation_weightage, preliminary_weightage FROM awards WHERE id = ?");
        $stmt->execute([$awardId]);
        $award = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $presentationWeight = floatval($award['presentation_weightage'] ?? 30) / 100;
        
        // Get judge scores for this institution-award
        $stmt = $this->db->prepare("
            SELECT 
                e.id as evaluation_id,
                e.presentation_score,
                e.total_achieved_marks,
                e.total_allocated_marks,
                e.percentage,
                e.status
            FROM evaluations e
            WHERE e.institution_id = ? AND e.award_id = ? AND e.status = 'submitted'
        ");
        $stmt->execute([$institutionId, $awardId]);
        $judgeScores = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Calculate average judge percentage
        $sumOfPercentages = 0;
        foreach ($judgeScores as $score) {
            $percentage = floatval($score['percentage'] ?? 0);
            $achieved = floatval($score['total_achieved_marks'] ?? 0);
            $allocated = floatval($score['total_allocated_marks'] ?? 0);
            
            // Calculate percentage if not set
            if ($percentage == 0 && $achieved > 0) {
                if ($allocated > 0) {
                    $percentage = ($achieved / $allocated) * 100;
                } else {
                    // Try to get from criteria marks
                    $criteriaStmt = $this->db->prepare("
                        SELECT SUM(allocated_marks) as total_allocated, SUM(achieved_marks) as total_achieved
                        FROM evaluation_criteria_marks 
                        WHERE evaluation_id = ?
                    ");
                    $criteriaStmt->execute([$score['evaluation_id']]);
                    $criteria = $criteriaStmt->fetch(PDO::FETCH_ASSOC);
                    
                    if ($criteria && floatval($criteria['total_allocated']) > 0) {
                        $percentage = (floatval($criteria['total_achieved']) / floatval($criteria['total_allocated'])) * 100;
                    } else {
                        $percentage = $achieved;
                    }
                }
            }
            
            $sumOfPercentages += $percentage;
        }
        
        // Average across ALL judges (including absent as 0)
        $averageJudgePercentage = 0;
        if ($totalJudges > 0) {
            $averageJudgePercentage = $sumOfPercentages / $totalJudges;
        }
        
        // Calculate weighted qualitative score
        $qualitativeWeighted = $averageJudgePercentage * $presentationWeight;
        
        // Total score = Static Preliminary + Weighted Presentation
        $totalScore = $preliminaryMarks + $qualitativeWeighted;
        
        return $totalScore;
    }
}

