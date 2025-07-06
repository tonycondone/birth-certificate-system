<?php
require_once 'vendor/autoload.php';

use App\Services\DependencyContainer;
use App\Repositories\BirthApplicationRepository;

// Enhanced Diagnostic Test for DependencyContainer
class DependencyContainerTest {
    private $container;

    public function __construct() {
        $this->container = DependencyContainer::getInstance();
    }

    public function runFullDiagnostic() {
        echo "🔍 Comprehensive Dependency Container Diagnostic\n";
        echo "===========================================\n\n";

        $tests = [
            'testDatabaseConnection' => 'Database Connection',
            'testBirthApplicationRepository' => 'Birth Application Repository',
            'testCertificateRepository' => 'Certificate Repository',
            'testUserRepository' => 'User Repository',
            'testNotificationService' => 'Notification Service',
            'testCertificateVerificationService' => 'Certificate Verification Service',
            'testAuthService' => 'Auth Service'
        ];

        $results = [];
        foreach ($tests as $method => $name) {
            try {
                $start = microtime(true);
                $this->$method();
                $duration = round((microtime(true) - $start) * 1000, 2);
                $results[$name] = [
                    'status' => '✅ PASS',
                    'duration' => $duration . 'ms'
                ];
            } catch (\Throwable $e) {
                $results[$name] = [
                    'status' => '❌ FAIL',
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ];
            }
        }

        $this->displayResults($results);
    }

    private function testDatabaseConnection() {
        $db = $this->container->getDatabase();
        if (!$db instanceof \PDO) {
            throw new \RuntimeException("Invalid database connection");
        }
    }

    private function testBirthApplicationRepository() {
        $repo = $this->container->getBirthApplicationRepository();
        if (!$repo instanceof BirthApplicationRepository) {
            throw new \RuntimeException("Invalid Birth Application Repository");
        }
    }

    private function testCertificateRepository() {
        $repo = $this->container->getCertificateRepository();
        if (!$repo instanceof \App\Repositories\CertificateRepository) {
            throw new \RuntimeException("Invalid Certificate Repository");
        }
    }

    private function testUserRepository() {
        $repo = $this->container->getUserRepository();
        if (!$repo instanceof \App\Repositories\UserRepository) {
            throw new \RuntimeException("Invalid User Repository");
        }
    }

    private function testNotificationService() {
        $service = $this->container->getNotificationService();
        if (!$service instanceof \App\Services\NotificationService) {
            throw new \RuntimeException("Invalid Notification Service");
        }
    }

    private function testCertificateVerificationService() {
        $service = $this->container->getCertificateVerificationService();
        if (!$service instanceof \App\Services\CertificateVerificationService) {
            throw new \RuntimeException("Invalid Certificate Verification Service");
        }
    }

    private function testAuthService() {
        $service = $this->container->getAuthService();
        if (!$service instanceof \App\Services\AuthService) {
            throw new \RuntimeException("Invalid Auth Service");
        }
    }

    private function displayResults($results) {
        echo "\n📊 Test Results:\n";
        echo "---------------\n";
        $passCount = 0;
        $failCount = 0;

        foreach ($results as $name => $result) {
            echo "$name: {$result['status']}\n";
            if ($result['status'] === '❌ FAIL') {
                $failCount++;
                echo "  Error: {$result['error']}\n";
                echo "  Trace: {$result['trace']}\n";
            } else {
                $passCount++;
                echo "  Duration: {$result['duration']}\n";
            }
        }

        echo "\n📈 Summary:\n";
        echo "Total Tests: " . count($results) . "\n";
        echo "Passed: $passCount\n";
        echo "Failed: $failCount\n";
    }
}

// Run the diagnostic
try {
    $diagnostic = new DependencyContainerTest();
    $diagnostic->runFullDiagnostic();
} catch (\Throwable $e) {
    echo "Critical Error: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
} 