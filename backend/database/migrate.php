<?php
/**
 * Database Migration Script
 * Creates missing tables and fixes schema
 */

require_once __DIR__ . '/../config/database.php';

$db = Database::getInstance()->getConnection();

echo "=== DATABASE MIGRATION ===\n\n";

// SQL statements to execute
$migrations = [
    // Create awards table
    "CREATE TABLE IF NOT EXISTS `awards` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `award_number` varchar(50) NOT NULL COMMENT 'Award identifier (e.g., 14, 6A, 6B)',
      `category` varchar(255) NOT NULL COMMENT 'Full award category name',
      `description` text NULL COMMENT 'Detailed description',
      `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
      `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
      PRIMARY KEY (`id`),
      UNIQUE KEY `award_number` (`award_number`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
    
    // Create institution_awards table
    "CREATE TABLE IF NOT EXISTS `institution_awards` (
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
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
];

// Execute migrations
foreach ($migrations as $index => $sql) {
    try {
        $db->exec($sql);
        echo "✅ Migration " . ($index + 1) . " completed\n";
    } catch (PDOException $e) {
        echo "⚠️  Migration " . ($index + 1) . " (might already exist): " . $e->getMessage() . "\n";
    }
}

// Alter institutions table to add missing columns
echo "\nAdding missing columns to institutions table...\n";
$alterStatements = [
    "ALTER TABLE `institutions` ADD COLUMN IF NOT EXISTS `type` varchar(50) DEFAULT 'other' AFTER `email`",
    "ALTER TABLE `institutions` ADD COLUMN IF NOT EXISTS `contact_person` varchar(100) NULL AFTER `type`",
    "ALTER TABLE `institutions` ADD COLUMN IF NOT EXISTS `contact_phone` varchar(20) NULL AFTER `contact_person`",
    "ALTER TABLE `institutions` ADD COLUMN IF NOT EXISTS `status` varchar(20) DEFAULT 'active' AFTER `contact_phone`",
    "ALTER TABLE `institutions` ADD COLUMN IF NOT EXISTS `image_path` varchar(255) NULL AFTER `status`",
    "ALTER TABLE `institutions` ADD COLUMN IF NOT EXISTS `image_url` varchar(255) NULL AFTER `image_path`",
    "ALTER TABLE `institutions` ADD COLUMN IF NOT EXISTS `created_at` timestamp DEFAULT CURRENT_TIMESTAMP",
    "ALTER TABLE `institutions` ADD COLUMN IF NOT EXISTS `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP"
];

foreach ($alterStatements as $sql) {
    try {
        $db->exec($sql);
        echo "✅ Column added\n";
    } catch (PDOException $e) {
        // Column might already exist, which is fine
        echo "ℹ️  Column already exists or error: " . substr($e->getMessage(), 0, 50) . "\n";
    }
}

// Add title column to users table if it doesn't exist
echo "\nAdding title column to users table...\n";
try {
    // Check if column exists
    $checkStmt = $db->query("SHOW COLUMNS FROM `users` LIKE 'title'");
    $columnExists = $checkStmt->fetch();
    
    if (!$columnExists) {
        // Add title column after email
        $db->exec("ALTER TABLE `users` ADD COLUMN `title` varchar(10) NULL AFTER `email`");
        echo "✅ Title column added to users table\n";
    } else {
        echo "ℹ️  Title column already exists in users table\n";
    }
} catch (PDOException $e) {
    echo "⚠️  Error adding title column: " . substr($e->getMessage(), 0, 100) . "\n";
}

// Insert award records
echo "\nInserting award records...\n";
try {
    $awards = [
        [14, '14', 'Award No. 14 - Financial Institution of the Year for Best Digital Payment'],
        [6, '6A', 'Award No. 6A - Most Popular Digital Payment Product - State Banks'],
        [7, '6B', 'Award No. 6B - Most Popular Digital Payment Product - Private Banks'],
        [8, '7', 'Award No. 7 - Best Digital Payment Innovation'],
        [9, '8', 'Award No. 8 - Best Digital Payment Security']
    ];
    
    $stmt = $db->prepare("INSERT IGNORE INTO awards (id, award_number, category) VALUES (?, ?, ?)");
    
    foreach ($awards as $award) {
        $stmt->execute($award);
        echo "✅ Award {$award[1]}: {$award[2]}\n";
    }
} catch (PDOException $e) {
    echo "❌ Error inserting awards: " . $e->getMessage() . "\n";
}

// Verify setup
echo "\n=== VERIFICATION ===\n";

try {
    // Count records
    $awardCount = $db->query("SELECT COUNT(*) as count FROM awards")->fetch()['count'];
    echo "✅ Awards in database: $awardCount\n";
    
    if ($awardCount > 0) {
        $awards = $db->query("SELECT id, award_number, category FROM awards ORDER BY id")->fetchAll();
        echo "\nAward Details:\n";
        foreach ($awards as $award) {
            echo "  - ID {$award['id']}: {$award['award_number']} - {$award['category']}\n";
        }
    }
    
    // Check institution_awards table
    $linkCount = $db->query("SELECT COUNT(*) as count FROM institution_awards")->fetch()['count'];
    echo "\n✅ Institution-Award links: $linkCount\n";
    
} catch (PDOException $e) {
    echo "❌ Error verifying: " . $e->getMessage() . "\n";
}

echo "\n=== MIGRATION COMPLETE ===\n";
echo "\nNext steps:\n";
echo "1. Verify database in MySQL: php diagnose_db.php\n";
echo "2. Test award insertion: php test_awards.php\n";
echo "3. Try creating an institution with awards in the UI\n";
