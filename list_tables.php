<?php
// Hardcoded database credentials
$host = '127.0.0.1';
$dbname = 'birth_certificate_system';
$user = 'root';
$pass = '1212';

try {
    // Create PDO connection
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Get all tables
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);

    echo "Tables in database '$dbname':\n";
    foreach ($tables as $table) {
        echo "- $table\n";
    }
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage() . "\n";
} 