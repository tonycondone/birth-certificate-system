<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "=== Certificate Debug Script ===\n";

try {
    require_once 'app/Database/Database.php';
    
    $pdo = App\Database\Database::getConnection();
    echo "✓ Database connection successful\n";
    
    // Check if certificates table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'certificates'");
    if ($stmt->rowCount() == 0) {
        echo "✗ certificates table does NOT exist\n";
        
        // Try to create the table
        echo "Creating certificates table...\n";
        $createSQL = "
            CREATE TABLE certificates (
                id INT AUTO_INCREMENT PRIMARY KEY,
                certificate_number VARCHAR(50) NOT NULL UNIQUE,
                application_id INT NOT NULL,
                qr_code_hash VARCHAR(255),
                issued_by INT,
                issued_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                status ENUM('active', 'revoked', 'expired') DEFAULT 'active',
                qr_code_data TEXT,
                digital_signature TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_certificate_number (certificate_number),
                INDEX idx_application_id (application_id),
                INDEX idx_status (status)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ";
        $pdo->exec($createSQL);
        echo "✓ certificates table created\n";
    } else {
        echo "✓ certificates table exists\n";
    }
    
    // Check certificates table structure
    $stmt = $pdo->query('DESCRIBE certificates');
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "Certificates table columns:\n";
    foreach ($columns as $col) {
        echo "  - {$col['Field']} ({$col['Type']}) {$col['Null']} {$col['Key']}\n";
    }
    
    // Check total certificates
    $stmt = $pdo->query('SELECT COUNT(*) FROM certificates');
    $count = $stmt->fetchColumn();
    echo "\nTotal certificates: $count\n";
    
    if ($count == 0) {
        echo "No certificates found. Let's create a test certificate.\n";
        
        // Check if there are any birth applications
        $stmt = $pdo->query('SELECT COUNT(*) FROM birth_applications');
        $appCount = $stmt->fetchColumn();
        echo "Total birth applications: $appCount\n";
        
        if ($appCount > 0) {
            // Get the first application
            $stmt = $pdo->query('SELECT id, child_first_name, child_last_name FROM birth_applications LIMIT 1');
            $app = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($app) {
                echo "Creating test certificate for application ID {$app['id']}...\n";
                
                $certNumber = 'BC' . date('Y') . date('m') . strtoupper(substr(md5(uniqid()), 0, 6));
                $insertStmt = $pdo->prepare("
                    INSERT INTO certificates (certificate_number, application_id, status, issued_at, qr_code_data) 
                    VALUES (?, ?, 'active', NOW(), ?)
                ");
                $qrData = json_encode(['certificate' => true, 'number' => $certNumber]);
                $insertStmt->execute([$certNumber, $app['id'], $qrData]);
                
                $newId = $pdo->lastInsertId();
                echo "✓ Test certificate created with ID: $newId, Number: $certNumber\n";
            }
        }
    } else {
        // Show existing certificates
        $stmt = $pdo->query('SELECT id, certificate_number, application_id, status FROM certificates ORDER BY id LIMIT 10');
        $certs = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo "First 10 certificates:\n";
        foreach ($certs as $cert) {
            echo "  ID: {$cert['id']}, Number: {$cert['certificate_number']}, App ID: {$cert['application_id']}, Status: {$cert['status']}\n";
        }
        
        // Check if certificate ID 15 exists
        $stmt = $pdo->prepare('SELECT * FROM certificates WHERE id = 15');
        $stmt->execute();
        $cert15 = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($cert15) {
            echo "\n✓ Certificate ID 15 EXISTS:\n";
            echo "  Number: {$cert15['certificate_number']}\n";
            echo "  Application ID: {$cert15['application_id']}\n";
            echo "  Status: {$cert15['status']}\n";
            echo "  Issued At: {$cert15['issued_at']}\n";
        } else {
            echo "\n✗ Certificate ID 15 does NOT exist\n";
        }
    }
    
    // Check birth_applications table
    $stmt = $pdo->query('SELECT COUNT(*) FROM birth_applications');
    $appCount = $stmt->fetchColumn();
    echo "\nTotal birth applications: $appCount\n";
    
    if ($appCount > 0) {
        // Check if there's an application that could be used for certificate 15
        $stmt = $pdo->query('SELECT id, child_first_name, child_last_name, status FROM birth_applications WHERE id <= 20 ORDER BY id DESC LIMIT 5');
        $apps = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo "Recent applications (ID <= 20):\n";
        foreach ($apps as $app) {
            echo "  ID: {$app['id']}, Child: {$app['child_first_name']} {$app['child_last_name']}, Status: {$app['status']}\n";
        }
    }
    
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}

echo "\n=== End Debug ===\n";
?> 