<?php
require_once 'app/Database/Database.php';

try {
    $pdo = App\Database\Database::getConnection();
    
    // Check users
    $stmt = $pdo->query('SELECT COUNT(*) FROM users');
    $userCount = $stmt->fetchColumn();
    echo "Users in database: $userCount\n";
    
    if ($userCount == 0) {
        echo "No users found. Creating a test user...\n";
        
        $stmt = $pdo->prepare("
            INSERT INTO users (first_name, last_name, email, password, role, status, created_at)
            VALUES (?, ?, ?, ?, ?, ?, NOW())
        ");
        
        $stmt->execute([
            'Test',
            'User',
            'test@example.com',
            password_hash('password123', PASSWORD_DEFAULT),
            'user',
            'active'
        ]);
        
        $userId = $pdo->lastInsertId();
        echo "Created test user with ID: $userId\n";
    } else {
        // Get first user
        $stmt = $pdo->query('SELECT id, first_name, last_name FROM users LIMIT 1');
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        $userId = $user['id'];
        echo "Using existing user: {$user['first_name']} {$user['last_name']} (ID: $userId)\n";
    }
    
    // Create a test notification
    $stmt = $pdo->prepare("
        INSERT INTO notifications (user_id, title, message, type, priority, created_at)
        VALUES (?, ?, ?, ?, ?, NOW())
    ");
    
    $stmt->execute([
        $userId,
        '🔔 Test Notification',
        'This is a test notification to verify the system is working correctly.',
        'info',
        'normal'
    ]);
    
    echo "Created test notification\n";
    
    // Check notification count
    $stmt = $pdo->query('SELECT COUNT(*) FROM notifications');
    $notificationCount = $stmt->fetchColumn();
    echo "Total notifications in database: $notificationCount\n";
    
    echo "✅ Notification system test completed successfully!\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?> 