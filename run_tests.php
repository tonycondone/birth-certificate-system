<?php
require 'vendor/autoload.php';

use PHPUnit\TextUI\Command;

class TestRunner {
    private $startTime;
    private $testResults = [
        'total' => 0,
        'passed' => 0,
        'failed' => 0,
        'skipped' => 0,
        'errors' => 0
    ];

    public function __construct() {
        $this->startTime = microtime(true);
    }

    public function run() {
        echo "🚀 Birth Certificate System Test Runner\n";
        echo "=====================================\n\n";

        // Ensure logs directory exists
        @mkdir('logs', 0755, true);
        @mkdir('test-results', 0755, true);

        // Run PHPUnit
        $command = new Command();
        $command->run([
            'phpunit',
            '--configuration', 'phpunit.xml',
            '--log-junit', 'test-results/junit.xml',
            '--coverage-html', 'coverage',
            '--colors=always'
        ], false);

        $this->generateReport();
    }

    private function generateReport() {
        $endTime = microtime(true);
        $duration = round($endTime - $this->startTime, 2);

        echo "\n📊 Test Execution Report\n";
        echo "----------------------\n";
        echo "Total Duration: {$duration} seconds\n";
        
        // Parse JUnit XML for detailed results
        $junitPath = 'test-results/junit.xml';
        if (file_exists($junitPath)) {
            $xml = simplexml_load_file($junitPath);
            
            $this->testResults['total'] = (int)$xml['tests'];
            $this->testResults['passed'] = (int)$xml['tests'] - 
                ((int)$xml['failures'] + (int)$xml['errors'] + (int)$xml['skipped']);
            $this->testResults['failed'] = (int)$xml['failures'];
            $this->testResults['skipped'] = (int)$xml['skipped'];
            $this->testResults['errors'] = (int)$xml['errors'];
        }

        // Display results
        foreach ($this->testResults as $key => $value) {
            echo ucfirst($key) . " Tests: $value\n";
        }

        // Determine overall status
        $overallStatus = ($this->testResults['failed'] === 0 && $this->testResults['errors'] === 0) 
            ? "✅ PASSED" 
            : "❌ FAILED";
        
        echo "\nOverall Status: $overallStatus\n";

        // Generate a summary log
        file_put_contents('logs/test_summary.log', json_encode([
            'timestamp' => date('Y-m-d H:i:s'),
            'duration' => $duration,
            'results' => $this->testResults,
            'status' => $overallStatus
        ], JSON_PRETTY_PRINT));
    }
}

// Run the tests
$testRunner = new TestRunner();
$testRunner->run(); 