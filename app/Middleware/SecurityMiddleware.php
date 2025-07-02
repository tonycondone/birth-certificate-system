<?php

namespace App\Middleware;

use App\Services\BlockchainService;
use Exception;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class SecurityMiddleware
{
    private $blockchainService;
    private $rateLimitCache = [];
    private const RATE_LIMIT_WINDOW = 60; // 1 minute
    private const MAX_REQUESTS = 100; // Max requests per window

    public function __construct(BlockchainService $blockchainService)
    {
        $this->blockchainService = $blockchainService;
    }

    /**
     * Apply security headers and enforce HTTPS
     */
    public static function apply(): void
    {
        // Force HTTPS in production
        if (($_ENV['APP_ENV'] ?? 'production') === 'production' && 
            (!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] !== 'on')) {
            // Use trusted host from APP_URL to avoid host header injection
            $appUrl = $_ENV['APP_URL'] ?? '';
            $parsedUrl = parse_url($appUrl);
            $trustedHost = $parsedUrl['host'] ?? $_SERVER['SERVER_NAME'];
            $redirectUrl = 'https://' . $trustedHost . $_SERVER['REQUEST_URI'];
            header('Location: ' . $redirectUrl, true, 301);
            exit;
        }

        // Set security headers
        self::setSecurityHeaders();
    }

    /**
     * Set recommended security headers
     */
    private static function setSecurityHeaders(): void
    {
        // Content Security Policy (CSP)
        $cspDirectives = [
            "default-src 'self'",
            "script-src 'self' https://cdn.jsdelivr.net",
            "style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net",
            "img-src 'self' data: https:",
            "font-src 'self' https://cdn.jsdelivr.net",
            "connect-src 'self'",
            "frame-ancestors 'self'",
            "form-action 'self'",
            "base-uri 'self'",
            "object-src 'none'"
        ];
        header("Content-Security-Policy: " . implode('; ', $cspDirectives));

        // X-Content-Type-Options
        header("X-Content-Type-Options: nosniff");

        // X-Frame-Options
        header("X-Frame-Options: SAMEORIGIN");

        // X-XSS-Protection
        header("X-XSS-Protection: 1; mode=block");

        // Referrer-Policy
        header("Referrer-Policy: strict-origin-when-cross-origin");

        // Permissions-Policy (formerly Feature-Policy)
        header("Permissions-Policy: camera=(), microphone=(), geolocation=()");

        // Strict-Transport-Security (HSTS)
        if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
            header("Strict-Transport-Security: max-age=31536000; includeSubDomains; preload");
        }

        // Cache control for sensitive pages
        if (self::isSensitivePage()) {
            header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
            header("Pragma: no-cache");
            header("Expires: 0");
        }
    }

    /**
     * Check if current page is sensitive (auth, profile, etc.)
     */
    private static function isSensitivePage(): bool
    {
        $sensitiveRoutes = [
            '/login',
            '/register',
            '/auth/',
            '/profile',
            '/dashboard',
            '/admin/',
            '/settings',
            '/applications'
        ];

        $currentPath = $_SERVER['REQUEST_URI'] ?? '';
        
        foreach ($sensitiveRoutes as $route) {
            if (strpos($currentPath, $route) === 0) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Validate and sanitize input data
     * 
     * @param array $data
     * @return array
     */
    public function validateInput(array $data): array
    {
        $sanitized = [];
        foreach ($data as $key => $value) {
            // Sanitize and validate different input types
            switch (true) {
                case is_string($value):
                    $sanitized[$key] = $this->sanitizeString($value);
                    break;
                case is_numeric($value):
                    $sanitized[$key] = $this->sanitizeNumeric($value);
                    break;
                case is_array($value):
                    $sanitized[$key] = $this->validateNestedInput($value);
                    break;
                default:
                    $sanitized[$key] = $value;
            }
        }
        return $sanitized;
    }

    /**
     * Sanitize string input
     * 
     * @param string $input
     * @return string
     */
    private function sanitizeString(string $input): string
    {
        // Trim, strip tags, and encode special characters
        return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
    }

    /**
     * Sanitize numeric input
     * 
     * @param mixed $input
     * @return float|int
     */
    private function sanitizeNumeric($input)
    {
        return filter_var($input, FILTER_SANITIZE_NUMBER_FLOAT, 
            FILTER_FLAG_ALLOW_FRACTION | FILTER_FLAG_ALLOW_THOUSAND);
    }

    /**
     * Validate nested input recursively
     * 
     * @param array $input
     * @return array
     */
    private function validateNestedInput(array $input): array
    {
        $validated = [];
        foreach ($input as $key => $value) {
            if (is_array($value)) {
                $validated[$key] = $this->validateNestedInput($value);
            } else {
                $validated[$key] = $this->sanitizeString($value);
            }
        }
        return $validated;
    }

    /**
     * Generate CSRF token
     * 
     * @return string
     */
    public function generateCSRFToken(): string
    {
        $token = bin2hex(random_bytes(32));
        $_SESSION['csrf_token'] = $token;
        return $token;
    }

    /**
     * Validate CSRF token
     * 
     * @param string $token
     * @return bool
     */
    public function validateCSRFToken(string $token): bool
    {
        return isset($_SESSION['csrf_token']) && 
               hash_equals($_SESSION['csrf_token'], $token);
    }

    /**
     * Implement rate limiting
     * 
     * @param string $identifier
     * @return bool
     */
    public function checkRateLimit(string $identifier): bool
    {
        $now = time();
        
        // Initialize or clean up rate limit entry
        if (!isset($this->rateLimitCache[$identifier])) {
            $this->rateLimitCache[$identifier] = [
                'count' => 1,
                'timestamp' => $now
            ];
            return true;
        }

        $entry = &$this->rateLimitCache[$identifier];
        
        // Check if we're still in the rate limit window
        if ($now - $entry['timestamp'] > self::RATE_LIMIT_WINDOW) {
            // Reset window
            $entry = [
                'count' => 1,
                'timestamp' => $now
            ];
            return true;
        }

        // Check request count
        if ($entry['count'] >= self::MAX_REQUESTS) {
            return false;
        }

        // Increment request count
        $entry['count']++;
        return true;
    }

    /**
     * Generate JWT token for authentication
     * 
     * @param array $payload
     * @return string
     */
    public function generateJWTToken(array $payload): string
    {
        $key = $_ENV['JWT_SECRET'] ?? bin2hex(random_bytes(32));
        $payload['iat'] = time();
        $payload['exp'] = time() + (60 * 60); // 1 hour expiration
        
        return JWT::encode($payload, $key, 'HS256');
    }

    /**
     * Validate JWT token
     * 
     * @param string $token
     * @return array|false
     */
    public function validateJWTToken(string $token)
    {
        try {
            $key = $_ENV['JWT_SECRET'] ?? bin2hex(random_bytes(32));
            return JWT::decode($token, new Key($key, 'HS256'));
        } catch (Exception $e) {
            // Log token validation failure
            return false;
        }
    }
} 