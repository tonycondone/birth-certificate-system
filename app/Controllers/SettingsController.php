<?php

namespace App\Controllers;

use App\Database\Database;
// use App\Services\NotificationService;
use Exception;

class SettingsController
{
    private $db;
    private $notificationService;

    public function __construct()
    {
        try {
            $this->db = Database::getConnection();
            // $this->notificationService = new NotificationService();
        } catch (Exception $e) {
            error_log("SettingsController initialization error: " . $e->getMessage());
            $this->db = null;
        }
    }

    /**
     * Display settings page
     */
    public function index()
    {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }

        try {
            $userId = $_SESSION['user_id'];
            
            // Get user information
            $stmt = $this->db->prepare("
                SELECT id, first_name, last_name, email, phone, role, created_at, updated_at, 
                       email_verified_at, two_factor_enabled, last_login_at, status
                FROM users 
                WHERE id = ?
            ");
            $stmt->execute([$userId]);
            $user = $stmt->fetch();

            if (!$user) {
                $_SESSION['error'] = 'User not found.';
                header('Location: /dashboard');
                exit;
            }

            // Get user statistics
            $stats = $this->getUserStats($userId);
            
            $pageTitle = 'Account Settings';
            include BASE_PATH . '/resources/views/settings/index.php';

        } catch (Exception $e) {
            error_log("Error loading settings: " . $e->getMessage());
            $_SESSION['error'] = 'Unable to load settings. Please try again.';
            header('Location: /dashboard');
            exit;
        }
    }

    /**
     * Update user profile
     */
    public function updateProfile()
    {
        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            return;
        }

