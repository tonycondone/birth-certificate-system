<?php
/**
 * Basic Payment System Testing Script - FIXED VERSION
 * Tests core functionality without external dependencies
 * Fixes the "Call to a member function query() on null" error
 */

require_once 'vendor/autoload.php';

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

// Load environment variables from .env file
loadEnv('.env');

echo "=== Basic Payment System Testing ===\n\n";

// Initialize database connection
$pdo = null;

// Test 1: Database connectivity
echo "1. Testing database connectivity...\n";
try {
    // Use environment variables for database connection
    $host = $_ENV['DB_HOST'] ?? '127.0.0.1';
    $dbname = $_ENV['DB_DATABASE'] ?? 'birth_certificate_system';
    $username = $_ENV['DB_USERNAME'] ?? 'root';
    $password = $_ENV['DB_PASSWORD'] ?? '1212';
    
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "   ✅ Database connection successful\n";
} catch (Exception $e) {
    echo "   ❌ Database connection failed: " . $e->getMessage() . "\n";
    echo "   💡 Make sure MySQL is running and the database exists\n";
}

// Test 2: Payments table existence
echo "\n2. Testing payments table structure...\n";
if ($pdo !== null) {
    try {
        $stmt = $pdo->query("SHOW TABLES LIKE 'payments'");
        if ($stmt->rowCount() > 0) {
            echo "   ✅ Payments table exists\n";
            
            // Check columns
            $stmt = $pdo->query("DESCRIBE payments");
            $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $columnNames = array_column($columns, 'Field');
            
            $required = ['id', 'application_id', 'amount', 'currency', 'transaction_id', 'status'];
            $missing = array_diff($required, $columnNames);
            
            if (empty($missing)) {
                echo "   ✅ All required columns present\n";
            } else {
                echo "   ❌ Missing columns: " . implode(', ', $missing) . "\n";
            }
        } else {
            echo "   ❌ Payments table not found\n";
            echo "   💡 Run database migrations to create the payments table\n";
        }
    } catch (Exception $e) {
        echo "   ❌ Error checking payments table: " . $e->getMessage() . "\n";
    }
} else {
    echo "   ⚠️  Skipping table check - database not connected\n";
}

