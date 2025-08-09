<?php

namespace App\Controllers;

use App\Database\Database;
use Exception;
use PDO;

/**
 * RegistrarController
 * 
 * Handles registrar-specific functionality including application review,
 * approval, rejection, and certificate issuance
 */
class RegistrarController
{
    private $db;

    public function __construct()
    {
        try {
            $this->db = Database::getConnection();
        } catch (Exception $e) {
            error_log("RegistrarController initialization error: " . $e->getMessage());
            $this->db = null;
        }
    }

    /**
     * Registrar dashboard
     */
    public function dashboard()
    {
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'registrar') {
            header('Location: /login');
            exit;
        }

        $pageTitle = 'Registrar Dashboard';
        
        // Get dashboard statistics
        $statistics = $this->getDashboardStatistics();
        
        // Get pending applications
        $pendingApplications = $this->getPendingApplications(5);
        
        // Get recent activities
        $recentActivities = $this->getRecentActivities($_SESSION['user_id'], 10);
        
        // Get notifications
        $notifications = $this->getNotifications($_SESSION['user_id'], 5);

        include BASE_PATH . '/resources/views/registrar/dashboard.php';
    }

    /**
     * View pending applications for review
     */
    public function pendingApplications()
    {
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'registrar') {
            header('Location: /login');
            exit;
        }

        $pageTitle = 'Pending Applications';
        
        // Get search and filter parameters
        $search = trim($_GET['search'] ?? '');
        $dateFilter = $_GET['date_filter'] ?? '';
        $page = max(1, intval($_GET['page'] ?? 1));
        $perPage = 10;
        $offset = ($page - 1) * $perPage;

        // Get pending applications with filters
        $applications = $this->getPendingApplicationsWithFilters($search, $dateFilter, $offset, $perPage);
        $totalCount = $this->countPendingApplications($search, $dateFilter);
        
        // Calculate pagination
        $totalPages = ceil($totalCount / $perPage);

        include BASE_PATH . '/resources/views/registrar/pending.php';
    }

    /**
     * Review specific application
     */
    public function reviewApplication($id)
    {
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'registrar') {
            header('Location: /login');
            exit;
        }

        $application = $this->getApplicationById($id);
        
        if (!$application) {
            $_SESSION['error'] = 'Application not found.';
            header('Location: /registrar/pending');
            exit;
        }

        $pageTitle = 'Review Application';
        $documents = $this->getApplicationDocuments($id);
        $history = $this->getApplicationHistory($id);

        include BASE_PATH . '/resources/views/registrar/review.php';
    }

    /**
     * Process application approval/rejection
     */
    public function processApplication()
    {
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'registrar') {
            http_response_code(403);
            echo json_encode(['error' => 'Unauthorized']);
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            return;
        }

        $applicationId = intval($_POST['application_id'] ?? 0);
        $action = $_POST['action'] ?? '';
        $comments = trim($_POST['comments'] ?? '');

        if (!$applicationId || !in_array($action, ['approve', 'reject'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid request']);
            return;
        }

        try {
            if ($action === 'approve') {
                $result = $this->approveApplication($applicationId, $_SESSION['user_id'], $comments);
            } else {
                $result = $this->rejectApplication($applicationId, $_SESSION['user_id'], $comments);
            }

            echo json_encode($result);
        } catch (Exception $e) {
            error_log("Error processing application: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['error' => 'Internal server error']);
        }
    }

    /**
     * Batch processing of applications
     */
    public function batchProcess()
    {
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'registrar') {
            http_response_code(403);
            echo json_encode(['error' => 'Unauthorized']);
            return;
        }

        // Handle both GET and POST requests
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            // For GET requests, return the batch processing form/interface
            $pageTitle = 'Batch Process Applications';
            
            // Get pending applications for processing
            $applications = $this->getPendingApplications(50);
            
            include BASE_PATH . '/resources/views/registrar/batch-process.php';
            return;
        } else if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            return;
        }

        // Process POST request for batch processing
        $applicationIds = $_POST['application_ids'] ?? [];
        $action = $_POST['action'] ?? '';
        $comments = trim($_POST['comments'] ?? '');

        if (empty($applicationIds) || !in_array($action, ['approve', 'reject'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid request']);
            return;
        }

        try {
            $results = [];
            $successCount = 0;
            $errorCount = 0;

            foreach ($applicationIds as $applicationId) {
                try {
                    if ($action === 'approve') {
                        $result = $this->approveApplication($applicationId, $_SESSION['user_id'], $comments);
                    } else {
                        $result = $this->rejectApplication($applicationId, $_SESSION['user_id'], $comments);
                    }
                    
                    if ($result['success']) {
                        $successCount++;
                    } else {
                        $errorCount++;
                    }
                    
                    $results[] = $result;
                } catch (Exception $e) {
                    $errorCount++;
                    $results[] = ['success' => false, 'message' => $e->getMessage()];
                }
            }

            echo json_encode([
                'success' => true,
                'message' => "Batch processing completed. {$successCount} successful, {$errorCount} errors.",
                'results' => $results
            ]);
        } catch (Exception $e) {
            error_log("Error in batch processing: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['error' => 'Internal server error']);
        }
    }

    /**
     * Generate reports
     */
    public function reports()
    {
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'registrar') {
            header('Location: /login');
            exit;
        }

        $pageTitle = 'Registrar Reports';
        
        $reportType = $_GET['type'] ?? 'monthly';
        $date = $_GET['date'] ?? date('Y-m-d');
        $startDate = $_GET['start_date'] ?? date('Y-m-01');
        $endDate = $_GET['end_date'] ?? date('Y-m-t');

        // Handle specific report types with date parameters
        if ($reportType === 'daily' && !empty($date)) {
            $startDate = $date;
            $endDate = $date;
        } else if ($reportType === 'weekly' && !empty($date)) {
            // Calculate the start and end of the week containing the given date
            $dayOfWeek = date('N', strtotime($date));
            $startDate = date('Y-m-d', strtotime("-" . ($dayOfWeek - 1) . " days", strtotime($date)));
            $endDate = date('Y-m-d', strtotime("+" . (7 - $dayOfWeek) . " days", strtotime($date)));
        }

        $reportData = $this->generateReport($reportType, $startDate, $endDate);
        $reportChartData = $this->prepareChartData($reportData, $reportType);

        include BASE_PATH . '/resources/views/registrar/reports.php';
    }
    
    /**
     * Prepare chart data for reports
     */
    private function prepareChartData($reportData, $reportType)
    {
        $chartData = [
            'labels' => [],
            'datasets' => [
                [
                    'label' => 'Total',
                    'data' => [],
                    'backgroundColor' => '#36a2eb'
                ],
                [
                    'label' => 'Approved',
                    'data' => [],
                    'backgroundColor' => '#4bc0c0'
                ],
                [
                    'label' => 'Rejected',
                    'data' => [],
                    'backgroundColor' => '#ff6384'
                ],
                [
                    'label' => 'Pending',
                    'data' => [],
                    'backgroundColor' => '#ffcd56'
                ]
            ]
        ];
        
        foreach ($reportData as $item) {
            if ($reportType === 'performance') {
                $chartData['labels'][] = $item['first_name'] . ' ' . $item['last_name'];
                $chartData['datasets'][0]['data'][] = $item['total_reviewed'];
                $chartData['datasets'][1]['data'][] = $item['approved'];
                $chartData['datasets'][2]['data'][] = $item['rejected'];
                $chartData['datasets'][3]['data'][] = 0; // No pending for performance report
            } else {
                $chartData['labels'][] = $item['date'];
                $chartData['datasets'][0]['data'][] = $item['total'];
                $chartData['datasets'][1]['data'][] = $item['approved'];
                $chartData['datasets'][2]['data'][] = $item['rejected'];
                $chartData['datasets'][3]['data'][] = $item['pending'];
            }
        }
        
        return json_encode($chartData);
    }

    /**
     * View approved applications
     */
    public function approved()
    {
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'registrar') {
            header('Location: /login');
            exit;
        }

        $pageTitle = 'Approved Applications';
        
        // Get search and filter parameters
        $search = trim($_GET['search'] ?? '');
        $dateFilter = $_GET['date_filter'] ?? '';
        $page = max(1, intval($_GET['page'] ?? 1));
        $perPage = 10;
        $offset = ($page - 1) * $perPage;

        // Get approved applications with filters
        $applications = $this->getApprovedApplicationsWithFilters($search, $dateFilter, $offset, $perPage);
        $totalCount = $this->countApprovedApplications($search, $dateFilter);
        
        // Calculate pagination
        $totalPages = ceil($totalCount / $perPage);

        include BASE_PATH . '/resources/views/registrar/approved.php';
    }

    /**
     * Get dashboard statistics
     */
    private function getDashboardStatistics()
    {
        try {
            $stats = [];
            
            // Total applications
            $stmt = $this->db->query("SELECT COUNT(*) FROM birth_applications");
            $stats['total_applications'] = $stmt->fetchColumn();
            
            // Pending applications
            $stmt = $this->db->query("SELECT COUNT(*) FROM birth_applications WHERE status IN ('submitted', 'under_review')");
            $stats['pending_applications'] = $stmt->fetchColumn();
            
            // Approved today
            $stmt = $this->db->query("SELECT COUNT(*) FROM birth_applications WHERE status = 'approved' AND DATE(updated_at) = CURDATE()");
            $stats['approved_today'] = $stmt->fetchColumn();
            
            // Rejected today
            $stmt = $this->db->query("SELECT COUNT(*) FROM birth_applications WHERE status = 'rejected' AND DATE(updated_at) = CURDATE()");
            $stats['rejected_today'] = $stmt->fetchColumn();
            
            // My approvals this month
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM birth_applications WHERE reviewed_by = ? AND status = 'approved' AND MONTH(updated_at) = MONTH(CURDATE()) AND YEAR(updated_at) = YEAR(CURDATE())");
            $stmt->execute([$_SESSION['user_id']]);
            $stats['my_approvals_month'] = $stmt->fetchColumn();
            
            // Average processing time
            $stmt = $this->db->query("SELECT AVG(TIMESTAMPDIFF(HOUR, submitted_at, reviewed_at)) FROM birth_applications WHERE reviewed_at IS NOT NULL");
            $stats['avg_processing_time'] = round($stmt->fetchColumn() ?? 0, 1);

            return $stats;
        } catch (Exception $e) {
            error_log("Error getting dashboard statistics: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get pending applications
     */
    private function getPendingApplications($limit = 10)
    {
        try {
            $stmt = $this->db->prepare("
                SELECT ba.*, 
                       CONCAT(ba.child_first_name, ' ', ba.child_last_name) as child_name,
                       u.email as applicant_email,
                       u.first_name as applicant_first_name,
                       u.last_name as applicant_last_name,
                       DATEDIFF(NOW(), ba.submitted_at) as days_pending
                FROM birth_applications ba
                JOIN users u ON ba.user_id = u.id
                WHERE ba.status IN ('submitted', 'under_review')
                ORDER BY ba.submitted_at ASC
                LIMIT ?
            ");
            $stmt->execute([$limit]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error getting pending applications: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get pending applications with filters
     */
    private function getPendingApplicationsWithFilters($search, $dateFilter, $offset, $limit)
    {
        try {
            $whereConditions = ["ba.status IN ('submitted', 'under_review')"];
            $params = [];

            if (!empty($search)) {
                $whereConditions[] = "(ba.child_first_name LIKE ? OR ba.child_last_name LIKE ? OR ba.application_number LIKE ? OR u.email LIKE ?)";
                $searchTerm = "%{$search}%";
                $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm, $searchTerm]);
            }

            if (!empty($dateFilter)) {
                switch ($dateFilter) {
                    case 'today':
                        $whereConditions[] = "DATE(ba.submitted_at) = CURDATE()";
                        break;
                    case 'week':
                        $whereConditions[] = "ba.submitted_at >= DATE_SUB(NOW(), INTERVAL 1 WEEK)";
                        break;
                    case 'month':
                        $whereConditions[] = "ba.submitted_at >= DATE_SUB(NOW(), INTERVAL 1 MONTH)";
                        break;
                }
            }

            $whereClause = implode(' AND ', $whereConditions);
            $params[] = $limit;
            $params[] = $offset;

            $stmt = $this->db->prepare("
                SELECT ba.*, 
                       CONCAT(ba.child_first_name, ' ', ba.child_last_name) as child_name,
                       u.email as applicant_email,
                       u.first_name as applicant_first_name,
                       u.last_name as applicant_last_name,
                       DATEDIFF(NOW(), ba.submitted_at) as days_pending
                FROM birth_applications ba
                JOIN users u ON ba.user_id = u.id
                WHERE {$whereClause}
                ORDER BY ba.submitted_at ASC
                LIMIT ? OFFSET ?
            ");
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error getting filtered applications: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Count pending applications
     */
    private function countPendingApplications($search, $dateFilter)
    {
        try {
            $whereConditions = ["ba.status IN ('submitted', 'under_review')"];
            $params = [];

            if (!empty($search)) {
                $whereConditions[] = "(ba.child_first_name LIKE ? OR ba.child_last_name LIKE ? OR ba.application_number LIKE ? OR u.email LIKE ?)";
                $searchTerm = "%{$search}%";
                $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm, $searchTerm]);
            }

            if (!empty($dateFilter)) {
                switch ($dateFilter) {
                    case 'today':
                        $whereConditions[] = "DATE(ba.submitted_at) = CURDATE()";
                        break;
                    case 'week':
                        $whereConditions[] = "ba.submitted_at >= DATE_SUB(NOW(), INTERVAL 1 WEEK)";
                        break;
                    case 'month':
                        $whereConditions[] = "ba.submitted_at >= DATE_SUB(NOW(), INTERVAL 1 MONTH)";
                        break;
                }
            }

            $whereClause = implode(' AND ', $whereConditions);

            $stmt = $this->db->prepare("
                SELECT COUNT(*) 
                FROM birth_applications ba
                JOIN users u ON ba.user_id = u.id
                WHERE {$whereClause}
            ");
            $stmt->execute($params);
            return $stmt->fetchColumn();
        } catch (Exception $e) {
            error_log("Error counting applications: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Get approved applications with filters
     */
    private function getApprovedApplicationsWithFilters($search, $dateFilter, $offset, $limit)
    {
        try {
            $whereConditions = ["ba.status = 'approved'"];
            $params = [];

            if (!empty($search)) {
                $whereConditions[] = "(ba.child_first_name LIKE ? OR ba.child_last_name LIKE ? OR ba.application_number LIKE ? OR u.email LIKE ?)";
                $searchTerm = "%{$search}%";
                $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm, $searchTerm]);
            }

            if (!empty($dateFilter)) {
                switch ($dateFilter) {
                    case 'today':
                        $whereConditions[] = "DATE(ba.reviewed_at) = CURDATE()";
                        break;
                    case 'week':
                        $whereConditions[] = "ba.reviewed_at >= DATE_SUB(NOW(), INTERVAL 1 WEEK)";
                        break;
                    case 'month':
                        $whereConditions[] = "ba.reviewed_at >= DATE_SUB(NOW(), INTERVAL 1 MONTH)";
                        break;
                }
            }

            $whereClause = implode(' AND ', $whereConditions);
            $params[] = $limit;
            $params[] = $offset;

            $stmt = $this->db->prepare("
                SELECT ba.*, 
                       CONCAT(ba.child_first_name, ' ', ba.child_last_name) as child_name,
                       u.email as applicant_email,
                       u.first_name as applicant_first_name,
                       u.last_name as applicant_last_name,
                       DATEDIFF(NOW(), ba.reviewed_at) as days_since_approval,
                       c.certificate_number
                FROM birth_applications ba
                JOIN users u ON ba.user_id = u.id
                LEFT JOIN certificates c ON ba.id = c.application_id
                WHERE {$whereClause}
                ORDER BY ba.reviewed_at DESC
                LIMIT ? OFFSET ?
            ");
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error getting approved applications: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Count approved applications
     */
    private function countApprovedApplications($search, $dateFilter)
    {
        try {
            $whereConditions = ["ba.status = 'approved'"];
            $params = [];

            if (!empty($search)) {
                $whereConditions[] = "(ba.child_first_name LIKE ? OR ba.child_last_name LIKE ? OR ba.application_number LIKE ? OR u.email LIKE ?)";
                $searchTerm = "%{$search}%";
                $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm, $searchTerm]);
            }

            if (!empty($dateFilter)) {
                switch ($dateFilter) {
                    case 'today':
                        $whereConditions[] = "DATE(ba.reviewed_at) = CURDATE()";
                        break;
                    case 'week':
                        $whereConditions[] = "ba.reviewed_at >= DATE_SUB(NOW(), INTERVAL 1 WEEK)";
                        break;
                    case 'month':
                        $whereConditions[] = "ba.reviewed_at >= DATE_SUB(NOW(), INTERVAL 1 MONTH)";
                        break;
                }
            }

            $whereClause = implode(' AND ', $whereConditions);

            $stmt = $this->db->prepare("
                SELECT COUNT(*) 
                FROM birth_applications ba
                JOIN users u ON ba.user_id = u.id
                WHERE {$whereClause}
            ");
            $stmt->execute($params);
            return $stmt->fetchColumn();
        } catch (Exception $e) {
            error_log("Error counting approved applications: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Get application by ID
     */
    private function getApplicationById($id)
    {
        try {
            $stmt = $this->db->prepare("
                SELECT ba.*, 
                       CONCAT(ba.child_first_name, ' ', ba.child_last_name) as child_name,
                       u.email as applicant_email,
                       u.first_name as applicant_first_name,
                       u.last_name as applicant_last_name,
                       u.phone_number as applicant_phone,
                       DATEDIFF(NOW(), ba.submitted_at) as days_pending
                FROM birth_applications ba
                JOIN users u ON ba.user_id = u.id
                WHERE ba.id = ?
            ");
            $stmt->execute([$id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error getting application: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Approve application
     */
    private function approveApplication($applicationId, $reviewerId, $comments)
    {
        try {
            $this->db->beginTransaction();

            // Update application status
            $stmt = $this->db->prepare("
                UPDATE birth_applications 
                SET status = 'approved', 
                    reviewed_by = ?, 
                    reviewed_at = NOW(), 
                    review_notes = ?
                WHERE id = ?
            ");
            $stmt->execute([$reviewerId, $comments, $applicationId]);

            // Generate certificate
            $certificateNumber = $this->generateCertificateNumber();
            $qrCodeHash = $this->generateQRCodeHash($certificateNumber);
            
            $stmt = $this->db->prepare("
                INSERT INTO certificates (
                    certificate_number, application_id, qr_code_hash, 
                    issued_by, issued_at, status
                ) VALUES (?, ?, ?, ?, NOW(), 'active')
            ");
            $stmt->execute([$certificateNumber, $applicationId, $qrCodeHash, $reviewerId]);

            // Log activity
            $this->logActivity($reviewerId, 'approve_application', "Approved application ID: {$applicationId}");

            // Send notification
            $this->sendNotification($applicationId, 'approved');

            $this->db->commit();

            return [
                'success' => true,
                'message' => 'Application approved successfully',
                'certificate_number' => $certificateNumber
            ];
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Error approving application: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error approving application'];
        }
    }

    /**
     * Reject application
     */
    private function rejectApplication($applicationId, $reviewerId, $comments)
    {
        try {
            $this->db->beginTransaction();

            // Update application status
            $stmt = $this->db->prepare("
                UPDATE birth_applications 
                SET status = 'rejected', 
                    reviewed_by = ?, 
                    reviewed_at = NOW(), 
                    review_notes = ?
                WHERE id = ?
            ");
            $stmt->execute([$reviewerId, $comments, $applicationId]);

            // Log activity
            $this->logActivity($reviewerId, 'reject_application', "Rejected application ID: {$applicationId}");

            // Send notification
            $this->sendNotification($applicationId, 'rejected');

            $this->db->commit();

            return [
                'success' => true,
                'message' => 'Application rejected successfully'
            ];
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Error rejecting application: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error rejecting application'];
        }
    }

    /**
     * Generate certificate number
     */
    private function generateCertificateNumber()
    {
        $prefix = 'BC';
        $year = date('Y');
        $month = date('m');
        $random = strtoupper(substr(md5(uniqid()), 0, 6));
        return $prefix . $year . $month . $random;
    }

    /**
     * Generate QR code hash
     */
    private function generateQRCodeHash($certificateNumber)
    {
        return hash('sha256', $certificateNumber . time() . uniqid());
    }

    /**
     * Get recent activities
     */
    private function getRecentActivities($userId, $limit)
    {
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM activity_log 
                WHERE user_id = ? 
                ORDER BY created_at DESC 
                LIMIT ?
            ");
            $stmt->execute([$userId, $limit]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error getting recent activities: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get notifications
     */
    private function getNotifications($userId, $limit)
    {
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM notifications 
                WHERE user_id = ? 
                ORDER BY created_at DESC 
                LIMIT ?
            ");
            $stmt->execute([$userId, $limit]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error getting notifications: " . $e->getMessage());
            return [];
        }
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
     * Send notification
     */
    private function sendNotification($applicationId, $type)
    {
        try {
            // Get application user
            $stmt = $this->db->prepare("SELECT user_id FROM birth_applications WHERE id = ?");
            $stmt->execute([$applicationId]);
            $userId = $stmt->fetchColumn();

            if ($userId) {
                $messages = [
                    'approved' => 'Your birth certificate application has been approved!',
                    'rejected' => 'Your birth certificate application has been rejected. Please contact support for details.'
                ];

                $message = $messages[$type] ?? 'Application status updated';

                $stmt = $this->db->prepare("
                    INSERT INTO notifications (user_id, type, message, application_id, created_at)
                    VALUES (?, ?, ?, ?, NOW())
                ");
                $stmt->execute([$userId, $type, $message, $applicationId]);
            }
        } catch (Exception $e) {
            error_log("Error sending notification: " . $e->getMessage());
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
     * Get application documents
     */
    private function getApplicationDocuments($applicationId)
    {
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM application_documents 
                WHERE application_id = ? 
                ORDER BY uploaded_at DESC
            ");
            $stmt->execute([$applicationId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error getting application documents: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get application history
     */
    private function getApplicationHistory($applicationId)
    {
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM application_progress 
                WHERE application_id = ? 
                ORDER BY created_at DESC
            ");
            $stmt->execute([$applicationId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error getting application history: " . $e->getMessage());
            return [];
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
                case 'monthly':
                    $stmt = $this->db->prepare("
                        SELECT 
                            DATE(submitted_at) as date,
                            COUNT(*) as total,
                            SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved,
                            SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected,
                            SUM(CASE WHEN status IN ('submitted', 'under_review') THEN 1 ELSE 0 END) as pending
                        FROM birth_applications
                        WHERE submitted_at BETWEEN ? AND ?
                        GROUP BY DATE(submitted_at)
                        ORDER BY date
                    ");
                    $stmt->execute([$startDate, $endDate]);
                    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    break;
                    
                case 'performance':
                    $stmt = $this->db->prepare("
                        SELECT 
                            u.first_name,
                            u.last_name,
                            COUNT(*) as total_reviewed,
                            SUM(CASE WHEN ba.status = 'approved' THEN 1 ELSE 0 END) as approved,
                            SUM(CASE WHEN ba.status = 'rejected' THEN 1 ELSE 0 END) as rejected,
                            AVG(TIMESTAMPDIFF(HOUR, ba.submitted_at, ba.reviewed_at)) as avg_processing_hours
                        FROM birth_applications ba
                        JOIN users u ON ba.reviewed_by = u.id
                        WHERE ba.reviewed_at BETWEEN ? AND ?
                        GROUP BY ba.reviewed_by
                        ORDER BY total_reviewed DESC
                    ");
                    $stmt->execute([$startDate, $endDate]);
                    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    break;
            }
            
            return $data;
        } catch (Exception $e) {
            error_log("Error generating report: " . $e->getMessage());
            return [];
        }
    }
}
