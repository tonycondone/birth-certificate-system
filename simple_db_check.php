<?php
require_once __DIR__ . '/app/Database/Database.php';

use App\Database\Database;

try {
    $pdo = Database::getConnection();
    echo "Database connection successful!\n";
    
    // Show all tables
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "Tables found:\n";
    foreach ($tables as $table) {
        echo "- $table\n";
        
        // Show structure of each table
        try {
            $stmt = $pdo->query("DESCRIBE $table");
            $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
            echo "  Columns: " . implode(', ', $columns) . "\n";
        } catch (Exception $e) {
            echo "  Error describing table: " . $e->getMessage() . "\n";
        }
        echo "\n";
    }
    
} catch (Exception $e) {
    echo "Database error: " . $e->getMessage() . "\n";
}