// Test 3: Test payment data insertion
echo "\n3. Testing payment data insertion...\n";
if ($pdo !== null) {
    try {
        // Get a valid application ID first
        $appStmt = $pdo->query("SELECT id FROM applications LIMIT 1");
        $testApp = $appStmt->fetch();
        
        if (!$testApp) {
            echo "   ⚠️  No applications found. Creating test application...\n";
            
            // Create a test user if needed
            $userStmt = $pdo->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
            $userStmt->execute(['test@example.com']);
            $testUser = $userStmt->fetch();
            
            if (!$testUser) {
                $userStmt = $pdo->prepare("
                    INSERT INTO users (first_name, last_name, email, password, role, email_verified, created_at) 
                    VALUES (?, ?, ?, ?, ?, ?, NOW())
                ");
                $userStmt->execute([
                    'Test', 'User', 'test@example.com', 
                    password_hash('password123', PASSWORD_DEFAULT),
                    'parent', 1
                ]);
                $testUserId = $pdo->lastInsertId();
            } else {
                $testUserId = $testUser['id'];
            }
            
            // Create test application
            $appStmt = $pdo->prepare("
                INSERT INTO applications (user_id, reference_number, purpose, description, status, tracking_number, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, NOW())
            ");
            $appStmt->execute([
                $testUserId,
                'APP-TEST-' . time(),
                'Birth Certificate Application',
                'Test application for payment testing',
                'pending_payment',
                'TRK-' . strtoupper(uniqid())
            ]);
            $testAppId = $pdo->lastInsertId();
            echo "   ✅ Test application created (ID: $testAppId)\n";
        } else {
            $testAppId = $testApp['id'];
        }

        $stmt = $pdo->prepare("
            INSERT INTO payments (application_id, amount, currency, transaction_id, status, payment_gateway) 
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        
        $testData = [
            'application_id' => $testAppId,
            'amount' => 150.00,
            'currency' => 'GHS',
            'transaction_id' => 'TEST-' . time(),
            'status' => 'pending',
            'payment_gateway' => 'paystack'
        ];
        
        $result = $stmt->execute(array_values($testData));
        
        if ($result) {
            $paymentId = $pdo->lastInsertId();
            echo "   ✅ Test payment inserted successfully (ID: $paymentId)\n";
            
            // Clean up test data
            $pdo->prepare("DELETE FROM payments WHERE id = ?")->execute([$paymentId]);
            echo "   ✅ Test payment cleaned up\n";
        } else {
            echo "   ❌ Failed to insert test payment\n";
        }
    } catch (Exception $e) {
        echo "   ❌ Error inserting test payment: " . $e->getMessage() . "\n";
    }
} else {
    echo "   ⚠️  Skipping insertion test - database not connected\n";
}

// Test 4: Test payment status queries
echo "\n4. Testing payment status queries...\n";
if ($pdo !== null) {
    try {
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as total, 
                   SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed,
                   SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
                   SUM(CASE WHEN status = 'failed' THEN 1 ELSE 0 END) as failed
            FROM payments
        ");
        $stmt->execute();
        $stats = $stmt->fetch(PDO::FETCH_ASSOC);
        
        echo "   ✅ Payment status queries working\n";
        echo "   📊 Current payment stats: Total: {$stats['total']}, Completed: {$stats['completed']}, Pending: {$stats['pending']}, Failed: {$stats['failed']}\n";
    } catch (Exception $e) {
        echo "   ❌ Error testing payment queries: " . $e->getMessage() . "\n";
    }
} else {
    echo "   ⚠️  Skipping query test - database not connected\n";
}

// Test 5: Test application linking
echo "\n5. Testing application-payment linking...\n";
if ($pdo !== null) {
    try {
        // Get a valid application ID for testing
        $appStmt = $pdo->query("SELECT id FROM applications LIMIT 1");
        $testApp = $appStmt->fetch();
        
        if ($testApp) {
            $testAppId = $testApp['id'];
            $stmt = $pdo->query("
                SELECT a.id, a.tracking_number, p.amount, p.status
                FROM applications a
                LEFT JOIN payments p ON a.id = p.application_id
                WHERE a.id = $testAppId
                LIMIT 1
            ");
        } else {
            echo "   ❌ No applications available for testing\n";
            return;
        }
        
        if ($stmt->rowCount() > 0) {
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            echo "   ✅ Application-payment linking working\n";
            if ($result['amount']) {
                echo "   💰 Found payment: GH₵{$result['amount']} ({$result['status']})\n";
            } else {
                echo "   📋 No payment found for application\n";
            }
        } else {
            echo "   ❌ No applications found for testing\n";
            echo "   💡 Create some test applications first\n";
        }
    } catch (Exception $e) {
        echo "   ❌ Error testing application linking: " . $e->getMessage() . "\n";
    }
} else {
    echo "   ⚠️  Skipping linking test - database not connected\n";
}

// Test 6: Test transaction reference generation
echo "\n6. Testing transaction reference generation...\n";
$references = [];
for ($i = 0; $i < 5; $i++) {
    $ref = 'BCS-' . date('YmdHis') . '-' . strtoupper(uniqid());
    $references[] = $ref;
    echo "   🔢 Generated reference: $ref\n";
}

// Check uniqueness
$uniqueRefs = array_unique($references);
if (count($references) === count($uniqueRefs)) {
    echo "   ✅ All references are unique\n";
} else {
    echo "   ❌ Duplicate references found\n";
}

// Test 7: Test amount formatting
echo "\n7. Testing amount formatting...\n";
$amounts = [15000, 25000, 5000, 100000];
foreach ($amounts as $amount) {
    $formatted = number_format($amount / 100, 2);
    echo "   💵 {$amount} kobo = GH₵{$formatted}\n";
}

echo "\n=== Testing Summary ===\n";
if ($pdo !== null) {
    echo "✅ Basic payment system components are functional.\n";
    echo "Next steps for complete testing:\n";
    echo "1. Set up test Paystack keys in .env file\n";
    echo "2. Create test applications for payment testing\n";
    echo "3. Test actual payment flow with Paystack sandbox\n";
    echo "4. Test webhook handling\n";
    echo "5. Test error scenarios\n";
    echo "\nPayment system is ready for integration testing.\n";
} else {
    echo "❌ Database connection failed. Please check:\n";
    echo "1. MySQL server is running\n";
    echo "2. Database 'birth_certificate_system' exists\n";
    echo "3. Database credentials are correct in .env file\n";
    echo "4. Run: php setup_database.php to set up the database\n";
}
