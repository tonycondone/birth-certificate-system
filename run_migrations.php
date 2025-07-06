<?php
require_once 'vendor/autoload.php';

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Print environment variables
echo "DB_HOST: " . getenv('DB_HOST') . "\n";
echo "DB_NAME: " . getenv('DB_NAME') . "\n";
echo "DB_USER: " . getenv('DB_USER') . "\n";
echo "DB_PASS: " . getenv('DB_PASS') . "\n";

// Hardcoded database credentials
$host = '127.0.0.1';
$dbname = 'birth_certificate_system';
$user = 'root';
$pass = '1212';

try {
    // Create PDO connection with buffered queries
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $user, $pass, [
        PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true
    ]);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Migration files directory
    $migrationDir = __DIR__ . '/database/migrations/';

    // Get all SQL migration files and sort them
    $migrationFiles = glob($migrationDir . '*.sql');
    sort($migrationFiles);

    // Run each migration
    foreach ($migrationFiles as $file) {
        $sql = file_get_contents($file);
        
        // Split SQL file into individual statements
        $statements = array_filter(array_map('trim', explode(';', $sql)));
        
        foreach ($statements as $statement) {
            if (!empty($statement)) {
                try {
                    $pdo->exec($statement);
                    echo "Executed: " . substr($statement, 0, 100) . "...\n";
                } catch (PDOException $e) {
                    // Log the error but continue with other migrations
                    echo "Warning in file " . basename($file) . ": " . $e->getMessage() . "\n";
                }
            }
        }
    }

    echo "Migration process completed.\n";
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage() . "\n";
} 