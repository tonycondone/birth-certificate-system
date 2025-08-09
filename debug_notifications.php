<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "=== Notification System Debug ===\n";

// Start session
session_start();

// Set up test user session
$_SESSION['user_id'] = 1;
$_SESSION['role'] = 'user';

try {
    // Test database connection
    require_once 'app/Database/Database.php';
    $pdo = App\Database\Database::getConnection();
    echo "✓ Database connection successful\n";
    
    // Check if notifications table exists and has correct structure
    $stmt = $pdo->query("SHOW TABLES LIKE 'notifications'");
    if ($stmt->rowCount() > 0) {
        echo "✓ Notifications table exists\n";
        
        // Check table structure
        $stmt = $pdo->query('DESCRIBE notifications');
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo "Table columns:\n";
        foreach ($columns as $col) {
            echo "  - {$col['Field']} ({$col['Type']})\n";
        }
    } else {
        echo "✗ Notifications table does not exist\n";
        exit;
    }
    
    // Test NotificationController instantiation
    require_once 'app/Controllers/NotificationController.php';
    echo "✓ NotificationController class loaded\n";
    
    $controller = new App\Controllers\NotificationController();
    echo "✓ NotificationController instantiated\n";
    
    // Test getting user notifications
    $userId = 1;
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ?");
    $stmt->execute([$userId]);
    $userNotifications = $stmt->fetchColumn();
    echo "✓ User has $userNotifications notifications\n";
    
    // Test the getUserNotifications method by accessing it through reflection
    $reflection = new ReflectionClass($controller);
    $method = $reflection->getMethod('getUserNotifications');
    $method->setAccessible(true);
    
    try {
        $notifications = $method->invoke($controller, $userId, 'all', 'all', 0, 10);
        echo "✓ getUserNotifications method works - returned " . count($notifications) . " notifications\n";
    } catch (Exception $e) {
        echo "✗ Error in getUserNotifications: " . $e->getMessage() . "\n";
    }
    
    // Test the index method
    echo "\nTesting index method...\n";
    ob_start();
    try {
        $controller->index();
        $output = ob_get_contents();
        echo "✓ Index method executed successfully\n";
        if (strpos($output, 'error') !== false) {
            echo "⚠ Output contains error messages\n";
        }
    } catch (Exception $e) {
        echo "✗ Error in index method: " . $e->getMessage() . "\n";
        echo "Stack trace: " . $e->getTraceAsString() . "\n";
    }
    ob_end_clean();
    
} catch (Exception $e) {
    echo "✗ Fatal error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}

echo "\n=== Debug Complete ===\n";
?> 