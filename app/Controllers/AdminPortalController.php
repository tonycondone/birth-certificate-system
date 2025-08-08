<?php

namespace App\Controllers;

use App\Database\Database;
use Exception;
use PDO;

/**
 * AdminPortalController
 * 
 * Handles admin-specific functionality including user management,
 * system monitoring, reports, and settings
 */
class AdminPortalController
{
    private $db;

    public function __construct()
    {
        try {
            $this->db = Database::getConnection();
        } catch (Exception $e) {
            error_log("AdminPortalController initialization error: " . $e->getMessage());
            $this->db = null;
        }
    }

    /**
     * Admin dashboard
     */
    public function dashboard()
    {
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
            header('Location: /login');
            exit;
        }

        $pageTitle = 'Admin Dashboard';
        
        // Get comprehensive dashboard statistics
        $statistics = $this->getDashboardStatistics();
        
        // Get system health metrics
        $systemHealth = $this->getSystemHealth();
        
        // Get recent activities
        $recentActivities = $this->getRecentActivities(10);
        
        // Get user statistics
        $userStats = $this->getUserStatistics();
        
        // Get application trends
        $applicationTrends = $this->getApplicationTrends();

        include BASE_PATH . '/resources/views/dashboard/admin.php';
    }

    /**
     * User management
     */
    public function users()
    {
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
            header('Location: /login');
            exit;
        }

        $pageTitle = 'User Management';
        
        // Get search and filter parameters
        $search = trim($_GET['search'] ?? '');
        $roleFilter = $_GET['role'] ?? '';
        $statusFilter = $_GET['status'] ?? '';
        $page = max(1, intval($_GET['page'] ?? 1));
        $perPage = 15;
        $offset = ($page - 1) * $perPage;

        // Get users with filters
        $users = $this->getUsersWithFilters($search, $roleFilter, $statusFilter, $offset, $perPage);
        $totalCount = $this->countUsers($search, $roleFilter, $statusFilter);
        
        // Calculate pagination
        $totalPages = ceil($totalCount / $perPage);

        include BASE_PATH . '/resources/views/admin/users.php';
    }

    /**
     * System monitoring
     */
    public function systemMonitoring()
    {
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
            header('Location: /login');
            exit;
        }

        $pageTitle = 'System Monitoring';
        
        // Get system metrics
        $systemMetrics = $this->getSystemMetrics();
        
        // Get performance data
        $performanceData = $this->getPerformanceData();
        
        // Get error logs
        $errorLogs = $this->getRecentErrorLogs(20);
        
        // Get database statistics
        $databaseStats = $this->getDatabaseStatistics();

        include BASE_PATH . '/resources/views/admin/monitoring.php';
    }

    /**
     * System settings
     */
    public function settings()
    {
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
            header('Location: /login');
            exit;
        }

        $pageTitle = 'System Settings';
        $success = null;
        $error = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $result = $this->updateSettings();
            if ($result['success']) {
                $success = $result['message'];
            } else {
                $error = $result['message'];
            }
        }

        // Get current settings
        $settings = $this->getSystemSettings();

        include BASE_PATH . '/resources/views/admin/settings.php';
    }

    /**
     * User actions (create, update, delete, etc.)
     */
    public function userAction()
    {
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
            http_response_code(403);
            echo json_encode(['error' => 'Unauthorized']);
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            return;
        }

        $action = $_POST['action'] ?? '';
        $userId = intval($_POST['user_id'] ?? 0);

        try {
            switch ($action) {
                case 'activate':
                    $result = $this->activateUser($userId);
                    break;
                case 'deactivate':
                    $result = $this->deactivateUser($userId);
                    break;
                case 'delete':
                    $result = $this->deleteUser($userId);
                    break;
                case 'reset_password':
                    $result = $this->resetUserPassword($userId);
                    break;
                case 'change_role':
                    $newRole = $_POST['new_role'] ?? '';
                    $result = $this->changeUserRole($userId, $newRole);
                    break;
                default:
                    $result = ['success' => false, 'message' => 'Invalid action'];
            }

            echo json_encode($result);
        } catch (Exception $e) {
            error_log("Error in user action: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['error' => 'Internal server error']);
        }
    }

    /**
     * Generate comprehensive reports
     */
    public function reports()
    {
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
            header('Location: /login');
            exit;
        }

        $pageTitle = 'Admin Reports';
        
        $reportType = $_GET['type'] ?? 'overview';
        $startDate = $_GET['start_date'] ?? date('Y-m-01');
        $endDate = $_GET['end_date'] ?? date('Y-m-t');

        $reportData = $this->generateReport($reportType, $startDate, $endDate);

        include BASE_PATH . '/resources/views/admin/reports.php';
    }

    /**
     * Get dashboard statistics
     */
    private function getDashboardStatistics()
    {
        try {
            $stats = [];
            
            // User statistics
            $stmt = $this->db->query("SELECT COUNT(*) FROM users");
            $stats['totalUsers'] = $stmt->fetchColumn();
            
            $stmt = $this->db->query("SELECT COUNT(*) FROM users WHERE status = 'active'");
            $stats['activeUsers'] = $stmt->fetchColumn();
            
            $stmt = $this->db->query("SELECT COUNT(*) FROM users WHERE DATE(created_at) = CURDATE()");
            $stats['newUsersToday'] = $stmt->fetchColumn();
            
            // Application statistics
            $stmt = $this->db->query("SELECT COUNT(*) FROM birth_applications");
            $stats['totalApplications'] = $stmt->fetchColumn();
            
            $stmt = $this->db->query("SELECT COUNT(*) FROM birth_applications WHERE status = 'submitted'");
            $stats['pendingApplications'] = $stmt->fetchColumn();
            
            $stmt = $this->db->query("SELECT COUNT(*) FROM birth_applications WHERE status = 'approved'");
            $stats['approvedApplications'] = $stmt->fetchColumn();
            
            $stmt = $this->db->query("SELECT COUNT(*) FROM birth_applications WHERE DATE(created_at) = CURDATE()");
            $stats['todayApplications'] = $stmt->fetchColumn();
            
            // Certificate statistics
            $stmt = $this->db->query("SELECT COUNT(*) FROM certificates WHERE status = 'active'");
            $stats['activeCertificates'] = $stmt->fetchColumn();
            
            $stmt = $this->db->query("SELECT COUNT(*) FROM certificates WHERE DATE(issued_at) = CURDATE()");
            $stats['certificatesIssuedToday'] = $stmt->fetchColumn();
            
            // System performance
            $stmt = $this->db->query("SELECT AVG(TIMESTAMPDIFF(HOUR, submitted_at, reviewed_at)) FROM birth_applications WHERE reviewed_at IS NOT NULL");
            $stats['avgProcessingTime'] = round($stmt->fetchColumn() ?? 0, 1);

            return $stats;
        } catch (Exception $e) {
            error_log("Error getting dashboard statistics: " . $e->getMessage());
            return [
                'totalUsers' => 0,
                'activeUsers' => 0,
                'newUsersToday' => 0,
                'totalApplications' => 0,
                'pendingApplications' => 0,
                'approvedApplications' => 0,
                'todayApplications' => 0,
                'activeCertificates' => 0,
                'certificatesIssuedToday' => 0,
                'avgProcessingTime' => 0
            ];
        }
    }

    /**
     * Get system health metrics
     */
    private function getSystemHealth()
    {
        try {
            $health = [];
            
            // Database health
            $stmt = $this->db->query("SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE()");
            $health['database_tables'] = $stmt->fetchColumn();
            
            // Disk usage (simulated)
            $health['disk_usage'] = rand(30, 70);
            
            // Memory usage (simulated)
            $health['memory_usage'] = rand(40, 80);
            
            // CPU usage (simulated)
            $health['cpu_usage'] = rand(20, 60);
            
            // API response time (simulated)
            $health['api_response_time'] = rand(100, 500);
            
            // Error rate (simulated)
            $health['error_rate'] = rand(0, 5);

            return $health;
        } catch (Exception $e) {
            error_log("Error getting system health: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get recent activities
     */
    private function getRecentActivities($limit)
    {
        try {
            $stmt = $this->db->prepare("
                SELECT al.*, u.first_name, u.last_name, u.email
                FROM activity_log al
                JOIN users u ON al.user_id = u.id
                ORDER BY al.created_at DESC
                LIMIT ?
            ");
            $stmt->execute([$limit]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error getting recent activities: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get user statistics
     */
    private function getUserStatistics()
    {
        try {
            $stats = [];
            
            // Users by role
            $stmt = $this->db->query("
                SELECT role, COUNT(*) as count 
                FROM users 
                GROUP BY role
            ");
            $stats['by_role'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Users by status
            $stmt = $this->db->query("
                SELECT status, COUNT(*) as count 
                FROM users 
                GROUP BY status
            ");
            $stats['by_status'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Registration trends (last 7 days)
            $stmt = $this->db->query("
                SELECT DATE(created_at) as date, COUNT(*) as count
                FROM users
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
                GROUP BY DATE(created_at)
                ORDER BY date
            ");
            $stats['registration_trend'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return $stats;
        } catch (Exception $e) {
            error_log("Error getting user statistics: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get application trends
     */
    private function getApplicationTrends()
    {
        try {
            $stmt = $this->db->query("
                SELECT 
                    DATE(created_at) as date,
                    COUNT(*) as total,
                    SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved,
                    SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected,
                    SUM(CASE WHEN status = 'submitted' THEN 1 ELSE 0 END) as pending
                FROM birth_applications
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                GROUP BY DATE(created_at)
                ORDER BY date
            ");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error getting application trends: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get users with filters
     */
    private function getUsersWithFilters($search, $roleFilter, $statusFilter, $offset, $limit)
    {
        try {
            $whereConditions = ["1=1"];
            $params = [];

            if (!empty($search)) {
                $whereConditions[] = "(first_name LIKE ? OR last_name LIKE ? OR email LIKE ? OR username LIKE ?)";
                $searchTerm = "%{$search}%";
                $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm, $searchTerm]);
            }

            if (!empty($roleFilter)) {
                $whereConditions[] = "role = ?";
                $params[] = $roleFilter;
            }

            if (!empty($statusFilter)) {
                $whereConditions[] = "status = ?";
                $params[] = $statusFilter;
            }

            $whereClause = implode(' AND ', $whereConditions);
            $params[] = $limit;
            $params[] = $offset;

            $stmt = $this->db->prepare("
                SELECT *
                FROM users
                WHERE {$whereClause}
                ORDER BY created_at DESC
                LIMIT ? OFFSET ?
            ");
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error getting filtered users: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Count users with filters
     */
    private function countUsers($search, $roleFilter, $statusFilter)
    {
        try {
            $whereConditions = ["1=1"];
            $params = [];

            if (!empty($search)) {
                $whereConditions[] = "(first_name LIKE ? OR last_name LIKE ? OR email LIKE ? OR username LIKE ?)";
                $searchTerm = "%{$search}%";
                $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm, $searchTerm]);
            }

            if (!empty($roleFilter)) {
                $whereConditions[] = "role = ?";
                $params[] = $roleFilter;
            }

            if (!empty($statusFilter)) {
                $whereConditions[] = "status = ?";
                $params[] = $statusFilter;
            }

            $whereClause = implode(' AND ', $whereConditions);

            $stmt = $this->db->prepare("
                SELECT COUNT(*)
                FROM users
                WHERE {$whereClause}
            ");
            $stmt->execute($params);
            return $stmt->fetchColumn();
        } catch (Exception $e) {
            error_log("Error counting users: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Activate user
     */
    private function activateUser($userId)
    {
        try {
            $stmt = $this->db->prepare("UPDATE users SET status = 'active' WHERE id = ?");
            $stmt->execute([$userId]);
            
            $this->logActivity($_SESSION['user_id'], 'activate_user', "Activated user ID: {$userId}");
            
            return ['success' => true, 'message' => 'User activated successfully'];
        } catch (Exception $e) {
            error_log("Error activating user: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error activating user'];
        }
    }

    /**
     * Deactivate user
     */
    private function deactivateUser($userId)
    {
        try {
            $stmt = $this->db->prepare("UPDATE users SET status = 'inactive' WHERE id = ?");
            $stmt->execute([$userId]);
            
            $this->logActivity($_SESSION['user_id'], 'deactivate_user', "Deactivated user ID: {$userId}");
            
            return ['success' => true, 'message' => 'User deactivated successfully'];
        } catch (Exception $e) {
            error_log("Error deactivating user: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error deactivating user'];
        }
    }

    /**
     * Delete user
     */
    private function deleteUser($userId)
    {
        try {
            // Don't allow deleting the current admin
            if ($userId == $_SESSION['user_id']) {
                return ['success' => false, 'message' => 'Cannot delete your own account'];
            }

            $stmt = $this->db->prepare("DELETE FROM users WHERE id = ?");
            $stmt->execute([$userId]);
            
            $this->logActivity($_SESSION['user_id'], 'delete_user', "Deleted user ID: {$userId}");
            
            return ['success' => true, 'message' => 'User deleted successfully'];
        } catch (Exception $e) {
            error_log("Error deleting user: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error deleting user'];
        }
    }

    /**
     * Reset user password
     */
    private function resetUserPassword($userId)
    {
        try {
            $newPassword = $this->generateRandomPassword();
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            
            $stmt = $this->db->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt->execute([$hashedPassword, $userId]);
            
            $this->logActivity($_SESSION['user_id'], 'reset_password', "Reset password for user ID: {$userId}");
            
            return [
                'success' => true, 
                'message' => 'Password reset successfully',
                'new_password' => $newPassword
            ];
        } catch (Exception $e) {
            error_log("Error resetting password: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error resetting password'];
        }
    }

    /**
     * Change user role
     */
    private function changeUserRole($userId, $newRole)
    {
        try {
            $validRoles = ['parent', 'hospital', 'registrar', 'admin'];
            if (!in_array($newRole, $validRoles)) {
                return ['success' => false, 'message' => 'Invalid role'];
            }

            $stmt = $this->db->prepare("UPDATE users SET role = ? WHERE id = ?");
            $stmt->execute([$newRole, $userId]);
            
            $this->logActivity($_SESSION['user_id'], 'change_role', "Changed role to {$newRole} for user ID: {$userId}");
            
            return ['success' => true, 'message' => 'User role updated successfully'];
        } catch (Exception $e) {
            error_log("Error changing user role: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error changing user role'];
        }
    }

    /**
     * Generate random password
     */
    private function generateRandomPassword($length = 12)
    {
        $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*';
        $password = '';
        for ($i = 0; $i < $length; $i++) {
            $password .= $characters[rand(0, strlen($characters) - 1)];
        }
        return $password;
    }

    /**
     * Log activity
     */
    private function logActivity($userId, $action, $description)
    {
        try {
            $this->ensureActivityLogTableExists();
            
            $stmt = $this->db->prepare("
                INSERT INTO activity_log (user_id, action, description, created_at)
                VALUES (?, ?, ?, NOW())
            ");
            $stmt->execute([$userId, $action, $description]);
        } catch (Exception $e) {
            error_log("Error logging activity: " . $e->getMessage());
        }
    }

    /**
     * Ensure activity log table exists
     */
    private function ensureActivityLogTableExists()
    {
        try {
            $this->db->exec("
                CREATE TABLE IF NOT EXISTS activity_log (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    user_id INT NOT NULL,
                    action VARCHAR(100) NOT NULL,
                    description TEXT,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                    INDEX idx_user_id (user_id),
                    INDEX idx_action (action)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");
        } catch (Exception $e) {
            error_log("Error creating activity log table: " . $e->getMessage());
        }
    }

    /**
     * Get system metrics
     */
    private function getSystemMetrics()
    {
        try {
            return [
                'uptime' => '99.9%',
                'response_time' => rand(100, 300) . 'ms',
                'throughput' => rand(50, 200) . ' req/min',
                'error_rate' => rand(0, 2) . '%',
                'active_sessions' => rand(10, 50),
                'database_connections' => rand(5, 20)
            ];
        } catch (Exception $e) {
            error_log("Error getting system metrics: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get performance data
     */
    private function getPerformanceData()
    {
        try {
            $data = [];
            for ($i = 23; $i >= 0; $i--) {
                $hour = date('H:i', strtotime("-{$i} hours"));
                $data[] = [
                    'time' => $hour,
                    'requests' => rand(50, 200),
                    'response_time' => rand(100, 500),
                    'errors' => rand(0, 10)
                ];
            }
            return $data;
        } catch (Exception $e) {
            error_log("Error getting performance data: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get recent error logs
     */
    private function getRecentErrorLogs($limit)
    {
        try {
            // Simulate error logs
            $errors = [];
            for ($i = 0; $i < $limit; $i++) {
                $errors[] = [
                    'timestamp' => date('Y-m-d H:i:s', strtotime("-{$i} hours")),
                    'level' => ['ERROR', 'WARNING', 'INFO'][rand(0, 2)],
                    'message' => 'Sample error message ' . ($i + 1),
                    'file' => '/path/to/file.php',
                    'line' => rand(10, 500)
                ];
            }
            return $errors;
        } catch (Exception $e) {
            error_log("Error getting error logs: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get database statistics
     */
    private function getDatabaseStatistics()
    {
        try {
            $stats = [];
            
            // Table sizes
            $stmt = $this->db->query("
                SELECT 
                    table_name,
                    table_rows,
                    ROUND(((data_length + index_length) / 1024 / 1024), 2) AS size_mb
                FROM information_schema.TABLES
                WHERE table_schema = DATABASE()
                ORDER BY (data_length + index_length) DESC
            ");
            $stats['tables'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Total database size
            $stmt = $this->db->query("
                SELECT ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS total_size_mb
                FROM information_schema.TABLES
                WHERE table_schema = DATABASE()
            ");
            $stats['total_size'] = $stmt->fetchColumn();

            return $stats;
        } catch (Exception $e) {
            error_log("Error getting database statistics: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get system settings
     */
    private function getSystemSettings()
    {
        try {
            $this->ensureSettingsTableExists();
            
            $stmt = $this->db->query("SELECT setting_key, setting_value FROM system_settings");
            $settings = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $settings[$row['setting_key']] = $row['setting_value'];
            }
            
            // Default settings if not found
            $defaults = [
                'site_name' => 'Birth Certificate System',
                'site_email' => 'admin@birthcertificate.gov',
                'max_file_size' => '10',
                'session_timeout' => '30',
                'maintenance_mode' => '0',
                'email_notifications' => '1',
                'auto_approval' => '0',
                'backup_frequency' => 'daily'
            ];
            
            return array_merge($defaults, $settings);
        } catch (Exception $e) {
            error_log("Error getting system settings: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Update system settings
     */
    private function updateSettings()
    {
        try {
            $this->ensureSettingsTableExists();
            
            $settings = [
                'site_name' => $_POST['site_name'] ?? '',
                'site_email' => $_POST['site_email'] ?? '',
                'max_file_size' => $_POST['max_file_size'] ?? '10',
                'session_timeout' => $_POST['session_timeout'] ?? '30',
                'maintenance_mode' => $_POST['maintenance_mode'] ?? '0',
                'email_notifications' => $_POST['email_notifications'] ?? '0',
                'auto_approval' => $_POST['auto_approval'] ?? '0',
                'backup_frequency' => $_POST['backup_frequency'] ?? 'daily'
            ];

            foreach ($settings as $key => $value) {
                $stmt = $this->db->prepare("
                    INSERT INTO system_settings (setting_key, setting_value, updated_at)
                    VALUES (?, ?, NOW())
                    ON DUPLICATE KEY UPDATE setting_value = ?, updated_at = NOW()
                ");
                $stmt->execute([$key, $value, $value]);
            }

            $this->logActivity($_SESSION['user_id'], 'update_settings', 'Updated system settings');

            return ['success' => true, 'message' => 'Settings updated successfully'];
        } catch (Exception $e) {
            error_log("Error updating settings: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error updating settings'];
        }
    }

    /**
     * Ensure settings table exists
     */
    private function ensureSettingsTableExists()
    {
        try {
            $this->db->exec("
                CREATE TABLE IF NOT EXISTS system_settings (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    setting_key VARCHAR(100) UNIQUE NOT NULL,
                    setting_value TEXT,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    INDEX idx_setting_key (setting_key)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");
        } catch (Exception $e) {
            error_log("Error creating settings table: " . $e->getMessage());
        }
    }

    /**
     * Generate report
     */
    private function generateReport($type, $startDate, $endDate)
    {
        try {
            $data = [];
            
            switch ($type) {
                case 'overview':
                    $data = $this->generateOverviewReport($startDate, $endDate);
                    break;
                case 'users':
                    $data = $this->generateUsersReport($startDate, $endDate);
                    break;
                case 'applications':
                    $data = $this->generateApplicationsReport($startDate, $endDate);
                    break;
                case 'performance':
                    $data = $this->generatePerformanceReport($startDate, $endDate);
                    break;
            }
            
            return $data;
        } catch (Exception $e) {
            error_log("Error generating report: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Generate overview report
     */
    private function generateOverviewReport($startDate, $endDate)
    {
        try {
            $report = [];
            
            // Summary statistics
            $stmt = $this->db->prepare("
                SELECT 
                    COUNT(*) as total_applications,
                    SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved,
                    SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected,
                    SUM(CASE WHEN status = 'submitted' THEN 1 ELSE 0 END) as pending
                FROM birth_applications
                WHERE created_at BETWEEN ? AND ?
            ");
            $stmt->execute([$startDate, $endDate]);
            $report['summary'] = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Daily breakdown
            $stmt = $this->db->prepare("
                SELECT 
                    DATE(created_at) as date,
                    COUNT(*) as total,
                    SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved,
                    SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected
                FROM birth_applications
                WHERE created_at BETWEEN ? AND ?
                GROUP BY DATE(created_at)
                ORDER BY date
            ");
            $stmt->execute([$startDate, $endDate]);
            $report['daily'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return $report;
        } catch (Exception $e) {
            error_log("Error generating overview report: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Generate users report
     */
    private function generateUsersReport($startDate, $endDate)
    {
        try {
            $report = [];
            
            // User registrations by role
            $stmt = $this->db->prepare("
                SELECT 
                    role,
                    COUNT(*) as count
                FROM users
                WHERE created_at BETWEEN ? AND ?
                GROUP BY role
            ");
            $stmt->execute([$startDate, $endDate]);
            $report['by_role'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Daily registrations
            $stmt = $this->db->prepare("
                SELECT 
                    DATE(created_at) as date,
                    COUNT(*) as count
                FROM users
                WHERE created_at BETWEEN ? AND ?
                GROUP BY DATE(created_at)
                ORDER BY date
            ");
            $stmt->execute([$startDate, $endDate]);
            $report['daily'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return $report;
        } catch (Exception $e) {
            error_log("Error generating users report: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Generate applications report
     */
    private function generateApplicationsReport($startDate, $endDate)
    {
        try {
            $report = [];
            
            // Applications by status
            $stmt = $this->db->prepare("
                SELECT 
                    status,
                    COUNT(*) as count
                FROM birth_applications
                WHERE created_at BETWEEN ? AND ?
                GROUP BY status
            ");
            $stmt->execute([$startDate, $endDate]);
            $report['by_status'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Processing times
            $stmt = $this->db->prepare("
                SELECT 
                    AVG(TIMESTAMPDIFF(HOUR, submitted_at, reviewed_at)) as avg_hours,
                    MIN(TIMESTAMPDIFF(HOUR, submitted_at, reviewed_at)) as min_hours,
                    MAX(TIMESTAMPDIFF(HOUR, submitted_at, reviewed_at)) as max_hours
                FROM birth_applications
                WHERE reviewed_at BETWEEN ? AND ?
            ");
            $stmt->execute([$startDate, $endDate]);
            $report['processing_times'] = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return $report;
        } catch (Exception $e) {
            error_log("Error generating applications report: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Generate performance report
     */
    private function generatePerformanceReport($startDate, $endDate)
    {
        try {
            $report = [];
            
            // Registrar performance
            $stmt = $this->db->prepare("
                SELECT 
                    u.first_name,
                    u.last_name,
                    COUNT(*) as total_processed,
                    SUM(CASE WHEN ba.status = 'approved' THEN 1 ELSE 0 END) as approved,
                    SUM(CASE WHEN ba.status = 'rejected' THEN 1 ELSE 0 END) as rejected,
                    AVG(TIMESTAMPDIFF(HOUR, ba.submitted_at, ba.reviewed_at)) as avg_processing_time
                FROM birth_applications ba
                JOIN users u ON ba.reviewed_by = u.id
                WHERE ba.reviewed_at BETWEEN ? AND ?
                GROUP BY ba.reviewed_by
                ORDER BY total_processed DESC
            ");
            $stmt->execute([$startDate, $endDate]);
            $report['registrar_performance'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return $report;
        } catch (Exception $e) {
            error_log("Error generating performance report: " . $e->getMessage());
            return [];
        }
    }
}
