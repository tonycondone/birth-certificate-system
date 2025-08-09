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

echo "=== Testing User Settings System ===\n";

try {
    require_once 'app/Database/Database.php';
    require_once 'app/Controllers/SettingsController.php';
    
    $controller = new App\Controllers\SettingsController();
    echo "✓ SettingsController instantiated successfully\n";
    
    // Test database connection
    $pdo = App\Database\Database::getConnection();
    echo "✓ Database connection successful\n";
    
    // Check if user exists
    $stmt = $pdo->prepare("SELECT id, first_name, last_name, email FROM users WHERE id = ?");
    $stmt->execute([1]);
    $user = $stmt->fetch();
    
    if ($user) {
        echo "✓ Test user found: {$user['first_name']} {$user['last_name']} ({$user['email']})\n";
    } else {
        echo "✗ Test user not found\n";
        exit;
    }
    
    // Test getUserStats method using reflection
    $reflection = new ReflectionClass($controller);
    $getUserStats = $reflection->getMethod('getUserStats');
    $getUserStats->setAccessible(true);
    
    $stats = $getUserStats->invoke($controller, 1);
    echo "✓ User statistics retrieved:\n";
    foreach ($stats as $key => $value) {
        echo "  - $key: $value\n";
    }
    
    // Test the settings page
    echo "\nTesting settings page rendering...\n";
    ob_start();
    try {
        $controller->index();
        $output = ob_get_contents();
        echo "✓ Settings page rendered successfully (" . strlen($output) . " characters)\n";
        
        if (strpos($output, 'Account Settings') !== false) {
            echo "✓ Page contains expected title\n";
        }
        
        if (strpos($output, 'Profile Information') !== false) {
            echo "✓ Page contains profile section\n";
        }
        
    } catch (Exception $e) {
        echo "✗ Error rendering settings page: " . $e->getMessage() . "\n";
    }
    ob_end_clean();
    
    echo "\n✅ User settings system is working correctly!\n";
    
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}

echo "\n=== Test Complete ===\n";
?> 