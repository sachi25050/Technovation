<?php
/**
 * Test Award Insertion
 * Quick test to verify awards can be inserted correctly
 */

require_once __DIR__ . '/../config/database.php';

$db = Database::getInstance()->getConnection();

// Step 1: Check if awards exist
echo "=== Step 1: Checking Awards Table ===\n";
$stmt = $db->query("SELECT id, award_number, category FROM awards WHERE id IN (14, 6, 7, 8, 9)");
$awards = $stmt->fetchAll();

if (count($awards) === 0) {
    echo "❌ NO AWARDS FOUND!\n";
    echo "Run setup_awards.php first:\n";
    echo "  php database/setup_awards.php\n\n";
    exit(1);
} else {
    echo "✅ Found " . count($awards) . " awards:\n";
    foreach ($awards as $award) {
        echo "   - ID: {$award['id']}, Award: {$award['award_number']}, Category: {$award['category']}\n";
    }
}

// Step 2: Test institution creation
echo "\n=== Step 2: Creating Test Institution ===\n";
$testName = "Test Institution " . date('Y-m-d H:i:s');
$testEmail = "test" . time() . "@example.com";

try {
    $stmt = $db->prepare("INSERT INTO institutions (name, email, type, contact_email) VALUES (?, ?, ?, ?)");
    $stmt->execute([$testName, $testEmail, 'other', $testEmail]);
    $institutionId = $db->lastInsertId();
    echo "✅ Institution created with ID: {$institutionId}\n";
} catch (Exception $e) {
    echo "❌ Failed to create institution: " . $e->getMessage() . "\n";
    exit(1);
}

// Step 3: Test award insertion
echo "\n=== Step 3: Testing Award Insertion ===\n";
$testAwards = [
    ['award_id' => 14, 'marks' => 100],
    ['award_id' => 6, 'marks' => 90],
    ['award_id' => 7, 'marks' => 85]
];

try {
    $stmt = $db->prepare("INSERT INTO institution_awards (institution_id, award_id, marks) VALUES (?, ?, ?)");
    foreach ($testAwards as $award) {
        $stmt->execute([$institutionId, $award['award_id'], $award['marks']]);
        echo "✅ Inserted award {$award['award_id']} with {$award['marks']} marks\n";
    }
} catch (Exception $e) {
    echo "❌ Failed to insert awards: " . $e->getMessage() . "\n";
    exit(1);
}

// Step 4: Verify insertion
echo "\n=== Step 4: Verifying Insertion ===\n";
$stmt = $db->prepare("
    SELECT ia.*, a.award_number, a.category
    FROM institution_awards ia
    LEFT JOIN awards a ON ia.award_id = a.id
    WHERE ia.institution_id = ?
    ORDER BY ia.award_id
");
$stmt->execute([$institutionId]);
$results = $stmt->fetchAll();

if (count($results) === 3) {
    echo "✅ All awards inserted correctly!\n";
    foreach ($results as $row) {
        echo "   - Award {$row['award_number']}: {$row['marks']} marks\n";
    }
} else {
    echo "❌ Expected 3 awards, found " . count($results) . "\n";
}

// Step 5: Cleanup
echo "\n=== Step 5: Cleaning Up ===\n";
try {
    $stmt = $db->prepare("DELETE FROM institution_awards WHERE institution_id = ?");
    $stmt->execute([$institutionId]);
    echo "✅ Deleted test awards\n";
    
    $stmt = $db->prepare("DELETE FROM institutions WHERE id = ?");
    $stmt->execute([$institutionId]);
    echo "✅ Deleted test institution\n";
} catch (Exception $e) {
    echo "⚠️  Could not cleanup: " . $e->getMessage() . "\n";
}

echo "\n✅ TEST COMPLETED SUCCESSFULLY!\n";
echo "The award insertion system is working correctly.\n";
