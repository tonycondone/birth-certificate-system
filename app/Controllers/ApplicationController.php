<?php

namespace App\Controllers;

use App\Database\Database;
use Exception;
use PDO;

/**
 * ApplicationController
 * 
 * Handles birth certificate application creation, management, and tracking
 */
class ApplicationController
{
    private $db;

    public function __construct()
    {
        try {
            $this->db = Database::getConnection();
        } catch (Exception $e) {
            error_log("ApplicationController initialization error: " . $e->getMessage());
            $this->db = null;
        }
    }

    /**
     * Display application creation form
     */
    public function create()
    {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }

        $pageTitle = 'New Birth Certificate Application';
        $success = null;
        $error = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $result = $this->processApplication();
            if ($result['success']) {
                $success = $result['message'];
                $applicationId = $result['application_id'];
                $trackingNumber = $result['tracking_number'];
            } else {
                $error = $result['message'];
            }
        }

        include BASE_PATH . '/resources/views/applications/create.php';
    }

    /**
     * Process new application submission
     */
    private function processApplication()
    {
        try {
            // Validate required fields
            $requiredFields = [
                'child_first_name', 'child_last_name', 'date_of_birth', 'gender',
                'place_of_birth', 'mother_first_name', 'mother_last_name'
            ];

            foreach ($requiredFields as $field) {
                if (empty(trim($_POST[$field] ?? ''))) {
                    return ['success' => false, 'message' => 'Please fill in all required fields.'];
                }
            }

            // Generate application number
            $applicationNumber = $this->generateApplicationNumber();

            // Prepare application data
            $applicationData = [
                'application_number' => $applicationNumber,
                // reference_number omitted for compatibility; DB trigger or later process may populate it
                'user_id' => $_SESSION['user_id'],
                'child_first_name' => trim($_POST['child_first_name']),
                'child_last_name' => trim($_POST['child_last_name']),
                'child_middle_name' => trim($_POST['child_middle_name'] ?? ''),
                'date_of_birth' => $_POST['date_of_birth'],
                'time_of_birth' => $_POST['time_of_birth'] ?? null,
                'place_of_birth' => trim($_POST['place_of_birth']),
                'gender' => $_POST['gender'],
                'weight_at_birth' => $_POST['weight_at_birth'] ?? null,
                'length_at_birth' => $_POST['length_at_birth'] ?? null,
                'father_first_name' => trim($_POST['father_first_name'] ?? ''),
                'father_last_name' => trim($_POST['father_last_name'] ?? ''),
                'father_national_id' => trim($_POST['father_national_id'] ?? ''),
                'father_phone' => trim($_POST['father_phone'] ?? ''),
                'father_email' => trim($_POST['father_email'] ?? ''),
                'mother_first_name' => trim($_POST['mother_first_name']),
                'mother_last_name' => trim($_POST['mother_last_name']),
                'mother_national_id' => trim($_POST['mother_national_id'] ?? ''),
                'mother_phone' => trim($_POST['mother_phone'] ?? ''),
                'mother_email' => trim($_POST['mother_email'] ?? ''),
                'hospital_name' => trim($_POST['hospital_name'] ?? ''),
                'attending_physician' => trim($_POST['attending_physician'] ?? ''),
                'physician_license' => trim($_POST['physician_license'] ?? ''),
                'status' => 'submitted',
                'created_at' => date('Y-m-d H:i:s')
            ];

            // Insert application
            $sql = "INSERT INTO birth_applications (" . implode(', ', array_keys($applicationData)) . ") 
                    VALUES (" . str_repeat('?,', count($applicationData) - 1) . "?)";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute(array_values($applicationData));
            
            $applicationId = $this->db->lastInsertId();

            // Create initial progress entry
            $this->createProgressEntry($applicationId, 'submitted', 'Application submitted, proceed to payment');

            // Redirect to payment page instead of submitting directly
            header("Location: /applications/{$applicationId}/pay");
            exit;

        } catch (Exception $e) {
            error_log("Application submission error: " . $e->getMessage());
            if ($this->db) {
                $errorInfo = $this->db->errorInfo();
                error_log("PDO Error Info: " . print_r($errorInfo, true));
            }
            return ['success' => false, 'message' => 'Error submitting application. Please try again.'];
        }

    }

    /**
     * Display user's applications
     */
    public function index()
    {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }

        $pageTitle = 'My Applications';
        $applications = $this->getUserApplications($_SESSION['user_id']);

        include BASE_PATH . '/resources/views/applications/index.php';
    }

    /**
     * Show specific application details
     */
    public function show($id)
    {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }

        // Generate CSRF token if not exists
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        $application = $this->getApplicationById($id);
        
        if (!$application) {
            $_SESSION['error'] = 'Application not found.';
            header('Location: /applications');
            exit;
        }

        // Check if user owns this application or is admin/registrar
        if ($application['user_id'] != $_SESSION['user_id'] && 
            !in_array($_SESSION['role'], ['admin', 'registrar'])) {
            $_SESSION['error'] = 'Access denied.';
            header('Location: /applications');
            exit;
        }

        $pageTitle = 'Application Details';
        $progress = $this->getApplicationProgress($id);
        $documents = $this->getApplicationDocuments($id);

        // Determine whether user should see Pay Now (no completed payment yet)
        $canPay = false;
        try {
            $stmt = $this->db->prepare("SELECT status FROM payments WHERE application_id = ? ORDER BY id DESC LIMIT 1");
            $stmt->execute([$id]);
            $lastPayment = $stmt->fetch();
            $canPay = !$lastPayment || strtolower($lastPayment['status'] ?? '') !== 'completed';
        } catch (\Exception $e) {
            // If payments table not available, allow Pay Now to be shown for submitted/pending
            $canPay = in_array($application['status'], ['submitted','pending','processing']);
        }

        include BASE_PATH . '/resources/views/applications/show.php';
    }

    /**
     * Track application by tracking number
     */
    public function track()
    {
        $pageTitle = 'Track Application';
        $trackingNumber = trim($_GET['tracking_number'] ?? '');
        $application = null;
        $progress = [];
        $error = null;

        if ($trackingNumber) {
            $application = $this->getApplicationByTrackingNumber($trackingNumber);
            if ($application) {
                $progress = $this->getApplicationProgress($application['id']);
                // Simulate fake progress for demonstration
                $progress = $this->simulateProgress($application, $progress);
            } else {
                $error = 'Application not found. Please check your tracking number.';
            }
        }

        include BASE_PATH . '/resources/views/applications/track.php';
    }

    /**
     * Delete application
     */
    public function delete($id)
    {
        error_log("ApplicationController::delete called with ID: $id");
        
        // Set proper headers early
        header('Content-Type: application/json');
        
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                error_log("Wrong request method: " . $_SERVER['REQUEST_METHOD']);
                http_response_code(405);
                echo json_encode(['success' => false, 'error' => 'Method not allowed']);
                return;
            }

            if (!isset($_SESSION['user_id'])) {
                error_log("No user_id in session");
                http_response_code(401);
                echo json_encode(['success' => false, 'error' => 'Unauthorized']);
                return;
            }

            // Check if database connection exists
            if (!$this->db) {
                error_log("Database connection not available");
                // Clean any output buffer before sending response
                if (ob_get_level()) {
                    ob_clean();
                }
                http_response_code(500);
                echo json_encode(['success' => false, 'error' => 'Database connection failed']);
                return;
            }

            $application = $this->getApplicationById($id);
            if (!$application) {
                error_log("Application $id not found");
                // Clean any output buffer before sending response
                if (ob_get_level()) {
                    ob_clean();
                }
                http_response_code(404);
                echo json_encode(['success' => false, 'error' => 'Application not found']);
                return;
            }

            error_log("Application found: " . json_encode($application));

            $isOwner = $application['user_id'] == $_SESSION['user_id'];
            $isStaff = in_array($_SESSION['role'] ?? '', ['admin', 'registrar']);
            if (!$isOwner && !$isStaff) {
                error_log("Access denied - User {$_SESSION['user_id']} not owner ({$application['user_id']}) or staff");
                // Clean any output buffer before sending response
                if (ob_get_level()) {
                    ob_clean();
                }
                http_response_code(403);
                echo json_encode(['success' => false, 'error' => 'You do not have permission to delete this application']);
                return;
            }

            try {
                // Block deletion if a completed payment exists
                $stmt = $this->db->prepare("SELECT COUNT(*) FROM payments WHERE application_id = ? AND LOWER(status) = 'completed'");
                $stmt->execute([$id]);
                $hasCompletedPayment = (int)$stmt->fetchColumn() > 0;
                if ($hasCompletedPayment) {
                    error_log("Cannot delete - completed payment exists");
                    http_response_code(409);
                    echo json_encode(['success' => false, 'error' => 'Cannot delete. Payment already completed.']);
                    return;
                }
            } catch (\Exception $e) {
                error_log("Payment check failed: " . $e->getMessage());
                // If payments table missing, fall back to status rule: allow only pending/submitted
            }

            $allowedStatuses = ['pending','submitted','processing','rejected'];
            if (!in_array(strtolower($application['status']), $allowedStatuses)) {
                error_log("Status '{$application['status']}' not allowed for deletion");
                http_response_code(409);
                echo json_encode(['success' => false, 'error' => 'Only pending, in-progress, or rejected applications can be deleted.']);
                return;
            }

            error_log("Starting deletion process for application $id");

            $this->db->beginTransaction();
            
            try {
                // Delete related documents
                $stmt = $this->db->prepare("DELETE FROM application_documents WHERE application_id = ?");
                $stmt->execute([$id]);
                // Delete progress
                $stmt = $this->db->prepare("DELETE FROM application_progress WHERE application_id = ?");
                $stmt->execute([$id]);
                // Delete tracking
                $stmt = $this->db->prepare("DELETE FROM application_tracking WHERE application_id = ?");
                $stmt->execute([$id]);
                // Delete pending payments (if any)
                try {
                    $stmt = $this->db->prepare("DELETE FROM payments WHERE application_id = ? AND LOWER(status) <> 'completed'");
                    $stmt->execute([$id]);
                } catch (\Exception $e) {
                    error_log("Failed to delete payments: " . $e->getMessage());
                    // ignore if table missing
                }
                // Delete application
                $stmt = $this->db->prepare("DELETE FROM birth_applications WHERE id = ?");
                $stmt->execute([$id]);
                
                $this->db->commit();

                error_log("Application $id deleted successfully");

                // Always return JSON for AJAX requests
                echo json_encode(['success' => true, 'message' => 'Application deleted successfully']);
                
            } catch (\Exception $e) {
                $this->db->rollBack();
                error_log('Transaction rollback error: ' . $e->getMessage());
                throw $e; // Re-throw to be caught by outer try-catch
            }
            
        } catch (\Exception $e) {
            error_log('Delete application error: ' . $e->getMessage());
            error_log('Error stack trace: ' . $e->getTraceAsString());
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Failed to delete application: ' . $e->getMessage()]);
        } catch (\Error $e) {
            error_log('Delete application fatal error: ' . $e->getMessage());
            error_log('Error stack trace: ' . $e->getTraceAsString());
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Internal server error']);
        }
    }

    /**
     * Get user's applications
     */
    private function getUserApplications($userId)
    {
        try {
            $stmt = $this->db->prepare("
                SELECT ba.*, 
                       CONCAT(ba.child_first_name, ' ', ba.child_last_name) as child_name,
                       t.tracking_number
                FROM birth_applications ba
                LEFT JOIN application_tracking t ON ba.id = t.application_id
                WHERE ba.user_id = ?
                ORDER BY ba.created_at DESC
            ");
            $stmt->execute([$userId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error getting user applications: " . $e->getMessage());
            return [];
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
                       t.tracking_number,
                       u.email as applicant_email,
                       u.first_name as applicant_first_name,
                       u.last_name as applicant_last_name
                FROM birth_applications ba
                LEFT JOIN application_tracking t ON ba.id = t.application_id
                LEFT JOIN users u ON ba.user_id = u.id
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
     * Get application by tracking number
     */
    private function getApplicationByTrackingNumber($trackingNumber)
    {
        try {
            $stmt = $this->db->prepare("
                SELECT ba.*, 
                       CONCAT(ba.child_first_name, ' ', ba.child_last_name) as child_name,
                       t.tracking_number
                FROM birth_applications ba
                JOIN application_tracking t ON ba.id = t.application_id
                WHERE t.tracking_number = ?
            ");
            $stmt->execute([$trackingNumber]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error getting application by tracking number: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Get application progress
     */
    private function getApplicationProgress($applicationId)
    {
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM application_progress 
                WHERE application_id = ? 
                ORDER BY created_at ASC
            ");
            $stmt->execute([$applicationId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error getting application progress: " . $e->getMessage());
            return [];
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
     * Simulate fake progress for demonstration
     */
    private function simulateProgress($application, $existingProgress)
    {
        $submittedDate = new \DateTime($application['submitted_at']);
        $now = new \DateTime();
        $daysSinceSubmission = $submittedDate->diff($now)->days;

        $fakeProgress = [
            [
                'status' => 'submitted',
                'description' => 'Application submitted and received',
                'created_at' => $application['submitted_at'],
                'completed' => true
            ]
        ];

        // Add fake progress based on days since submission
        if ($daysSinceSubmission >= 1) {
            $fakeProgress[] = [
                'status' => 'initial_review',
                'description' => 'Initial document review completed',
                'created_at' => $submittedDate->modify('+1 day')->format('Y-m-d H:i:s'),
                'completed' => true
            ];
        }

        if ($daysSinceSubmission >= 3) {
            $fakeProgress[] = [
                'status' => 'verification',
                'description' => 'Hospital verification in progress',
                'created_at' => $submittedDate->modify('+3 days')->format('Y-m-d H:i:s'),
                'completed' => true
            ];
        }

        if ($daysSinceSubmission >= 5) {
            $fakeProgress[] = [
                'status' => 'registrar_review',
                'description' => 'Under registrar review',
                'created_at' => $submittedDate->modify('+5 days')->format('Y-m-d H:i:s'),
                'completed' => $daysSinceSubmission >= 7
            ];
        }

        if ($daysSinceSubmission >= 7) {
            $status = $application['status'] === 'approved' ? 'approved' : 'pending_approval';
            $fakeProgress[] = [
                'status' => $status,
                'description' => $application['status'] === 'approved' ? 
                    'Application approved - Certificate being generated' : 
                    'Pending final approval',
                'created_at' => $submittedDate->modify('+7 days')->format('Y-m-d H:i:s'),
                'completed' => $application['status'] === 'approved'
            ];
        }

        return $fakeProgress;
    }

    /**
     * Create tracking record
     */
    private function createTrackingRecord($applicationId, $trackingNumber)
    {
        try {
            // Check if tracking table exists, if not create it
            $this->ensureTrackingTableExists();
            
            $stmt = $this->db->prepare("
                INSERT INTO application_tracking (application_id, tracking_number, created_at)
                VALUES (?, ?, NOW())
            ");
            $stmt->execute([$applicationId, $trackingNumber]);
        } catch (Exception $e) {
            error_log("Error creating tracking record: " . $e->getMessage());
        }
    }

    /**
     * Create progress entry
     */
    private function createProgressEntry($applicationId, $status, $description)
    {
        try {
            // Check if progress table exists, if not create it
            $this->ensureProgressTableExists();
            
            $stmt = $this->db->prepare("
                INSERT INTO application_progress (application_id, status, description, created_at)
                VALUES (?, ?, ?, NOW())
            ");
            $stmt->execute([$applicationId, $status, $description]);
        } catch (Exception $e) {
            error_log("Error creating progress entry: " . $e->getMessage());
        }
    }

    /**
     * Send application notification
     */
    private function sendApplicationNotification($applicationId, $status)
    {
        try {
            // Check if notifications table exists, if not create it
            $this->ensureNotificationsTableExists();
            
            $stmt = $this->db->prepare("
                SELECT user_id FROM birth_applications WHERE id = ?
            ");
            $stmt->execute([$applicationId]);
            $userId = $stmt->fetchColumn();

            if ($userId) {
                $message = $this->getNotificationMessage($status);
                $stmt = $this->db->prepare("
                    INSERT INTO notifications (user_id, type, message, application_id, created_at)
                    VALUES (?, ?, ?, ?, NOW())
                ");
                $stmt->execute([$userId, $status, $message, $applicationId]);
            }
        } catch (Exception $e) {
            error_log("Error sending notification: " . $e->getMessage());
        }
    }

    /**
     * Get notification message based on status
     */
    private function getNotificationMessage($status)
    {
        $messages = [
            'submitted' => 'Your birth certificate application has been submitted successfully.',
            'under_review' => 'Your application is now under review.',
            'approved' => 'Congratulations! Your application has been approved.',
            'rejected' => 'Your application has been rejected. Please contact support for details.',
            'certificate_issued' => 'Your birth certificate has been issued and is ready for download.'
        ];

        return $messages[$status] ?? 'Application status updated.';
    }

    /**
     * Generate unique application number
     */
    private function generateApplicationNumber()
    {
        $prefix = 'APP';
        $year = date('Y');
        $month = date('m');
        $random = str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
        return $prefix . $year . $month . $random;
    }

    /**
     * Generate unique reference number
     */
    private function generateReferenceNumber()
    {
        $prefix = 'REF';
        $year = date('Y');
        $month = date('m');
        $random = strtoupper(substr(md5(uniqid()), 0, 6));
        return $prefix . $year . $month . $random;
    }

    /**
     * Ensure tracking table exists
     */
    private function ensureTrackingTableExists()
    {
        try {
            $this->db->exec("
                CREATE TABLE IF NOT EXISTS application_tracking (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    application_id INT NOT NULL,
                    tracking_number VARCHAR(50) UNIQUE NOT NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (application_id) REFERENCES birth_applications(id) ON DELETE CASCADE,
                    INDEX idx_tracking_number (tracking_number)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");
        } catch (Exception $e) {
            error_log("Error creating tracking table: " . $e->getMessage());
        }
    }

    /**
     * Ensure progress table exists
     */
    private function ensureProgressTableExists()
    {
        try {
            $this->db->exec("
                CREATE TABLE IF NOT EXISTS application_progress (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    application_id INT NOT NULL,
                    status VARCHAR(50) NOT NULL,
                    description TEXT,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (application_id) REFERENCES birth_applications(id) ON DELETE CASCADE,
                    INDEX idx_application_id (application_id)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");
        } catch (Exception $e) {
            error_log("Error creating progress table: " . $e->getMessage());
        }
    }

    /**
     * Ensure notifications table exists
     */
    private function ensureNotificationsTableExists()
    {
        try {
            $this->db->exec("
                CREATE TABLE IF NOT EXISTS notifications (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    user_id INT NOT NULL,
                    type VARCHAR(50) NOT NULL,
                    message TEXT NOT NULL,
                    application_id INT NULL,
                    read_at TIMESTAMP NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                    FOREIGN KEY (application_id) REFERENCES birth_applications(id) ON DELETE SET NULL,
                    INDEX idx_user_id (user_id)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");
        } catch (Exception $e) {
            error_log("Error creating notifications table: " . $e->getMessage());
        }
    }

    /**
     * Ensure documents table exists
     */
    private function ensureDocumentsTableExists()
    {
        try {
            $this->db->exec("
                CREATE TABLE IF NOT EXISTS application_documents (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    application_id INT NOT NULL,
                    document_type VARCHAR(100) NOT NULL,
                    file_name VARCHAR(255) NOT NULL,
                    file_path VARCHAR(500) NOT NULL,
                    file_size INT NOT NULL,
                    mime_type VARCHAR(100) NOT NULL,
                    uploaded_by INT NOT NULL,
                    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (application_id) REFERENCES birth_applications(id) ON DELETE CASCADE,
                    FOREIGN KEY (uploaded_by) REFERENCES users(id) ON DELETE CASCADE,
                    INDEX idx_application_id (application_id)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");
        } catch (Exception $e) {
            error_log("Error creating documents table: " . $e->getMessage());
        }
    }
}
