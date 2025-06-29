<?php
/**
 * Database Migration Runner
 * Runs all SQL migration files in the database/migrations directory
 */

// Load environment variables
if (file_exists(__DIR__ . '/.env')) {
    $envContent = file_get_contents(__DIR__ . '/.env');
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

// Database configuration
$host = $_ENV['DB_HOST'] ?? '127.0.0.1';
$dbname = $_ENV['DB_NAME'] ?? 'birth_certificate_system';
$username = $_ENV['DB_USER'] ?? 'root';
$password = $_ENV['DB_PASS'] ?? '';

echo "Database Migration Runner\n";
echo "========================\n\n";
echo "Database Configuration:\n";
echo "Host: $host\n";
echo "Database: $dbname\n";
echo "User: $username\n\n";

try {
    // Use the existing Database class
    require_once __DIR__ . '/app/Database/Database.php';
    $pdo = \App\Database\Database::getConnection();
    
    echo "Connected to database successfully!\n\n";
    
    // Get all migration files
    $migrationFiles = glob(__DIR__ . '/database/migrations/*.sql');
    sort($migrationFiles);
    
    if (empty($migrationFiles)) {
        echo "No migration files found!\n";
        exit(1);
    }
    
    echo "Found " . count($migrationFiles) . " migration files.\n\n";
    
    // Run each migration
    foreach ($migrationFiles as $file) {
        $filename = basename($file);
        echo "Running migration: $filename\n";
        
        $sql = file_get_contents($file);
        if (empty(trim($sql))) {
            echo "  Skipping empty file: $filename\n";
            continue;
        }
        
        // Split SQL by semicolon to handle multiple statements
        $statements = array_filter(array_map('trim', explode(';', $sql)));
        
        foreach ($statements as $statement) {
            if (!empty($statement)) {
                $pdo->exec($statement);
            }
        }
        
        echo "  ✓ Migration completed: $filename\n";
    }
    
    echo "\nAll migrations completed successfully!\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
} 