<?php
// Comprehensive Application Diagnostic Script

// Enable full error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Load Composer's autoloader
require_once __DIR__ . '/vendor/autoload.php';

// Import necessary classes
use App\Services\DependencyContainer;
use App\Database\Database;

function runDiagnostics() {
    echo "Birth Certificate System - Comprehensive Diagnostics\n";
    echo "==================================================\n\n";

    // 1. Database Connection Test
    echo "1. Database Connection Test:\n";
    try {
        $db = Database::getConnection();
        echo "   ✓ Database connection successful\n";
        
        // Basic query test
        $stmt = $db->query("SELECT 1");
        $result = $stmt->fetchColumn();
        echo "   ✓ Basic database query successful (result: $result)\n";
    } catch (Exception $e) {
        echo "   ✗ Database Connection Failed: " . $e->getMessage() . "\n";
    }

    // 2. Dependency Container Test
    echo "\n2. Dependency Container Test:\n";
    try {
        $container = DependencyContainer::getInstance();
        echo "   ✓ Dependency Container instantiated\n";

        // Test individual service creation
        $services = [
            'Database' => fn() => $container->getDatabase(),
            'BirthApplicationRepository' => fn() => $container->getBirthApplicationRepository(),
            'CertificateRepository' => fn() => $container->getCertificateRepository(),
            'UserRepository' => fn() => $container->getUserRepository(),
            'AuthService' => fn() => $container->getAuthService(),
            'NotificationService' => fn() => $container->getNotificationService(),
            'CertificateVerificationService' => fn() => $container->getCertificateVerificationService()
        ];

        foreach ($services as $name => $serviceCall) {
            try {
                $service = $serviceCall();
                echo "   ✓ $name service created successfully\n";
            } catch (Exception $e) {
                echo "   ✗ Failed to create $name service: " . $e->getMessage() . "\n";
            }
        }
    } catch (Exception $e) {
        echo "   ✗ Dependency Container Test Failed: " . $e->getMessage() . "\n";
    }

    // 3. Environment Configuration Test
    echo "\n3. Environment Configuration Test:\n";
    $envFiles = [
        __DIR__ . '/.env',
        __DIR__ . '/env.example'
    ];

    $foundEnvFile = false;
    foreach ($envFiles as $envFile) {
        if (file_exists($envFile)) {
            $foundEnvFile = true;
            echo "   ✓ Environment file found: $envFile\n";
            
            // Basic env parsing test
            $envContent = file_get_contents($envFile);
            $envLines = explode("\n", $envContent);
            $validEnvLines = array_filter($envLines, function($line) {
                $line = trim($line);
                return !empty($line) && strpos($line, '#') !== 0 && strpos($line, '=') !== false;
            });
            
            echo "   ✓ Found " . count($validEnvLines) . " valid environment variables\n";
            break;
        }
    }

    if (!$foundEnvFile) {
        echo "   ✗ No environment file found\n";
    }

    // 4. PHP Extension Check
    echo "\n4. Required PHP Extensions:\n";
    $requiredExtensions = ['pdo', 'pdo_mysql', 'json'];
    foreach ($requiredExtensions as $ext) {
        if (extension_loaded($ext)) {
            echo "   ✓ $ext extension loaded\n";
        } else {
            echo "   ✗ $ext extension NOT loaded\n";
        }
    }

    echo "\nDiagnostic Complete.\n";
}

// Run the diagnostics
runDiagnostics(); 