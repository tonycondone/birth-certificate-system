<?php
/**
 * Database Setup Script for Birth Certificate System
 * This script will create the database and run all migrations
 */

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "=== Birth Certificate System Database Setup ===\n\n";

// Load environment variables
if (file_exists('.env')) {
    $lines = file('.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, '#') === 0) continue;
        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            $_ENV[trim($key)] = trim($value, '"');
        }
    }
}

// Database configuration
$host = $_ENV['DB_HOST'] ?? '127.0.0.1';
$dbname = $_ENV['DB_DATABASE'] ?? 'birth_certificate_system';
$username = $_ENV['DB_USERNAME'] ?? 'root';
$password = $_ENV['DB_PASSWORD'] ?? '1212';

echo "Database Configuration:\n";
echo "  Host: $host\n";
echo "  Database: $dbname\n";
echo "  Username: $username\n\n";

try {
    // Connect to MySQL server (without database)
    echo "1. Connecting to MySQL server...\n";
    $pdo = new PDO("mysql:host=$host;charset=utf8mb4", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    echo "   ✓ Connected to MySQL server\n";

    // Create database if it doesn't exist
    echo "2. Creating database if not exists...\n";
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbname` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "   ✓ Database '$dbname' ready\n";

    // Connect to the specific database
    echo "3. Connecting to database...\n";
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    echo "   ✓ Connected to database '$dbname'\n";

    // Get list of migration files
    echo "4. Running database migrations...\n";
    $migrationDir = __DIR__ . '/database/migrations';
    $migrationFiles = glob($migrationDir . '/*.sql');
    sort($migrationFiles);

    if (empty($migrationFiles)) {
        echo "   ! No migration files found in $migrationDir\n";
    } else {
        foreach ($migrationFiles as $file) {
            $filename = basename($file);
            echo "   Running: $filename\n";
            
            $sql = file_get_contents($file);
            if ($sql === false) {
                echo "   ✗ Failed to read $filename\n";
                continue;
            }

            try {
                // Split SQL file by semicolons and execute each statement
                $statements = array_filter(array_map('trim', explode(';', $sql)));
                foreach ($statements as $statement) {
                    if (!empty($statement)) {
                        $pdo->exec($statement);
                    }
                }
                echo "   ✓ $filename executed successfully\n";
            } catch (PDOException $e) {
                echo "   ! Warning in $filename: " . $e->getMessage() . "\n";
                // Continue with other migrations
            }
        }
    }

    // Create sample data
    echo "5. Creating sample data...\n";
    
    // Create admin user
    $adminEmail = 'admin@birthcert.gov';
    $adminPassword = password_hash('admin123', PASSWORD_DEFAULT);
    
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$adminEmail]);
    
    if (!$stmt->fetch()) {
        $stmt = $pdo->prepare("
            INSERT INTO users (username, email, password, first_name, last_name, role, status, email_verified_at) 
            VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
        ");
        $stmt->execute(['admin', $adminEmail, $adminPassword, 'System', 'Administrator', 'admin', 'active']);
        echo "   ✓ Admin user created (email: $adminEmail, password: admin123)\n";
    } else {
        echo "   ✓ Admin user already exists\n";
    }

    // Create sample parent user
    $parentEmail = 'parent@example.com';
    $parentPassword = password_hash('parent123', PASSWORD_DEFAULT);
    
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$parentEmail]);
    
    if (!$stmt->fetch()) {
        $stmt = $pdo->prepare("
            INSERT INTO users (username, email, password, first_name, last_name, role, status, email_verified_at) 
            VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
        ");
        $stmt->execute(['parent1', $parentEmail, $parentPassword, 'John', 'Doe', 'parent', 'active']);
        echo "   ✓ Sample parent user created (email: $parentEmail, password: parent123)\n";
    } else {
        echo "   ✓ Sample parent user already exists\n";
    }

    // Create sample registrar user
    $registrarEmail = 'registrar@birthcert.gov';
    $registrarPassword = password_hash('registrar123', PASSWORD_DEFAULT);
    
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$registrarEmail]);
    
    if (!$stmt->fetch()) {
        $stmt = $pdo->prepare("
            INSERT INTO users (username, email, password, first_name, last_name, role, status, email_verified_at) 
            VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
        ");
        $stmt->execute(['registrar1', $registrarEmail, $registrarPassword, 'Jane', 'Smith', 'registrar', 'active']);
        echo "   ✓ Sample registrar user created (email: $registrarEmail, password: registrar123)\n";
    } else {
        echo "   ✓ Sample registrar user already exists\n";
    }

    // Create sample hospital user
    $hospitalEmail = 'hospital@example.com';
    $hospitalPassword = password_hash('hospital123', PASSWORD_DEFAULT);
    
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$hospitalEmail]);
    
    if (!$stmt->fetch()) {
        $stmt = $pdo->prepare("
            INSERT INTO users (username, email, password, first_name, last_name, role, status, email_verified_at, hospital_id) 
            VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), ?)
        ");
        $stmt->execute(['hospital1', $hospitalEmail, $hospitalPassword, 'Dr. Michael', 'Johnson', 'hospital', 'active', 'HOSP001']);
        echo "   ✓ Sample hospital user created (email: $hospitalEmail, password: hospital123)\n";
    } else {
        echo "   ✓ Sample hospital user already exists\n";
    }

    // Create sample birth application and certificate
    echo "6. Creating sample birth certificate data...\n";
    
    // Get parent user ID
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$parentEmail]);
    $parentUser = $stmt->fetch();
    
    if ($parentUser) {
        // Check if sample application exists
        $stmt = $pdo->prepare("SELECT id FROM birth_applications WHERE application_number = ?");
        $stmt->execute(['APP2024001']);
        
        if (!$stmt->fetch()) {
            // Create sample birth application
            $stmt = $pdo->prepare("
                INSERT INTO birth_applications (
                    application_number, user_id, child_first_name, child_last_name, 
                    date_of_birth, place_of_birth, gender, 
                    mother_first_name, mother_last_name, 
                    father_first_name, father_last_name,
                    hospital_name, status, submitted_at
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
            ");
            $stmt->execute([
                'APP2024001', $parentUser['id'], 'Emma', 'Doe', 
                '2024-01-15', 'City General Hospital', 'female',
                'Jane', 'Doe', 'John', 'Doe',
                'City General Hospital', 'approved'
            ]);
            $applicationId = $pdo->lastInsertId();
            echo "   ✓ Sample birth application created\n";
            
            // Create sample certificate
            $stmt = $pdo->prepare("
                INSERT INTO certificates (
                    certificate_number, application_id, qr_code_hash, 
                    issued_by, status, issued_at
                ) VALUES (?, ?, ?, ?, ?, NOW())
            ");
            $stmt->execute([
                'BC2024001ABC123', $applicationId, 
                hash('sha256', 'BC2024001ABC123' . time()),
                1, 'active'
            ]);
            echo "   ✓ Sample certificate created (Number: BC2024001ABC123)\n";
        } else {
            echo "   ✓ Sample data already exists\n";
        }
    }

    // Show final status
    echo "\n7. Database setup complete!\n";
    
    // Show table counts
    $tables = ['users', 'birth_applications', 'certificates', 'applications'];
    foreach ($tables as $table) {
        try {
            $stmt = $pdo->query("SELECT COUNT(*) as count FROM $table");
            $result = $stmt->fetch();
            echo "   $table: {$result['count']} records\n";
        } catch (PDOException $e) {
            echo "   $table: table not found\n";
        }
    }

    echo "\n=== Setup Complete ===\n";
    echo "You can now access the system at: http://localhost:8000\n";
    echo "\nTest Accounts:\n";
    echo "  Admin: admin@birthcert.gov / admin123\n";
    echo "  Parent: parent@example.com / parent123\n";
    echo "  Registrar: registrar@birthcert.gov / registrar123\n";
    echo "  Hospital: hospital@example.com / hospital123\n";
    echo "\nTest Certificate: BC2024001ABC123\n";

} catch (PDOException $e) {
    echo "✗ Database Error: " . $e->getMessage() . "\n";
    exit(1);
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    exit(1);
}