        try {
            $userId = $_SESSION['user_id'];
            $data = json_decode(file_get_contents('php://input'), true);

            // Validate required fields
            $firstName = trim($data['first_name'] ?? '');
            $lastName = trim($data['last_name'] ?? '');
            $phone = trim($data['phone'] ?? '');

            if (empty($firstName) || empty($lastName)) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'First name and last name are required']);
                return;
            }

            // Update user profile
            $stmt = $this->db->prepare("
                UPDATE users 
                SET first_name = ?, last_name = ?, phone = ?, updated_at = NOW()
                WHERE id = ?
            ");
            
            $success = $stmt->execute([$firstName, $lastName, $phone, $userId]);

            if ($success) {
                // Update session data
                $_SESSION['user']['first_name'] = $firstName;
                $_SESSION['user']['last_name'] = $lastName;

                // Send notification
                $this->sendNotification(
                    $userId,
                    '✅ Profile Updated',
                    'Your profile information has been updated successfully.',
                    'success',
                    'normal'
                );

                $this->logActivity($userId, 'profile_updated', 'Profile information updated');

                echo json_encode(['success' => true, 'message' => 'Profile updated successfully']);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Failed to update profile']);
            }

        } catch (Exception $e) {
            error_log("Error updating profile: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Internal server error']);
        }
    }

    /**
     * Change user password
     */
    public function changePassword()
    {
        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            return;
        }

        try {
            $userId = $_SESSION['user_id'];
            $data = json_decode(file_get_contents('php://input'), true);

            $currentPassword = $data['current_password'] ?? '';
            $newPassword = $data['new_password'] ?? '';
            $confirmPassword = $data['confirm_password'] ?? '';

            // Validate inputs
            if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'All password fields are required']);
                return;
            }

            if ($newPassword !== $confirmPassword) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'New passwords do not match']);
                return;
            }

            if (strlen($newPassword) < 8) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Password must be at least 8 characters long']);
                return;
            }

            // Verify current password
            $stmt = $this->db->prepare("SELECT password FROM users WHERE id = ?");
            $stmt->execute([$userId]);
            $user = $stmt->fetch();

            if (!$user || !password_verify($currentPassword, $user['password'])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Current password is incorrect']);
                return;
            }

            // Update password
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            $stmt = $this->db->prepare("
                UPDATE users 
                SET password = ?, updated_at = NOW()
                WHERE id = ?
            ");
            
            $success = $stmt->execute([$hashedPassword, $userId]);

            if ($success) {
                // Send notification
                $this->sendNotification(
                    $userId,
                    '🔐 Password Changed',
                    'Your password has been changed successfully. If this wasn\'t you, please contact support immediately.',
                    'info',
                    'high'
                );

                $this->logActivity($userId, 'password_changed', 'Password changed successfully');

                echo json_encode(['success' => true, 'message' => 'Password changed successfully']);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Failed to change password']);
            }

        } catch (Exception $e) {
            error_log("Error changing password: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Internal server error']);
        }
    }

    /**
     * Delete user account
     */
    public function deleteAccount()
    {
        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            return;
        }

        try {
            $userId = $_SESSION['user_id'];
            $data = json_decode(file_get_contents('php://input'), true);

            $password = $data['password'] ?? '';
            $confirmation = $data['confirmation'] ?? '';

            // Validate inputs
            if (empty($password)) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Password is required']);
                return;
            }

            if ($confirmation !== 'DELETE') {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Please type "DELETE" to confirm']);
                return;
            }

            // Verify password
            $stmt = $this->db->prepare("SELECT password, first_name, last_name, email FROM users WHERE id = ?");
            $stmt->execute([$userId]);
            $user = $stmt->fetch();

            if (!$user || !password_verify($password, $user['password'])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Password is incorrect']);
                return;
            }

            // Check for pending applications
            $stmt = $this->db->prepare("
                SELECT COUNT(*) FROM birth_applications 
                WHERE user_id = ? AND status IN ('pending', 'under_review', 'submitted')
            ");
            $stmt->execute([$userId]);
            $pendingCount = $stmt->fetchColumn();

            if ($pendingCount > 0) {
                http_response_code(400);
                echo json_encode([
                    'success' => false, 
                    'message' => "You have $pendingCount pending application(s). Please wait for them to be processed or cancel them before deleting your account."
                ]);
                return;
            }

            $this->db->beginTransaction();

            try {
                // Log the account deletion
                $this->logActivity($userId, 'account_deletion_initiated', 'User initiated account deletion');

                // Mark user as deleted instead of hard delete to preserve data integrity
                $stmt = $this->db->prepare("
                    UPDATE users 
                    SET status = 'deleted', 
                        email = CONCAT(email, '_deleted_', UNIX_TIMESTAMP()),
                        updated_at = NOW(),
                        deleted_at = NOW()
                    WHERE id = ?
                ");
                $stmt->execute([$userId]);

                // Soft delete user applications (mark as cancelled)
                $stmt = $this->db->prepare("
                    UPDATE birth_applications 
                    SET status = 'cancelled', updated_at = NOW()
                    WHERE user_id = ? AND status IN ('draft', 'rejected')
                ");
                $stmt->execute([$userId]);

                $this->db->commit();

                // Clear session
                session_destroy();

                echo json_encode([
                    'success' => true, 
                    'message' => 'Account deleted successfully. You will be redirected to the homepage.',
                    'redirect' => '/'
                ]);

            } catch (Exception $e) {
                $this->db->rollBack();
                throw $e;
            }

        } catch (Exception $e) {
            error_log("Error deleting account: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Internal server error']);
        }
    }

    /**
     * Get user applications for management
     */
    public function getApplications()
    {
        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            return;
        }

        try {
            $userId = $_SESSION['user_id'];
            $page = max(1, intval($_GET['page'] ?? 1));
            $perPage = 10;
            $offset = ($page - 1) * $perPage;

            // Get user applications
            $stmt = $this->db->prepare("
                SELECT ba.id, ba.application_number, ba.child_first_name, ba.child_last_name,
                       ba.status, ba.created_at, ba.updated_at, ba.rejection_reason,
                       c.certificate_number
                FROM birth_applications ba
                LEFT JOIN certificates c ON ba.id = c.application_id
                WHERE ba.user_id = ?
                ORDER BY ba.created_at DESC
                LIMIT ? OFFSET ?
            ");
            $stmt->execute([$userId, $perPage, $offset]);
            $applications = $stmt->fetchAll();

            // Get total count
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM birth_applications WHERE user_id = ?");
            $stmt->execute([$userId]);
            $totalCount = $stmt->fetchColumn();

            $totalPages = ceil($totalCount / $perPage);

            echo json_encode([
                'success' => true,
                'applications' => $applications,
                'pagination' => [
                    'current_page' => $page,
                    'total_pages' => $totalPages,
                    'total_count' => $totalCount,
                    'per_page' => $perPage
                ]
            ]);

        } catch (Exception $e) {
            error_log("Error getting applications: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Internal server error']);
        }
    }

    /**
     * Delete specific application
     */
    public function deleteApplication($applicationId)
    {
        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            return;
        }

        try {
            $userId = $_SESSION['user_id'];

            // Check if application belongs to user and can be deleted
            $stmt = $this->db->prepare("
                SELECT id, application_number, status, child_first_name, child_last_name
                FROM birth_applications 
                WHERE id = ? AND user_id = ?
            ");
            $stmt->execute([$applicationId, $userId]);
            $application = $stmt->fetch();

            if (!$application) {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'Application not found']);
                return;
            }

            // Check if application can be deleted
            $deletableStatuses = ['draft', 'rejected', 'cancelled'];
            if (!in_array($application['status'], $deletableStatuses)) {
                http_response_code(400);
                echo json_encode([
                    'success' => false, 
                    'message' => "Applications with status '{$application['status']}' cannot be deleted"
                ]);
                return;
            }

            $this->db->beginTransaction();

            try {
                // Log the deletion
                $this->logActivity($userId, 'application_deleted', "Deleted application {$application['application_number']}");

                // Delete the application (hard delete for draft/rejected applications)
                $stmt = $this->db->prepare("DELETE FROM birth_applications WHERE id = ? AND user_id = ?");
                $stmt->execute([$applicationId, $userId]);

                $this->db->commit();

                // Send notification
                $this->notificationService->sendNotification(
                    $userId,
                    '🗑️ Application Deleted',
                    "Application for {$application['child_first_name']} {$application['child_last_name']} has been deleted successfully.",
                    'info',
                    'normal'
                );

                echo json_encode(['success' => true, 'message' => 'Application deleted successfully']);

            } catch (Exception $e) {
                $this->db->rollBack();
                throw $e;
            }

        } catch (Exception $e) {
            error_log("Error deleting application: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Internal server error']);
        }
    }

    /**
     * Export user data (GDPR compliance)
     */
    public function exportData()
    {
        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            return;
        }

        try {
            $userId = $_SESSION['user_id'];

            // Get user data
            $stmt = $this->db->prepare("
                SELECT id, first_name, last_name, email, phone, role, created_at, updated_at, last_login_at
                FROM users WHERE id = ?
            ");
            $stmt->execute([$userId]);
            $userData = $stmt->fetch();

            // Get applications
            $stmt = $this->db->prepare("
                SELECT * FROM birth_applications WHERE user_id = ?
            ");
            $stmt->execute([$userId]);
            $applications = $stmt->fetchAll();

            // Get certificates
            $stmt = $this->db->prepare("
                SELECT c.* FROM certificates c
                JOIN birth_applications ba ON c.application_id = ba.id
                WHERE ba.user_id = ?
            ");
            $stmt->execute([$userId]);
            $certificates = $stmt->fetchAll();

            // Get notifications
            $stmt = $this->db->prepare("
                SELECT * FROM notifications WHERE user_id = ?
            ");
            $stmt->execute([$userId]);
            $notifications = $stmt->fetchAll();

            $exportData = [
                'user' => $userData,
                'applications' => $applications,
                'certificates' => $certificates,
                'notifications' => $notifications,
                'export_date' => date('Y-m-d H:i:s'),
                'export_format' => 'JSON'
            ];

            // Set headers for download
            header('Content-Type: application/json');
            header('Content-Disposition: attachment; filename="user_data_export_' . date('Y-m-d') . '.json"');
            
            echo json_encode($exportData, JSON_PRETTY_PRINT);

            // Log the export
            $this->logActivity($userId, 'data_exported', 'User data exported');

        } catch (Exception $e) {
            error_log("Error exporting data: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Internal server error']);
        }
    }

    /**
     * Get user statistics
     */
    private function getUserStats($userId)
    {
        try {
            $stats = [];

            // Application statistics
            $stmt = $this->db->prepare("
                SELECT 
                    COUNT(*) as total_applications,
                    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_applications,
                    SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved_applications,
                    SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected_applications,
                    SUM(CASE WHEN status = 'certificate_issued' THEN 1 ELSE 0 END) as issued_certificates
                FROM birth_applications 
                WHERE user_id = ?
            ");
            $stmt->execute([$userId]);
            $appStats = $stmt->fetch();

            // Notification statistics
            $stmt = $this->db->prepare("
                SELECT 
                    COUNT(*) as total_notifications,
                    SUM(CASE WHEN is_read = 0 THEN 1 ELSE 0 END) as unread_notifications
                FROM notifications 
                WHERE user_id = ?
            ");
            $stmt->execute([$userId]);
            $notifStats = $stmt->fetch();

            return array_merge($appStats ?: [], $notifStats ?: []);

        } catch (Exception $e) {
            error_log("Error getting user stats: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Log user activity
     */
    private function logActivity($userId, $action, $description)
    {
        try {
            // Ensure activity_log table exists
            $this->db->exec("
                CREATE TABLE IF NOT EXISTS activity_log (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    user_id INT NOT NULL,
                    action VARCHAR(100) NOT NULL,
                    description TEXT,
                    ip_address VARCHAR(45),
                    user_agent TEXT,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    INDEX idx_user_id (user_id),
                    INDEX idx_action (action),
                    INDEX idx_created_at (created_at)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
            ");

            $stmt = $this->db->prepare("
                INSERT INTO activity_log (user_id, action, description, ip_address, user_agent, created_at)
                VALUES (?, ?, ?, ?, ?, NOW())
            ");
            
            $stmt->execute([
                $userId,
                $action,
                $description,
                $_SERVER['REMOTE_ADDR'] ?? 'unknown',
                $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
            ]);

        } catch (Exception $e) {
            error_log("Failed to log activity: " . $e->getMessage());
        }
    }
} 