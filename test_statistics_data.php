<?php
require_once __DIR__ . '/app/Database/Database.php';

use App\Database\Database;

try {
    $pdo = Database::getConnection();
    
    echo "Creating test data for statistics verification...\n";
    
    // Get a user ID to use for test applications
    $stmt = $pdo->query("SELECT id FROM users LIMIT 1");
    $user = $stmt->fetch();
    
    if (!$user) {
        echo "No users found. Creating a test user...\n";
        $stmt = $pdo->prepare("INSERT INTO users (first_name, last_name, email, password, role, email_verified) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute(['Test', 'Parent', 'test@example.com', password_hash('password123', PASSWORD_DEFAULT), 'parent', 1]);
        $userId = $pdo->lastInsertId();
    } else {
        $userId = $user['id'];
    }
    
    echo "Using user ID: $userId\n";
    
    // Create test applications
    $applications = [
        [
            'child_first_name' => 'John',
            'child_last_name' => 'Doe',
            'date_of_birth' => '2024-01-15',
            'place_of_birth' => 'City General Hospital',
            'gender' => 'male',
            'mother_first_name' => 'Jane',
            'mother_last_name' => 'Doe',
            'status' => 'submitted'
        ],
        [
            'child_first_name' => 'Mary',
            'child_last_name' => 'Smith',
            'date_of_birth' => '2024-02-20',
            'place_of_birth' => 'Regional Medical Center',
            'gender' => 'female',
            'mother_first_name' => 'Sarah',
            'mother_last_name' => 'Smith',
            'status' => 'approved'
        ],
        [
            'child_first_name' => 'David',
            'child_last_name' => 'Johnson',
            'date_of_birth' => '2024-03-10',
            'place_of_birth' => 'Community Hospital',
            'gender' => 'male',
            'mother_first_name' => 'Lisa',
            'mother_last_name' => 'Johnson',
            'status' => 'approved'
        ]
    ];
    
    $applicationIds = [];
    
    foreach ($applications as $app) {
        $stmt = $pdo->prepare("
            INSERT INTO birth_applications (
                user_id, child_first_name, child_last_name, date_of_birth, 
                place_of_birth, gender, mother_first_name, mother_last_name, 
                status, application_number, submitted_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        $appNumber = 'APP' . date('Ymd') . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT);
        
        $stmt->execute([
            $userId,
            $app['child_first_name'],
            $app['child_last_name'],
            $app['date_of_birth'],
            $app['place_of_birth'],
            $app['gender'],
            $app['mother_first_name'],
            $app['mother_last_name'],
            $app['status'],
            $appNumber,
            date('Y-m-d H:i:s')
        ]);
        
        $applicationId = $pdo->lastInsertId();
        $applicationIds[] = $applicationId;
        
        echo "Created application ID: $applicationId with status: {$app['status']}\n";
    }
    
    // Verify the data
    echo "\n=== VERIFICATION ===\n";
    
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM birth_applications");
    $totalApps = $stmt->fetch()['count'];
    echo "Total Applications: $totalApps\n";
    
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM birth_applications WHERE status = 'submitted'");
    $pendingApps = $stmt->fetch()['count'];
    echo "Submitted Applications: $pendingApps\n";
    
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM birth_applications WHERE status = 'approved'");
    $approvedApps = $stmt->fetch()['count'];
    echo "Approved Applications: $approvedApps\n";
    
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
    $totalUsers = $stmt->fetch()['count'];
    echo "Total Users: $totalUsers\n";
    
    echo "\nTest data created successfully!\n";
    echo "Now refresh the homepage to see updated statistics.\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
