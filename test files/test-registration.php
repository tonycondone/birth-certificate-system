<?php
// Load environment variables
if (file_exists(__DIR__ . '/../.env')) {
    $envContent = file_get_contents(__DIR__ . '/../.env');
    $envLines = explode("\n", $envContent);
    foreach ($envLines as $line) {
        $line = trim($line);
        if (empty($line) || strpos($line, '#') === 0) continue;
        
        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value, '"\'');
            $_ENV[$key] = $value;
            putenv("$key=$value");
        }
    }
}

// Test registration system
require_once __DIR__ . '/../app/Database/Database.php';
require_once __DIR__ . '/../app/Auth/Authentication.php';

echo "Testing Registration System\n";
echo "==========================\n\n";

try {
    // Test database connection
    $pdo = App\Database\Database::getConnection();
    echo "✓ Database connection successful\n";
    
    // Test user creation
    $auth = new App\Auth\Authentication($pdo);
    
    $testUser = [
        'username' => 'testuser_' . time(),
        'email' => 'test' . time() . '@example.com',
        'password' => 'testpass123',
        'first_name' => 'Test',
        'last_name' => 'User',
        'phone_number' => '1234567890',
        'role' => 'parent',
        'national_id' => 'TEST123456'
    ];
    
    $auth->register($testUser);
    echo "✓ User registration successful\n";
    
    // Test login
    $loginResult = $auth->login($testUser['email'], 'testpass123');
    echo "✓ User login successful\n";
    
    // Clean up - delete test user
    $stmt = $pdo->prepare("DELETE FROM users WHERE email = ?");
    $stmt->execute([$testUser['email']]);
    echo "✓ Test user cleaned up\n";
    
    echo "\n✅ Registration system is working correctly!\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}


?>
