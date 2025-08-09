<?php
require_once 'app/Database/Database.php';

try {
    $pdo = App\Database\Database::getConnection();
    echo "Checking and fixing notifications table...\n";
    
    // Get current columns
    $stmt = $pdo->query('DESCRIBE notifications');
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $columnNames = array_column($columns, 'Field');
    
    echo "Current columns: " . implode(', ', $columnNames) . "\n";
    
    // Check if we're missing the updated_at column that the view expects
    if (!in_array('updated_at', $columnNames)) {
        echo "Adding updated_at column...\n";
        $pdo->exec("ALTER TABLE notifications ADD COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER created_at");
        echo "✓ Added updated_at column\n";
    }
    
    // Ensure all columns have proper defaults
    $pdo->exec("ALTER TABLE notifications MODIFY COLUMN is_read TINYINT(1) DEFAULT 0");
    $pdo->exec("ALTER TABLE notifications MODIFY COLUMN type ENUM('info','success','warning','error','announcement') DEFAULT 'info'");
    $pdo->exec("ALTER TABLE notifications MODIFY COLUMN priority ENUM('low','normal','high','urgent') DEFAULT 'normal'");
    
    echo "✓ Updated column defaults\n";
    
    // Test creating a sample notification to ensure everything works
    $stmt = $pdo->prepare("
        INSERT INTO notifications (user_id, title, message, type, priority, created_at)
        VALUES (?, ?, ?, ?, ?, NOW())
    ");
    
    $stmt->execute([1, 'Test Notification', 'This is a test to verify the table structure works.', 'info', 'normal']);
    echo "✓ Test notification created successfully\n";
    
    // Show final table structure
    echo "\nFinal table structure:\n";
    $stmt = $pdo->query('DESCRIBE notifications');
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($columns as $col) {
        echo "  - {$col['Field']} ({$col['Type']}) {$col['Null']} {$col['Key']} {$col['Default']}\n";
    }
    
    echo "\n✅ Notifications table is now properly configured!\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?> 