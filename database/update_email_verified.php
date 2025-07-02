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
    
    // Prepare and execute the update statement
    $stmt = $conn->prepare('UPDATE users SET email_verified = 1, email_verified_at = NOW()');
    $stmt->execute();
    
    echo "Successfully updated " . $stmt->rowCount() . " users to be email verified.\n";
    
    // Verify the update
    $stmt = $conn->query('SELECT COUNT(*) as total, SUM(email_verified) as verified FROM users');
    $counts = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "Total Users: " . $counts['total'] . "\n";
    echo "Verified Users: " . $counts['verified'] . "\n";
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?> 