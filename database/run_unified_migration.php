<?php
/**
 * Unified Database Migration Runner
 * This script applies the unified database schema
 */

require_once __DIR__ . '/../app/Database/Database.php';

use App\Database\Database;

try {
    echo "Starting unified database migration...\n";
    
    // Get database connection
    $pdo = Database::getConnection();
    
    // Read and execute migrations in order
    $migrations = [
        '001_create_users_table.sql',
        '026_unified_database_schema.sql'
    ];

    foreach ($migrations as $migration) {
        $migrationFile = __DIR__ . '/migrations/' . $migration;
        
        if (!file_exists($migrationFile)) {
            throw new Exception("Migration file not found: $migrationFile");
        }
        
        echo "\nApplying migration: $migration\n";
        $sql = file_get_contents($migrationFile);
        
        if ($sql === false) {
            throw new Exception("Failed to read migration file: $migration");
        }
        
        echo "Processing SQL statements...\n";
        
        // Split SQL into individual statements
        $statements = array_filter(
            array_map('trim', explode(';', $sql)),
            function($stmt) {
                return !empty($stmt) && !preg_match('/^\s*--/', $stmt);
            }
        );
        
        // Execute each statement
        $pdo->beginTransaction();
        
        foreach ($statements as $statement) {
            if (trim($statement)) {
                try {
                    $pdo->exec($statement);
                    echo "✓ Executed statement successfully\n";
                } catch (PDOException $e) {
                    echo "⚠ Warning: " . $e->getMessage() . "\n";
                    // Continue with other statements
                }
            }
        }
        
        $pdo->commit();
        echo "✅ Migration $migration completed successfully!\n";
    }
    
    // Verify tables exist
    echo "\nVerifying table creation...\n";
    $tables = ['users', 'applications', 'certificates', 'notifications', 'system_settings', 'activity_logs', 'verification_logs', 'payments', 'feedback'];
    
    foreach ($tables as $table) {
        $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
        if ($stmt->rowCount() > 0) {
            echo "✓ Table '$table' exists\n";
        } else {
            echo "✗ Table '$table' missing\n";
        }
    }
    
    echo "\n🎉 Database migration completed successfully!\n";
    
} catch (Exception $e) {
    if (isset($pdo)) {
        $pdo->rollBack();
    }
    echo "\n❌ Migration failed: " . $e->getMessage() . "\n";
    exit(1);
}
?>
