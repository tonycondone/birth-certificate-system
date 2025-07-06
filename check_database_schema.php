<?php
require_once __DIR__ . '/app/Database/Database.php';

use App\Database\Database;

try {
    $pdo = Database::getConnection();
    
    echo "=== CHECKING DATABASE SCHEMA ===\n\n";
    
    // Check what tables exist
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "Existing tables:\n";
    foreach ($tables as $table) {
        echo "- $table\n";
    }
    
    echo "\n";
    
    // Check applications table structure if it exists
    if (in_array('applications', $tables)) {
        echo "=== APPLICATIONS TABLE STRUCTURE ===\n";
        $stmt = $pdo->query("DESCRIBE applications");
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($columns as $column) {
            echo "- {$column['Field']} ({$column['Type']}) - {$column['Null']} - {$column['Key']}\n";
        }
    } else {
        echo "Applications table does not exist!\n";
    }
    
    echo "\n";
    
    // Check birth_applications table structure if it exists
    if (in_array('birth_applications', $tables)) {
        echo "=== BIRTH_APPLICATIONS TABLE STRUCTURE ===\n";
        $stmt = $pdo->query("DESCRIBE birth_applications");
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($columns as $column) {
            echo "- {$column['Field']} ({$column['Type']}) - {$column['Null']} - {$column['Key']}\n";
        }
    } else {
        echo "Birth_applications table does not exist!\n";
    }
    
    echo "\n";
    
    // Check certificates table structure if it exists
    if (in_array('certificates', $tables)) {
        echo "=== CERTIFICATES TABLE STRUCTURE ===\n";
        $stmt = $pdo->query("DESCRIBE certificates");
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($columns as $column) {
            echo "- {$column['Field']} ({$column['Type']}) - {$column['Null']} - {$column['Key']}\n";
        }
    } else {
        echo "Certificates table does not exist!\n";
    }
    
    echo "\n";
    
    // Check users table structure
    if (in_array('users', $tables)) {
        echo "=== USERS TABLE STRUCTURE ===\n";
        $stmt = $pdo->query("DESCRIBE users");
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($columns as $column) {
            echo "- {$column['Field']} ({$column['Type']}) - {$column['Null']} - {$column['Key']}\n";
        }
        
        // Count users
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
        $userCount = $stmt->fetch()['count'];
        echo "\nTotal users in database: $userCount\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
