<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'app/Database/Database.php';

echo "=== Live Notification Demo ===\n";

try {
    $pdo = App\Database\Database::getConnection();
    echo "✓ Database connected\n";
    
    // Get a test user
    $stmt = $pdo->prepare("SELECT id, first_name, last_name FROM users WHERE id = 1");
    $stmt->execute();
    $user = $stmt->fetch();
    
    if (!$user) {
        echo "✗ No test user found (ID: 1)\n";
        exit;
    }
    
    echo "✓ Test user: {$user['first_name']} {$user['last_name']}\n";
    
    // Send different types of test notifications
    $notifications = [
        [
            'title' => '🎉 Welcome to Live Notifications!',
            'message' => 'Your notification bell is now live and will update in real-time.',
            'type' => 'success',
            'priority' => 'high'
        ],
        [
            'title' => '📋 Application Update',
            'message' => 'Your birth certificate application #APP2025080001 is now under review.',
            'type' => 'info',
            'priority' => 'normal'
        ],
        [
            'title' => '💳 Payment Reminder',
            'message' => 'Payment of $25.00 is required to process your application.',
            'type' => 'warning',
            'priority' => 'high'
        ],
        [
            'title' => '🏆 Certificate Ready',
            'message' => 'Your birth certificate is ready for download!',
            'type' => 'success',
            'priority' => 'urgent'
        ]
    ];
    
    echo "\nSending test notifications...\n";
    
    foreach ($notifications as $index => $notification) {
        $stmt = $pdo->prepare("
            INSERT INTO notifications (user_id, title, message, type, priority, created_at)
            VALUES (?, ?, ?, ?, ?, NOW())
        ");
        
        $result = $stmt->execute([
            1, // User ID
            $notification['title'],
            $notification['message'],
            $notification['type'],
            $notification['priority']
        ]);
        
        if ($result) {
            echo "✓ Sent: {$notification['title']}\n";
        } else {
            echo "✗ Failed to send: {$notification['title']}\n";
        }
        
        // Add delay between notifications to simulate real-time updates
        sleep(2);
    }
    
    // Get final notification count
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = 1 AND is_read = 0");
    $stmt->execute();
    $unreadCount = $stmt->fetchColumn();
    
    echo "\n✅ Demo complete!\n";
    echo "📊 Total unread notifications: $unreadCount\n";
    echo "🔔 Open http://localhost:8000 to see the live notification bell in action!\n";
    echo "\nThe notification bell will:\n";
    echo "  • Show real-time count updates\n";
    echo "  • Display browser notifications (if permission granted)\n";
    echo "  • Show in-app toast notifications\n";
    echo "  • Play subtle notification sounds\n";
    echo "  • Auto-refresh every 15 seconds\n";
    echo "  • Update immediately when tab becomes active\n";
    
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
}

echo "\n=== Demo Complete ===\n";
?> 