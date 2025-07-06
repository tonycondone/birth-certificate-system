<?php
/**
 * Log Rotation Script
 * 
 * Manages system log retention and archiving
 * Should be run periodically via cron job or scheduled task
 */

require_once __DIR__ . '/../vendor/autoload.php';

use App\Services\LoggingService;
use App\Database\Database;

class LogRotationManager {
    private LoggingService $loggingService;

    public function __construct() {
        $this->loggingService = new LoggingService(Database::getConnection());
    }

    /**
     * Execute log rotation process
     */
    public function rotate(): void {
        try {
            // Start timing the rotation process
            $startTime = microtime(true);

            // Retrieve logging configuration
            $config = require __DIR__ . '/../config/logging.php';
            $retentionDays = $config['logging']['rotation']['retention_days'];

            // Perform log rotation
            $rotationResult = $this->loggingService->rotateLogs($retentionDays);

            // Calculate execution time
            $executionTime = round(microtime(true) - $startTime, 2);

            // Log rotation result
            $this->loggingService->log(
                $rotationResult ? 'info' : 'warning', 
                'log_rotation', 
                sprintf(
                    'Log rotation completed. Retention period: %d days. Execution time: %s seconds', 
                    $retentionDays, 
                    $executionTime
                ),
                [
                    'success' => $rotationResult,
                    'retention_days' => $retentionDays,
                    'execution_time' => $executionTime
                ]
            );

            // Output result for CLI or logging
            echo $rotationResult 
                ? "Log rotation successful.\n" 
                : "Log rotation failed.\n";
        } catch (Exception $e) {
            // Log any exceptions during rotation
            $this->loggingService->log(
                'critical', 
                'log_rotation', 
                'Log rotation process failed',
                [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]
            );

            // Output error for CLI
            echo "Log rotation error: " . $e->getMessage() . "\n";
            exit(1);
        }
    }
}

// Run log rotation if script is executed directly
if (php_sapi_name() === 'cli') {
    $logRotationManager = new LogRotationManager();
    $logRotationManager->rotate();
} 