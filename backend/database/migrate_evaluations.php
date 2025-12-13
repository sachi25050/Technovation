<?php
/**
 * Database Migration Script - Evaluations Tables
 * Creates tables for storing judge evaluations with criteria marks
 * 
 * Run this script: php migrate_evaluations.php
 */

require_once __DIR__ . '/../core/Database.php';

$db = Database::getInstance()->getConnection();

echo "=== EVALUATIONS TABLE MIGRATION ===\n\n";

// SQL statements to execute
$migrations = [
    // 1. Create award_criteria table first (referenced by evaluation_criteria_marks)
    "award_criteria" => "CREATE TABLE IF NOT EXISTS `award_criteria` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `award_id` int(11) NOT NULL COMMENT 'Reference to awards.id',
      `name` varchar(255) NOT NULL COMMENT 'Criterion name',
      `description` text NULL COMMENT 'Criterion description',
      `allocated_marks` decimal(10,2) NOT NULL DEFAULT 0 COMMENT 'Maximum marks for this criterion',
      `display_order` int(11) DEFAULT 0 COMMENT 'Display order (1-10)',
      `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
      `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
      PRIMARY KEY (`id`),
      KEY `idx_award_id` (`award_id`),
      KEY `idx_display_order` (`display_order`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
    
    // 2. Create evaluations table
    "evaluations" => "CREATE TABLE IF NOT EXISTS `evaluations` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `judge_id` int(11) NOT NULL COMMENT 'Reference to users.id (the judge)',
      `institution_id` int(11) NOT NULL COMMENT 'Reference to institutions.id',
      `award_id` int(11) NOT NULL COMMENT 'Reference to awards.id',
      
      `criteria_1_marks` decimal(10,2) DEFAULT NULL COMMENT 'Achieved marks for criterion 1',
      `criteria_2_marks` decimal(10,2) DEFAULT NULL COMMENT 'Achieved marks for criterion 2',
      `criteria_3_marks` decimal(10,2) DEFAULT NULL COMMENT 'Achieved marks for criterion 3',
      `criteria_4_marks` decimal(10,2) DEFAULT NULL COMMENT 'Achieved marks for criterion 4',
      `criteria_5_marks` decimal(10,2) DEFAULT NULL COMMENT 'Achieved marks for criterion 5',
      `criteria_6_marks` decimal(10,2) DEFAULT NULL COMMENT 'Achieved marks for criterion 6',
      `criteria_7_marks` decimal(10,2) DEFAULT NULL COMMENT 'Achieved marks for criterion 7',
      `criteria_8_marks` decimal(10,2) DEFAULT NULL COMMENT 'Achieved marks for criterion 8',
      `criteria_9_marks` decimal(10,2) DEFAULT NULL COMMENT 'Achieved marks for criterion 9',
      `criteria_10_marks` decimal(10,2) DEFAULT NULL COMMENT 'Achieved marks for criterion 10',
      
      `total_achieved_marks` decimal(10,2) DEFAULT 0 COMMENT 'Sum of all criteria achieved marks',
      `total_allocated_marks` decimal(10,2) DEFAULT 0 COMMENT 'Sum of all criteria allocated marks',
      
      `presentation_score` decimal(10,2) DEFAULT 0 COMMENT 'Score for Presentation (weighted)',
      `preliminary_score` decimal(10,2) DEFAULT 0 COMMENT 'Preliminary Volume Wise Score (weighted)',
      `aggregated_score` decimal(10,2) DEFAULT 0 COMMENT 'Aggregated Score (Presentation + Preliminary)',
      
      `presentation_weightage` decimal(5,2) DEFAULT 0 COMMENT 'Presentation weightage percentage',
      `preliminary_weightage` decimal(5,2) DEFAULT 0 COMMENT 'Preliminary weightage percentage',
      
      `comments` text NULL COMMENT 'Judge comments/notes',
      `status` enum('draft', 'submitted', 'approved', 'rejected') DEFAULT 'draft' COMMENT 'Evaluation status',
      `submitted_at` timestamp NULL COMMENT 'When the evaluation was submitted',
      
      `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
      `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
      
      PRIMARY KEY (`id`),
      UNIQUE KEY `unique_judge_evaluation` (`judge_id`, `institution_id`, `award_id`),
      KEY `idx_judge_id` (`judge_id`),
      KEY `idx_institution_id` (`institution_id`),
      KEY `idx_award_id` (`award_id`),
      KEY `idx_status` (`status`),
      KEY `idx_created_at` (`created_at`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
    
    // 3. Create evaluation_criteria_marks table (normalized approach)
    "evaluation_criteria_marks" => "CREATE TABLE IF NOT EXISTS `evaluation_criteria_marks` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `evaluation_id` int(11) NOT NULL COMMENT 'Reference to evaluations.id',
      `criterion_id` int(11) NOT NULL COMMENT 'Reference to award_criteria.id',
      `criterion_name` varchar(255) NULL COMMENT 'Criterion name (denormalized for reporting)',
      `display_order` int(11) DEFAULT 0 COMMENT 'Order of the criterion (1-10)',
      `allocated_marks` decimal(10,2) NOT NULL DEFAULT 0 COMMENT 'Maximum marks for this criterion',
      `achieved_marks` decimal(10,2) NOT NULL DEFAULT 0 COMMENT 'Marks achieved by institution',
      `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
      `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
      PRIMARY KEY (`id`),
      UNIQUE KEY `unique_evaluation_criterion` (`evaluation_id`, `criterion_id`),
      KEY `idx_evaluation_id` (`evaluation_id`),
      KEY `idx_criterion_id` (`criterion_id`),
      KEY `idx_display_order` (`display_order`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
];

// Execute table creation
echo "Creating tables...\n\n";
foreach ($migrations as $tableName => $sql) {
    try {
        $db->exec($sql);
        echo "✅ Table '$tableName' created/verified\n";
    } catch (PDOException $e) {
        echo "⚠️  Table '$tableName': " . $e->getMessage() . "\n";
    }
}

// Add foreign key constraints separately (to handle existing tables)
echo "\nAdding foreign key constraints...\n";

$foreignKeys = [
    // award_criteria foreign key
    "fk_criteria_award" => "ALTER TABLE `award_criteria` 
        ADD CONSTRAINT `fk_criteria_award` 
        FOREIGN KEY (`award_id`) 
        REFERENCES `awards` (`id`) 
        ON DELETE CASCADE 
        ON UPDATE CASCADE",
    
    // evaluations foreign keys
    "fk_evaluations_judge" => "ALTER TABLE `evaluations` 
        ADD CONSTRAINT `fk_evaluations_judge` 
        FOREIGN KEY (`judge_id`) 
        REFERENCES `users` (`id`) 
        ON DELETE CASCADE 
        ON UPDATE CASCADE",
    
    "fk_evaluations_institution" => "ALTER TABLE `evaluations` 
        ADD CONSTRAINT `fk_evaluations_institution` 
        FOREIGN KEY (`institution_id`) 
        REFERENCES `institutions` (`id`) 
        ON DELETE CASCADE 
        ON UPDATE CASCADE",
    
    "fk_evaluations_award" => "ALTER TABLE `evaluations` 
        ADD CONSTRAINT `fk_evaluations_award` 
        FOREIGN KEY (`award_id`) 
        REFERENCES `awards` (`id`) 
        ON DELETE CASCADE 
        ON UPDATE CASCADE",
    
    // evaluation_criteria_marks foreign keys
    "fk_ecm_evaluation" => "ALTER TABLE `evaluation_criteria_marks` 
        ADD CONSTRAINT `fk_ecm_evaluation` 
        FOREIGN KEY (`evaluation_id`) 
        REFERENCES `evaluations` (`id`) 
        ON DELETE CASCADE 
        ON UPDATE CASCADE",
    
    "fk_ecm_criterion" => "ALTER TABLE `evaluation_criteria_marks` 
        ADD CONSTRAINT `fk_ecm_criterion` 
        FOREIGN KEY (`criterion_id`) 
        REFERENCES `award_criteria` (`id`) 
        ON DELETE CASCADE 
        ON UPDATE CASCADE"
];

foreach ($foreignKeys as $constraintName => $sql) {
    try {
        $db->exec($sql);
        echo "✅ Foreign key '$constraintName' added\n";
    } catch (PDOException $e) {
        // Constraint might already exist
        if (strpos($e->getMessage(), 'Duplicate') !== false || strpos($e->getMessage(), 'already exists') !== false) {
            echo "ℹ️  Foreign key '$constraintName' already exists\n";
        } else {
            echo "⚠️  Foreign key '$constraintName': " . substr($e->getMessage(), 0, 80) . "\n";
        }
    }
}

// Add columns to awards table if they don't exist
echo "\nUpdating awards table...\n";

$awardColumns = [
    "status" => "ALTER TABLE `awards` ADD COLUMN `status` varchar(20) DEFAULT 'active' AFTER `description`",
    "presentation_weightage" => "ALTER TABLE `awards` ADD COLUMN `presentation_weightage` decimal(5,2) DEFAULT 10.00 COMMENT 'Presentation weightage percentage'",
    "preliminary_weightage" => "ALTER TABLE `awards` ADD COLUMN `preliminary_weightage` decimal(5,2) DEFAULT 90.00 COMMENT 'Preliminary weightage percentage'",
    "total_marks" => "ALTER TABLE `awards` ADD COLUMN `total_marks` decimal(10,2) DEFAULT 100.00 COMMENT 'Total marks for this award'"
];

foreach ($awardColumns as $columnName => $sql) {
    try {
        // Check if column exists
        $checkStmt = $db->query("SHOW COLUMNS FROM `awards` LIKE '$columnName'");
        $exists = $checkStmt->fetch();
        
        if (!$exists) {
            $db->exec($sql);
            echo "✅ Column 'awards.$columnName' added\n";
        } else {
            echo "ℹ️  Column 'awards.$columnName' already exists\n";
        }
    } catch (PDOException $e) {
        echo "⚠️  Column 'awards.$columnName': " . substr($e->getMessage(), 0, 60) . "\n";
    }
}

// Add missing columns to evaluations table (in case it already existed)
echo "\nAdding evaluation columns (if missing)...\n";

$evaluationColumns = [
    ['criteria_1_marks', 'decimal(10,2) DEFAULT NULL COMMENT "Achieved marks for criterion 1"'],
    ['criteria_2_marks', 'decimal(10,2) DEFAULT NULL COMMENT "Achieved marks for criterion 2"'],
    ['criteria_3_marks', 'decimal(10,2) DEFAULT NULL COMMENT "Achieved marks for criterion 3"'],
    ['criteria_4_marks', 'decimal(10,2) DEFAULT NULL COMMENT "Achieved marks for criterion 4"'],
    ['criteria_5_marks', 'decimal(10,2) DEFAULT NULL COMMENT "Achieved marks for criterion 5"'],
    ['criteria_6_marks', 'decimal(10,2) DEFAULT NULL COMMENT "Achieved marks for criterion 6"'],
    ['criteria_7_marks', 'decimal(10,2) DEFAULT NULL COMMENT "Achieved marks for criterion 7"'],
    ['criteria_8_marks', 'decimal(10,2) DEFAULT NULL COMMENT "Achieved marks for criterion 8"'],
    ['criteria_9_marks', 'decimal(10,2) DEFAULT NULL COMMENT "Achieved marks for criterion 9"'],
    ['criteria_10_marks', 'decimal(10,2) DEFAULT NULL COMMENT "Achieved marks for criterion 10"'],
    ['total_achieved_marks', 'decimal(10,2) DEFAULT 0 COMMENT "Sum of all criteria achieved marks"'],
    ['total_allocated_marks', 'decimal(10,2) DEFAULT 0 COMMENT "Sum of all criteria allocated marks"'],
    ['presentation_score', 'decimal(10,2) DEFAULT 0 COMMENT "Score for Presentation (weighted)"'],
    ['preliminary_score', 'decimal(10,2) DEFAULT 0 COMMENT "Preliminary Volume Wise Score (weighted)"'],
    ['aggregated_score', 'decimal(10,2) DEFAULT 0 COMMENT "Aggregated Score"'],
    ['presentation_weightage', 'decimal(5,2) DEFAULT 0 COMMENT "Presentation weightage percentage"'],
    ['preliminary_weightage', 'decimal(5,2) DEFAULT 0 COMMENT "Preliminary weightage percentage"']
];

foreach ($evaluationColumns as $col) {
    $name = $col[0];
    $definition = $col[1];
    
    try {
        $checkStmt = $db->query("SHOW COLUMNS FROM `evaluations` LIKE '$name'");
        $exists = $checkStmt->fetch();
        
        if (!$exists) {
            $db->exec("ALTER TABLE `evaluations` ADD COLUMN `$name` $definition");
            echo "✅ Column 'evaluations.$name' added\n";
        } else {
            echo "ℹ️  Column 'evaluations.$name' already exists\n";
        }
    } catch (PDOException $e) {
        echo "⚠️  Column 'evaluations.$name': " . substr($e->getMessage(), 0, 60) . "\n";
    }
}

// Verification
echo "\n=== VERIFICATION ===\n";

$tables = ['evaluations', 'evaluation_criteria_marks', 'award_criteria'];

foreach ($tables as $table) {
    try {
        $stmt = $db->query("SELECT COUNT(*) as count FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = '$table'");
        $result = $stmt->fetch();
        echo "✅ Table '$table' has {$result['count']} columns\n";
    } catch (PDOException $e) {
        echo "❌ Table '$table' verification failed\n";
    }
}

// Show evaluations table structure
echo "\n=== EVALUATIONS TABLE STRUCTURE ===\n";
try {
    $stmt = $db->query("DESCRIBE evaluations");
    $columns = $stmt->fetchAll();
    
    echo sprintf("%-25s %-30s %-10s\n", "Column", "Type", "Null");
    echo str_repeat("-", 70) . "\n";
    
    foreach ($columns as $col) {
        echo sprintf("%-25s %-30s %-10s\n", $col['Field'], $col['Type'], $col['Null']);
    }
} catch (PDOException $e) {
    echo "Could not describe evaluations table: " . $e->getMessage() . "\n";
}

echo "\n=== MIGRATION COMPLETE ===\n";
echo "\nThe following tables have been created/updated:\n";
echo "1. evaluations - Stores judge evaluations with up to 10 criteria columns\n";
echo "2. evaluation_criteria_marks - Normalized criteria marks for flexibility\n";
echo "3. award_criteria - Criteria definitions per award\n";
echo "\nFields stored in evaluations table:\n";
echo "  - judge_id (Name of the Judge)\n";
echo "  - institution_id (Institute Name)\n";
echo "  - award_id (Award Category)\n";
echo "  - criteria_1_marks to criteria_10_marks (Up to 10 criteria)\n";
echo "  - presentation_score (Score for the Presentation)\n";
echo "  - preliminary_score (Preliminary Volume Wise Score)\n";
echo "  - aggregated_score (Aggregated Score)\n";

