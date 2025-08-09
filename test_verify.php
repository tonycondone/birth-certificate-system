<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "=== Certificate Verification Test ===\n";

try {
    require_once 'app/Database/Database.php';
    
    $pdo = App\Database\Database::getConnection();
    echo "✓ Database connection successful\n";
    
    // Test certificate number
    $certNumber = 'BC202508D7C911';
    echo "Testing certificate: $certNumber\n";
    
    // Check if certificate exists
    $stmt = $pdo->prepare('SELECT certificate_number, status, application_id FROM certificates WHERE certificate_number = ?');
    $stmt->execute([$certNumber]);
    $cert = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($cert) {
        echo "✓ Certificate found:\n";
        echo "  Number: {$cert['certificate_number']}\n";
        echo "  Status: {$cert['status']}\n";
        echo "  Application ID: {$cert['application_id']}\n";
        
        // Test the verification URL
        echo "\nTesting verification URL...\n";
        $url = "http://localhost:8000/verify?certificate_number=$certNumber";
        echo "URL: $url\n";
        
        // Test the NEW regex validation
        if (preg_match('/^BC[A-Z0-9]{12}$/', $certNumber)) {
            echo "✓ Certificate number format is valid (BC + 12 chars)\n";
        } else {
            echo "✗ Certificate number format is invalid\n";
            echo "  Length: " . strlen($certNumber) . "\n";
            echo "  Pattern: " . $certNumber . "\n";
        }
        
        // Test accessing the verification page directly
        echo "\nTesting direct URL access...\n";
        $verifyUrl = "http://localhost:8000/verify?certificate_number=$certNumber";
        echo "Try visiting: $verifyUrl\n";
        
    } else {
        echo "✗ Certificate not found\n";
    }
    
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}

echo "\n=== End Test ===\n";
?> 