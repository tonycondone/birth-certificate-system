<?php

namespace App\Controllers;

use App\Database\Database;

class HomeController
{
    public function index()
    {
        // Check if user is logged in - but don't redirect automatically to dashboard
        // This prevents redirect loops between home and dashboard
        $isLoggedIn = isset($_SESSION['user_id']);
        
        // Get user role if logged in
        $userRole = $_SESSION['role'] ?? null;
        
        // Create welcome message
        $welcomeMessage = $this->createWelcomeMessage($isLoggedIn, $userRole);
        
        // Get system statistics
        $statistics = $this->getSystemStatistics();
        
        // Pass the login status and other data to the view
        include BASE_PATH . '/resources/views/home.php';
    }
    
    /**
     * Creates a personalized welcome message based on user status
     * This method demonstrates:
     * - Function creation
     * - Conditional logic (if/else statements)
     * - String concatenation
     * - Parameter passing
     */
    private function createWelcomeMessage($isLoggedIn, $userRole)
    {
        if ($isLoggedIn) {
            // User is logged in - create role-specific message
            switch ($userRole) {
                case 'parent':
                    return "Welcome back! Ready to manage your birth certificate applications?";
                case 'hospital':
                    return "Welcome! You have pending birth verifications to review.";
                case 'registrar':
                    return "Welcome! New applications are waiting for your approval.";
                case 'admin':
                    return "Welcome, Administrator! System overview and management tools are available.";
                default:
                    return "Welcome back! How can we help you today?";
            }
        } else {
            // User is not logged in - show general welcome
            return "Welcome to the Digital Birth Certificate System! Please log in or register to get started.";
        }
    }
    
    /**
     * Fetches basic statistics from the database
     * This method demonstrates:
     * - Database connections
     * - SQL queries
     * - Error handling
     * - Data processing
     */
    private function getSystemStatistics()
    {
        try {
            // Get database connection
            $pdo = Database::getConnection();
            
            // Initialize statistics array
            $stats = [
                'total_users' => 0,
                'total_applications' => 0,
                'pending_applications' => 0,
                'approved_certificates' => 0
            ];
            
            // Count total users
            $stmt = $pdo->query("SELECT COUNT(*) as count FROM users WHERE status = 'active'");
            $result = $stmt->fetch();
            $stats['total_users'] = $result['count'];
            
            // Count total applications
            $stmt = $pdo->query("SELECT COUNT(*) as count FROM birth_applications");
            $result = $stmt->fetch();
            $stats['total_applications'] = $result['count'];
            
            // Count pending applications
            $stmt = $pdo->query("SELECT COUNT(*) as count FROM birth_applications WHERE status = 'submitted'");
            $result = $stmt->fetch();
            $stats['pending_applications'] = $result['count'];
            
            // Count approved certificates
            $stmt = $pdo->query("SELECT COUNT(*) as count FROM birth_applications WHERE status = 'approved'");
            $result = $stmt->fetch();
            $stats['approved_certificates'] = $result['count'];
            
            return $stats;
            
        } catch (\PDOException $e) {
            // Log the error (in a real application, you'd log this properly)
            error_log("Database error: " . $e->getMessage());
            
            // Return default values if database fails
            return [
                'total_users' => 'N/A',
                'total_applications' => 'N/A',
                'pending_applications' => 'N/A',
                'approved_certificates' => 'N/A'
            ];
        }
    }
    
