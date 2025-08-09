<?php
require_once 'app/Database/Database.php';
$pdo = App\Database\Database::getConnection();

echo "Creating additional test certificates...\n";

// First, let's check the actual table structure
$stmt = $pdo->query('DESCRIBE certificates');
$columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo "Actual certificate table columns:\n";
foreach ($columns as $col) {
    echo "  - {$col['Field']} ({$col['Type']})\n";
}
echo "\n";

for ($i = 10; $i <= 20; $i++) {
    // Check if there's a birth application for this ID
    $stmt = $pdo->prepare('SELECT id, child_first_name, child_last_name FROM birth_applications WHERE id = ?');
    $stmt->execute([$i]);
    $app = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($app) {
        // Check if certificate already exists for this application
        $checkStmt = $pdo->prepare('SELECT id FROM certificates WHERE application_id = ?');
        $checkStmt->execute([$app['id']]);
        if ($checkStmt->fetch()) {
            echo "Certificate already exists for application {$app['id']}\n";
            continue;
        }
        
        $certNumber = 'BC' . date('Y') . date('m') . strtoupper(substr(md5(uniqid() . $i), 0, 6));
        
        // Use the correct column names based on the actual table structure
        $insertStmt = $pdo->prepare('
            INSERT INTO certificates (certificate_number, application_id, status, issued_at, qr_code_hash, issued_by) 
            VALUES (?, ?, "active", NOW(), ?, 1)
        ');
        $qrHash = hash('sha256', $certNumber . time());
        $insertStmt->execute([$certNumber, $app['id'], $qrHash]);
        
        $newId = $pdo->lastInsertId();
        echo "Created certificate ID $newId for application {$app['id']} ({$app['child_first_name']} {$app['child_last_name']})\n";
    } else {
        echo "No birth application found with ID $i\n";
    }
}

// Show final status
$stmt = $pdo->query('SELECT COUNT(*) FROM certificates');
$count = $stmt->fetchColumn();
echo "\nTotal certificates now: $count\n";

$stmt = $pdo->query('SELECT MAX(id) FROM certificates');
$maxId = $stmt->fetchColumn();
echo "Highest certificate ID: $maxId\n";

// Check if certificate ID 15 exists now
$stmt = $pdo->prepare('SELECT id, certificate_number, application_id FROM certificates WHERE id = 15');
$stmt->execute();
$cert15 = $stmt->fetch(PDO::FETCH_ASSOC);
if ($cert15) {
    echo "✓ Certificate ID 15 now exists: {$cert15['certificate_number']} (App ID: {$cert15['application_id']})\n";
} else {
    echo "✗ Certificate ID 15 still does not exist\n";
}

echo "Done!\n";
?> 