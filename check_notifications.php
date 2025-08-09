<?php
require_once 'app/Database/Database.php';

try {
    $pdo = App\Database\Database::getConnection();
    echo "=== Notifications Table Check ===\n";
    
    // Check if table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'notifications'");
    $tableExists = $stmt->rowCount() > 0;
    
    if ($tableExists) {
        echo "✓ Notifications table exists\n";
        
        // Show structure
        echo "\nTable structure:\n";
        $stmt = $pdo->query('DESCRIBE notifications');
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($columns as $col) {
            echo "  - {$col['Field']} ({$col['Type']}) {$col['Null']} {$col['Key']}\n";
        }
        
        // Show sample data
        echo "\nSample notifications:\n";
        $stmt = $pdo->query('SELECT * FROM notifications LIMIT 5');
        $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($notifications as $notif) {
            echo "  - ID: {$notif['id']}, User: {$notif['user_id']}, Title: {$notif['title']}\n";
        }
        
    } else {
        echo "✗ Notifications table does not exist\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?> 