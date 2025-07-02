<?php

namespace App\Middleware;

use App\Auth\Authentication;
use App\Database\Database;

class RoleMiddleware
{
    private array $allowedRoles;

    public function __construct(array $allowedRoles = [])
    {
        $this->allowedRoles = $allowedRoles;
    }

    public function handle(): bool
    {
        // If no roles are specified, allow access
        if (empty($this->allowedRoles)) {
            return true;
        }

        try {
            // Check if user is authenticated
            if (!isset($_SESSION['user']) || !isset($_SESSION['user']['role'])) {
                $this->redirect('/login', 'Please login to continue');
                return false;
            }

            // Get current user's role
            $userRole = $_SESSION['user']['role'];

            // Check if user's role is in the allowed roles
            if (!in_array($userRole, $this->allowedRoles)) {
                $this->redirect('/', 'Access denied');
                return false;
            }

            return true;
        } catch (\Exception $e) {
            error_log($e->getMessage());
            $this->redirect('/login', 'Authentication error');
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