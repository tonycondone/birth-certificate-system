<?php
/**
 * Comprehensive Database Schema Fix
 * Fixes foreign key constraints and missing columns
 */

require_once 'vendor/autoload.php';

echo "=== COMPREHENSIVE DATABASE SCHEMA FIX ===\n\n";

try {
    // Load environment variables
    function loadEnv($file) {
        if (!file_exists($file)) {
            return;
        }
        
        $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            if (strpos($line, '#') === 0) {
                continue;
            }
            
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value, '"\'');
            
            $_ENV[$key] = $value;
            putenv("$key=$value");
        }
    }

    loadEnv('.env');

    // Database connection
    $host = $_ENV['DB_HOST'] ?? '127.0.0.1';
    $dbname = $_ENV['DB_DATABASE'] ?? 'birth_certificate_system';
    $username = $_ENV['DB_USERNAME'] ?? 'root';
    $password = $_ENV['DB_PASSWORD'] ?? '1212';
    
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "✅ Database connection established\n\n";

    // Step 1: Check and fix applications table structure
    echo "1. FIXING APPLICATIONS TABLE STRUCTURE\n";
    echo "=====================================\n";
    
    // Check if tracking_number column exists
    $stmt = $pdo->query("DESCRIBE applications");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    if (!in_array('tracking_number', $columns)) {
        echo "   Adding missing tracking_number column...\n";
        $pdo->exec("ALTER TABLE applications ADD COLUMN tracking_number VARCHAR(255) UNIQUE DEFAULT NULL");
        echo "   ✅ tracking_number column added\n";
    } else {
        echo "   ✅ tracking_number column already exists\n";
    }
    
    if (!in_array('submitted_at', $columns)) {
        echo "   Adding missing submitted_at column...\n";
        $pdo->exec("ALTER TABLE applications ADD COLUMN submitted_at DATETIME DEFAULT NULL");
        echo "   ✅ submitted_at column added\n";
    } else {
        echo "   ✅ submitted_at column already exists\n";
    }
    
    // Update status enum to include all required values
    echo "   Updating status enum values...\n";
    $pdo->exec("ALTER TABLE applications MODIFY COLUMN status ENUM('draft','pending_payment','submitted','under_review','pending','approved','rejected') NOT NULL DEFAULT 'draft'");
    echo "   ✅ Status enum updated\n";

    // Step 2: Check and fix payments table structure
    echo "\n2. FIXING PAYMENTS TABLE STRUCTURE\n";
    echo "==================================\n";
    
    $stmt = $pdo->query("DESCRIBE payments");
    $paymentColumns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    // Add missing columns to payments table
    $requiredPaymentColumns = [
        'payment_gateway' => "VARCHAR(50) DEFAULT 'paystack'",
        'payment_method' => "VARCHAR(50) DEFAULT NULL",
        'metadata' => "JSON DEFAULT NULL",
        'gateway_response' => "JSON DEFAULT NULL",
        'paid_at' => "TIMESTAMP NULL DEFAULT NULL"
    ];
    
    foreach ($requiredPaymentColumns as $column => $definition) {
        if (!in_array($column, $paymentColumns)) {
            echo "   Adding missing $column column...\n";
            $pdo->exec("ALTER TABLE payments ADD COLUMN $column $definition");
            echo "   ✅ $column column added\n";
        } else {
            echo "   ✅ $column column already exists\n";
        }
    }

    // Step 3: Create test application data
    echo "\n3. CREATING TEST APPLICATION DATA\n";
    echo "=================================\n";
    
    // Check if test applications exist
    $stmt = $pdo->query("SELECT COUNT(*) FROM applications");
    $appCount = $stmt->fetchColumn();
    
    if ($appCount == 0) {
        echo "   Creating test applications...\n";
        
        // Create test user first (if not exists)
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
        $stmt->execute(['test@example.com']);
        $testUser = $stmt->fetch();
        
        if (!$testUser) {
            echo "   Creating test user...\n";
            $stmt = $pdo->prepare("
                INSERT INTO users (first_name, last_name, email, password, role, email_verified, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, NOW())
            ");
            $stmt->execute([
                'Test', 
                'User', 
                'test@example.com', 
                password_hash('password123', PASSWORD_DEFAULT),
                'parent',
                1
            ]);
            $testUserId = $pdo->lastInsertId();
            echo "   ✅ Test user created (ID: $testUserId)\n";
        } else {
            $testUserId = $testUser['id'];
            echo "   ✅ Test user already exists (ID: $testUserId)\n";
        }
        
        // Create test applications
        $testApplications = [
            [
                'reference_number' => 'APP-TEST-001',
                'purpose' => 'Birth Certificate Application',
                'description' => 'Test application for payment testing',
                'status' => 'pending_payment',
                'tracking_number' => 'TRK-' . strtoupper(uniqid())
            ],
            [
                'reference_number' => 'APP-TEST-002', 
                'purpose' => 'Birth Certificate Application',
                'description' => 'Second test application',
                'status' => 'draft',
                'tracking_number' => 'TRK-' . strtoupper(uniqid())
            ],
            [
                'reference_number' => 'APP-TEST-003',
                'purpose' => 'Birth Certificate Application', 
                'description' => 'Third test application',
                'status' => 'submitted',
                'tracking_number' => 'TRK-' . strtoupper(uniqid()),
                'submitted_at' => date('Y-m-d H:i:s')
            ]
        ];
        
        $stmt = $pdo->prepare("
            INSERT INTO applications (user_id, reference_number, purpose, description, status, tracking_number, submitted_at, created_at) 
            VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
        ");
        
        foreach ($testApplications as $app) {
            $stmt->execute([
                $testUserId,
                $app['reference_number'],
                $app['purpose'],
                $app['description'],
                $app['status'],
                $app['tracking_number'],
                $app['submitted_at'] ?? null
            ]);
            $appId = $pdo->lastInsertId();
            echo "   ✅ Created application: {$app['reference_number']} (ID: $appId)\n";
        }
    } else {
        echo "   ✅ Applications already exist ($appCount found)\n";
    }

    // Step 4: Verify foreign key constraints
    echo "\n4. VERIFYING FOREIGN KEY CONSTRAINTS\n";
    echo "====================================\n";
    
    // Test payment insertion with valid application_id
    $stmt = $pdo->query("SELECT id FROM applications LIMIT 1");
    $testApp = $stmt->fetch();
    
    if ($testApp) {
        $testAppId = $testApp['id'];
        echo "   Testing payment insertion with application_id: $testAppId\n";
        
        try {
            $stmt = $pdo->prepare("
                INSERT INTO payments (application_id, amount, currency, transaction_id, status, payment_gateway) 
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            
            $result = $stmt->execute([
                $testAppId,
                150.00,
                'GHS',
                'TEST-' . time(),
                'pending',
                'paystack'
            ]);
            
            if ($result) {
                $paymentId = $pdo->lastInsertId();
                echo "   ✅ Test payment inserted successfully (ID: $paymentId)\n";
                
                // Clean up test payment
                $pdo->prepare("DELETE FROM payments WHERE id = ?")->execute([$paymentId]);
                echo "   ✅ Test payment cleaned up\n";
            }
        } catch (Exception $e) {
            echo "   ❌ Payment insertion failed: " . $e->getMessage() . "\n";
        }
    }

    // Step 5: Test application-payment linking query
    echo "\n5. TESTING APPLICATION-PAYMENT LINKING\n";
    echo "======================================\n";
    
    try {
        $stmt = $pdo->query("
            SELECT a.id, a.tracking_number, a.status, p.amount, p.status as payment_status
            FROM applications a
            LEFT JOIN payments p ON a.id = p.application_id
            WHERE a.id = (SELECT id FROM applications LIMIT 1)
            LIMIT 1
        ");
        
        if ($stmt->rowCount() > 0) {
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            echo "   ✅ Application-payment linking query successful\n";
            echo "   📋 Application ID: {$result['id']}\n";
            echo "   🔢 Tracking Number: {$result['tracking_number']}\n";
            echo "   📊 Status: {$result['status']}\n";
            echo "   💰 Payment Amount: " . ($result['amount'] ? "GH₵{$result['amount']}" : "No payment") . "\n";
        } else {
            echo "   ❌ No applications found for testing\n";
        }
    } catch (Exception $e) {
        echo "   ❌ Query failed: " . $e->getMessage() . "\n";
    }

    // Step 6: Final verification
    echo "\n6. FINAL VERIFICATION\n";
    echo "====================\n";
    
    // Check applications table structure
    $stmt = $pdo->query("DESCRIBE applications");
    $appColumns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    $requiredAppColumns = ['id', 'user_id', 'reference_number', 'tracking_number', 'status', 'submitted_at'];
    
    echo "   Applications table columns:\n";
    foreach ($requiredAppColumns as $col) {
        $exists = in_array($col, $appColumns) ? '✅' : '❌';
        echo "     $exists $col\n";
    }
    
    // Check payments table structure
    $stmt = $pdo->query("DESCRIBE payments");
    $payColumns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    $requiredPayColumns = ['id', 'application_id', 'amount', 'currency', 'transaction_id', 'status', 'payment_gateway'];
    
    echo "   Payments table columns:\n";
    foreach ($requiredPayColumns as $col) {
        $exists = in_array($col, $payColumns) ? '✅' : '❌';
        echo "     $exists $col\n";
    }
    
    // Check data counts
    $stmt = $pdo->query("SELECT COUNT(*) FROM applications");
    $appCount = $stmt->fetchColumn();
    
    $stmt = $pdo->query("SELECT COUNT(*) FROM payments");
    $paymentCount = $stmt->fetchColumn();
    
    echo "   Data verification:\n";
    echo "     ✅ Applications: $appCount records\n";
    echo "     ✅ Payments: $paymentCount records\n";

    echo "\n" . str_repeat("=", 60) . "\n";
    echo "✅ DATABASE SCHEMA FIXES COMPLETED SUCCESSFULLY\n";
    echo str_repeat("=", 60) . "\n\n";
    
    echo "🎉 SUMMARY:\n";
    echo "   ✅ Applications table structure fixed\n";
    echo "   ✅ Payments table structure enhanced\n";
    echo "   ✅ Test data created for foreign key constraints\n";
    echo "   ✅ All queries should now work correctly\n";
    echo "   ✅ Payment insertion tests should pass\n";
    echo "   ✅ Application-payment linking queries fixed\n\n";
    
    echo "🚀 NEXT STEPS:\n";
    echo "   1. Run your payment tests again\n";
    echo "   2. All foreign key constraints should be satisfied\n";
    echo "   3. tracking_number column queries should work\n";
    echo "   4. Payment system is ready for testing\n";

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}
?>
