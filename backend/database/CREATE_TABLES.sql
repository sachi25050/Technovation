-- ============================================
-- Award Management System Tables
-- ============================================

-- 1. Create 'awards' table if it doesn't exist
CREATE TABLE IF NOT EXISTS `awards` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `award_number` varchar(50) NOT NULL COMMENT 'Award identifier (e.g., 14, 6A, 6B)',
  `category` varchar(255) NOT NULL COMMENT 'Full award category name',
  `description` text NULL COMMENT 'Detailed description',
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `award_number` (`award_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Create 'institution_awards' table if it doesn't exist
CREATE TABLE IF NOT EXISTS `institution_awards` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `institution_id` int(11) NOT NULL COMMENT 'Reference to institutions.id',
  `award_id` int(11) NOT NULL COMMENT 'Reference to awards.id',
  `marks` decimal(10,2) DEFAULT 0 COMMENT 'Marks obtained for this award',
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_institution_award` (`institution_id`, `award_id`),
  KEY `idx_institution_id` (`institution_id`),
  KEY `idx_award_id` (`award_id`),
  -- Foreign key constraints
  CONSTRAINT `fk_institution_awards_institution` 
    FOREIGN KEY (`institution_id`) 
    REFERENCES `institutions` (`id`) 
    ON DELETE CASCADE 
    ON UPDATE CASCADE,
  CONSTRAINT `fk_institution_awards_award` 
    FOREIGN KEY (`award_id`) 
    REFERENCES `awards` (`id`) 
    ON DELETE CASCADE 
    ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Verify/Update 'institutions' table structure
-- Add missing columns if needed
ALTER TABLE `institutions` 
ADD COLUMN IF NOT EXISTS `type` varchar(50) DEFAULT 'other' AFTER `email`,
ADD COLUMN IF NOT EXISTS `contact_person` varchar(100) NULL AFTER `type`,
ADD COLUMN IF NOT EXISTS `contact_phone` varchar(20) NULL AFTER `contact_person`,
ADD COLUMN IF NOT EXISTS `status` varchar(20) DEFAULT 'active' AFTER `contact_phone`,
ADD COLUMN IF NOT EXISTS `image_path` varchar(255) NULL AFTER `status`,
ADD COLUMN IF NOT EXISTS `image_url` varchar(255) NULL AFTER `image_path`,
ADD COLUMN IF NOT EXISTS `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
ADD COLUMN IF NOT EXISTS `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;

-- 4. Insert award records if they don't exist
INSERT IGNORE INTO `awards` (`id`, `award_number`, `category`, `description`) VALUES
(14, '14', 'Award No. 14 - Financial Institution of the Year for Best Digital Payment', 'Recognizing the best digital payment solution by financial institutions'),
(6, '6A', 'Award No. 6A - Most Popular Digital Payment Product - State Banks', 'Recognition for the most popular digital payment product from state banks'),
(7, '6B', 'Award No. 6B - Most Popular Digital Payment Product - Private Banks', 'Recognition for the most popular digital payment product from private banks'),
(8, '7', 'Award No. 7 - Best Digital Payment Innovation', 'Recognizing the best innovation in digital payment technology'),
(9, '8', 'Award No. 8 - Best Digital Payment Security', 'Recognizing excellence in digital payment security and fraud prevention');

-- 5. Verify data integrity
-- Show awards
SELECT COUNT(*) as total_awards FROM awards;

-- Show sample institution_awards (if any)
SELECT COUNT(*) as total_links FROM institution_awards;

-- Show all award IDs that should exist
SELECT id, award_number, category FROM awards ORDER BY id;
