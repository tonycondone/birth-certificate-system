<?php

namespace App\Controllers;

use App\Auth\Authentication;
use App\Database\Database;
use App\Middleware\AuthMiddleware;
use PDO;
use Exception;

/**
 * Class AdminController
 *
 * Provides administrative functions such as system dashboard,
 * user management, application management, reports, and settings.
 * Protected by role-based access control for admins and registrars.
 */
class AdminController
{
    private PDO $db;
    private Authentication $auth;

    public function __construct()
    {
        // Initialize database connection
        $database = new Database();
        $this->db = $database->getConnection();
        $this->auth = new Authentication($this->db);

        // Ensure user is authenticated and is an admin
        $this->checkAdminAccess();
    }

    /**
     * Check if the current user has admin access
     */
    private function checkAdminAccess(): void
    {
        // Prefer existing Authentication session; fallback to core session keys
        $currentUser = $this->auth->getCurrentUser();
        if (!$currentUser && isset($_SESSION['user_id'])) {
            $currentUser = [
                'id' => $_SESSION['user_id'],
                'role' => $_SESSION['role'] ?? ''
            ];
        }
        if (!$currentUser || !in_array($currentUser['role'], ['admin', 'registrar'])) {
            header('Location: /login');
            exit;
        }
    }

    /**
     * Display admin dashboard with system statistics
     */
    public function dashboard(): void
    {
        try {
            $stats = $this->getSystemStats();
            $recentActivity = $this->getRecentActivity();
            $pendingApplications = $this->getPendingApplications();
            
            // Include the dashboard view
            include BASE_PATH . '/resources/views/dashboard/admin.php';
        } catch (Exception $e) {
            error_log("Admin Dashboard Error: " . $e->getMessage());
            $_SESSION['error'] = "Error loading dashboard data";
            header('Location: /error/500');
            exit;
        }
    }

