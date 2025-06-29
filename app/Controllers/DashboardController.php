<?php

namespace App\Controllers;

use App\Database\Database;

class DashboardController
{
    public function index()
    {
        // Check if user is logged in
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }
        
        $pageTitle = 'Dashboard - Digital Birth Certificate System';
        
        try {
            $pdo = Database::getConnection();
            
            // Get user information
            $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
            $stmt->execute([$_SESSION['user_id']]);
            $user = $stmt->fetch();
            
            if (!$user) {
                session_destroy();
                header('Location: /login');
                exit;
            }
            
            // Get user-specific data based on role
            $dashboardData = $this->getDashboardData($pdo, $user);
            
            include __DIR__ . '/../../resources/views/dashboard/index.php';
            
        } catch (Exception $e) {
            error_log("Dashboard error: " . $e->getMessage());
            $_SESSION['error'] = 'Unable to load dashboard. Please try again.';
            header('Location: /home');
            exit;
        }
    }
    
    private function getDashboardData($pdo, $user)
    {
        $data = [
            'user' => $user,
            'recent_applications' => [],
            'statistics' => []
        ];
        
        switch ($user['role']) {
            case 'parent':
                $data = $this->getParentDashboard($pdo, $user);
                break;
            case 'hospital':
                $data = $this->getHospitalDashboard($pdo, $user);
                break;
            case 'registrar':
                $data = $this->getRegistrarDashboard($pdo, $user);
                break;
            case 'admin':
                $data = $this->getAdminDashboard($pdo, $user);
                break;
            default:
                $data = $this->getDefaultDashboard($pdo, $user);
        }
        
        return $data;
    }
    
    private function getParentDashboard($pdo, $user)
    {
        // Get user's applications
        $stmt = $pdo->prepare("
            SELECT * FROM birth_applications 
            WHERE user_id = ? 
            ORDER BY created_at DESC 
            LIMIT 5
        ");
        $stmt->execute([$user['id']]);
        
        return [
            'user' => $user,
            'recent_applications' => $stmt->fetchAll(),
            'statistics' => [
                'total_applications' => $this->countUserApplications($pdo, $user['id']),
                'pending_applications' => $this->countUserApplicationsByStatus($pdo, $user['id'], 'submitted'),
                'approved_certificates' => $this->countUserApplicationsByStatus($pdo, $user['id'], 'approved')
            ]
        ];
    }
    
    private function getHospitalDashboard($pdo, $user)
    {
        // Get applications for hospital verification
        $stmt = $pdo->prepare("
            SELECT ba.*, u.first_name, u.last_name 
            FROM birth_applications ba
            JOIN users u ON ba.user_id = u.id
            WHERE ba.hospital_id = ? AND ba.status = 'submitted'
            ORDER BY ba.created_at DESC 
            LIMIT 10
        ");
        $stmt->execute([$user['hospital_id']]);
        
        return [
            'user' => $user,
            'pending_verifications' => $stmt->fetchAll(),
            'statistics' => [
                'pending_verifications' => $this->countHospitalPendingVerifications($pdo, $user['hospital_id']),
                'total_verified' => $this->countHospitalVerified($pdo, $user['hospital_id'])
            ]
        ];
    }
    
    private function getRegistrarDashboard($pdo, $user)
    {
        // Get applications pending registrar approval
        $stmt = $pdo->prepare("
            SELECT ba.*, u.first_name, u.last_name 
            FROM birth_applications ba
            JOIN users u ON ba.user_id = u.id
            WHERE ba.status = 'under_review'
            ORDER BY ba.created_at DESC 
            LIMIT 10
        ");
        $stmt->execute();
        
        return [
            'user' => $user,
            'pending_approvals' => $stmt->fetchAll(),
            'statistics' => [
                'pending_approvals' => $this->countPendingApprovals($pdo),
                'total_approved' => $this->countApprovedApplications($pdo)
            ]
        ];
        }
    
    private function getAdminDashboard($pdo, $user)
    {
        // Get system-wide statistics
        return [
            'user' => $user,
            'statistics' => [
                'total_users' => $this->countTotalUsers($pdo),
                'total_applications' => $this->countTotalApplications($pdo),
                'pending_applications' => $this->countApplicationsByStatus($pdo, 'submitted'),
                'approved_certificates' => $this->countApplicationsByStatus($pdo, 'approved')
            ]
        ];
    }
    
    private function getDefaultDashboard($pdo, $user)
    {
        return [
            'user' => $user,
            'statistics' => [
                'total_applications' => $this->countTotalApplications($pdo)
            ]
        ];
    }
    
    // Helper methods for counting
    private function countUserApplications($pdo, $userId)
    {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM birth_applications WHERE user_id = ?");
        $stmt->execute([$userId]);
        return $stmt->fetchColumn();
    }
    
    private function countUserApplicationsByStatus($pdo, $userId, $status)
    {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM birth_applications WHERE user_id = ? AND status = ?");
        $stmt->execute([$userId, $status]);
        return $stmt->fetchColumn();
    }
    
    private function countHospitalPendingVerifications($pdo, $hospitalId)
    {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM birth_applications WHERE hospital_id = ? AND status = 'submitted'");
        $stmt->execute([$hospitalId]);
        return $stmt->fetchColumn();
    }
    
    private function countHospitalVerified($pdo, $hospitalId)
    {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM birth_applications WHERE hospital_id = ? AND status = 'under_review'");
        $stmt->execute([$hospitalId]);
        return $stmt->fetchColumn();
    }
    
    private function countPendingApprovals($pdo)
    {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM birth_applications WHERE status = 'under_review'");
        $stmt->execute();
        return $stmt->fetchColumn();
    }
    
    private function countApprovedApplications($pdo)
    {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM birth_applications WHERE status = 'approved'");
        $stmt->execute();
        return $stmt->fetchColumn();
    }
    
    private function countTotalUsers($pdo)
    {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE status = 'active'");
        $stmt->execute();
        return $stmt->fetchColumn();
    }
    
    private function countTotalApplications($pdo)
    {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM birth_applications");
        $stmt->execute();
        return $stmt->fetchColumn();
    }
    
    private function countApplicationsByStatus($pdo, $status)
    {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM birth_applications WHERE status = ?");
        $stmt->execute([$status]);
        return $stmt->fetchColumn();
    }
} 