    /**
     * Shows user profile page (requires authentication)
     * This method demonstrates:
     * - Session management
     * - Authentication checks
     * - User data retrieval
     * - Protected routes
     */
    public function profile()
    {
        // Check if user is logged in
        if (!isset($_SESSION['user_id'])) {
            $_SESSION['error'] = 'Please log in to view your profile.';
            header('Location: /login');
            exit;
        }
        
        try {
            // Get user data from database
            $pdo = Database::getConnection();
            $stmt = $pdo->prepare('SELECT * FROM users WHERE id = ? AND status = "active"');
            $stmt->execute([$_SESSION['user_id']]);
            $user = $stmt->fetch();
            
            if (!$user) {
                throw new Exception('User not found or account inactive');
            }
            
            // Get user's recent activity (applications, etc.)
            $recentActivity = $this->getUserRecentActivity($_SESSION['user_id']);
            
            // Include the profile view
            $pageTitle = 'My Profile - Digital Birth Certificate System';
            require_once __DIR__ . '/../../resources/views/profile.php';
            
        } catch (Exception $e) {
            $_SESSION['error'] = 'Error loading profile: ' . $e->getMessage();
            header('Location: /');
            exit;
        }
    }
    
    /**
     * Updates user profile information
     * This method demonstrates:
     * - Form processing for authenticated users
     * - Data validation and sanitization
     * - Database updates
     * - Security checks
     */
    public function updateProfile()
    {
        // Check if user is logged in
        if (!isset($_SESSION['user_id'])) {
            $_SESSION['error'] = 'Please log in to update your profile.';
            header('Location: /login');
            exit;
        }
        
        // Check if this is a POST request
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /profile');
            exit;
        }
        
        try {
            // Validate and sanitize input
            $firstName = $this->validateInput($_POST['first_name'] ?? '', 'First name is required', 2, 100);
            $lastName = $this->validateInput($_POST['last_name'] ?? '', 'Last name is required', 2, 100);
            $phoneNumber = $this->validateInput($_POST['phone_number'] ?? '', 'Phone number is required', 5, 50);
            
            // Update user in database
            $pdo = Database::getConnection();
            $stmt = $pdo->prepare('
                UPDATE users 
                SET first_name = ?, last_name = ?, phone_number = ?, updated_at = CURRENT_TIMESTAMP 
                WHERE id = ?
            ');
            $stmt->execute([$firstName, $lastName, $phoneNumber, $_SESSION['user_id']]);
            
            // Update session data
            $_SESSION['user_first_name'] = $firstName;
            $_SESSION['user_last_name'] = $lastName;
            
            $_SESSION['success'] = 'Profile updated successfully!';
            header('Location: /profile');
            exit;
            
        } catch (Exception $e) {
            $_SESSION['error'] = 'Error updating profile: ' . $e->getMessage();
            header('Location: /profile');
            exit;
        }
    }
    
