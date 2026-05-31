<?php
// Run this script from project root: php scripts/make_notifications_nullable.php
// It will attempt to make notifications.application_id nullable and preserve the FK.
require __DIR__ . '/../config.php';

try {
    echo "Attempting to modify notifications.application_id to allow NULL...\n";
    $pdo->exec("ALTER TABLE notifications MODIFY COLUMN application_id INT NULL");
    echo "Column modified successfully.\n";
    exit(0);
} catch (PDOException $e) {
    echo "Initial MODIFY failed: " . $e->getMessage() . "\n";
    // Try to find and drop foreign key, then modify and re-add
    try {
        $row = $pdo->query("SHOW CREATE TABLE notifications")->fetch(PDO::FETCH_ASSOC);
        $create = $row['Create Table'] ?? $row['Create Table'] ?? '';
        if (preg_match('/CONSTRAINT `([^`]+)` FOREIGN KEY \(`application_id`\)/i', $create, $m)) {
            $fk = $m[1];
            echo "Found foreign key: $fk. Dropping it...\n";
            $pdo->exec("ALTER TABLE notifications DROP FOREIGN KEY `$fk`");
            echo "Dropped foreign key. Modifying column...\n";
            $pdo->exec("ALTER TABLE notifications MODIFY COLUMN application_id INT NULL");
            echo "Column modified. Re-adding foreign key...\n";
            $pdo->exec("ALTER TABLE notifications ADD CONSTRAINT `$fk` FOREIGN KEY (application_id) REFERENCES applications(id) ON DELETE CASCADE");
            echo "Foreign key re-added. Done.\n";
            exit(0);
        } else {
            echo "Could not locate foreign key name in CREATE TABLE output.\n";
            echo "Please run the ALTER manually or inspect SHOW CREATE TABLE notifications.\n";
            exit(2);
        }
    } catch (PDOException $e2) {
        echo "Failed to alter table: " . $e2->getMessage() . "\n";
        exit(3);
    }
}
