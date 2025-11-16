<?php
/**
 * Setup Awards - Insert award categories into database
 * Run this once to populate the awards table
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../core/Database.php';

$db = Database::getInstance()->getConnection();

// Awards to insert
$awards = [
    [
        'id' => 14,
        'award_number' => '14',
        'category' => 'Award No. 14 - Financial Institution of the Year for Best Digital Payment'
    ],
    [
        'id' => 6,
        'award_number' => '6A',
        'category' => 'Award No. 6A - Most Popular Digital Payment Product - State Banks'
    ],
    [
        'id' => 7,
        'award_number' => '6B',
        'category' => 'Award No. 6B - Most Popular Digital Payment Product - Private Banks'
    ],
    [
        'id' => 8,
        'award_number' => '7',
        'category' => 'Award No. 7 - Best Digital Payment Innovation'
    ],
    [
        'id' => 9,
        'award_number' => '8',
        'category' => 'Award No. 8 - Best Digital Payment Security'
    ]
];

try {
    $stmt = $db->prepare("INSERT INTO awards (id, award_number, category) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE award_number = VALUES(award_number), category = VALUES(category)");
    
    foreach ($awards as $award) {
        $stmt->execute([
            $award['id'],
            $award['award_number'],
            $award['category']
        ]);
        echo "✓ Inserted/Updated award: {$award['category']}\n";
    }
    
    echo "\n✅ Awards setup completed successfully!\n";
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}