    /**
     * Gets user's recent activity
     * This method demonstrates:
     * - Database queries with joins
     * - Data formatting
     * - Role-based data access
     */
    private function getUserRecentActivity($userId)
    {
        try {
            $pdo = Database::getConnection();
            $userRole = $_SESSION['user']['role'] ?? '';
            
            $activities = [];
            
            // Get recent applications (for parents)
            if ($userRole === 'parent') {
                $stmt = $pdo->prepare('
                    SELECT 
                        application_number,
                        child_first_name,
                        child_last_name,
                        status,
                        submitted_at
                    FROM birth_applications 
                    WHERE parent_id = ? 
                    ORDER BY submitted_at DESC 
                    LIMIT 5
                ');
                $stmt->execute([$userId]);
                $applications = $stmt->fetchAll();
                
                foreach ($applications as $app) {
                    $activities[] = [
                        'type' => 'application',
                        'title' => 'Birth Certificate Application',
                        'description' => "Application #{$app['application_number']} for {$app['child_first_name']} {$app['child_last_name']}",
                        'status' => ucfirst(str_replace('_', ' ', $app['status'])),
                        'date' => date('M j, Y', strtotime($app['submitted_at'])),
                        'icon' => 'fas fa-baby'
                    ];
                }
            }
            
            // Get recent verifications (for hospitals)
            if ($userRole === 'hospital') {
                $stmt = $pdo->prepare('
                    SELECT 
                        ba.application_number,
                        ba.child_first_name,
                        ba.child_last_name,
                        ba.hospital_verified_at
                    FROM birth_applications ba
                    WHERE ba.hospital_verified_by = ? 
                    ORDER BY ba.hospital_verified_at DESC 
                    LIMIT 5
                ');
                $stmt->execute([$userId]);
                $verifications = $stmt->fetchAll();
                
                foreach ($verifications as $ver) {
                    $activities[] = [
                        'type' => 'verification',
                        'title' => 'Birth Verification',
                        'description' => "Verified application #{$ver['application_number']} for {$ver['child_first_name']} {$ver['child_last_name']}",
                        'status' => 'Verified',
                        'date' => date('M j, Y', strtotime($ver['hospital_verified_at'])),
                        'icon' => 'fas fa-check-circle'
                    ];
                }
            }
            
            return $activities;
            
        } catch (Exception $e) {
            error_log("Error getting user activity: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Shows certificate dashboard with relationships
     * This method demonstrates:
     * - Complex JOIN queries
     * - Database relationships
     * - Data aggregation
     * - Role-based data access
     */
    public function certificateDashboard()
    {
        // Check if user is logged in
        if (!isset($_SESSION['user_id'])) {
            $_SESSION['error'] = 'Please log in to view the certificate dashboard.';
            header('Location: /login');
            exit;
        }
        
        try {
            $pdo = Database::getConnection();
            $userRole = $_SESSION['user']['role'] ?? '';
            $userId = $_SESSION['user']['id'];
            
            $dashboardData = [
                'certificates' => [],
                'statistics' => [],
                'recentActivity' => []
            ];
            
            // Get certificates based on user role
            if ($userRole === 'parent') {
                $dashboardData = $this->getParentCertificateData($pdo, $userId);
            } elseif ($userRole === 'registrar') {
                $dashboardData = $this->getRegistrarCertificateData($pdo, $userId);
            } elseif ($userRole === 'admin') {
                $dashboardData = $this->getAdminCertificateData($pdo);
            } else {
                throw new Exception('Access denied for this role');
            }
            
            // Include the dashboard view
            $pageTitle = 'Certificate Dashboard - Digital Birth Certificate System';
            require_once __DIR__ . '/../../resources/views/certificate-dashboard.php';
            
        } catch (Exception $e) {
            $_SESSION['error'] = 'Error loading dashboard: ' . $e->getMessage();
            header('Location: /');
            exit;
        }
    }
    
    /**
     * Gets certificate data for parents
     * Demonstrates: JOIN queries, data relationships
     */
    private function getParentCertificateData($pdo, $userId)
    {
        // Complex JOIN query to get certificates with application details
        $stmt = $pdo->prepare('
            SELECT 
                c.certificate_number,
                c.issue_date,
                c.status as certificate_status,
                c.pdf_path,
                ba.application_number,
                ba.child_first_name,
                ba.child_last_name,
                ba.date_of_birth,
                ba.gender,
                u.first_name as issued_by_name,
                u.last_name as issued_by_last_name
            FROM certificates c
            INNER JOIN birth_applications ba ON c.application_id = ba.id
            INNER JOIN users u ON c.issued_by = u.id
            WHERE ba.parent_id = ?
            ORDER BY c.issue_date DESC
            LIMIT 10
        ');
        $stmt->execute([$userId]);
        $certificates = $stmt->fetchAll();
        
        // Get statistics
        $stmt = $pdo->prepare('
            SELECT 
                COUNT(*) as total_certificates,
                SUM(CASE WHEN c.status = "active" THEN 1 ELSE 0 END) as active_certificates,
                SUM(CASE WHEN c.status = "expired" THEN 1 ELSE 0 END) as expired_certificates
            FROM certificates c
            INNER JOIN birth_applications ba ON c.application_id = ba.id
            WHERE ba.parent_id = ?
        ');
        $stmt->execute([$userId]);
        $statistics = $stmt->fetch();
        
        return [
            'certificates' => $certificates,
            'statistics' => $statistics,
            'recentActivity' => $this->getCertificateActivity($pdo, $userId, 'parent')
        ];
    }
    
    /**
     * Gets certificate data for registrars
     * Demonstrates: Complex queries with multiple conditions
     */
    private function getRegistrarCertificateData($pdo, $userId)
    {
        // Get certificates issued by this registrar
        $stmt = $pdo->prepare('
            SELECT 
                c.certificate_number,
                c.issue_date,
                c.status as certificate_status,
                ba.application_number,
                ba.child_first_name,
                ba.child_last_name,
                ba.date_of_birth,
                ba.gender,
                p.first_name as parent_first_name,
                p.last_name as parent_last_name
            FROM certificates c
            INNER JOIN birth_applications ba ON c.application_id = ba.id
            INNER JOIN users p ON ba.parent_id = p.id
            WHERE c.issued_by = ?
            ORDER BY c.issue_date DESC
            LIMIT 10
        ');
        $stmt->execute([$userId]);
        $certificates = $stmt->fetchAll();
        
        // Get statistics
        $stmt = $pdo->prepare('
            SELECT 
                COUNT(*) as total_issued,
                SUM(CASE WHEN c.status = "active" THEN 1 ELSE 0 END) as active_certificates,
                SUM(CASE WHEN c.status = "revoked" THEN 1 ELSE 0 END) as revoked_certificates,
                COUNT(DISTINCT DATE(c.issue_date)) as days_worked
            FROM certificates c
            WHERE c.issued_by = ?
        ');
        $stmt->execute([$userId]);
        $statistics = $stmt->fetch();
        
        return [
            'certificates' => $certificates,
            'statistics' => $statistics,
            'recentActivity' => $this->getCertificateActivity($pdo, $userId, 'registrar')
        ];
    }
    
    /**
     * Gets certificate data for admins
     * Demonstrates: System-wide data aggregation
     */
    private function getAdminCertificateData($pdo)
    {
        // Get all certificates with full details
        $stmt = $pdo->prepare('
            SELECT 
                c.certificate_number,
                c.issue_date,
                c.status as certificate_status,
                ba.application_number,
                ba.child_first_name,
                ba.child_last_name,
                ba.date_of_birth,
                ba.gender,
                p.first_name as parent_first_name,
                p.last_name as parent_last_name,
                r.first_name as registrar_first_name,
                r.last_name as registrar_last_name
            FROM certificates c
            INNER JOIN birth_applications ba ON c.application_id = ba.id
            INNER JOIN users p ON ba.parent_id = p.id
            INNER JOIN users r ON c.issued_by = r.id
            ORDER BY c.issue_date DESC
            LIMIT 20
        ');
        $stmt->execute();
        $certificates = $stmt->fetchAll();
        
        // Get system-wide statistics
        $stmt = $pdo->prepare('
            SELECT 
                COUNT(*) as total_certificates,
                SUM(CASE WHEN c.status = "active" THEN 1 ELSE 0 END) as active_certificates,
                SUM(CASE WHEN c.status = "expired" THEN 1 ELSE 0 END) as expired_certificates,
                SUM(CASE WHEN c.status = "revoked" THEN 1 ELSE 0 END) as revoked_certificates,
                COUNT(DISTINCT c.issued_by) as total_registrars,
                COUNT(DISTINCT ba.parent_id) as total_parents
            FROM certificates c
            INNER JOIN birth_applications ba ON c.application_id = ba.id
        ');
        $stmt->execute();
        $statistics = $stmt->fetch();
        
        return [
            'certificates' => $certificates,
            'statistics' => $statistics,
            'recentActivity' => $this->getCertificateActivity($pdo, null, 'admin')
        ];
    }
    
    /**
     * Gets certificate activity for different user types
     * Demonstrates: Conditional queries based on user role
     */
    private function getCertificateActivity($pdo, $userId, $userRole)
    {
        $activities = [];
        
        if ($userRole === 'parent') {
            // Get recent certificate activities for parents
            $stmt = $pdo->prepare('
                SELECT 
                    c.certificate_number,
                    c.issue_date,
                    c.status,
                    ba.child_first_name,
                    ba.child_last_name
                FROM certificates c
                INNER JOIN birth_applications ba ON c.application_id = ba.id
                WHERE ba.parent_id = ?
                ORDER BY c.issue_date DESC
                LIMIT 5
            ');
            $stmt->execute([$userId]);
            $results = $stmt->fetchAll();
            
            foreach ($results as $result) {
                $activities[] = [
                    'type' => 'certificate_issued',
                    'title' => 'Certificate Issued',
                    'description' => "Certificate #{$result['certificate_number']} for {$result['child_first_name']} {$result['child_last_name']}",
                    'status' => ucfirst($result['status']),
                    'date' => date('M j, Y', strtotime($result['issue_date'])),
                    'icon' => 'fas fa-certificate'
                ];
            }
        } elseif ($userRole === 'registrar') {
            // Get recent certificate activities for registrars
            $stmt = $pdo->prepare('
                SELECT 
                    c.certificate_number,
                    c.issue_date,
                    ba.child_first_name,
                    ba.child_last_name
                FROM certificates c
                INNER JOIN birth_applications ba ON c.application_id = ba.id
                WHERE c.issued_by = ?
                ORDER BY c.issue_date DESC
                LIMIT 5
            ');
            $stmt->execute([$userId]);
            $results = $stmt->fetchAll();
            
            foreach ($results as $result) {
                $activities[] = [
                    'type' => 'certificate_issued',
                    'title' => 'Certificate Issued',
                    'description' => "Issued certificate #{$result['certificate_number']} for {$result['child_first_name']} {$result['child_last_name']}",
                    'status' => 'Issued',
                    'date' => date('M j, Y', strtotime($result['issue_date'])),
                    'icon' => 'fas fa-stamp'
                ];
            }
        } elseif ($userRole === 'admin') {
            // Get recent system-wide certificate activities
            $stmt = $pdo->prepare('
                SELECT 
                    c.certificate_number,
                    c.issue_date,
                    c.status,
                    ba.child_first_name,
                    ba.child_last_name,
                    r.first_name as registrar_first_name,
                    r.last_name as registrar_last_name
                FROM certificates c
                INNER JOIN birth_applications ba ON c.application_id = ba.id
                INNER JOIN users r ON c.issued_by = r.id
                ORDER BY c.issue_date DESC
                LIMIT 5
            ');
            $stmt->execute();
            $results = $stmt->fetchAll();
            
            foreach ($results as $result) {
                $activities[] = [
                    'type' => 'certificate_issued',
                    'title' => 'Certificate Issued',
                    'description' => "Certificate #{$result['certificate_number']} issued by {$result['registrar_first_name']} {$result['registrar_last_name']}",
                    'status' => ucfirst($result['status']),
                    'date' => date('M j, Y', strtotime($result['issue_date'])),
                    'icon' => 'fas fa-certificate'
                ];
            }
        }
        
        return $activities;
    }
    
    /**
     * API endpoint for getting system statistics
     * This method demonstrates:
     * - RESTful API design
     * - JSON responses
     * - API authentication
     * - CORS handling
     */
    public function apiStatistics()
    {
        // Set JSON content type
        header('Content-Type: application/json');
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization');
        
        // Handle preflight requests
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(200);
            exit;
        }
        
        try {
            // Check API authentication (simple token-based for demo)
            $apiKey = $_SERVER['HTTP_AUTHORIZATION'] ?? $_GET['api_key'] ?? '';
            if (!$this->validateApiKey($apiKey)) {
                $this->sendJsonResponse(['error' => 'Invalid API key'], 401);
                return;
            }
            
            $pdo = Database::getConnection();
            
            // Get comprehensive system statistics
            $statistics = $this->getApiStatistics($pdo);
            
            $this->sendJsonResponse([
                'success' => true,
                'data' => $statistics,
                'timestamp' => date('Y-m-d H:i:s'),
                'version' => '1.0.0'
            ]);
            
        } catch (Exception $e) {
            $this->sendJsonResponse([
                'success' => false,
                'error' => $e->getMessage(),
                'timestamp' => date('Y-m-d H:i:s')
            ], 500);
        }
    }
    
    /**
     * API endpoint for certificate verification
     * This method demonstrates:
     * - Public API endpoints
     * - Input validation
     * - Error handling
     * - Response formatting
     */
    public function apiVerifyCertificate()
    {
        header('Content-Type: application/json');
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type');
        
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(200);
            exit;
        }
        
        try {
            $certificateNumber = $_GET['certificate_number'] ?? $_POST['certificate_number'] ?? '';
            
            if (empty($certificateNumber)) {
                $this->sendJsonResponse([
                    'success' => false,
                    'error' => 'Certificate number is required'
                ], 400);
                return;
            }
            
            $verificationResult = $this->verifyCertificateApi($certificateNumber);
            
            $this->sendJsonResponse([
                'success' => true,
                'data' => $verificationResult,
                'timestamp' => date('Y-m-d H:i:s')
            ]);
            
        } catch (Exception $e) {
            $this->sendJsonResponse([
                'success' => false,
                'error' => $e->getMessage(),
                'timestamp' => date('Y-m-d H:i:s')
            ], 500);
        }
    }
    
    /**
     * API endpoint for user applications (requires authentication)
     * This method demonstrates:
     * - Authenticated API endpoints
     * - User-specific data
     * - Pagination
     * - Filtering
     */
    public function apiUserApplications()
    {
        header('Content-Type: application/json');
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization');
        
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(200);
            exit;
        }
        
        try {
            // Check if user is logged in
            if (!isset($_SESSION['user_id'])) {
                $this->sendJsonResponse([
                    'success' => false,
                    'error' => 'Authentication required'
                ], 401);
                return;
            }
            
            $userId = $_SESSION['user_id'];
            $page = (int)($_GET['page'] ?? 1);
            $limit = (int)($_GET['limit'] ?? 10);
            $status = $_GET['status'] ?? '';
            
            $applications = $this->getUserApplicationsApi($userId, $page, $limit, $status);
            
            $this->sendJsonResponse([
                'success' => true,
                'data' => $applications,
                'pagination' => [
                    'page' => $page,
                    'limit' => $limit,
                    'total' => $applications['total'] ?? 0
                ],
                'timestamp' => date('Y-m-d H:i:s')
            ]);
            
        } catch (Exception $e) {
            $this->sendJsonResponse([
                'success' => false,
                'error' => $e->getMessage(),
                'timestamp' => date('Y-m-d H:i:s')
            ], 500);
        }
    }
    
    /**
     * Validates API key (simple implementation for demo)
     */
    private function validateApiKey($apiKey)
    {
        // In a real application, you'd validate against a database
        $validKeys = ['demo_api_key_123', 'test_key_456'];
        return in_array($apiKey, $validKeys);
    }
    
    /**
     * Sends JSON response with proper headers
     */
    private function sendJsonResponse($data, $statusCode = 200)
    {
        http_response_code($statusCode);
        echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    /**
     * Gets comprehensive system statistics for API
     */
    private function getApiStatistics($pdo)
    {
        $stats = [];
        
        // User statistics
        $stmt = $pdo->query('
            SELECT 
                COUNT(*) as total_users,
                SUM(CASE WHEN role = "parent" THEN 1 ELSE 0 END) as total_parents,
                SUM(CASE WHEN role = "hospital" THEN 1 ELSE 0 END) as total_hospitals,
                SUM(CASE WHEN role = "registrar" THEN 1 ELSE 0 END) as total_registrars,
                SUM(CASE WHEN role = "admin" THEN 1 ELSE 0 END) as total_admins
            FROM users 
            WHERE status = "active"
        ');
        $stats['users'] = $stmt->fetch();
        
        // Application statistics
        $stmt = $pdo->query('
            SELECT 
                COUNT(*) as total_applications,
                SUM(CASE WHEN status = "submitted" THEN 1 ELSE 0 END) as pending_applications,
                SUM(CASE WHEN status = "approved" THEN 1 ELSE 0 END) as approved_applications,
                SUM(CASE WHEN status = "rejected" THEN 1 ELSE 0 END) as rejected_applications
            FROM birth_applications
        ');
        $stats['applications'] = $stmt->fetch();
        
        // Certificate statistics
        $stmt = $pdo->query('
            SELECT 
                COUNT(*) as total_certificates,
                SUM(CASE WHEN status = "active" THEN 1 ELSE 0 END) as active_certificates,
                SUM(CASE WHEN status = "expired" THEN 1 ELSE 0 END) as expired_certificates,
                SUM(CASE WHEN status = "revoked" THEN 1 ELSE 0 END) as revoked_certificates
            FROM certificates
        ');
        $stats['certificates'] = $stmt->fetch();
        
        // Recent activity
        $stmt = $pdo->query('
            SELECT 
                DATE(created_at) as date,
                COUNT(*) as count
            FROM birth_applications 
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
            GROUP BY DATE(created_at)
            ORDER BY date DESC
            LIMIT 7
        ');
        $stats['recent_activity'] = $stmt->fetchAll();
        
        return $stats;
    }
    
    /**
     * Verifies certificate via API
     */
    private function verifyCertificateApi($certificateNumber)
    {
        $pdo = Database::getConnection();
        
        $stmt = $pdo->prepare('
            SELECT 
                c.certificate_number,
                c.status as certificate_status,
                c.issue_date,
                c.expiry_date,
                ba.child_first_name,
                ba.child_last_name,
                ba.date_of_birth,
                ba.gender,
                ba.place_of_birth,
                p.first_name as parent_first_name,
                p.last_name as parent_last_name
            FROM certificates c
            INNER JOIN birth_applications ba ON c.application_id = ba.id
            INNER JOIN users p ON ba.parent_id = p.id
            WHERE c.certificate_number = ?
        ');
        $stmt->execute([$certificateNumber]);
        $certificate = $stmt->fetch();
        
        if (!$certificate) {
            return [
                'valid' => false,
                'message' => 'Certificate not found'
            ];
        }
        
        if ($certificate['certificate_status'] !== 'active') {
            return [
                'valid' => false,
                'message' => 'Certificate is ' . $certificate['certificate_status'],
                'certificate' => $certificate
            ];
        }
        
        return [
            'valid' => true,
            'message' => 'Certificate is valid',
            'certificate' => $certificate
        ];
    }
    
    /**
     * Gets user applications for API
     */
    private function getUserApplicationsApi($userId, $page, $limit, $status)
    {
        $pdo = Database::getConnection();
        $offset = ($page - 1) * $limit;
        
        $whereClause = 'WHERE parent_id = ?';
        $params = [$userId];
        
        if (!empty($status)) {
            $whereClause .= ' AND status = ?';
            $params[] = $status;
        }
        
        // Get total count
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM birth_applications $whereClause");
        $stmt->execute($params);
        $total = $stmt->fetchColumn();
        
        // Get applications
        $stmt = $pdo->prepare("
            SELECT 
                application_number,
                child_first_name,
                child_last_name,
                date_of_birth,
                gender,
                status,
                submitted_at,
                hospital_verified_at,
                registrar_verified_at
            FROM birth_applications 
            $whereClause
            ORDER BY submitted_at DESC
            LIMIT ? OFFSET ?
        ");
        
        $params[] = $limit;
        $params[] = $offset;
        $stmt->execute($params);
        $applications = $stmt->fetchAll();
        
        return [
            'applications' => $applications,
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
            'pages' => ceil($total / $limit)
        ];
    }
} 