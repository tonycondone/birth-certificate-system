<?php
require_once __DIR__ . '/app/Auth/Authentication.php';
require_once __DIR__ . '/app/Database/Database.php';

echo "Testing Password Validation\n";
echo "==========================\n\n";

// Test cases
$testPasswords = [
    '123456789Asd=', // Should pass (has letters, numbers, 8+ chars)
    'password123',   // Should pass (has letters, numbers, 8+ chars)
    'Password1',     // Should pass (has letters, numbers, 8+ chars)
    '12345678',      // Should fail (no letters)
    'abcdefgh',      // Should fail (no numbers)
    '1234567',       // Should fail (too short, no letters)
    'abc123',        // Should fail (too short)
    '123456789Asd',  // Should pass (has letters, numbers, 8+ chars)
];

$auth = new App\Auth\Authentication();

foreach ($testPasswords as $password) {
    try {
        $auth->validatePassword($password);
        echo "✓ PASS: '$password' - Valid password\n";
    } catch (Exception $e) {
        echo "✗ FAIL: '$password' - " . $e->getMessage() . "\n";
    }
}

echo "\nTesting Complete!\n";
?> 