    /**
     * Get system-wide statistics
     * @return array System statistics
     * @throws Exception if there's a database error
     */
    public function getSystemStats(): array
    {
        try {
            // Total Users
            $stmt = $this->db->query("SELECT COUNT(*) FROM users");
            $totalUsers = $stmt->fetchColumn();

            // Total Applications
            $stmt = $this->db->query("SELECT COUNT(*) FROM birth_applications");
            $totalApplications = $stmt->fetchColumn();

            // Pending Verifications
            $stmt = $this->db->query("
                SELECT COUNT(*) 
                FROM birth_applications 
                WHERE status = 'pending_verification'
            ");
            $pendingVerifications = $stmt->fetchColumn();

            // Certificates Issued
            $stmt = $this->db->query("SELECT COUNT(*) FROM certificates");
            $certificatesIssued = $stmt->fetchColumn();

            return [
                'totalUsers' => $totalUsers,
                'totalApplications' => $totalApplications,
                'pendingVerifications' => $pendingVerifications,
                'certificatesIssued' => $certificatesIssued
            ];
        } catch (Exception $e) {
            error_log("Error fetching system stats: " . $e->getMessage());
            throw new Exception("Failed to fetch system statistics");
        }
    }

    /**
     * Get recent system activity
     */
    private function getRecentActivity(int $limit = 10): array
    {
        try {
            $stmt = $this->db->prepare("
                SELECT a.*, u.first_name, u.last_name, u.role
                FROM activity_log a
                LEFT JOIN users u ON a.user_id = u.id
                ORDER BY a.timestamp DESC
                LIMIT ?
            ");
            $stmt->execute([$limit]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error fetching recent activity: " . $e->getMessage());
            throw new Exception("Failed to fetch recent activity");
        }
    }

    /**
     * Get pending applications
     */
    private function getPendingApplications(int $limit = 5): array
    {
        try {
            $stmt = $this->db->prepare("
                SELECT ba.*, u.first_name, u.last_name
                FROM birth_applications ba
                JOIN users u ON ba.user_id = u.id
                WHERE ba.status IN ('submitted','under_review')
                ORDER BY ba.created_at DESC
                LIMIT ?
            ");
            $stmt->execute([$limit]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error fetching pending applications: " . $e->getMessage());
            throw new Exception("Failed to fetch pending applications");
        }
    }

    /**
     * Update user information
     * @param int $userId User ID to update
     * @throws Exception if validation fails or user not found
     */
    public function updateUser(int $userId): void
    {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
                throw new Exception('Invalid request method');
            }

            // Get PUT data
            parse_str(file_get_contents("php://input"), $putData);

            // Validate user exists
            $stmt = $this->db->prepare("SELECT * FROM users WHERE id = ?");
            $stmt->execute([$userId]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$user) {
                throw new Exception('User not found');
            }

            // Build update data
            $updates = [];
            $params = [];

            if (isset($putData['email'])) {
                $updates[] = "email = ?";
                $params[] = $putData['email'];
            }

            if (isset($putData['first_name'])) {
                $updates[] = "first_name = ?";
                $params[] = $putData['first_name'];
            }

            if (isset($putData['last_name'])) {
                $updates[] = "last_name = ?";
                $params[] = $putData['last_name'];
            }

            if (isset($putData['role'])) {
                $updates[] = "role = ?";
                $params[] = $putData['role'];
            }

            if (isset($putData['status'])) {
                $updates[] = "status = ?";
                $params[] = $putData['status'];
            }

            if (empty($updates)) {
                throw new Exception('No fields to update');
            }

            // Add user ID to params
            $params[] = $userId;

            // Update user
            $sql = "UPDATE users SET " . implode(", ", $updates) . " WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);

            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'message' => 'User updated successfully'
            ]);
        } catch (Exception $e) {
            error_log("Error updating user: " . $e->getMessage());
            header('Content-Type: application/json');
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Delete a user
     * @param int $userId User ID to delete
     * @throws Exception if user not found or is admin
     */
    public function deleteUser(int $userId): void
    {
        try {
            // Validate user exists and is not an admin
            $stmt = $this->db->prepare("
                SELECT role 
                FROM users 
                WHERE id = ?
            ");
            $stmt->execute([$userId]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$user) {
                throw new Exception('User not found');
            }

            if ($user['role'] === 'admin') {
                throw new Exception('Cannot delete admin users');
            }

            // Soft delete the user
            $stmt = $this->db->prepare("
                UPDATE users 
                SET status = 'deleted', 
                    deleted_at = NOW() 
                WHERE id = ?
            ");
            $stmt->execute([$userId]);

            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'message' => 'User deleted successfully'
            ]);
        } catch (Exception $e) {
            error_log("Error deleting user: " . $e->getMessage());
            header('Content-Type: application/json');
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Get list of users with optional filtering
     * @throws Exception if there's a database error
     */
    public function getUsers(): void
    {
        try {
            $search = $_GET['search'] ?? '';
            $role = $_GET['role'] ?? '';
            $status = $_GET['status'] ?? '';
            
            $where = [];
            $params = [];
            
            if ($search) {
                $where[] = "(first_name LIKE ? OR last_name LIKE ? OR email LIKE ?)";
                $searchParam = "%$search%";
                $params = array_merge($params, [$searchParam, $searchParam, $searchParam]);
            }
            
            if ($role) {
                $where[] = "role = ?";
                $params[] = $role;
            }
            
            if ($status) {
                $where[] = "status = ?";
                $params[] = $status;
            }
            
            $whereClause = $where ? 'WHERE ' . implode(' AND ', $where) : '';
            
            $stmt = $this->db->prepare("
                SELECT id, username, email, first_name, last_name, role, status, created_at
                FROM users
                $whereClause
                ORDER BY created_at DESC
            ");
            $stmt->execute($params);
            $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'users' => $users]);
        } catch (Exception $e) {
            error_log("Error fetching users: " . $e->getMessage());
            header('Content-Type: application/json');
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Error fetching users']);
        }
    }

    /**
     * Create a new user
     */
    public function createUser(): void
    {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception('Invalid request method');
            }

            $data = [
                'username' => $_POST['username'] ?? '',
                'email' => $_POST['email'] ?? '',
                'password' => $_POST['password'] ?? '',
                'first_name' => $_POST['firstName'] ?? '',
                'last_name' => $_POST['lastName'] ?? '',
                'role' => $_POST['role'] ?? '',
                'status' => 'active'
            ];

            // Validate required fields
            foreach ($data as $key => $value) {
                if (empty($value)) {
                    throw new Exception("$key is required");
                }
            }

            // Create user using Authentication class
            $this->auth->register($data);

            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'message' => 'User created successfully']);
        } catch (Exception $e) {
            error_log("Error creating user: " . $e->getMessage());
            header('Content-Type: application/json');
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Update user status (activate/deactivate)
     */
    public function updateUserStatus(): void
    {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception('Invalid request method');
            }

            $userId = $_POST['userId'] ?? null;
            $status = $_POST['status'] ?? null;

            if (!$userId || !$status) {
                throw new Exception('User ID and status are required');
            }

            if (!in_array($status, ['active', 'inactive'])) {
                throw new Exception('Invalid status');
            }

            $stmt = $this->db->prepare("
                UPDATE users 
                SET status = ?, updated_at = NOW() 
                WHERE id = ?
            ");
            $stmt->execute([$status, $userId]);

            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'message' => 'User status updated successfully']);
        } catch (Exception $e) {
            error_log("Error updating user status: " . $e->getMessage());
            header('Content-Type: application/json');
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Get activity logs with filtering and pagination
     * @param int $page Page number
     * @param int $limit Items per page
     * @param string|null $type Activity type filter
     * @param int|null $userId User ID filter
     * @param string|null $startDate Start date filter
     * @param string|null $endDate End date filter
     * @return array Activity logs and pagination info
     * @throws Exception if there's a database error
     */
    public function getActivityLogs(
        int $page = 1,
        int $limit = 10,
        ?string $type = null,
        ?int $userId = null,
        ?string $startDate = null,
        ?string $endDate = null
    ): array {
        try {
            $where = [];
            $params = [];

            if ($type) {
                $where[] = "action = ?";
                $params[] = $type;
            }

            if ($userId) {
                $where[] = "user_id = ?";
                $params[] = $userId;
            }

            if ($startDate) {
                $where[] = "timestamp >= ?";
                $params[] = $startDate;
            }

            if ($endDate) {
                $where[] = "timestamp <= ?";
                $params[] = $endDate;
            }

            $whereClause = $where ? 'WHERE ' . implode(' AND ', $where) : '';

            // Get total count
            $countSql = "SELECT COUNT(*) FROM activity_log $whereClause";
            $stmt = $this->db->prepare($countSql);
            $stmt->execute($params);
            $total = $stmt->fetchColumn();

            // Calculate offset
            $offset = ($page - 1) * $limit;

            // Get logs
            $sql = "
                SELECT al.*, u.first_name, u.last_name, u.email
                FROM activity_log al
                LEFT JOIN users u ON al.user_id = u.id
                $whereClause
                ORDER BY al.timestamp DESC
                LIMIT ? OFFSET ?
            ";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([...$params, $limit, $offset]);
            $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return [
                'logs' => $logs,
                'pagination' => [
                    'total' => $total,
                    'page' => $page,
                    'limit' => $limit,
                    'pages' => ceil($total / $limit)
                ]
            ];
        } catch (Exception $e) {
            error_log("Error fetching activity logs: " . $e->getMessage());
            throw new Exception('Failed to fetch activity logs');
        }
    }

    /**
     * Get system settings
     * @throws Exception if there's a database error
     */
    public function getSettings(): void
    {
        try {
            $stmt = $this->db->query("SELECT * FROM system_settings");
            $settings = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'settings' => $settings]);
        } catch (Exception $e) {
            error_log("Error fetching settings: " . $e->getMessage());
            header('Content-Type: application/json');
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Error fetching settings']);
        }
    }

    /**
     * Update system settings
     */
    public function updateSettings(): void
    {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception('Invalid request method');
            }

            $settings = [
                'system_name' => $_POST['systemName'] ?? null,
                'smtp_host' => $_POST['smtpHost'] ?? null,
                'smtp_user' => $_POST['smtpUser'] ?? null,
                'smtp_pass' => $_POST['smtpPass'] ?? null,
                'sms_api_key' => $_POST['smsApiKey'] ?? null,
                'sms_secret' => $_POST['smsSecret'] ?? null
            ];

            foreach ($settings as $key => $value) {
                if ($value !== null) {
                    $stmt = $this->db->prepare("
                        INSERT INTO system_settings (setting_key, setting_value)
                        VALUES (?, ?)
                        ON DUPLICATE KEY UPDATE setting_value = ?
                    ");
                    $stmt->execute([$key, $value, $value]);
                }
            }

            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'message' => 'Settings updated successfully']);
        } catch (Exception $e) {
            error_log("Error updating settings: " . $e->getMessage());
            header('Content-Type: application/json');
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Error updating settings']);
        }
    }

    /**
     * Display the list of generic applications in the admin panel.
     * Retrieves applications joined with user data and renders the view.
     *
     * @return void
     */
    public function genericApplications(): void
    {
        try {
            $stmt = $this->db->query(
                'SELECT a.*, u.first_name, u.last_name, u.email 
                 FROM applications a 
                 JOIN users u ON a.user_id = u.id 
                 ORDER BY a.created_at DESC'
            );
            $applications = $stmt->fetchAll(PDO::FETCH_ASSOC);

            include BASE_PATH . '/resources/views/admin/generic_applications.php';
        } catch (Exception $e) {
            error_log("Admin Generic Applications Error: " . $e->getMessage());
            $_SESSION['error'] = "Error loading applications data";
            header('Location: /error/500');
            exit;
        }
    }

    /**
     * List all applications for admin
     */
    public function applications(): void
    {
        try {
            $this->checkAdminAccess();
            $search = trim($_GET['search'] ?? '');
            $status = $_GET['status'] ?? '';
            $page = max(1, intval($_GET['page'] ?? 1));
            $perPage = 20;
            $offset = ($page - 1) * $perPage;

            $where = [];
            $params = [];
            if ($search) {
                $where[] = "(ba.child_first_name LIKE ? OR ba.child_last_name LIKE ? OR ba.application_number LIKE ? OR u.email LIKE ?)";
                $q = "%$search%";
                array_push($params, $q, $q, $q, $q);
            }
            if ($status) {
                $where[] = "ba.status = ?";
                $params[] = $status;
            }
            $whereClause = $where ? ('WHERE ' . implode(' AND ', $where)) : '';

            $countStmt = $this->db->prepare("SELECT COUNT(*) FROM birth_applications ba JOIN users u ON ba.user_id = u.id $whereClause");
            $countStmt->execute($params);
            $total = (int)$countStmt->fetchColumn();

            $paramsWithPaging = array_merge($params, [$perPage, $offset]);
            $stmt = $this->db->prepare("
                SELECT ba.*, u.first_name, u.last_name, u.email
                FROM birth_applications ba
                JOIN users u ON ba.user_id = u.id
                $whereClause
                ORDER BY ba.created_at DESC
                LIMIT ? OFFSET ?
            ");
            $stmt->execute($paramsWithPaging);
            $applications = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Reuse a generic admin view if available; otherwise, return JSON
            if (file_exists(BASE_PATH . '/resources/views/dashboard/pending.php')) {
                $pageTitle = 'All Applications';
                include BASE_PATH . '/resources/views/dashboard/pending.php';
            } else {
                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'total' => $total, 'applications' => $applications]);
            }
        } catch (Exception $e) {
            error_log('Admin applications error: ' . $e->getMessage());
            $_SESSION['error'] = 'Unable to load applications';
            header('Location: /admin/dashboard');
        }
    }

    /**
     * Approve a specific application (quick action)
     */
    public function approveApplication(int $id): void
    {
        try {
            $this->checkAdminAccess();

            $reviewerId = $_SESSION['user_id'] ?? null;
            if (!$reviewerId) {
                header('Location: /login');
                exit;
            }

            $this->db->beginTransaction();

            // Update application status
            $stmt = $this->db->prepare("UPDATE birth_applications SET status = 'approved', reviewed_by = ?, reviewed_at = NOW() WHERE id = ?");
            $stmt->execute([$reviewerId, $id]);

            // Generate certificate
            $certificateNumber = 'BC' . date('Y') . date('m') . strtoupper(substr(md5(uniqid()), 0, 6));
            $qrCodeHash = hash('sha256', $certificateNumber . time() . uniqid());
            $stmt = $this->db->prepare("INSERT INTO certificates (certificate_number, application_id, qr_code_hash, issued_by, issued_at, status) VALUES (?, ?, ?, ?, NOW(), 'active')");
            $stmt->execute([$certificateNumber, $id, $qrCodeHash, $reviewerId]);

            $this->db->commit();

            // Redirect back with success
            $_SESSION['success'] = 'Application approved successfully';
            header('Location: /admin/applications');
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log('Admin approveApplication error: ' . $e->getMessage());
            $_SESSION['error'] = 'Failed to approve application';
            header('Location: /admin/applications');
        }
    }

    /**
     * Reject a specific application (quick action)
     */
    public function rejectApplication(int $id): void
    {
        try {
            $this->checkAdminAccess();

            $reviewerId = $_SESSION['user_id'] ?? null;
            if (!$reviewerId) {
                header('Location: /login');
                exit;
            }

            $comments = trim($_POST['comments'] ?? '');

            $stmt = $this->db->prepare("UPDATE birth_applications SET status = 'rejected', reviewed_by = ?, reviewed_at = NOW(), review_notes = ? WHERE id = ?");
            $stmt->execute([$reviewerId, $comments, $id]);

            $_SESSION['success'] = 'Application rejected';
            header('Location: /admin/applications');
        } catch (Exception $e) {
            error_log('Admin rejectApplication error: ' . $e->getMessage());
            $_SESSION['error'] = 'Failed to reject application';
            header('Location: /admin/applications');
        }
    }

    /**
     * Bulk approve/reject applications
     */
    public function bulkApplicationAction(): void
    {
        try {
            $this->checkAdminAccess();
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                http_response_code(405);
                echo json_encode(['success' => false, 'message' => 'Method not allowed']);
                return;
            }

            $ids = $_POST['application_ids'] ?? [];
            $action = $_POST['action'] ?? '';
            $comments = trim($_POST['comments'] ?? '');

            if (empty($ids) || !in_array($action, ['approve', 'reject'])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Invalid request']);
                return;
            }

            $reviewerId = $_SESSION['user_id'] ?? null;
            $success = 0; $errors = 0;

            if ($action === 'approve') {
                foreach ($ids as $id) {
                    try {
                        $this->db->beginTransaction();
                        $stmt = $this->db->prepare("UPDATE birth_applications SET status = 'approved', reviewed_by = ?, reviewed_at = NOW() WHERE id = ?");
                        $stmt->execute([$reviewerId, $id]);
                        $certificateNumber = 'BC' . date('Y') . date('m') . strtoupper(substr(md5(uniqid()), 0, 6));
                        $qrCodeHash = hash('sha256', $certificateNumber . time() . uniqid());
                        $stmt = $this->db->prepare("INSERT INTO certificates (certificate_number, application_id, qr_code_hash, issued_by, issued_at, status) VALUES (?, ?, ?, ?, NOW(), 'active')");
                        $stmt->execute([$certificateNumber, $id, $qrCodeHash, $reviewerId]);
                        $this->db->commit();
                        $success++;
                    } catch (Exception $e) {
                        $this->db->rollBack();
                        $errors++;
                    }
                }
            } else {
                $stmt = $this->db->prepare("UPDATE birth_applications SET status = 'rejected', reviewed_by = ?, reviewed_at = NOW(), review_notes = ? WHERE id = ?");
                foreach ($ids as $id) {
                    try {
                        $stmt->execute([$reviewerId, $comments, $id]);
                        $success++;
                    } catch (Exception $e) {
                        $errors++;
                    }
                }
            }

            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'message' => "Bulk action complete: {$success} success, {$errors} errors."]);
        } catch (Exception $e) {
            error_log('Admin bulkApplicationAction error: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Internal server error']);
        }
    }

    /**
     * Export applications CSV
     */
    public function exportApplications(): void
    {
        try {
            $this->checkAdminAccess();
            $stmt = $this->db->query("SELECT id, application_number, user_id, status, submitted_at, reviewed_at FROM birth_applications ORDER BY created_at DESC");
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="applications_export.csv"');
            $out = fopen('php://output', 'w');
            if (!empty($rows)) {
                fputcsv($out, array_keys($rows[0]));
                foreach ($rows as $row) { fputcsv($out, $row); }
            }
            fclose($out);
        } catch (Exception $e) {
            error_log('Admin exportApplications error: ' . $e->getMessage());
            $_SESSION['error'] = 'Failed to export applications';
            header('Location: /admin/applications');
        }
    }
}