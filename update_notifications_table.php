<?php
require_once 'app/Database/Database.php';

try {
    $pdo = App\Database\Database::getConnection();
    
    echo "Adding missing columns to notifications table...\n";
    
    // Add priority column
    try {
        $pdo->exec("ALTER TABLE notifications ADD COLUMN priority ENUM('low', 'normal', 'high', 'urgent') DEFAULT 'normal' AFTER type");
        echo "✓ Added priority column\n";
    } catch (Exception $e) {
        if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
            echo "✓ Priority column already exists\n";
        } else {
            echo "Error adding priority column: " . $e->getMessage() . "\n";
        }
    }
    
    // Add scheduled_for column
    try {
        $pdo->exec("ALTER TABLE notifications ADD COLUMN scheduled_for TIMESTAMP NULL AFTER read_at");
        echo "✓ Added scheduled_for column\n";
    } catch (Exception $e) {
        if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
            echo "✓ Scheduled_for column already exists\n";
        } else {
            echo "Error adding scheduled_for column: " . $e->getMessage() . "\n";
        }
    }
    
    // Add metadata column
    try {
        $pdo->exec("ALTER TABLE notifications ADD COLUMN metadata JSON NULL AFTER scheduled_for");
        echo "✓ Added metadata column\n";
    } catch (Exception $e) {
        if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
            echo "✓ Metadata column already exists\n";
        } else {
            echo "Error adding metadata column: " . $e->getMessage() . "\n";
        }
    }
    
    // Update type enum to include 'announcement'
    try {
        $pdo->exec("ALTER TABLE notifications MODIFY COLUMN type ENUM('info', 'success', 'warning', 'error', 'announcement') DEFAULT 'info'");
        echo "✓ Updated type column to include 'announcement'\n";
    } catch (Exception $e) {
        echo "Error updating type column: " . $e->getMessage() . "\n";
    }
    
    echo "\nTable structure updated successfully!\n";
    
    // Show current table structure
    echo "\nCurrent notifications table structure:\n";
    $stmt = $pdo->query('DESCRIBE notifications');
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($columns as $col) {
        echo "  - {$col['Field']} ({$col['Type']}) {$col['Null']} {$col['Key']}\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?> 