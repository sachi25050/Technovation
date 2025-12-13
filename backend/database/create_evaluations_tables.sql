-- ============================================
-- Judge Evaluations Tables Migration
-- ============================================
-- This migration creates tables to store judge evaluations
-- including marking criteria achieved values (up to 10 criteria),
-- presentation scores, preliminary scores, and aggregated scores.
-- ============================================

-- 1. Create 'evaluations' table - Main evaluation records
-- Stores the complete evaluation entry by a judge for an institution's award
CREATE TABLE IF NOT EXISTS `evaluations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `judge_id` int(11) NOT NULL COMMENT 'Reference to users.id (the judge)',
  `institution_id` int(11) NOT NULL COMMENT 'Reference to institutions.id',
  `award_id` int(11) NOT NULL COMMENT 'Reference to awards.id',
  
  -- Criteria achieved marks (up to 10 criteria)
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
  
  -- Total and calculated scores
  `total_achieved_marks` decimal(10,2) DEFAULT 0 COMMENT 'Sum of all criteria achieved marks',
  `total_allocated_marks` decimal(10,2) DEFAULT 0 COMMENT 'Sum of all criteria allocated marks',
  
  -- Weighted scores
  `presentation_score` decimal(10,2) DEFAULT 0 COMMENT 'Score for Presentation (weighted)',
  `preliminary_score` decimal(10,2) DEFAULT 0 COMMENT 'Preliminary Volume Wise Score (weighted)',
  `aggregated_score` decimal(10,2) DEFAULT 0 COMMENT 'Aggregated Score (Presentation + Preliminary)',
  
  -- Weightage percentages (stored for reference)
  `presentation_weightage` decimal(5,2) DEFAULT 0 COMMENT 'Presentation weightage percentage',
  `preliminary_weightage` decimal(5,2) DEFAULT 0 COMMENT 'Preliminary weightage percentage',
  
  -- Additional fields
  `comments` text NULL COMMENT 'Judge comments/notes',
  `status` enum('draft', 'submitted', 'approved', 'rejected') DEFAULT 'draft' COMMENT 'Evaluation status',
  `submitted_at` timestamp NULL COMMENT 'When the evaluation was submitted',
  
  -- Timestamps
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  
  PRIMARY KEY (`id`),
  
  -- Ensure a judge can only have one evaluation per institution-award combination
  UNIQUE KEY `unique_judge_evaluation` (`judge_id`, `institution_id`, `award_id`),
  
  -- Indexes for faster lookups
  KEY `idx_judge_id` (`judge_id`),
  KEY `idx_institution_id` (`institution_id`),
  KEY `idx_award_id` (`award_id`),
  KEY `idx_status` (`status`),
  KEY `idx_created_at` (`created_at`),
  
  -- Foreign key constraints
  CONSTRAINT `fk_evaluations_judge` 
    FOREIGN KEY (`judge_id`) 
    REFERENCES `users` (`id`) 
    ON DELETE CASCADE 
    ON UPDATE CASCADE,
  CONSTRAINT `fk_evaluations_institution` 
    FOREIGN KEY (`institution_id`) 
    REFERENCES `institutions` (`id`) 
    ON DELETE CASCADE 
    ON UPDATE CASCADE,
  CONSTRAINT `fk_evaluations_award` 
    FOREIGN KEY (`award_id`) 
    REFERENCES `awards` (`id`) 
    ON DELETE CASCADE 
    ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- 2. Create 'evaluation_criteria_marks' table - Detailed criteria marks (normalized)
-- This table provides flexibility for any number of criteria per evaluation
-- Use this for detailed reporting and when criteria vary per award
CREATE TABLE IF NOT EXISTS `evaluation_criteria_marks` (
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
  
  -- Ensure no duplicate criteria per evaluation
  UNIQUE KEY `unique_evaluation_criterion` (`evaluation_id`, `criterion_id`),
  
  -- Indexes
  KEY `idx_evaluation_id` (`evaluation_id`),
  KEY `idx_criterion_id` (`criterion_id`),
  KEY `idx_display_order` (`display_order`),
  
  -- Foreign key constraints
  CONSTRAINT `fk_ecm_evaluation` 
    FOREIGN KEY (`evaluation_id`) 
    REFERENCES `evaluations` (`id`) 
    ON DELETE CASCADE 
    ON UPDATE CASCADE,
  CONSTRAINT `fk_ecm_criterion` 
    FOREIGN KEY (`criterion_id`) 
    REFERENCES `award_criteria` (`id`) 
    ON DELETE CASCADE 
    ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- 3. Create 'award_criteria' table if not exists
-- Stores the criteria definitions for each award
CREATE TABLE IF NOT EXISTS `award_criteria` (
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
  KEY `idx_display_order` (`display_order`),
  
  -- Foreign key constraint
  CONSTRAINT `fk_criteria_award` 
    FOREIGN KEY (`award_id`) 
    REFERENCES `awards` (`id`) 
    ON DELETE CASCADE 
    ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- 4. Add status column to awards table if not exists
ALTER TABLE `awards` 
ADD COLUMN IF NOT EXISTS `status` varchar(20) DEFAULT 'active' AFTER `description`,
ADD COLUMN IF NOT EXISTS `presentation_weightage` decimal(5,2) DEFAULT 10.00 COMMENT 'Presentation weightage percentage',
ADD COLUMN IF NOT EXISTS `preliminary_weightage` decimal(5,2) DEFAULT 90.00 COMMENT 'Preliminary weightage percentage',
ADD COLUMN IF NOT EXISTS `total_marks` decimal(10,2) DEFAULT 100.00 COMMENT 'Total marks for this award';


-- ============================================
-- Verification Queries
-- ============================================
-- Run these to verify the tables were created correctly:

-- SELECT 'evaluations' as table_name, COUNT(*) as columns FROM information_schema.COLUMNS WHERE TABLE_NAME = 'evaluations';
-- SELECT 'evaluation_criteria_marks' as table_name, COUNT(*) as columns FROM information_schema.COLUMNS WHERE TABLE_NAME = 'evaluation_criteria_marks';
-- SELECT 'award_criteria' as table_name, COUNT(*) as columns FROM information_schema.COLUMNS WHERE TABLE_NAME = 'award_criteria';

-- DESCRIBE evaluations;
-- DESCRIBE evaluation_criteria_marks;
-- DESCRIBE award_criteria;

