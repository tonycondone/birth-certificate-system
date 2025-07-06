<?php
namespace App\Handlers;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Formatter\JsonFormatter;

class ErrorHandler {
    private static $instance = null;
    private $logger;

    private function __construct() {
        $this->logger = new Logger('birth_certificate_system');
        
        // File log handler
        $fileHandler = new StreamHandler(__DIR__ . '/../../logs/error.log', Logger::ERROR);
        $fileHandler->setFormatter(new JsonFormatter());
        $this->logger->pushHandler($fileHandler);
    }

    public static function getInstance(): self {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function handleException(\Throwable $exception, bool $fatal = false) {
        // Log the full exception details
        $this->logger->error('Unhandled Exception', [
            'message' => $exception->getMessage(),
            'code' => $exception->getCode(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => $exception->getTraceAsString()
        ]);

        // Notify admin (could be expanded to email, Slack, etc.)
        $this->notifyAdministrator($exception);

        if ($fatal) {
            $this->renderFatalErrorPage($exception);
            exit(1);
        }
    }

    public function logSecurityEvent(string $eventType, array $context = []) {
        $this->logger->warning("Security Event: $eventType", $context);
    }

    private function notifyAdministrator(\Throwable $exception) {
        // Placeholder for admin notification logic
        // Could send email, Slack message, etc.
    }

    private function renderFatalErrorPage(\Throwable $exception) {
        http_response_code(500);
        
        // Sanitized error display
        $errorMessage = $this->sanitizeErrorMessage($exception->getMessage());
        
        echo <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>System Error</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f4f4; text-align: center; padding-top: 50px; }
        .error-container { background-color: white; border-radius: 5px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); max-width: 600px; margin: 0 auto; padding: 30px; }
        h1 { color: #d9534f; }
        p { color: #333; }
    </style>
</head>
<body>
    <div class="error-container">
        <h1>System Error</h1>
        <p>An unexpected error occurred. Our team has been notified.</p>
        <small>Error Reference: {$this->generateErrorReference()}</small>
    </div>
</body>
</html>
HTML;
    }

    private function sanitizeErrorMessage(string $message): string {
        // Remove sensitive information
        return preg_replace([
            '/database/i', 
            '/password/i', 
            '/credentials/i'
        ], '***', $message);
    }

    private function generateErrorReference(): string {
        return strtoupper(bin2hex(random_bytes(4)));
    }

    public function handleShutdown() {
        $error = error_get_last();
        if ($error !== null) {
            $this->handleException(new \ErrorException(
                $error['message'], 
                0, 
                $error['type'], 
                $error['file'], 
                $error['line']
            ), true);
        }
    }
} 