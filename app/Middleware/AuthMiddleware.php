<?php

namespace App\Middleware;

use App\Auth\Authentication;
use App\Database\Database;

class AuthMiddleware
{
    private Authentication $auth;
    private array $config;

    public function __construct()
    {
        $this->auth = new Authentication(Database::getConnection());
        $this->config = [
            'login_url' => '/login',
            'session_timeout' => 1800, // 30 minutes
            'excluded_paths' => [
                '/login',
                '/register',
                '/verify',
                '/auth/login',
                '/auth/register',
                '/auth/logout'
            ]
        ];
    }

    public function handle(string $path, array $roles = []): bool
    {
        // Skip authentication for excluded paths
        if (in_array($path, $this->config['excluded_paths'])) {
            return true;
        }

        try {
            // Check session timeout
            if (isset($_SESSION['last_activity']) && 
                (time() - $_SESSION['last_activity'] > $this->config['session_timeout'])) {
                $this->auth->logout();
                $this->redirect($this->config['login_url'], 'Session expired. Please login again.');
                return false;
            }

            // Check authentication
            if (!$this->auth->isAuthenticated()) {
                $this->redirect($this->config['login_url'], 'Please login to continue');
                return false;
            }

            // Refresh session
            $this->auth->refreshSession();

            // Check roles if specified
            if (!empty($roles)) {
                $userRole = $_SESSION['user']['role'] ?? '';
                if (!in_array($userRole, $roles)) {
                    $this->redirect('/', 'Access denied');
                    return false;
                }
            }

            return true;
        } catch (\Exception $e) {
            error_log($e->getMessage());
            $this->redirect($this->config['login_url'], 'Authentication error');
            return false;
        }
    }

    private function redirect(string $url, string $message = ''): void
    {
        if ($message) {
            $_SESSION['error'] = $message;
        }
        // Validate URL: must be a relative path starting with '/', no protocol allowed
        if (strpos($url, '/') !== 0 || preg_match('/https?:\/\//i', $url) || strpos($url, "\n") !== false) {
            $url = '/';
        }
        header('Location: ' . $url);
        exit;
    }
}