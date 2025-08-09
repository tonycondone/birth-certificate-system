<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session and set user
session_start();
$_SESSION['user_id'] = 1;
$_SESSION['role'] = 'user';

echo "Testing NotificationController step by step...\n";

try {
    require_once 'app/Database/Database.php';
    require_once 'app/Controllers/NotificationController.php';
    
    $controller = new App\Controllers\NotificationController();
    $userId = 1;
    $userRole = 'user';
    
    echo "✓ Controller instantiated\n";
    
    // Test getUserNotifications method
    $reflection = new ReflectionClass($controller);
    
    // Test getUserNotifications
    $getUserNotifications = $reflection->getMethod('getUserNotifications');
    $getUserNotifications->setAccessible(true);
    
    $notifications = $getUserNotifications->invoke($controller, $userId, 'all', 'all', 0, 15);
    echo "✓ getUserNotifications returned " . count($notifications) . " notifications\n";
    
    // Test countUserNotifications
    $countUserNotifications = $reflection->getMethod('countUserNotifications');
    $countUserNotifications->setAccessible(true);
    
    $totalCount = $countUserNotifications->invoke($controller, $userId, 'all', 'all');
    echo "✓ countUserNotifications returned $totalCount\n";
    
    // Test getNotificationStats
    $getNotificationStats = $reflection->getMethod('getNotificationStats');
    $getNotificationStats->setAccessible(true);
    
    $stats = $getNotificationStats->invoke($controller, $userId, $userRole);
    echo "✓ getNotificationStats returned:\n";
    print_r($stats);
    
    echo "\n✅ All methods work correctly!\n";
    
    // Test if view file exists
    $viewPath = __DIR__ . '/resources/views/notifications.php';
    if (file_exists($viewPath)) {
        echo "✓ View file exists: $viewPath\n";
    } else {
        echo "✗ View file missing: $viewPath\n";
    }
    
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}
?> 