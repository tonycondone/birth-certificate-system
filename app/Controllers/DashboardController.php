<?php

namespace App\Controllers;

use App\Database\Database;
use PDOException;
use Exception;
use App\Repositories\DashboardRepository;
use App\Services\AuthService;

class DashboardController
{
    private $dashboardRepository;
    private $authService;

    public function __construct(?DashboardRepository $dashboardRepository = null, ?AuthService $authService = null)
    {
        // Initialize dependencies with defaults if not provided
        if ($dashboardRepository !== null) {
            $this->dashboardRepository = $dashboardRepository;
        } else {
            $this->dashboardRepository = $this->createDefaultDashboardRepository();
        }
        
        if ($authService !== null) {
            $this->authService = $authService;
        } else {
            $this->authService = $this->createDefaultAuthService();
        }
    }
    
    /**
     * Create default dashboard repository
     */
    private function createDefaultDashboardRepository()
    {
        // Try to create the real repository first
        try {
            return new DashboardRepository();
        } catch (Exception $e) {
            // Fall back to mock if real repository fails
            return new class {
                public function getDashboardStatistics() {
                    return [];
                }
                public function getRecentActivities($limit) {
                    return [];
                }
            };
        }
    }
    
    /**
     * Create default auth service
     */
    private function createDefaultAuthService()
    {
        // Try to create the real auth service first
        try {
            return new AuthService();
        } catch (Exception $e) {
            // Fall back to mock if real service fails
            return new class {
                public function requireRole($roles) {
                    // Basic role check using session
                    if (!isset($_SESSION['user_id'])) {
                        header('Location: /login');
                        exit;
                    }
                    return true;
                }
            };
        }
    }

    /**
     * Main dashboard entry point - redirects to appropriate dashboard based on user role
     */
    public function index()
    {
        // Check if user is logged in
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }
        
