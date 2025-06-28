<?php

namespace App\Middleware;

use PDO;
use Exception;
use DateTime;

class RateLimitMiddleware
{
    private PDO $db;
    private const RATE_LIMITS = [
        'login' => ['requests' => 5, 'window' => 300], // 5 requests per 5 minutes
        'register' => ['requests' => 3, 'window' => 3600], // 3 requests per hour
        'api' => ['requests' => 100, 'window' => 60], // 100 requests per minute
        'default' => ['requests' => 30, 'window' => 60] // 30 requests per minute
    ];

    public function __construct(PDO $db = null)
    {
        if ($db === null) {
            require_once __DIR__ . '/../Database/Database.php';
            $database = new \App\Database\Database();
            $this->db = $database->getConnection();
        } else {
            $this->db = $db;
        }
    }

    /**
     * Handle the rate limiting check
     * @param string $route The route being accessed
     * @param string $ipAddress The client IP address
     * @throws Exception if rate limit is exceeded
     */
    public function handle(string $route, string $ipAddress): void
    {
        // Determine which rate limit to apply
        $limit = $this->getRateLimit($route);
        
        // Clean up old rate limit entries
        $this->cleanupRateLimits();
        
        // Count recent requests
        $count = $this->countRecentRequests($route, $ipAddress, $limit['window']);
        
        if ($count >= $limit['requests']) {
            // Calculate retry after time
            $retryAfter = $this->getRetryAfterTime($route, $ipAddress);
            
            header('Retry-After: ' . $retryAfter);
            header('X-RateLimit-Limit: ' . $limit['requests']);
            header('X-RateLimit-Remaining: 0');
            
            throw new Exception('Rate limit exceeded. Please try again later.', 429);
        }
        
        // Record this request
        $this->recordRequest($route, $ipAddress);
        
        // Set rate limit headers
        header('X-RateLimit-Limit: ' . $limit['requests']);
        header('X-RateLimit-Remaining: ' . ($limit['requests'] - $count - 1));
    }

    /**
     * Get the appropriate rate limit for the route
     */
    private function getRateLimit(string $route): array
    {
        foreach (self::RATE_LIMITS as $pattern => $limit) {
            if (strpos($route, $pattern) !== false) {
                return $limit;
            }
        }
        return self::RATE_LIMITS['default'];
    }

    /**
     * Count recent requests within the time window
     */
    private function countRecentRequests(string $route, string $ipAddress, int $window): int
    {
        $stmt = $this->db->prepare('
            SELECT COUNT(*) 
            FROM rate_limits 
            WHERE route LIKE ? 
            AND ip_address = ? 
            AND timestamp > DATE_SUB(NOW(), INTERVAL ? SECOND)
        ');
        $stmt->execute(["%$route%", $ipAddress, $window]);
        return (int)$stmt->fetchColumn();
    }

    /**
     * Record a new request
     */
    private function recordRequest(string $route, string $ipAddress): void
    {
        $stmt = $this->db->prepare('
            INSERT INTO rate_limits (route, ip_address) 
            VALUES (?, ?)
        ');
        $stmt->execute([$route, $ipAddress]);
    }

    /**
     * Clean up old rate limit entries
     */
    private function cleanupRateLimits(): void
    {
        $stmt = $this->db->prepare('
            DELETE FROM rate_limits 
            WHERE timestamp < DATE_SUB(NOW(), INTERVAL 1 DAY)
        ');
        $stmt->execute();
    }

    /**
     * Calculate retry after time
     */
    private function getRetryAfterTime(string $route, string $ipAddress): int
    {
        $limit = $this->getRateLimit($route);
        
        $stmt = $this->db->prepare('
            SELECT timestamp 
            FROM rate_limits 
            WHERE route LIKE ? 
            AND ip_address = ? 
            ORDER BY timestamp ASC 
            LIMIT 1
        ');
        $stmt->execute(["%$route%", $ipAddress]);
        $oldestRequest = $stmt->fetchColumn();
        
        if (!$oldestRequest) {
            return $limit['window'];
        }
        
        $oldestTime = new DateTime($oldestRequest);
        $now = new DateTime();
        $diff = $now->getTimestamp() - $oldestTime->getTimestamp();
        
        return max(0, $limit['window'] - $diff);
    }
}