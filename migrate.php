<?php
require_once __DIR__ . '/vendor/autoload.php';
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Manually load environment variables from .env or .env.example
$envFile = file_exists(__DIR__ . '/.env') ? __DIR__ . '/.env' : __DIR__ . '/.env.example';
if (!file_exists($envFile)) {
    echo "No environment file found (.env or .env.example).\n";
    exit(1);
}
$envLines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
foreach ($envLines as $line) {
    $line = trim($line);
    if ($line === '' || strpos($line, '#') === 0) {
        continue;
    }
    if (strpos($line, '=') !== false) {
        list($key, $value) = explode('=', $line, 2);
        $key = trim($key);
        $value = trim($value, "'\"");
        $_ENV[$key] = $value;
        putenv("$key=$value");
    }
}

echo "Starting migrations...\n";

$dbHost = getenv('DB_HOST') ?: null;
$dbName = getenv('DB_NAME') ?: null;
$dbUser = getenv('DB_USER') ?: null;
$dbPass = getenv('DB_PASS') ?: null;

if (!$dbHost || !$dbName || !$dbUser) {
    echo "Database configuration incomplete in .env.\n";
    exit(1);
}

$dsn = "mysql:host={$dbHost};dbname={$dbName};charset=utf8mb4";

try {
    $pdo = new \PDO($dsn, $dbUser, $dbPass, [
        \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
    ]);
} catch (\PDOException $e) {
    echo "Connection failed: " . $e->getMessage() . "\n";
    exit(1);
}

$migrationDir = __DIR__ . '/database/migrations';
$files = glob($migrationDir . '/*.sql');
// Sort migration files by their numeric prefix
usort($files, function ($a, $b) {
    preg_match('/^(\d+)_/', basename($a), $matchA);
    preg_match('/^(\d+)_/', basename($b), $matchB);
    $numA = isset($matchA[1]) ? (int)$matchA[1] : 0;
    $numB = isset($matchB[1]) ? (int)$matchB[1] : 0;
    return $numA <=> $numB;
});

foreach ($files as $file) {
    $filename = basename($file);
    echo "Running migration: {$filename}\n";
    // Skip migrations already applied in Phase 1 (001-016)
    $num = (int) preg_replace('/_.*/', '', $filename);
    if ($num < 17) {
        echo "Skipping old migration {$filename}\n\n";
        continue;
    }
    try {
        $sql = file_get_contents($file);
        if ($sql === false) {
            throw new \Exception("Failed to read file {$filename}");
        }
        $pdo->exec($sql);
        echo "Migration {$filename} completed successfully.\n\n";
    } catch (\Throwable $e) {
        // Skip migrations that have already been applied (duplicate table/column errors)
        if (strpos($e->getMessage(), 'already exists') !== false) {
            echo "Skipping already applied migration {$filename}: " . $e->getMessage() . "\n\n";
            continue;
        }
        // Other errors: abort
        echo "Error running migration {$filename}: " . $e->getMessage() . "\n";
        exit(1);
    }
}

echo "All migrations completed successfully!\n"; 