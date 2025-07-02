<?php
// Script to run a specific migration

try {
    echo "Connecting to database...\n";
    // Connect to the database
    $pdo = new PDO('mysql:host=127.0.0.1;dbname=birth_certificate_system', 'root', '1212');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "Connected successfully!\n";
    
    // Read the migration file
    $migrationFile = 'database/migrations/024_add_email_verified_to_users.sql';
    echo "Reading migration file: $migrationFile\n";
    $sql = file_get_contents($migrationFile);
    
    if (!$sql) {
        echo "Failed to read migration file\n";
        exit(1);
    }
    
    // Execute the migration
    echo "Executing migration...\n";
    $pdo->exec($sql);
    echo "Migration executed successfully!\n";
    
    // Verify the migration
    echo "Verifying migration...\n";
    $stmt = $pdo->query("DESCRIBE users");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $foundEmailVerified = false;
    $foundEmailVerifiedAt = false;
    
    foreach ($columns as $column) {
        if ($column['Field'] === 'email_verified') {
            $foundEmailVerified = true;
            echo "Column 'email_verified' exists with type: " . $column['Type'] . "\n";
        }
        if ($column['Field'] === 'email_verified_at') {
            $foundEmailVerifiedAt = true;
            echo "Column 'email_verified_at' exists with type: " . $column['Type'] . "\n";
        }
    }
    
    if (!$foundEmailVerified) {
        echo "Column 'email_verified' was not created\n";
    }
    
    if (!$foundEmailVerifiedAt) {
        echo "Column 'email_verified_at' was not created\n";
    }
    
} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage() . "\n";
} 