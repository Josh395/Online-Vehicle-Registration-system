<?php
include 'config.php';

try {
    // Add rejection_reason column if it doesn't exist
    $pdo->exec("ALTER TABLE applications ADD COLUMN rejection_reason TEXT DEFAULT NULL AFTER payment_status");
    echo "✓ Database schema updated successfully. rejection_reason column added.\n";
} catch (PDOException $e) {
    if (strpos($e->getMessage(), 'Duplicate column') !== false) {
        echo "✓ Column already exists.\n";
    } else {
        echo "✗ Error: " . $e->getMessage() . "\n";
    }
}
?>
