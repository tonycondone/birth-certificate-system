<?php
// Test database connection and create database if needed

// Define base path
define('BASE_PATH', __DIR__);

// Load environment variables
if (file_exists(__DIR__ . '/.env')) {
    $envFile = __DIR__ . '/.env';
} else {
    die('Environment file not found.');
}

// Load environment variables
$envContent = file_get_contents($envFile);
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

// Include the Database class
require_once __DIR__ . '/app/Database/Database.php';

echo "Testing database connection...\n";

try {
    // Attempt to connect to the database
    $pdo = \App\Database\Database::getConnection();
    echo "Connection successful!\n";
    
    // Test query
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "Database tables:\n";
    foreach ($tables as $table) {
        echo "- $table\n";
    }
    
} catch (Exception $e) {
    echo "Connection failed: " . $e->getMessage() . "\n";
    
    // Debug information
    echo "\nDebug information:\n";
    echo "Host: " . ($_ENV['DB_HOST'] ?? 'Not set') . "\n";
    echo "Database: " . ($_ENV['DB_DATABASE'] ?? 'Not set') . "\n";
    echo "Username: " . ($_ENV['DB_USERNAME'] ?? 'Not set') . "\n";
    echo "Password: " . (isset($_ENV['DB_PASSWORD']) ? '[HIDDEN]' : 'Not set') . "\n";
}

echo "\nDone.\n"; 