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
    
    // Prepare and execute the ALTER TABLE statement
    $stmt = $conn->prepare('ALTER TABLE users MODIFY COLUMN email_verified TINYINT(1) DEFAULT 1');
    $stmt->execute();
    
    // Update all existing users to have email_verified = 1
    $stmt = $conn->prepare('UPDATE users SET email_verified = 1 WHERE email_verified IS NULL');
    $stmt->execute();
    
    echo "Successfully modified email_verified column.\n";
    echo "Updated " . $stmt->rowCount() . " user(s) to have email_verified = 1\n";
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?> 