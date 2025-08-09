<?php
require_once 'app/Database/Database.php';

try {
    $pdo = App\Database\Database::getConnection();
    
    echo "=== Creating Sample Notifications ===\n";
    
    // Get users
    $stmt = $pdo->query("SELECT id, first_name, last_name FROM users LIMIT 5");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($users)) {
        echo "No users found.\n";
        exit;
    }
    
    echo "Found " . count($users) . " users\n";
    
    // Sample notifications
    $notifications = [
        [
            'title' => '🎉 Welcome to Birth Certificate System',
            'message' => 'Welcome! You can now apply for birth certificates online.',
            'type' => 'success',
            'priority' => 'normal'
        ],
        [
            'title' => '📋 Application Submitted',
            'message' => 'Your application has been submitted and is under review.',
            'type' => 'info',
            'priority' => 'normal'
        ],
        [
            'title' => '✅ Application Approved',
            'message' => 'Great news! Your application has been approved.',
            'type' => 'success',
            'priority' => 'high'
        ],
        [
            'title' => '💳 Payment Required',
            'message' => 'Payment is required to complete your application.',
            'type' => 'warning',
            'priority' => 'high'
        ],
        [
            'title' => '🚨 Urgent: Action Required',
            'message' => 'Immediate action is required for your application.',
            'type' => 'error',
            'priority' => 'urgent'
        ]
    ];
    
    $totalCreated = 0;
    
    // Create notifications for each user
    foreach ($users as $user) {
        echo "Creating notifications for {$user['first_name']} {$user['last_name']}...\n";
        
        // Create 2-4 notifications per user
        $numNotifications = rand(2, 4);
        
        for ($i = 0; $i < $numNotifications; $i++) {
            $notification = $notifications[array_rand($notifications)];
            
            $stmt = $pdo->prepare("
                INSERT INTO notifications (user_id, title, message, type, priority, is_read, created_at)
                VALUES (?, ?, ?, ?, ?, ?, NOW())
            ");
            
            $isRead = rand(1, 3) === 1 ? 1 : 0; // 33% chance of being read
            
            $success = $stmt->execute([
                $user['id'],
                $notification['title'],
                $notification['message'],
                $notification['type'],
                $notification['priority'],
                $isRead
            ]);
            
            if ($success) {
                $totalCreated++;
                
                // If marked as read, set read_at
                if ($isRead) {
                    $notificationId = $pdo->lastInsertId();
                    $stmt = $pdo->prepare("UPDATE notifications SET read_at = NOW() - INTERVAL ? HOUR WHERE id = ?");
                    $stmt->execute([rand(1, 24), $notificationId]);
                }
            }
        }
    }
    
    // Create a system announcement
    echo "Creating system announcement...\n";
    $stmt = $pdo->query("SELECT id FROM users WHERE status = 'active'");
    $allUsers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($allUsers as $user) {
        $stmt = $pdo->prepare("
            INSERT INTO notifications (user_id, title, message, type, priority, created_at)
            VALUES (?, ?, ?, ?, ?, NOW())
        ");
        
        $stmt->execute([
            $user['id'],
            '📢 System Announcement',
            'Welcome to the upgraded notification system! You can now receive real-time updates.',
            'announcement',
            'normal'
        ]);
        $totalCreated++;
    }
    
    echo "\n✅ Created $totalCreated notifications successfully!\n";
    
    // Show statistics
    $stmt = $pdo->query("
        SELECT 
            COUNT(*) as total,
            SUM(CASE WHEN is_read = 0 THEN 1 ELSE 0 END) as unread,
            SUM(CASE WHEN priority = 'urgent' THEN 1 ELSE 0 END) as urgent,
            SUM(CASE WHEN type = 'announcement' THEN 1 ELSE 0 END) as announcements
        FROM notifications
    ");
    $stats = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "\nNotification Statistics:\n";
    echo "- Total: {$stats['total']}\n";
    echo "- Unread: {$stats['unread']}\n";
    echo "- Urgent: {$stats['urgent']}\n";
    echo "- Announcements: {$stats['announcements']}\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?> 