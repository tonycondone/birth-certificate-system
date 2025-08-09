<?php
require_once 'app/Database/Database.php';
$pdo = App\Database\Database::getConnection();

echo "Creating additional test certificates...\n";

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
        $insertStmt = $pdo->prepare('
            INSERT INTO certificates (certificate_number, application_id, status, issued_at, qr_code_data) 
            VALUES (?, ?, "active", NOW(), ?)
        ');
        $qrData = json_encode(['certificate' => true, 'number' => $certNumber]);
        $insertStmt->execute([$certNumber, $app['id'], $qrData]);
        
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

echo "Done!\n";
?> 