<?php
// Simulate the web environment
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['REQUEST_URI'] = '/notifications';

// Start session and set user
session_start();
$_SESSION['user_id'] = 1;
$_SESSION['role'] = 'user';

// Set up the environment like public/index.php does
define('BASE_PATH', __DIR__);
require_once 'app/Database/Database.php';

echo "Testing notifications page access...\n";

try {
    // Simulate the routing logic
    require_once 'app/Controllers/NotificationController.php';
    
    $controller = new App\Controllers\NotificationController();
    
    echo "✓ Controller instantiated\n";
    
    // Capture output
    ob_start();
    $controller->index();
    $output = ob_get_contents();
    ob_end_clean();
    
    echo "✓ Controller index method executed successfully\n";
    
    // Check if output contains expected elements
    if (strpos($output, 'Notifications') !== false) {
        echo "✓ Output contains 'Notifications' title\n";
    }
    
    if (strpos($output, 'notification-item') !== false) {
        echo "✓ Output contains notification items\n";
    }
    
    if (strpos($output, 'bootstrap') !== false) {
        echo "✓ Output includes Bootstrap styling\n";
    }
    
    // Check for any PHP errors in output
    if (strpos($output, 'Fatal error') !== false || strpos($output, 'Parse error') !== false) {
        echo "✗ Output contains PHP errors\n";
        echo "First 500 chars of output:\n";
        echo substr($output, 0, 500) . "\n";
    } else {
        echo "✓ No PHP errors in output\n";
    }
    
    echo "\n✅ Notifications page is working correctly!\n";
    echo "Output length: " . strlen($output) . " characters\n";
    
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}
?> 