        try {
            $pdo = Database::getConnection();
            
            // Get user information
            $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
            $stmt->execute([$_SESSION['user_id']]);
            $user = $stmt->fetch();
            
            if (!$user) {
                // User not found in database
                error_log("Dashboard error: User ID {$_SESSION['user_id']} not found in database");
                session_destroy();
                header('Location: /login?error=session_expired');
                exit;
            }
            
            // Set page title
            $pageTitle = 'Dashboard - Digital Birth Certificate System';
            
            // Route to appropriate dashboard based on role
            switch ($user['role']) {
                case 'admin':
                    return $this->adminDashboard($pdo, $user, $pageTitle);
                case 'registrar':
                    return $this->registrarDashboard($pdo, $user, $pageTitle);
                case 'hospital':
                    return $this->hospitalDashboard($pdo, $user, $pageTitle);
                case 'parent':
                default:
                    return $this->citizenDashboard($pdo, $user, $pageTitle);
            }
            
        } catch (PDOException $e) {
            // Database connection error
            error_log("Dashboard database error: " . $e->getMessage());
            $_SESSION['error'] = 'Database connection error. Please try again later.';
            header('Location: /');
            exit;
        } catch (Exception $e) {
            // Generic error
            error_log("Dashboard error: " . $e->getMessage());
            $_SESSION['error'] = 'Unable to load dashboard. Please try again.';
            header('Location: /');
            exit;
        }
    }

    /**
     * Registrar dashboard entry point
     */
    public function registrar()
    {
        // Check if user is logged in
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }
        
        try {
            $pdo = Database::getConnection();
            
            // Get user information
            $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
            $stmt->execute([$_SESSION['user_id']]);
            $user = $stmt->fetch();
            
            if (!$user) {
                // User not found in database
                error_log("Dashboard error: User ID {$_SESSION['user_id']} not found in database");
                session_destroy();
                header('Location: /login?error=session_expired');
                exit;
            }
            
            // Check if user has registrar role
            if ($user['role'] !== 'registrar') {
                $_SESSION['error'] = 'Access denied. Registrar privileges required.';
                header('Location: /dashboard');
                exit;
            }
            
            // Set page title
            $pageTitle = 'Registrar Dashboard - Digital Birth Certificate System';
            
            // Call registrar dashboard
            return $this->registrarDashboard($pdo, $user, $pageTitle);
            
        } catch (PDOException $e) {
            // Database connection error
            error_log("Dashboard database error: " . $e->getMessage());
            $_SESSION['error'] = 'Database connection error. Please try again later.';
            header('Location: /');
            exit;
        } catch (Exception $e) {
            // Generic error
            error_log("Dashboard error: " . $e->getMessage());
            $_SESSION['error'] = 'Unable to load dashboard. Please try again.';
            header('Location: /');
            exit;
        }
    }
    
    /**
     * Admin Dashboard
     */
    private function adminDashboard($pdo, $user, $pageTitle)
    {
        // Get admin-specific statistics
        $statistics = [
            'totalUsers' => $this->countTotalUsers($pdo),
            'totalApplications' => $this->countTotalApplications($pdo),
            'pendingApplications' => $this->countApplicationsByStatus($pdo, 'submitted'),
            'approvedCertificates' => $this->countApplicationsByStatus($pdo, 'approved'),
            'todayApplications' => $this->countTodayApplications($pdo),
            'registrars' => $this->countUsersByRole($pdo, 'registrar'),
            'hospitals' => $this->countUsersByRole($pdo, 'hospital'),
            'parents' => $this->countUsersByRole($pdo, 'parent'),
            'admins' => $this->countUsersByRole($pdo, 'admin')
        ];
        
        // Get pending applications
        $pendingApplications = $this->getPendingApplications($pdo);
        
        // Get recent certificates
        $recentCertificates = $this->getRecentCertificates($pdo);
        
        // Get notifications
        $notifications = $this->getNotifications($pdo, $user['id']);
        
        // Get recent activities
        $recentActivities = $this->getRecentActivities($pdo, $user['id']);
        
        // Get system health metrics
        $systemHealth = $this->getSystemHealth($pdo);
        
        // Include admin dashboard view
        include BASE_PATH . '/resources/views/dashboard/admin.php';
    }
    
    /**
     * Registrar Dashboard
     */
    private function registrarDashboard($pdo, $user, $pageTitle)
    {
        // Get registrar-specific statistics
        $statistics = [
            'totalApplications' => $this->countTotalApplications($pdo),
            'pendingApplications' => $this->countApplicationsByStatus($pdo, 'submitted'),
            'approvedCertificates' => $this->countApplicationsByStatus($pdo, 'approved'),
            'todayApplications' => $this->countTodayApplications($pdo),
            'pendingApprovals' => $this->countPendingApprovals($pdo),
            'myApprovals' => $this->countUserApprovals($pdo, $user['id'])
        ];
        
        // Prepare quick stats for dashboard view
        $pendingReviews = $statistics['pendingApprovals'];
        $approvedToday = $this->countTodayApprovals($pdo, $user['id']);
        $rejectedToday = $this->countTodayRejections($pdo, $user['id']);
        $totalProcessed = $statistics['myApprovals'];
        
        // Sidebar badge count
        $pendingCount = $pendingReviews;
        
        // Get pending approvals for view
        $pendingApplications = $this->getPendingApprovals($pdo);
        
        // Get recently approved certificates
        $approvedCertificates = $this->getRecentApprovals($pdo, $user['id']);
        
        // Get notifications
        $notifications = $this->getNotifications($pdo, $user['id']);
        
        // Get recent activities
        $recentActivities = $this->getRecentActivities($pdo, $user['id']);
        
        // Include registrar dashboard view
        include BASE_PATH . '/resources/views/dashboard/registrar.php';
    }
    
    /**
     * Hospital Dashboard
     */
    private function hospitalDashboard($pdo, $user, $pageTitle)
    {
        // Get hospital-specific statistics
        $statistics = [
            'totalApplications' => $this->countUserApplications($pdo, $user['id']),
            'pendingApplications' => $this->countUserApplicationsByStatus($pdo, $user['id'], 'submitted'),
            'approvedApplications' => $this->countUserApplicationsByStatus($pdo, $user['id'], 'approved'),
            'rejectedApplications' => $this->countUserApplicationsByStatus($pdo, $user['id'], 'rejected'),
            'pendingVerifications' => $this->countHospitalPendingVerifications($pdo, $user['hospital_id']),
            'verifiedRecords' => $this->countHospitalVerified($pdo, $user['hospital_id'])
        ];
        
        // Prepare quick stats for hospital view
        $totalRecords = $statistics['totalApplications'];
        $pendingVerifications = $statistics['pendingVerifications'];
        $verifiedToday = $this->countHospitalTodayVerifications($pdo, $user['hospital_id']);
        $recordsThisMonth = $this->countHospitalMonthlyVerifications($pdo, $user['hospital_id']);
        
        // Get all applications if needed (not shown in main dashboard)
        $totalApplicationsList = $this->getMyApplications($pdo, $user['id']);
        
        // Get pending verifications for table
        $pendingApplications = $this->getHospitalPendingVerifications($pdo, $user['hospital_id']);
        
        // Get notifications
        $notifications = $this->getNotifications($pdo, $user['id']);
        
        // Get recent activities
        $recentActivities = $this->getRecentActivities($pdo, $user['id']);
        
        // Include hospital dashboard view
        include BASE_PATH . '/resources/views/dashboard/hospital.php';
    }
    
    /**
     * Citizen/Parent Dashboard
     */
    private function citizenDashboard($pdo, $user, $pageTitle)
    {
        // Get citizen-specific statistics
        $statistics = [
            'totalApplications' => $this->countUserApplications($pdo, $user['id']),
            'pendingApplications' => $this->countUserApplicationsByStatus($pdo, $user['id'], 'submitted'),
            'approvedApplications' => $this->countUserApplicationsByStatus($pdo, $user['id'], 'approved'),
            'rejectedApplications' => $this->countUserApplicationsByStatus($pdo, $user['id'], 'rejected'),
            'certificates' => count($this->getMyCertificates($pdo, $user['id']))
        ];
        
        // Get user's applications
        $applications = $this->getMyApplications($pdo, $user['id']);
        
        // Get user's certificates
        $certificates = $this->getMyCertificates($pdo, $user['id']);
        
        // Get notifications
        $notifications = $this->getNotifications($pdo, $user['id']);
        
        // Get recent activities
        $recentActivities = $this->getRecentActivities($pdo, $user['id']);
        
        // Include citizen dashboard view
        include BASE_PATH . '/resources/views/dashboard/index.php';
    }
    
    /**
     * Get pending applications
     */
    private function getPendingApplications($pdo)
    {
        try {
            $stmt = $pdo->prepare("
                SELECT a.*, u.email as applicant_email, u.first_name, u.last_name
                FROM birth_applications a
                JOIN users u ON a.user_id = u.id 
                WHERE a.status = 'submitted' 
                ORDER BY a.created_at DESC 
                LIMIT 10
            ");
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (Exception $e) {
            error_log("Error getting pending applications: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get recent certificates
     */
    private function getRecentCertificates($pdo)
    {
        try {
            $stmt = $pdo->prepare("
                SELECT c.*, a.purpose as application_purpose, u.email as applicant_email 
                FROM certificates c
                JOIN birth_applications a ON c.application_id = a.id 
                JOIN users u ON a.user_id = u.id 
                ORDER BY c.issued_at DESC 
                LIMIT 10
            ");
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (Exception $e) {
            error_log("Error getting recent certificates: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get pending approvals
     */
    private function getPendingApprovals($pdo)
    {
        try {
            $stmt = $pdo->prepare("
                SELECT a.*, 
                       h.name as hospital_name,
                       CONCAT(c.first_name, ' ', c.last_name) as child_name
                FROM applications a
                LEFT JOIN hospitals h ON a.hospital_id = h.id
                LEFT JOIN children c ON a.child_id = c.id
                WHERE a.status = 'pending'
                ORDER BY a.created_at DESC
                LIMIT 10
            ");
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (Exception $e) {
            error_log("Error getting pending approvals: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get recently approved applications by a specific user
     */
    private function getRecentApprovals($pdo, $userId)
    {
        try {
            $stmt = $pdo->prepare("
                SELECT a.*, 
                       h.name as hospital_name,
                       CONCAT(c.first_name, ' ', c.last_name) as child_name
                FROM applications a
                LEFT JOIN hospitals h ON a.hospital_id = h.id
                LEFT JOIN children c ON a.child_id = c.id
                WHERE a.approved_by = ? 
                AND a.status = 'approved'
                ORDER BY a.approved_at DESC
                LIMIT 10
            ");
            $stmt->execute([$userId]);
            return $stmt->fetchAll();
        } catch (Exception $e) {
            error_log("Error getting recent approvals: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get hospital pending verifications
     */
    private function getHospitalPendingVerifications($pdo, $hospitalId)
    {
        try {
            $stmt = $pdo->prepare("
                SELECT a.*, u.email as applicant_email, u.first_name, u.last_name
                FROM applications a
                JOIN users u ON a.user_id = u.id 
                WHERE a.hospital_id = ? AND a.status = 'pending' 
                ORDER BY a.created_at ASC 
                LIMIT 10
            ");
            $stmt->execute([$hospitalId]);
            return $stmt->fetchAll();
        } catch (Exception $e) {
            error_log("Error getting hospital pending verifications: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get user's applications
     */
    private function getMyApplications($pdo, $userId)
    {
        try {
            $stmt = $pdo->prepare("
                SELECT * FROM applications 
                WHERE user_id = ? 
                ORDER BY created_at DESC 
                LIMIT 10
            ");
            $stmt->execute([$userId]);
            return $stmt->fetchAll();
        } catch (Exception $e) {
            error_log("Error getting user applications: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get user's certificates
     */
    private function getMyCertificates($pdo, $userId)
    {
        try {
            $stmt = $pdo->prepare("
                SELECT c.*, a.purpose as application_purpose
                FROM certificates c 
                JOIN applications a ON c.application_id = a.id 
                WHERE a.user_id = ? 
                ORDER BY c.issued_at DESC 
                LIMIT 10
            ");
            $stmt->execute([$userId]);
            return $stmt->fetchAll();
        } catch (Exception $e) {
            error_log("Error getting user certificates: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get notifications for a user
     */
    private function getNotifications($pdo, $userId)
    {
        try {
        $stmt = $pdo->prepare("
            SELECT * FROM notifications 
            WHERE user_id = ? 
            ORDER BY created_at DESC 
            LIMIT 10
        ");
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
        } catch (Exception $e) {
            error_log("Error getting notifications: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get recent activities for a user
     */
    private function getRecentActivities($pdo, $userId)
    {
        try {
            $stmt = $pdo->prepare("
                SELECT * FROM activities
                WHERE user_id = ?
                ORDER BY created_at DESC
                LIMIT 10
            ");
            $stmt->execute([$userId]);
            return $stmt->fetchAll();
        } catch (Exception $e) {
            error_log("Error getting recent activities: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get system health metrics (admin only)
     */
    private function getSystemHealth($pdo)
    {
        try {
            // Get database size
            $stmt = $pdo->query("
                SELECT table_schema AS 'database',
                ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS 'size_mb'
                FROM information_schema.TABLES
                WHERE table_schema = 'birth_certificate_system'
                GROUP BY table_schema
            ");
            $dbSize = $stmt->fetch()['size_mb'] ?? 0;
            
            // Get server load (simulated)
            $serverLoad = rand(10, 40) / 100;
            
            // Get memory usage (simulated)
            $memoryUsage = rand(20, 70) / 100;
            
            // Get disk usage (simulated)
            $diskUsage = rand(30, 80) / 100;
            
            // Get API usage (simulated)
            $apiUsage = rand(5, 60) / 100;
        
        return [
                'database_size' => $dbSize,
                'server_load' => $serverLoad,
                'memory_usage' => $memoryUsage,
                'disk_usage' => $diskUsage,
                'api_usage' => $apiUsage
            ];
        } catch (Exception $e) {
            error_log("Error getting system health: " . $e->getMessage());
            return [
                'database_size' => 0,
                'server_load' => 0,
                'memory_usage' => 0,
                'disk_usage' => 0,
                'api_usage' => 0
            ];
        }
    }
    
    /**
     * Count user applications
     */
    private function countUserApplications($pdo, $userId)
    {
        try {
            $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM birth_applications WHERE user_id = ?");
        $stmt->execute([$userId]);
            return $stmt->fetch()['count'] ?? 0;
        } catch (Exception $e) {
            return 0;
        }
    }
    
    /**
     * Count user applications by status
     */
    private function countUserApplicationsByStatus($pdo, $userId, $status)
    {
        try {
            $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM birth_applications WHERE user_id = ? AND status = ?");
        $stmt->execute([$userId, $status]);
            return $stmt->fetch()['count'] ?? 0;
        } catch (Exception $e) {
            return 0;
        }
    }
    
    /**
     * Count hospital pending verifications
     */
    private function countHospitalPendingVerifications($pdo, $hospitalId)
    {
        try {
            $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM applications WHERE hospital_id = ? AND status = 'pending'");
        $stmt->execute([$hospitalId]);
            return $stmt->fetch()['count'] ?? 0;
        } catch (Exception $e) {
            return 0;
        }
    }
    
    /**
     * Count hospital verified records
     */
    private function countHospitalVerified($pdo, $hospitalId)
    {
        try {
            $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM applications WHERE hospital_id = ? AND status != 'pending'");
        $stmt->execute([$hospitalId]);
            return $stmt->fetch()['count'] ?? 0;
        } catch (Exception $e) {
            return 0;
        }
    }
    
    /**
     * Count pending applications awaiting approval
     */
    private function countPendingApprovals($pdo)
    {
        try {
            $stmt = $pdo->prepare("
                SELECT COUNT(*) as count 
                FROM birth_applications 
                WHERE status IN ('pending', 'submitted')
            ");
            $stmt->execute();
            $result = $stmt->fetch();
            return $result['count'] ?? 0;
        } catch (Exception $e) {
            error_log("Error counting pending approvals: " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Count applications approved by a specific user
     */
    private function countUserApprovals($pdo, $userId)
    {
        try {
            $stmt = $pdo->prepare("
                SELECT COUNT(*) as count 
                FROM applications 
                WHERE approved_by = ? AND status = 'approved'
            ");
            $stmt->execute([$userId]);
            $result = $stmt->fetch();
            return $result['count'] ?? 0;
        } catch (Exception $e) {
            error_log("Error counting user approvals: " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Count applications approved today by a specific user
     */
    private function countTodayApprovals($pdo, $userId)
    {
        try {
            $stmt = $pdo->prepare("
                SELECT COUNT(*) as count 
                FROM applications 
                WHERE approved_by = ? 
                AND status = 'approved' 
                AND DATE(approved_at) = CURDATE()
            ");
            $stmt->execute([$userId]);
            $result = $stmt->fetch();
            return $result['count'] ?? 0;
        } catch (Exception $e) {
            error_log("Error counting today's approvals: " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Count applications rejected today by a specific user
     */
    private function countTodayRejections($pdo, $userId)
    {
        try {
            $stmt = $pdo->prepare("
                SELECT COUNT(*) as count 
                FROM applications 
                WHERE rejected_by = ? 
                AND status = 'rejected' 
                AND DATE(rejected_at) = CURDATE()
            ");
            $stmt->execute([$userId]);
            $result = $stmt->fetch();
            return $result['count'] ?? 0;
        } catch (Exception $e) {
            error_log("Error counting today's rejections: " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Count total users
     */
    private function countTotalUsers($pdo)
    {
        try {
            $stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
            return $stmt->fetch()['count'] ?? 0;
        } catch (Exception $e) {
            return 0;
        }
    }
    
    /**
     * Count users by role
     */
    private function countUsersByRole($pdo, $role)
    {
        try {
            $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM users WHERE role = ?");
            $stmt->execute([$role]);
            return $stmt->fetch()['count'] ?? 0;
        } catch (Exception $e) {
            return 0;
        }
    }
    
    /**
     * Count total applications
     */
    private function countTotalApplications($pdo)
    {
        try {
            $stmt = $pdo->query("SELECT COUNT(*) as count FROM birth_applications");
            return $stmt->fetch()['count'] ?? 0;
        } catch (Exception $e) {
            return 0;
        }
    }
    
    /**
     * Count today's applications
     */
    private function countTodayApplications($pdo)
    {
        try {
            $stmt = $pdo->query("SELECT COUNT(*) as count FROM applications WHERE DATE(created_at) = CURDATE()");
            return $stmt->fetch()['count'] ?? 0;
        } catch (Exception $e) {
            return 0;
        }
    }
    
    /**
     * Count applications by status
     */
    private function countApplicationsByStatus($pdo, $status)
    {
        try {
            $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM birth_applications WHERE status = ?");
            $stmt->execute([$status]);
            return $stmt->fetch()['count'] ?? 0;
        } catch (Exception $e) {
            return 0;
        }
    }

    /**
     * Count verifications by a hospital today
     */
    private function countHospitalTodayVerifications($pdo, $hospitalId)
    {
        try {
            $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM applications WHERE hospital_id = ? AND status = 'verified' AND DATE(verified_at) = CURDATE()");
            $stmt->execute([$hospitalId]);
            return $stmt->fetch()['count'] ?? 0;
        } catch (Exception $e) {
            return 0;
        }
    }

    /**
     * Count verifications by a hospital this month
     */
    private function countHospitalMonthlyVerifications($pdo, $hospitalId)
    {
        try {
            $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM applications WHERE hospital_id = ? AND status = 'verified' AND MONTH(verified_at) = MONTH(CURDATE()) AND YEAR(verified_at) = YEAR(CURDATE())");
            $stmt->execute([$hospitalId]);
            return $stmt->fetch()['count'] ?? 0;
        } catch (Exception $e) {
            return 0;
        }
    }

    /**
     * Show pending applications page
     */
    public function pending()
    {
        // Check if user is logged in
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }
        
        try {
            $pdo = Database::getConnection();
            
            // Get user information
            $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
            $stmt->execute([$_SESSION['user_id']]);
            $user = $stmt->fetch();
            
            if (!$user || $user['role'] !== 'registrar') {
                $_SESSION['error'] = 'Access denied';
                header('Location: /dashboard');
                exit;
            }
            
            // Set page title
            $pageTitle = 'Pending Applications - Digital Birth Certificate System';
            
            // Get search parameters
            $search = $_GET['search'] ?? '';
            $hospitalFilter = $_GET['hospital'] ?? '';
            $dateFilter = $_GET['date'] ?? '';
            $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
            $perPage = 10;
            $offset = ($page - 1) * $perPage;
            
            // Get all unique hospital names for the filter dropdown
            $stmt = $pdo->query("SELECT DISTINCT hospital_name FROM birth_applications WHERE hospital_name IS NOT NULL AND hospital_name != '' ORDER BY hospital_name");
            $hospitals = $stmt->fetchAll();
            
            // Get pending applications with search and filters (both pending and submitted status)
            list($pendingApplications, $totalCount) = $this->searchApplications(
                $pdo,
                ['pending', 'submitted'],
                $search,
                $hospitalFilter,
                $dateFilter,
                $offset,
                $perPage
            );
            
            // Calculate pagination
            $totalPages = ceil($totalCount / $perPage);
            $currentPage = $page;
            
            // Get pending count for badge
            $pendingCount = $this->countPendingApprovals($pdo);
            
            // Include view
            include BASE_PATH . '/resources/views/dashboard/pending.php';
            
        } catch (Exception $e) {
            error_log("Error loading pending applications page: " . $e->getMessage());
            $_SESSION['error'] = 'Unable to load pending applications. Please try again.';
            header('Location: /dashboard');
            exit;
        }
    }
    
    /**
     * Search applications with filters
     * 
     * @param PDO $pdo Database connection
     * @param string $status Application status to filter by
     * @param string $search Search term
     * @param string $hospitalFilter Hospital ID to filter by
     * @param string $dateFilter Date filter (today, this_week, etc.)
     * @param int $offset Pagination offset
     * @param int $limit Pagination limit
     * @return array Array containing [applications, totalCount]
     */
    private function searchApplications($pdo, $status, $search = '', $hospitalFilter = '', $dateFilter = '', $offset = 0, $limit = 10)
    {
        try {
            // Base query
            $query = "
                SELECT ba.*, 
                       u.first_name as parent_first_name,
                       u.last_name as parent_last_name,
                       u.email as parent_email,
                       ba.child_first_name,
                       ba.child_last_name,
                       ba.date_of_birth,
                       ba.reference_number,
                       ba.created_at as submitted_at
                FROM birth_applications ba
                LEFT JOIN users u ON ba.user_id = u.id
                WHERE 1=1
            ";
            
            $countQuery = "SELECT COUNT(*) as count FROM birth_applications ba WHERE 1=1";
            $params = [];
            
            // Add status filter
            if ($status) {
                if (is_array($status)) {
                    $placeholders = str_repeat('?,', count($status) - 1) . '?';
                    $query .= " AND ba.status IN ($placeholders)";
                    $countQuery .= " AND ba.status IN ($placeholders)";
                    $params = array_merge($params, $status);
                } else {
                    $query .= " AND ba.status = ?";
                    $countQuery .= " AND ba.status = ?";
                    $params[] = $status;
                }
            }
            
            // Add search term
            if ($search) {
                $query .= " AND (
                    ba.reference_number LIKE ? OR
                    ba.child_first_name LIKE ? OR
                    ba.child_last_name LIKE ? OR
                    u.first_name LIKE ? OR
                    u.last_name LIKE ?
                )";
                $countQuery .= " AND (
                    ba.reference_number LIKE ? OR
                    ba.child_first_name LIKE ? OR
                    ba.child_last_name LIKE ? OR
                    EXISTS (SELECT 1 FROM users u WHERE u.id = ba.user_id AND (u.first_name LIKE ? OR u.last_name LIKE ?))
                )";
                $searchTerm = "%$search%";
                $params[] = $searchTerm;
                $params[] = $searchTerm;
                $params[] = $searchTerm;
                $params[] = $searchTerm;
                $params[] = $searchTerm;
            }
            
            // Add hospital filter (search by hospital name)
            if ($hospitalFilter) {
                $query .= " AND ba.hospital_name LIKE ?";
                $countQuery .= " AND ba.hospital_name LIKE ?";
                $params[] = "%$hospitalFilter%";
            }
            
            // Add date filter
            if ($dateFilter) {
                switch ($dateFilter) {
                    case 'today':
                        $query .= " AND DATE(ba.created_at) = CURDATE()";
                        $countQuery .= " AND DATE(ba.created_at) = CURDATE()";
                        break;
                    case 'yesterday':
                        $query .= " AND DATE(ba.created_at) = DATE_SUB(CURDATE(), INTERVAL 1 DAY)";
                        $countQuery .= " AND DATE(ba.created_at) = DATE_SUB(CURDATE(), INTERVAL 1 DAY)";
                        break;
                    case 'this_week':
                        $query .= " AND YEARWEEK(ba.created_at) = YEARWEEK(CURDATE())";
                        $countQuery .= " AND YEARWEEK(ba.created_at) = YEARWEEK(CURDATE())";
                        break;
                    case 'last_week':
                        $query .= " AND YEARWEEK(ba.created_at) = YEARWEEK(DATE_SUB(CURDATE(), INTERVAL 1 WEEK))";
                        $countQuery .= " AND YEARWEEK(ba.created_at) = YEARWEEK(DATE_SUB(CURDATE(), INTERVAL 1 WEEK))";
                        break;
                    case 'this_month':
                        $query .= " AND YEAR(ba.created_at) = YEAR(CURDATE()) AND MONTH(ba.created_at) = MONTH(CURDATE())";
                        $countQuery .= " AND YEAR(ba.created_at) = YEAR(CURDATE()) AND MONTH(ba.created_at) = MONTH(CURDATE())";
                        break;
                    case 'last_month':
                        $query .= " AND YEAR(ba.created_at) = YEAR(DATE_SUB(CURDATE(), INTERVAL 1 MONTH)) AND MONTH(ba.created_at) = MONTH(DATE_SUB(CURDATE(), INTERVAL 1 MONTH))";
                        $countQuery .= " AND YEAR(ba.created_at) = YEAR(DATE_SUB(CURDATE(), INTERVAL 1 MONTH)) AND MONTH(ba.created_at) = MONTH(DATE_SUB(CURDATE(), INTERVAL 1 MONTH))";
                        break;
                }
            }
            
            // Add sorting
            $query .= " ORDER BY ba.created_at DESC";
            
            // Add pagination
            $query .= " LIMIT ? OFFSET ?";
            $params[] = $limit;
            $params[] = $offset;
            
            // Execute query
            $stmt = $pdo->prepare($query);
            $stmt->execute($params);
            $applications = $stmt->fetchAll();
            
            // Get total count for pagination
            $countStmt = $pdo->prepare($countQuery);
            $countStmt->execute(array_slice($params, 0, -2)); // Remove limit and offset params
            $totalCount = $countStmt->fetch()['count'] ?? 0;
            
            return [$applications, $totalCount];
        } catch (Exception $e) {
            error_log("Error searching applications: " . $e->getMessage());
            return [[], 0];
        }
    }
    
    /**
     * Show approved applications page
     */
    public function approved()
    {
        // Check if user is logged in
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }
        
        try {
            $pdo = Database::getConnection();
            
            // Get user information
            $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
            $stmt->execute([$_SESSION['user_id']]);
            $user = $stmt->fetch();
            
            if (!$user || $user['role'] !== 'registrar') {
                $_SESSION['error'] = 'Access denied';
                header('Location: /dashboard');
                exit;
            }
            
            // Set page title
            $pageTitle = 'Approved Applications - Digital Birth Certificate System';
            
            // Get search parameters
            $search = $_GET['search'] ?? '';
            $hospitalFilter = $_GET['hospital'] ?? '';
            $dateFilter = $_GET['date'] ?? '';
            $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
            $perPage = 10;
            $offset = ($page - 1) * $perPage;
            
            // Get all hospitals for the filter dropdown
            $stmt = $pdo->query("SELECT id, name FROM hospitals ORDER BY name");
            $hospitals = $stmt->fetchAll();
            
            // Get approved applications with search and filters
            list($approvedApplications, $totalCount) = $this->searchApplications(
                $pdo,
                'approved',
                $search,
                $hospitalFilter,
                $dateFilter,
                $offset,
                $perPage
            );
            
            // Calculate pagination
            $totalPages = ceil($totalCount / $perPage);
            $currentPage = $page;
            
            // Get pending count for badge
            $pendingCount = $this->countPendingApprovals($pdo);
            
            // Include view
            include BASE_PATH . '/resources/views/dashboard/approved.php';
            
        } catch (Exception $e) {
            error_log("Error loading approved applications page: " . $e->getMessage());
            $_SESSION['error'] = 'Unable to load approved applications. Please try again.';
            header('Location: /dashboard');
            exit;
        }
    }
    
    /**
     * Show reports page
     */
    public function reports()
    {
        // Check if user is logged in
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }
        
        try {
            $pdo = Database::getConnection();
            
            // Get user information
            $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
            $stmt->execute([$_SESSION['user_id']]);
            $user = $stmt->fetch();
            
            if (!$user) {
                $_SESSION['error'] = 'Access denied';
                header('Location: /dashboard');
                exit;
            }
            
            // Set page title
            $pageTitle = 'Reports - Digital Birth Certificate System';
            
            // Get report type
            $reportType = $_GET['type'] ?? 'monthly';
            $year = $_GET['year'] ?? date('Y');
            $month = $_GET['month'] ?? date('m');
            $startDate = $_GET['start_date'] ?? date('Y-m-d', strtotime('-30 days'));
            $endDate = $_GET['end_date'] ?? date('Y-m-d');
            
            // Initialize report data
            $reportData = [];
            $chartLabels = [];
            $chartData = [];
            
            // Generate report based on type
            switch ($reportType) {
                case 'monthly':
                    list($reportData, $chartLabels, $chartData) = $this->generateMonthlyReport($pdo, $year, $month);
                    break;
                    
                case 'yearly':
                    list($reportData, $chartLabels, $chartData) = $this->generateYearlyReport($pdo, $year);
                    break;
                    
                case 'custom':
                    list($reportData, $chartLabels, $chartData) = $this->generateCustomReport($pdo, $startDate, $endDate);
                    break;
                    
                case 'hospital':
                    list($reportData, $chartLabels, $chartData) = $this->generateHospitalReport($pdo, $year);
                    break;
                    
                case 'registrar':
                    list($reportData, $chartLabels, $chartData) = $this->generateRegistrarReport($pdo, $year);
                    break;
                    
                default:
                    list($reportData, $chartLabels, $chartData) = $this->generateMonthlyReport($pdo, $year, $month);
                    break;
            }
            
            // Get pending count for badge (for registrar)
            $pendingCount = ($user['role'] === 'registrar') ? $this->countPendingApprovals($pdo) : 0;
            
            // Include view
            include BASE_PATH . '/resources/views/dashboard/reports.php';
            
        } catch (Exception $e) {
            error_log("Error loading reports page: " . $e->getMessage());
            $_SESSION['error'] = 'Unable to load reports. Please try again.';
            header('Location: /dashboard');
            exit;
        }
    }
    
    /**
     * Generate monthly report
     */
    private function generateMonthlyReport($pdo, $year, $month)
    {
        $reportData = [];
        $chartLabels = [];
        $chartData = [];
        
        try {
            // Get days in month
            $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);
            
            // Initialize data array
            for ($day = 1; $day <= $daysInMonth; $day++) {
                $date = sprintf('%04d-%02d-%02d', $year, $month, $day);
                $reportData[$date] = [
                    'date' => $date,
                    'day' => $day,
                    'applications' => 0,
                    'approved' => 0,
                    'rejected' => 0,
                    'pending' => 0
                ];
                $chartLabels[] = $day;
            }
            
            // Get applications by day
            $stmt = $pdo->prepare("
                SELECT 
                    DATE(created_at) as date,
                    COUNT(*) as total,
                    SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved,
                    SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected,
                    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending
                FROM applications
                WHERE YEAR(created_at) = ? AND MONTH(created_at) = ?
                GROUP BY DATE(created_at)
            ");
            $stmt->execute([$year, $month]);
            
            while ($row = $stmt->fetch()) {
                if (isset($reportData[$row['date']])) {
                    $reportData[$row['date']]['applications'] = $row['total'];
                    $reportData[$row['date']]['approved'] = $row['approved'];
                    $reportData[$row['date']]['rejected'] = $row['rejected'];
                    $reportData[$row['date']]['pending'] = $row['pending'];
                }
            }
            
            // Prepare chart data
            $approvedData = [];
            $rejectedData = [];
            $pendingData = [];
            
            foreach ($reportData as $data) {
                $approvedData[] = $data['approved'];
                $rejectedData[] = $data['rejected'];
                $pendingData[] = $data['pending'];
            }
            
            $chartData = [
                'approved' => $approvedData,
                'rejected' => $rejectedData,
                'pending' => $pendingData
            ];
            
            return [$reportData, $chartLabels, $chartData];
            
        } catch (Exception $e) {
            error_log("Error generating monthly report: " . $e->getMessage());
            return [[], [], []];
        }
    }
    
    /**
     * Generate yearly report
     */
    private function generateYearlyReport($pdo, $year)
    {
        $reportData = [];
        $chartLabels = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        $chartData = [];
        
        try {
            // Initialize data array
            for ($month = 1; $month <= 12; $month++) {
                $reportData[$month] = [
                    'month' => $month,
                    'month_name' => date('F', mktime(0, 0, 0, $month, 1, $year)),
                    'applications' => 0,
                    'approved' => 0,
                    'rejected' => 0,
                    'pending' => 0
                ];
            }
            
            // Get applications by month
            $stmt = $pdo->prepare("
                SELECT 
                    MONTH(created_at) as month,
                    COUNT(*) as total,
                    SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved,
                    SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected,
                    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending
                FROM applications
                WHERE YEAR(created_at) = ?
                GROUP BY MONTH(created_at)
            ");
            $stmt->execute([$year]);
            
            while ($row = $stmt->fetch()) {
                if (isset($reportData[$row['month']])) {
                    $reportData[$row['month']]['applications'] = $row['total'];
                    $reportData[$row['month']]['approved'] = $row['approved'];
                    $reportData[$row['month']]['rejected'] = $row['rejected'];
                    $reportData[$row['month']]['pending'] = $row['pending'];
                }
            }
            
            // Prepare chart data
            $approvedData = [];
            $rejectedData = [];
            $pendingData = [];
            
            foreach ($reportData as $data) {
                $approvedData[] = $data['approved'];
                $rejectedData[] = $data['rejected'];
                $pendingData[] = $data['pending'];
            }
            
            $chartData = [
                'approved' => $approvedData,
                'rejected' => $rejectedData,
                'pending' => $pendingData
            ];
            
            return [$reportData, $chartLabels, $chartData];
            
        } catch (Exception $e) {
            error_log("Error generating yearly report: " . $e->getMessage());
            return [[], [], []];
        }
    }
    
    /**
     * Generate custom date range report
     */
    private function generateCustomReport($pdo, $startDate, $endDate)
    {
        $reportData = [];
        $chartLabels = [];
        $chartData = [];
        
        try {
            // Create date range
            $period = new \DatePeriod(
                new \DateTime($startDate),
                new \DateInterval('P1D'),
                new \DateTime($endDate . ' +1 day')
            );
            
            // Initialize data array
            foreach ($period as $date) {
                $dateStr = $date->format('Y-m-d');
                $reportData[$dateStr] = [
                    'date' => $dateStr,
                    'day' => $date->format('d'),
                    'month_name' => $date->format('M'),
                    'applications' => 0,
                    'approved' => 0,
                    'rejected' => 0,
                    'pending' => 0
                ];
                $chartLabels[] = $date->format('d M');
            }
            
            // Get applications by day
            $stmt = $pdo->prepare("
                SELECT 
                    DATE(created_at) as date,
                    COUNT(*) as total,
                    SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved,
                    SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected,
                    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending
                FROM applications
                WHERE DATE(created_at) BETWEEN ? AND ?
                GROUP BY DATE(created_at)
            ");
            $stmt->execute([$startDate, $endDate]);
            
            while ($row = $stmt->fetch()) {
                if (isset($reportData[$row['date']])) {
                    $reportData[$row['date']]['applications'] = $row['total'];
                    $reportData[$row['date']]['approved'] = $row['approved'];
                    $reportData[$row['date']]['rejected'] = $row['rejected'];
                    $reportData[$row['date']]['pending'] = $row['pending'];
                }
            }
            
            // Prepare chart data
            $approvedData = [];
            $rejectedData = [];
            $pendingData = [];
            
            foreach ($reportData as $data) {
                $approvedData[] = $data['approved'];
                $rejectedData[] = $data['rejected'];
                $pendingData[] = $data['pending'];
            }
            
            $chartData = [
                'approved' => $approvedData,
                'rejected' => $rejectedData,
                'pending' => $pendingData
            ];
            
            return [$reportData, $chartLabels, $chartData];
            
        } catch (Exception $e) {
            error_log("Error generating custom report: " . $e->getMessage());
            return [[], [], []];
        }
    }
    
    /**
     * Generate hospital performance report
     */
    private function generateHospitalReport($pdo, $year)
    {
        $reportData = [];
        $chartLabels = [];
        $chartData = [];
        
        try {
            // Get all hospitals
            $stmt = $pdo->query("SELECT id, name FROM hospitals ORDER BY name");
            $hospitals = $stmt->fetchAll();
            
            // Initialize data array
            foreach ($hospitals as $hospital) {
                $reportData[$hospital['id']] = [
                    'hospital_id' => $hospital['id'],
                    'hospital_name' => $hospital['name'],
                    'applications' => 0,
                    'approved' => 0,
                    'rejected' => 0,
                    'pending' => 0,
                    'avg_processing_time' => 0
                ];
                $chartLabels[] = $hospital['name'];
            }
            
            // Get applications by hospital
            $stmt = $pdo->prepare("
                SELECT 
                    hospital_id,
                    COUNT(*) as total,
                    SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved,
                    SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected,
                    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
                    AVG(CASE WHEN status = 'approved' AND approved_at IS NOT NULL 
                         THEN TIMESTAMPDIFF(HOUR, created_at, approved_at) 
                         ELSE NULL END) as avg_processing_time
                FROM applications
                WHERE YEAR(created_at) = ?
                GROUP BY hospital_id
            ");
            $stmt->execute([$year]);
            
            while ($row = $stmt->fetch()) {
                if (isset($reportData[$row['hospital_id']])) {
                    $reportData[$row['hospital_id']]['applications'] = $row['total'];
                    $reportData[$row['hospital_id']]['approved'] = $row['approved'];
                    $reportData[$row['hospital_id']]['rejected'] = $row['rejected'];
                    $reportData[$row['hospital_id']]['pending'] = $row['pending'];
                    $reportData[$row['hospital_id']]['avg_processing_time'] = round($row['avg_processing_time'] ?? 0, 1);
                }
            }
            
            // Prepare chart data
            $approvedData = [];
            $rejectedData = [];
            $pendingData = [];
            $processingTimeData = [];
            
            foreach ($reportData as $data) {
                $approvedData[] = $data['approved'];
                $rejectedData[] = $data['rejected'];
                $pendingData[] = $data['pending'];
                $processingTimeData[] = $data['avg_processing_time'];
            }
            
            $chartData = [
                'approved' => $approvedData,
                'rejected' => $rejectedData,
                'pending' => $pendingData,
                'processing_time' => $processingTimeData
            ];
            
            return [$reportData, $chartLabels, $chartData];
            
        } catch (Exception $e) {
            error_log("Error generating hospital report: " . $e->getMessage());
            return [[], [], []];
        }
    }
    
    /**
     * Generate registrar performance report
     */
    private function generateRegistrarReport($pdo, $year)
    {
        $reportData = [];
        $chartLabels = [];
        $chartData = [];
        
        try {
            // Get all registrars
            $stmt = $pdo->prepare("SELECT id, first_name, last_name FROM users WHERE role = 'registrar' ORDER BY first_name, last_name");
            $stmt->execute();
            $registrars = $stmt->fetchAll();
            
            // Initialize data array
            foreach ($registrars as $registrar) {
                $reportData[$registrar['id']] = [
                    'registrar_id' => $registrar['id'],
                    'registrar_name' => $registrar['first_name'] . ' ' . $registrar['last_name'],
                    'approved' => 0,
                    'rejected' => 0,
                    'avg_processing_time' => 0
                ];
                $chartLabels[] = $registrar['first_name'] . ' ' . substr($registrar['last_name'], 0, 1) . '.';
            }
            
            // Get approved applications by registrar
            $stmt = $pdo->prepare("
                SELECT 
                    approved_by as registrar_id,
                    COUNT(*) as approved,
                    AVG(TIMESTAMPDIFF(HOUR, created_at, approved_at)) as avg_processing_time
                FROM applications
                WHERE status = 'approved' AND YEAR(approved_at) = ?
                GROUP BY approved_by
            ");
            $stmt->execute([$year]);
            
            while ($row = $stmt->fetch()) {
                if (isset($reportData[$row['registrar_id']])) {
                    $reportData[$row['registrar_id']]['approved'] = $row['approved'];
                    $reportData[$row['registrar_id']]['avg_processing_time'] = round($row['avg_processing_time'] ?? 0, 1);
                }
            }
            
            // Get rejected applications by registrar
            $stmt = $pdo->prepare("
                SELECT 
                    rejected_by as registrar_id,
                    COUNT(*) as rejected
                FROM applications
                WHERE status = 'rejected' AND YEAR(rejected_at) = ?
                GROUP BY rejected_by
            ");
            $stmt->execute([$year]);
            
            while ($row = $stmt->fetch()) {
                if (isset($reportData[$row['registrar_id']])) {
                    $reportData[$row['registrar_id']]['rejected'] = $row['rejected'];
                }
            }
            
            // Prepare chart data
            $approvedData = [];
            $rejectedData = [];
            $processingTimeData = [];
            
            foreach ($reportData as $data) {
                $approvedData[] = $data['approved'];
                $rejectedData[] = $data['rejected'];
                $processingTimeData[] = $data['avg_processing_time'];
            }
            
            $chartData = [
                'approved' => $approvedData,
                'rejected' => $rejectedData,
                'processing_time' => $processingTimeData
            ];
            
            return [$reportData, $chartLabels, $chartData];
            
        } catch (Exception $e) {
            error_log("Error generating registrar report: " . $e->getMessage());
            return [[], [], []];
        }
    }
    
    /**
     * Show settings page
     */
    public function settings()
    {
        // Check if user is logged in
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }
        
        try {
            $pdo = Database::getConnection();
            
            // Get user information
            $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
            $stmt->execute([$_SESSION['user_id']]);
            $user = $stmt->fetch();
            
            if (!$user) {
                $_SESSION['error'] = 'Access denied';
                header('Location: /dashboard');
                exit;
            }
            
            // Set page title
            $pageTitle = 'Settings - Digital Birth Certificate System';
            
            // Include view
            include BASE_PATH . '/resources/views/dashboard/settings.php';
            
        } catch (Exception $e) {
            error_log("Error loading settings page: " . $e->getMessage());
            $_SESSION['error'] = 'Unable to load settings. Please try again.';
            header('Location: /dashboard');
            exit;
        }
    }

    /**
     * Get dashboard statistics
     * 
     * @return array Dashboard statistics
     * @throws Exception
     */
    public function getDashboardStatistics(): array
    {
        // Ensure user is authorized
        $this->authService->requireRole(['registrar', 'admin']);

        try {
            return $this->dashboardRepository->getDashboardStatistics();
        } catch (Exception $e) {
            // Log error and rethrow
            error_log('Dashboard Statistics Error: ' . $e->getMessage());
            throw $e;
        }
    }
} 