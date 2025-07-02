<?php
// Simple script to update user email verification status

try {
    echo "Connecting to database...\n";
    // Connect to the database
    $pdo = new PDO('mysql:host=127.0.0.1;dbname=birth_certificate_system', 'root', '1212');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "Connected successfully!\n";
    
    // Check if user exists
    $email = 'tou@gmail.com';
    echo "Checking if user exists: $email\n";
    $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ?');
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user) {
        echo "User found! Current status:\n";
        echo "ID: " . $user['id'] . "\n";
        echo "Email: " . $user['email'] . "\n";
        echo "Email Verified: " . ($user['email_verified'] ? 'Yes' : 'No') . "\n";
        
        // Update the user
        echo "Updating user...\n";
        $stmt = $pdo->prepare('UPDATE users SET email_verified = 1 WHERE email = ?');
        $stmt->execute([$email]);
        echo "User updated: " . $stmt->rowCount() . " row(s)\n";
        
        // Verify the update
        echo "Verifying update...\n";
        $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ?');
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        echo "Updated user details:\n";
        echo "ID: " . $user['id'] . "\n";
        echo "Email: " . $user['email'] . "\n";
        echo "Email Verified: " . ($user['email_verified'] ? 'Yes' : 'No') . "\n";
    } else {
        echo "User not found with email: $email\n";
    }
    
} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage() . "\n";
} 