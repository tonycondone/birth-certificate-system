<?php
// Database connection parameters
$host = '127.0.0.1';
$dbname = 'birth_certificate_system';
$username = 'root';
$password = '1212';  // Replace with your actual database password

try {
    // Create PDO connection
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    
    // Set the PDO error mode to exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Get column details
    $stmt = $conn->query('DESCRIBE users');
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Find email_verified column
    $emailVerifiedColumn = null;
    foreach ($columns as $column) {
        if ($column['Field'] === 'email_verified') {
            $emailVerifiedColumn = $column;
            break;
        }
    }
    
    // Print column details
    if ($emailVerifiedColumn) {
        echo "Email Verified Column Details:\n";
        echo "Field: " . $emailVerifiedColumn['Field'] . "\n";
        echo "Type: " . $emailVerifiedColumn['Type'] . "\n";
        echo "Null: " . $emailVerifiedColumn['Null'] . "\n";
        echo "Key: " . $emailVerifiedColumn['Key'] . "\n";
        echo "Default: " . ($emailVerifiedColumn['Default'] ?? 'NULL') . "\n";
        echo "Extra: " . $emailVerifiedColumn['Extra'] . "\n";
        
        // Check current data
        $stmt = $conn->query('SELECT COUNT(*) as total, SUM(email_verified) as verified FROM users');
        $counts = $stmt->fetch(PDO::FETCH_ASSOC);
        
        echo "\nUser Verification Status:\n";
        echo "Total Users: " . $counts['total'] . "\n";
        echo "Verified Users: " . $counts['verified'] . "\n";
    } else {
        echo "Email Verified column not found in users table.\n";
    }
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?> 