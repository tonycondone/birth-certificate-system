<?php
namespace App\Middleware;

use App\Services\LoggingService;
use Closure;
use Exception;

class LoggingMiddleware {
    private LoggingService $loggingService;

    public function __construct(LoggingService $loggingService) {
        $this->loggingService = $loggingService;
    }

    /**
     * Intercept and log system events
     * 
     * @param mixed $request Incoming request
     * @param Closure $next Next middleware/handler
     * @return mixed Response
     */
    public function handle($request, Closure $next) {
        // Start performance tracking
        $startTime = microtime(true);
        $memoryStart = memory_get_usage();

        try {
            // Log incoming request details
            $this->logRequestDetails($request);

            // Process request
            $response = $next($request);

            // Log response and performance metrics
            $this->logResponseDetails($response, $startTime, $memoryStart);

            return $response;
        } catch (Exception $e) {
            // Log any unhandled exceptions
            $this->logExceptionDetails($e, $startTime, $memoryStart);
            throw $e;
        }
    }

    /**
     * Log incoming request details
     * 
     * @param mixed $request Incoming request
     */
    private function logRequestDetails($request): void {
        $this->loggingService->log(
            'info', 
            'request', 
            'Incoming request processed',
            [
                'method' => $_SERVER['REQUEST_METHOD'] ?? 'UNKNOWN',
                'path' => $_SERVER['REQUEST_URI'] ?? 'UNKNOWN',
                'ip' => $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN',
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'UNKNOWN',
                'request_time' => $_SERVER['REQUEST_TIME_FLOAT'] ?? microtime(true),
                'user_id' => $_SESSION['user_id'] ?? null,
                'request_data' => $this->sanitizeRequestData($request)
            ]
        );
    }

    /**
     * Log response details and performance metrics
     * 
     * @param mixed $response Response object
     * @param float $startTime Request start time
     * @param int $memoryStart Initial memory usage
     */
    private function logResponseDetails($response, float $startTime, int $memoryStart): void {
        $endTime = microtime(true);
        $executionTime = $endTime - $startTime;
        $memoryEnd = memory_get_usage();
        $memoryPeak = memory_get_peak_usage();

        // Check performance thresholds from config
        $config = require __DIR__ . '/../../config/logging.php';
        $performanceThresholds = $config['logging']['performance_thresholds'];

        // Determine log level based on execution time
        $logLevel = $executionTime > $performanceThresholds['http_request'] 
            ? 'warning' 
            : 'info';

        $this->loggingService->log(
            $logLevel, 
            'performance', 
            'Request processing completed',
            [
                'execution_time' => $executionTime,
                'memory_start' => $memoryStart,
                'memory_end' => $memoryEnd,
                'memory_peak' => $memoryPeak,
                'memory_diff' => $memoryEnd - $memoryStart,
                'response_status' => $this->getResponseStatus($response)
            ]
        );
    }

    /**
     * Log exception details
     * 
     * @param Exception $exception Caught exception
     * @param float $startTime Request start time
     * @param int $memoryStart Initial memory usage
     */
    private function logExceptionDetails(
        Exception $exception, 
        float $startTime, 
        int $memoryStart
    ): void {
        $endTime = microtime(true);
        $executionTime = $endTime - $startTime;
        $memoryEnd = memory_get_usage();

        $this->loggingService->log(
            'critical', 
            'exception', 
            'Unhandled exception occurred',
            [
                'exception_message' => $exception->getMessage(),
                'exception_code' => $exception->getCode(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'trace' => $exception->getTraceAsString(),
                'execution_time' => $executionTime,
                'memory_usage' => $memoryEnd - $memoryStart
            ]
        );
    }

    /**
     * Sanitize request data to remove sensitive information
     * 
     * @param mixed $request Request object
     * @return array Sanitized request data
     */
    private function sanitizeRequestData($request): array {
        $config = require __DIR__ . '/../../config/logging.php';
        $sensitiveFields = $config['logging']['sensitive_fields'];

        // Convert request to array and sanitize
        $requestData = is_array($request) ? $request : (array) $request;
        
        // Remove sensitive fields
        foreach ($sensitiveFields as $field) {
            if (isset($requestData[$field])) {
                $requestData[$field] = '***REDACTED***';
            }
        }

        return $requestData;
    }

    /**
     * Extract response status
     * 
     * @param mixed $response Response object
     * @return string Response status
     */
    private function getResponseStatus($response): string {
        // Implement logic to extract response status
        // This will depend on your specific response object structure
        if (method_exists($response, 'getStatusCode')) {
            return (string) $response->getStatusCode();
        }

        return 'UNKNOWN';
    }
} 