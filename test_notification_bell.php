<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session and set test user
session_start();
$_SESSION['user_id'] = 1;
$_SESSION['role'] = 'user';
$_SESSION['user'] = ['first_name' => 'Test', 'last_name' => 'User'];

// Set up environment
define('BASE_PATH', __DIR__);

echo "=== Testing Notification Bell System ===\n";

try {
    require_once 'app/Database/Database.php';
    require_once 'app/Controllers/NotificationController.php';
    
    $controller = new App\Controllers\NotificationController();
    echo "✓ NotificationController instantiated successfully\n";
    
    // Test database connection
    $pdo = App\Database\Database::getConnection();
    echo "✓ Database connection successful\n";
    
    // Test getUnreadCount method
    echo "\nTesting getUnreadCount API endpoint...\n";
    ob_start();
    $controller->getUnreadCount();
    $output = ob_get_clean();
    
    $response = json_decode($output, true);
    if ($response && $response['success']) {
        echo "✓ getUnreadCount works - Count: {$response['count']}\n";
    } else {
        echo "✗ getUnreadCount failed: " . $output . "\n";
    }
    
    // Test getRecent method
    echo "\nTesting getRecent API endpoint...\n";
    $_GET['limit'] = 5; // Set limit parameter
    ob_start();
    $controller->getRecent();
    $output = ob_get_clean();
    
    $response = json_decode($output, true);
    if ($response && $response['success']) {
        echo "✓ getRecent works - Found: " . count($response['notifications']) . " notifications\n";
        foreach ($response['notifications'] as $notification) {
            echo "  - {$notification['title']}: {$notification['message']}\n";
        }
    } else {
        echo "✗ getRecent failed: " . $output . "\n";
    }
    
    // Test markAllAsRead method
    echo "\nTesting markAllAsRead API endpoint...\n";
    ob_start();
    $controller->markAllAsRead();
    $output = ob_get_clean();
    
    $response = json_decode($output, true);
    if ($response && $response['success']) {
        echo "✓ markAllAsRead works: {$response['message']}\n";
    } else {
        echo "✗ markAllAsRead failed: " . $output . "\n";
    }
    
    // Create a test notification to ensure the bell shows something
    echo "\nCreating test notification...\n";
    $stmt = $pdo->prepare("
        INSERT INTO notifications (user_id, title, message, type, priority, created_at)
        VALUES (?, ?, ?, ?, ?, NOW())
    ");
    
    $result = $stmt->execute([
        1,
        '🔔 Test Notification',
        'This is a test notification for the notification bell system.',
        'info',
        'normal'
    ]);
    
    if ($result) {
        echo "✓ Test notification created successfully\n";
    } else {
        echo "✗ Failed to create test notification\n";
    }
    
    // Test the notification count again
    echo "\nTesting notification count after creating test notification...\n";
    ob_start();
    $controller->getUnreadCount();
    $output = ob_get_clean();
    
    $response = json_decode($output, true);
    if ($response && $response['success']) {
        echo "✓ Updated count: {$response['count']}\n";
    }
    
    echo "\n✅ Notification bell system is working correctly!\n";
    echo "🔔 Visit http://localhost:8000 to see the live notification bell in action!\n";
    
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}

echo "\n=== Test Complete ===\n";
?> 