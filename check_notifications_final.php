<?php
require_once 'app/Database/Database.php';

try {
    $pdo = App\Database\Database::getConnection();
    
    echo "=== Notification System Status ===\n";
    
    // Check total notifications
    $stmt = $pdo->query('SELECT COUNT(*) FROM notifications');
    $total = $stmt->fetchColumn();
    echo "Total notifications: $total\n";
    
    // Check unread notifications
    $stmt = $pdo->query('SELECT COUNT(*) FROM notifications WHERE is_read = 0');
    $unread = $stmt->fetchColumn();
    echo "Unread notifications: $unread\n";
    
    // Check by type
    $stmt = $pdo->query('SELECT type, COUNT(*) as count FROM notifications GROUP BY type');
    $types = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "\nNotifications by type:\n";
    foreach ($types as $type) {
        echo "- {$type['type']}: {$type['count']}\n";
    }
    
    // Check by priority
    $stmt = $pdo->query('SELECT priority, COUNT(*) as count FROM notifications GROUP BY priority');
    $priorities = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "\nNotifications by priority:\n";
    foreach ($priorities as $priority) {
        echo "- {$priority['priority']}: {$priority['count']}\n";
    }
    
    // Show sample notifications
    echo "\nSample notifications:\n";
    $stmt = $pdo->query('SELECT title, type, priority, is_read FROM notifications ORDER BY created_at DESC LIMIT 5');
    $samples = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($samples as $sample) {
        $status = $sample['is_read'] ? 'READ' : 'UNREAD';
        echo "- [{$sample['type']}] [{$sample['priority']}] [{$status}] {$sample['title']}\n";
    }
    
    echo "\n✅ Notification system is ready!\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?> 