<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database connection configurations to try
$dbConfigs = [
    ['host' => 'localhost', 'name' => 'birth_certificate_system', 'user' => 'root', 'pass' => '1212'],
    ['host' => '127.0.0.1', 'name' => 'birth_certificate_system', 'user' => 'root', 'pass' => '1212']
];

$connectionSuccessful = false;

foreach ($dbConfigs as $config) {
    try {
        // Attempt database connection
        $db = new PDO(
            "mysql:host={$config['host']};dbname={$config['name']}", 
            $config['user'], 
            $config['pass']
        );
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        echo "Database Connection Successful!\n";
        echo "Connection Details:\n";
        echo "Host: {$config['host']}\n";
        echo "Database: {$config['name']}\n";
        echo "User: {$config['user']}\n";
        
        $connectionSuccessful = true;

        // Check for registrar/admin users
        $userQuery = "SELECT id, username, role FROM users WHERE role IN ('registrar', 'admin')";
        $userStmt = $db->query($userQuery);
        $users = $userStmt->fetchAll(PDO::FETCH_ASSOC);

        echo "\nRegistrar/Admin Users:\n";
        if (empty($users)) {
            echo "No registrar or admin users found. Creating test user...\n";
            
            // Create test registrar user
            $insertUserQuery = "
                INSERT INTO users 
                (username, email, password, role, first_name, last_name) 
                VALUES 
                ('test_registrar', 'registrar@example.com', 
                 '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 
                 'registrar', 'Test', 'Registrar')
            ";
            $db->exec($insertUserQuery);
            echo "Test registrar user created.\n";
        } else {
            foreach ($users as $user) {
                echo "- ID: {$user['id']}, Username: {$user['username']}, Role: {$user['role']}\n";
            }
        }

        // Check for pending applications
        $appQuery = "SELECT id, child_first_name, child_last_name, status FROM birth_applications WHERE status = 'pending'";
        $appStmt = $db->query($appQuery);
        $applications = $appStmt->fetchAll(PDO::FETCH_ASSOC);

        echo "\nPending Applications:\n";
        if (empty($applications)) {
            echo "No pending applications found. Creating test application...\n";
            
            // Create test application
            $insertAppQuery = "
                INSERT INTO birth_applications 
                (user_id, child_first_name, child_last_name, status, created_at) 
                VALUES 
                ((SELECT id FROM users WHERE email = 'registrar@example.com' LIMIT 1), 
                 'Test', 'Child', 'pending', NOW())
            ";
            $db->exec($insertAppQuery);
            echo "Test application created.\n";
        } else {
            foreach ($applications as $app) {
                echo "- ID: {$app['id']}, Child: {$app['child_first_name']} {$app['child_last_name']}, Status: {$app['status']}\n";
            }
        }

        break; // Stop trying other configurations if successful
    } catch (PDOException $e) {
        echo "Connection Failed with config: " . json_encode($config) . "\n";
        echo "Error: " . $e->getMessage() . "\n\n";
    }
}

if (!$connectionSuccessful) {
    echo "Could not connect to the database. Please check your database configuration.\n";
    echo "\nPHP Version: " . phpversion() . "\n";
    echo "PDO Drivers: " . implode(', ', PDO::getAvailableDrivers()) . "\n";
} 