<?php

namespace App\Controllers;

use App\Middleware\AuthMiddleware;

class DashboardController
{
    public function index()
    {
        // Check authentication
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }
        
        $userRole = $_SESSION['user_role'];
        $userId = $_SESSION['user_id'];
        
        // Redirect to role-specific dashboard
        switch ($userRole) {
            case 'parent':
                include __DIR__ . '/../../resources/views/dashboard/parent.php';
                break;
            case 'hospital':
                include __DIR__ . '/../../resources/views/dashboard/hospital.php';
                break;
            case 'registrar':
                include __DIR__ . '/../../resources/views/dashboard/registrar.php';
                break;
            case 'admin':
                include __DIR__ . '/../../resources/views/dashboard/admin.php';
                break;
            default:
                header('Location: /login');
                exit;
        }
    }
} 