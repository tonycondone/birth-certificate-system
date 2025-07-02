<?php

namespace App\Services;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Formatter\JsonFormatter;
use Monolog\Processor\IntrospectionProcessor;
use Monolog\Processor\WebProcessor;
use Exception;
use Throwable;

class LoggingService
{
    private static ?Logger $logger = null;
    private static ?LoggingService $instance = null;
    
    private const LOG_LEVELS = [
        'debug' => Logger::DEBUG,
        'info' => Logger::INFO,
        'notice' => Logger::NOTICE,
        'warning' => Logger::WARNING,
        'error' => Logger::ERROR,
        'critical' => Logger::CRITICAL,
        'alert' => Logger::ALERT,
        'emergency' => Logger::EMERGENCY
    ];
    
    private $auditLogger;
    
    /**
     * Get singleton instance
     */
    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        
        return self::$instance;
    }
    
    /**
     * Private constructor for singleton pattern
     */
    private function __construct()
    {
        $this->initializeLogger();
    }
    
    /**
     * Initialize the logger with appropriate handlers
     */
    private function initializeLogger(): void
    {
        try {
            // Create logger instance
            self::$logger = new Logger('birth_certificate_system');
            
            // Set log format
            $dateFormat = "Y-m-d H:i:s";
            $output = "[%datetime%] %level_name% %channel%: %message% %context% %extra%\n";
            $formatter = new JsonFormatter($dateFormat);
            
            // Add processors
            self::$logger->pushProcessor(new IntrospectionProcessor());
            self::$logger->pushProcessor(new WebProcessor());
            
            // Add handlers based on environment
            $logLevel = self::LOG_LEVELS[strtolower($_ENV['LOG_LEVEL'] ?? 'error')];
            $logPath = $_ENV['LOG_PATH'] ?? __DIR__ . '/../../storage/logs';
            
            // Ensure log directory exists
            if (!is_dir($logPath)) {
                mkdir($logPath, 0755, true);
            }
            
            // Add daily rotating file handler for all logs
            $allLogsHandler = new RotatingFileHandler(
                $logPath . '/app.log',
                10, // Keep 10 days of logs
                $logLevel
            );
            $allLogsHandler->setFormatter($formatter);
            self::$logger->pushHandler($allLogsHandler);
            
            // Add separate handler for errors
            if ($logLevel <= Logger::ERROR) {
                $errorHandler = new RotatingFileHandler(
                    $logPath . '/error.log',
                    90, // Keep 90 days of error logs
                    Logger::ERROR
                );
                $errorHandler->setFormatter($formatter);
                self::$logger->pushHandler($errorHandler);
            }
            
            // Add security log handler
            $securityHandler = new RotatingFileHandler(
                $logPath . '/security.log',
                365, // Keep 1 year of security logs
                Logger::INFO
            );
            $securityHandler->setFormatter($formatter);
            self::$logger->pushHandler($securityHandler);
            
            // Add stream handler for development
            if (($_ENV['APP_ENV'] ?? 'production') === 'development') {
                $streamHandler = new StreamHandler('php://stderr', Logger::DEBUG);
                $streamHandler->setFormatter($formatter);
                self::$logger->pushHandler($streamHandler);
            }

            // Audit-specific logger
            $this->auditLogger = new Logger('audit_trail');
            $auditHandler = new StreamHandler(
                $logPath . '/audit.log', 
                Logger::INFO
            );
            $auditHandler->setFormatter(new JsonFormatter());
            $this->auditLogger->pushHandler($auditHandler);
        } catch (Exception $e) {
            // Fallback to error_log if logger initialization fails
            error_log('Failed to initialize logger: ' . $e->getMessage());
        }
    }
    
    /**
     * Log a debug message
     */
    public function debug(string $message, array $context = []): void
    {
        if (self::$logger) {
            self::$logger->debug($message, $context);
        }
    }
    
    /**
     * Log an info message
     */
    public function info(string $message, array $context = []): void
    {
        if (self::$logger) {
            self::$logger->info($message, $context);
        }
    }
    
    /**
     * Log a notice message
     */
    public function notice(string $message, array $context = []): void
    {
        if (self::$logger) {
            self::$logger->notice($message, $context);
        }
    }
    
    /**
     * Log a warning message
     */
    public function warning(string $message, array $context = []): void
    {
        if (self::$logger) {
            self::$logger->warning($message, $context);
        }
    }
    
    /**
     * Log an error message
     */
    public function error(string $message, array $context = []): void
    {
        if (self::$logger) {
            try {
                $context['trace'] = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
                self::$logger->error($message, $context);
            } catch (Exception $e) {
                // Fallback to error_log silently
                @error_log('ERROR: ' . $message);
            }
        } else {
            // Fallback to error_log silently
            @error_log('ERROR: ' . $message);
        }
    }
    
    /**
     * Log a critical message
     */
    public function critical(string $message, array $context = []): void
    {
        if (self::$logger) {
            try {
                self::$logger->critical($message, $context);
            } catch (Exception $e) {
                // Fallback to error_log silently
                @error_log('CRITICAL: ' . $message);
            }
        } else {
            // Fallback to error_log silently
            @error_log('CRITICAL: ' . $message);
        }
    }
    
    /**
     * Log an alert message
     */
    public function alert(string $message, array $context = []): void
    {
        if (self::$logger) {
            self::$logger->alert($message, $context);
        } else {
            // Fallback to error_log
            error_log('ALERT: ' . $message);
        }
    }
    
    /**
     * Log an emergency message
     */
    public function emergency(string $message, array $context = []): void
    {
        if (self::$logger) {
            self::$logger->emergency($message, $context);
        } else {
            // Fallback to error_log
            error_log('EMERGENCY: ' . $message);
        }
    }
    
    /**
     * Log a security event
     */
    public function logSecurity(string $message, array $context = []): void
    {
        $context['log_type'] = 'security';
        $this->info($message, $context);
        
        // For critical security issues, also log as critical
        if (isset($context['critical']) && $context['critical']) {
            $this->critical('SECURITY: ' . $message, $context);
        }
    }
    
    /**
     * Log an exception with full details
     */
    public function logException(Throwable $exception): void
    {
        try {
            $errorDetails = [
                'message' => $exception->getMessage(),
                'code' => $exception->getCode(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'trace' => $exception->getTraceAsString()
            ];
            
            $this->error('UNHANDLED_EXCEPTION', $errorDetails);
        } catch (Exception $e) {
            // Fallback to error_log silently if anything goes wrong
            @error_log('Exception: ' . $exception->getMessage() . ' in ' . $exception->getFile() . ' on line ' . $exception->getLine());
        }
    }
    
    /**
     * Log a database query (for debugging)
     */
    public function logQuery(string $query, array $params = [], ?float $executionTime = null): void
    {
        if (($_ENV['LOG_QUERIES'] ?? false) === true) {
            $context = [
                'query' => $query,
                'params' => $params
            ];
            
            if ($executionTime !== null) {
                $context['execution_time'] = $executionTime . 'ms';
            }
            
            $this->debug('Database query', $context);
        }
    }
    
    /**
     * Log user activity
     */
    public function logUserActivity(int $userId, string $action, string $details, array $context = []): void
    {
        $context['user_id'] = $userId;
        $context['action'] = $action;
        $context['ip_address'] = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $context['user_agent'] = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
        
        $this->info('User activity: ' . $details, $context);
    }

    /**
     * Log security-related events
     * 
     * @param string $event
     * @param array $details
     */
    public function securityEvent(string $event, array $details = []): void {
        $logEntry = array_merge([
            'event' => $event,
            'timestamp' => date('Y-m-d H:i:s'),
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
        ], $details);

        $this->logger->warning('SECURITY_EVENT: ' . $event, $logEntry);
    }

    /**
     * Log audit trail entries
     * 
     * @param string $action
     * @param string $entity
     * @param array $details
     */
    public function auditTrail(string $action, string $entity, array $details = []): void {
        $auditEntry = [
            'action' => $action,
            'entity' => $entity,
            'timestamp' => date('Y-m-d H:i:s'),
            'user_id' => $_SESSION['user_id'] ?? null,
            'details' => $details
        ];

        $this->auditLogger->info('AUDIT_TRAIL', $auditEntry);
    }

    /**
     * Performance logging
     * 
     * @param string $operation
     * @param float $executionTime
     * @param array $details
     */
    public function performanceLog(string $operation, float $executionTime, array $details = []): void {
        $performanceEntry = array_merge([
            'operation' => $operation,
            'execution_time' => $executionTime,
            'timestamp' => date('Y-m-d H:i:s')
        ], $details);

        if ($executionTime > 1.0) {
            $this->logger->warning('PERFORMANCE_SLOW', $performanceEntry);
        } else {
            $this->logger->info('PERFORMANCE', $performanceEntry);
        }
    }
} 