<?php
namespace App\Middleware;

use Exception;

class CSRFMiddleware {
    private const TOKEN_KEY = '_csrf_token';
    private const HEADER_KEY = 'X-CSRF-Token';

    /**
     * Generate a CSRF token
     * 
     * @return string CSRF token
     */
    public function generateToken(): string {
        // Use cryptographically secure random bytes
        $token = bin2hex(random_bytes(32));
        
        // Store token in session
        $_SESSION[self::TOKEN_KEY] = $token;
        
        return $token;
    }

    /**
     * Validate CSRF token
     * 
     * @param string|null $token Token to validate
     * @return bool Validation result
     */
    public function validateToken(?string $token = null): bool {
        // Check if session exists
        if (!isset($_SESSION)) {
            session_start();
        }

        // If no token provided, check request sources
        if ($token === null) {
            $token = $_POST[self::TOKEN_KEY] ?? 
                     $_GET[self::TOKEN_KEY] ?? 
                     $_SERVER['HTTP_'.str_replace('-', '_', strtoupper(self::HEADER_KEY))] ?? 
                     null;
        }

        // Validate token
        return isset($_SESSION[self::TOKEN_KEY]) && 
               $token !== null && 
               hash_equals($_SESSION[self::TOKEN_KEY], $token);
    }

    /**
     * Middleware to protect routes
     * 
     * @param callable $next Next middleware or route handler
     * @return mixed
     * @throws Exception If CSRF validation fails
     */
    public function protect(callable $next) {
        // Skip GET requests (typically safe)
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            return $next();
        }

        // Validate token
        if (!$this->validateToken()) {
            http_response_code(403);
            throw new Exception('CSRF token validation failed');
        }

        return $next();
    }

    /**
     * Inject CSRF token into forms
     * 
     * @param string $formHtml Original form HTML
     * @return string Form HTML with CSRF token
     */
    public function injectTokenToForm(string $formHtml): string {
        $token = $this->generateToken();
        
        // Inject hidden input field
        $csrfField = sprintf(
            '<input type="hidden" name="%s" value="%s">',
            self::TOKEN_KEY,
            $token
        );

        // Insert token before form closing tag
        return str_replace('</form>', $csrfField . '</form>', $formHtml);
    }

    /**
     * Set CSRF protection headers
     * 
     * @return void
     */
    public function setProtectionHeaders(): void {
        // Prevent CSRF via HTTP headers
        header('X-Frame-Options: DENY');
        header('X-XSS-Protection: 1; mode=block');
        header('Referrer-Policy: strict-origin-when-cross-origin');
    }
} 