<?php
// Enable full error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set base path
$basePath = __DIR__;

// Database connection details
$dbHost = 'localhost';
$dbName = 'birth_certificate_system';
$dbUser = 'root';
$dbPass = '1212';

// Logging function
function logMessage($message) {
    echo $message . "\n";
    $logFile = __DIR__ . '/verification_test_log.txt';
    file_put_contents($logFile, $message . "\n", FILE_APPEND);
}

// Function to generate unique application number
function generateApplicationNumber() {
    return 'APP-' . date('Y') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
}

// Function to generate QR code hash
function generateQRCodeHash($certificateNumber) {
    return hash('sha256', $certificateNumber . time());
}

try {
    // Attempt database connection
    $db = new PDO("mysql:host={$dbHost};dbname={$dbName}", $dbUser, $dbPass);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    logMessage("Database Connection Successful!");

    // Inspect certificates table structure
    $columnsQuery = "DESCRIBE certificates";
    $columnsStmt = $db->query($columnsQuery);
    $columns = $columnsStmt->fetchAll(PDO::FETCH_ASSOC);
    
    logMessage("Certificates Table Structure:");
    foreach ($columns as $column) {
        logMessage(json_encode($column));
    }

    // Inspect birth_applications table structure
    $columnsQuery = "DESCRIBE birth_applications";
    $columnsStmt = $db->query($columnsQuery);
    $columns = $columnsStmt->fetchAll(PDO::FETCH_ASSOC);
    
    logMessage("Birth Applications Table Structure:");
    foreach ($columns as $column) {
        logMessage(json_encode($column));
    }

    // Find a pending application
    $appQuery = "SELECT id, user_id, status FROM birth_applications LIMIT 5";
    $stmt = $db->prepare($appQuery);
    $stmt->execute();
    $applications = $stmt->fetchAll(PDO::FETCH_ASSOC);

    logMessage("Applications found: " . count($applications));
    foreach ($applications as $app) {
        logMessage("Application ID: {$app['id']}, User ID: {$app['user_id']}, Status: {$app['status']}");
    }

    // Find an admin/registrar user
    $userQuery = "SELECT id, username, role FROM users WHERE role IN ('admin', 'registrar') LIMIT 5";
    $stmt = $db->prepare($userQuery);
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    logMessage("Users found: " . count($users));
    foreach ($users as $user) {
        logMessage("User ID: {$user['id']}, Username: {$user['username']}, Role: {$user['role']}");
    }

    // Verification test
    $pendingAppQuery = "SELECT id, user_id FROM birth_applications WHERE status = 'submitted' LIMIT 1";
    $stmt = $db->prepare($pendingAppQuery);
    $stmt->execute();
    $application = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$application) {
        logMessage("No submitted applications found. Creating a test application...");
        
        // Find a user to associate with the application
        $userQuery = "SELECT id FROM users LIMIT 1";
        $userStmt = $db->prepare($userQuery);
        $userStmt->execute();
        $user = $userStmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            logMessage("No users found to create a test application!");
            exit(1);
        }

        // Generate unique application number
        $applicationNumber = generateApplicationNumber();

        // Create a test application
        $insertQuery = "INSERT INTO birth_applications 
                        (user_id, application_number, child_first_name, child_last_name, status, 
                         date_of_birth, place_of_birth, gender, 
                         mother_first_name, mother_last_name, 
                         created_at, submitted_at) 
                        VALUES (?, ?, 'Test', 'Child', 'submitted', 
                                '2023-01-01', 'Test City', 'male', 
                                'Mother', 'Test', NOW(), NOW())";
        $insertStmt = $db->prepare($insertQuery);
        $insertStmt->execute([$user['id'], $applicationNumber]);
        
        $application = [
            'id' => $db->lastInsertId(),
            'user_id' => $user['id']
        ];
        
        logMessage("Test application created with ID: {$application['id']}, Number: {$applicationNumber}");
    }

    // Find an admin/registrar user
    $verifierQuery = "SELECT id FROM users WHERE role IN ('admin', 'registrar') LIMIT 1";
    $verifierStmt = $db->prepare($verifierQuery);
    $verifierStmt->execute();
    $verifier = $verifierStmt->fetch(PDO::FETCH_ASSOC);

    if (!$verifier) {
        logMessage("No admin/registrar users found. Creating a test user...");
        
        // Create a test registrar user
        $insertUserQuery = "INSERT INTO users 
                            (username, email, password, role, first_name, last_name) 
                            VALUES 
                            ('test_registrar', 'registrar@example.com', 
                             '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 
                             'registrar', 'Test', 'Registrar')";
        $db->exec($insertUserQuery);
        
        $verifier = [
            'id' => $db->lastInsertId()
        ];
        
        logMessage("Test registrar user created with ID: {$verifier['id']}");
    }

    // Simulate verification process
    $applicationId = $application['id'];
    $verifierId = $verifier['id'];

    // Update application status
    $updateQuery = "UPDATE birth_applications 
                    SET status = 'approved', 
                        reviewed_at = NOW(), 
                        reviewed_by = ? 
                    WHERE id = ?";
    $updateStmt = $db->prepare($updateQuery);
    $updateStmt->execute([$verifierId, $applicationId]);

    // Create certificate
    $certificateNumber = 'BC' . date('Y') . str_pad($applicationId, 6, '0', STR_PAD_LEFT);
    $qrCodeHash = generateQRCodeHash($certificateNumber);

    $certificateQuery = "INSERT INTO certificates (application_id, certificate_number, qr_code_hash, issued_at, issued_by) 
                         VALUES (?, ?, ?, NOW(), ?)";
    $certificateStmt = $db->prepare($certificateQuery);
    $certificateStmt->execute([
        $applicationId, 
        $certificateNumber, 
        $qrCodeHash,
        $verifierId
    ]);

    logMessage("Verification Successful!");
    logMessage("Certificate Number: {$certificateNumber}");
    logMessage("QR Code Hash: {$qrCodeHash}");

    // Validate certificate
    $validateQuery = "SELECT c.*, ba.child_first_name, ba.child_last_name 
                      FROM certificates c
                      JOIN birth_applications ba ON c.application_id = ba.id
                      WHERE c.certificate_number = ?";
    $validateStmt = $db->prepare($validateQuery);
    $validateStmt->execute([$certificateNumber]);
    $certificateDetails = $validateStmt->fetch(PDO::FETCH_ASSOC);

    logMessage("\nCertificate Validation:");
    logMessage(print_r($certificateDetails, true));

} catch (Exception $e) {
    logMessage("Verification Test Failed: " . $e->getMessage());
    logMessage("Trace: " . $e->getTraceAsString());
    exit(1);
} 