<?php
echo "Testing PDO MySQL Extension\n";
echo "===========================\n\n";

// Check if PDO is available
if (!extension_loaded('pdo')) {
    echo "✗ PDO extension is not loaded\n";
    exit(1);
}
echo "✓ PDO extension is loaded\n";

// Check if PDO MySQL is available
if (!extension_loaded('pdo_mysql')) {
    echo "✗ PDO MySQL extension is not loaded\n";
    echo "Available PDO drivers: " . implode(', ', PDO::getAvailableDrivers()) . "\n";
    exit(1);
}
echo "✓ PDO MySQL extension is loaded\n";

// Try to connect to MySQL
try {
    $pdo = new PDO("mysql:host=127.0.0.1", "root", "1212", [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    echo "✓ Successfully connected to MySQL server\n";
    
    // Check MySQL version
    $version = $pdo->query('SELECT VERSION()')->fetchColumn();
    echo "✓ MySQL version: $version\n";
    
} catch (PDOException $e) {
    echo "✗ Failed to connect to MySQL: " . $e->getMessage() . "\n";
} 