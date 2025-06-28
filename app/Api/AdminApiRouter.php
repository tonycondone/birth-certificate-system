<?php

namespace App\Api;

use App\Controllers\AdminController;
use Exception;

class AdminApiRouter
{
    private AdminController $adminController;

    public function __construct()
    {
        $this->adminController = new AdminController();
    }

    /**
     * Handle API requests
     */
    public function handleRequest(string $endpoint): void
    {
        try {
            // Ensure it's an API request
            if (!str_starts_with($_SERVER['REQUEST_URI'], '/api/admin/')) {
                throw new Exception('Invalid API endpoint');
            }

            // Set JSON response headers
            header('Content-Type: application/json');
            
            // CSRF protection for non-GET requests
            if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
                $this->validateCsrfToken();
            }

            // Route the request
            switch ($endpoint) {
                case 'stats':
                    $this->handleStatsRequest();
                    break;
                    
                case 'users':
                    $this->handleUsersRequest();
                    break;
                    
                case 'settings':
                    $this->handleSettingsRequest();
                    break;
                    
                case 'activity-log':
                    $this->handleActivityLogRequest();
                    break;
                    
                default:
                    throw new Exception('Unknown endpoint');
            }
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Validate CSRF token
     */
    private function validateCsrfToken(): void
    {
        $token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;
        if (!$token || $token !== $_SESSION['csrf_token']) {
            throw new Exception('Invalid CSRF token');
        }
    }

    /**
     * Handle system stats request
     */
    private function handleStatsRequest(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            throw new Exception('Method not allowed');
        }

        $stats = $this->adminController->getSystemStats();
        echo json_encode([
            'success' => true,
            'data' => $stats
        ]);
    }

    /**
     * Handle users requests
     */
    private function handleUsersRequest(): void
    {
        switch ($_SERVER['REQUEST_METHOD']) {
            case 'GET':
                $this->adminController->getUsers();
                break;

            case 'POST':
                $this->adminController->createUser();
                break;

            case 'PUT':
                if (isset($_GET['id'])) {
                    $this->adminController->updateUser((int)$_GET['id']);
                } else {
                    throw new Exception('User ID required');
                }
                break;

            case 'DELETE':
                if (isset($_GET['id'])) {
                    $this->adminController->deleteUser((int)$_GET['id']);
                } else {
                    throw new Exception('User ID required');
                }
                break;

            default:
                throw new Exception('Method not allowed');
        }
    }

    /**
     * Handle settings requests
     */
    private function handleSettingsRequest(): void
    {
        switch ($_SERVER['REQUEST_METHOD']) {
            case 'GET':
                $this->adminController->getSettings();
                break;

            case 'POST':
                $this->adminController->updateSettings();
                break;

            default:
                throw new Exception('Method not allowed');
        }
    }

    /**
     * Handle activity log requests
     */
    private function handleActivityLogRequest(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            throw new Exception('Method not allowed');
        }

        // Get query parameters
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
        $type = $_GET['type'] ?? null;
        $userId = isset($_GET['userId']) ? (int)$_GET['userId'] : null;
        $startDate = $_GET['startDate'] ?? null;
        $endDate = $_GET['endDate'] ?? null;

        try {
            $logs = $this->adminController->getActivityLogs(
                $page,
                $limit,
                $type,
                $userId,
                $startDate,
                $endDate
            );

            echo json_encode([
                'success' => true,
                'data' => $logs
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Error fetching activity logs'
            ]);
        }
    }
}