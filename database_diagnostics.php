<?php
// Enable full error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Function to test database connection
function testDatabaseConnection($host, $user, $pass, $dbName = null) {
    try {
        $dsn = "mysql:host={$host}";
        if ($dbName) {
            $dsn .= ";dbname={$dbName}";
        }
        
        $db = new PDO($dsn, $user, $pass);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        echo "✅ Connection Successful!\n";
        echo "Host: {$host}\n";
        echo "User: {$user}\n";
        
        // List databases
        $stmt = $db->query("SHOW DATABASES");
        $databases = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        echo "\nAvailable Databases:\n";
        foreach ($databases as $database) {
            echo "- {$database}\n";
        }
        
        return true;
    } catch (PDOException $e) {
        echo "❌ Connection Failed: " . $e->getMessage() . "\n";
        return false;
    }
}

// Diagnostic Information
echo "🔍 PHP Database Diagnostics\n";
echo "========================\n\n";

// PHP and PDO Information
echo "PHP Version: " . phpversion() . "\n";
echo "PDO Drivers: " . implode(', ', PDO::getAvailableDrivers()) . "\n\n";

// Test Configurations
$testConfigs = [
    ['host' => 'localhost', 'user' => 'root', 'pass' => ''],
    ['host' => 'localhost', 'user' => 'root', 'pass' => 'root'],
    ['host' => '127.0.0.1', 'user' => 'root', 'pass' => ''],
    ['host' => '127.0.0.1', 'user' => 'root', 'pass' => 'root']
];

// Specific database to test
$specificDatabase = 'birth_certificate_system';

echo "🔬 Testing Database Connections:\n";
foreach ($testConfigs as $config) {
    echo "\nTesting: {$config['host']} (User: {$config['user']})\n";
    
    // Test general connection
    testDatabaseConnection($config['host'], $config['user'], $config['pass']);
    
    // Test specific database connection
    echo "\nTesting Database: {$specificDatabase}\n";
    testDatabaseConnection(
        $config['host'], 
        $config['user'], 
        $config['pass'], 
        $specificDatabase
    );
}

// Additional System Checks
echo "\n🖥️ System Checks:\n";
echo "MySQL Command Available: " . (shell_exec('mysql --version') ? 'Yes' : 'No') . "\n";
echo "XAMPP/MySQL Service Running: " . (shell_exec('tasklist | findstr "mysqld.exe"') ? 'Yes' : 'No') . "\n"; 