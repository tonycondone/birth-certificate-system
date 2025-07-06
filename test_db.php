<?php

// Load environment variables
if (file_exists('.env')) {
    $lines = file('.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, '#') === 0) continue;
        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            $_ENV[trim($key)] = trim($value, '"');
        }
    }
}

require_once 'app/Database/Database.php';

use App\Database\Database;

echo "=== Birth Certificate System Database Test ===\n\n";

// Test database connection
echo "1. Testing database connection...\n";
try {
    $diagnostics = Database::getDiagnostics();
    
    echo "   Host: " . $diagnostics['host'] . "\n";
    echo "   Database: " . $diagnostics['database'] . "\n";
    echo "   Username: " . $diagnostics['username'] . "\n";
    echo "   Status: " . $diagnostics['connection_status'] . "\n";
    
    if ($diagnostics['connection_status'] === 'Connected') {
        echo "   ✓ Database connection successful!\n";
        echo "   Tables found: " . count($diagnostics['tables']) . "\n";
        if (!empty($diagnostics['tables'])) {
            echo "   Table list: " . implode(', ', $diagnostics['tables']) . "\n";
        }
    } else {
        echo "   ✗ Database connection failed: " . ($diagnostics['error'] ?? 'Unknown error') . "\n";
    }
} catch (Exception $e) {
    echo "   ✗ Error: " . $e->getMessage() . "\n";
}

echo "\n2. Testing basic query...\n";
try {
    if (Database::testConnection()) {
        echo "   ✓ Basic query test passed!\n";
    } else {
        echo "   ✗ Basic query test failed!\n";
    }
} catch (Exception $e) {
    echo "   ✗ Query test error: " . $e->getMessage() . "\n";
}

echo "\n=== Test Complete ===\n";