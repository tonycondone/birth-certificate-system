<?php
require_once 'app/Database/Database.php';
$pdo = App\Database\Database::getConnection();

echo "Cleaning up test certificates...\n";

// First, let's see what we have
$stmt = $pdo->query('SELECT COUNT(*) FROM certificates');
$totalBefore = $stmt->fetchColumn();
echo "Total certificates before cleanup: $totalBefore\n";

// Show the certificates we're about to remove (IDs 10 and above, which were the test ones)
$stmt = $pdo->query('SELECT id, certificate_number, application_id FROM certificates WHERE id >= 10 ORDER BY id');
$testCerts = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "Test certificates to be removed:\n";
foreach ($testCerts as $cert) {
    echo "  ID: {$cert['id']}, Number: {$cert['certificate_number']}, App ID: {$cert['application_id']}\n";
}

// Remove the test certificates (keep the original 9)
$deleteStmt = $pdo->prepare('DELETE FROM certificates WHERE id >= 10');
$deleteStmt->execute();
$deletedCount = $deleteStmt->rowCount();

echo "\nDeleted $deletedCount test certificates\n";

// Check final status
$stmt = $pdo->query('SELECT COUNT(*) FROM certificates');
$totalAfter = $stmt->fetchColumn();
echo "Total certificates after cleanup: $totalAfter\n";

$stmt = $pdo->query('SELECT MAX(id) FROM certificates');
$maxId = $stmt->fetchColumn();
echo "Highest certificate ID now: $maxId\n";

// Show remaining certificates
echo "\nRemaining certificates:\n";
$stmt = $pdo->query('SELECT id, certificate_number, application_id FROM certificates ORDER BY id');
$remainingCerts = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($remainingCerts as $cert) {
    echo "  ID: {$cert['id']}, Number: {$cert['certificate_number']}, App ID: {$cert['application_id']}\n";
}

echo "\n✓ Cleanup completed!\n";
?> 