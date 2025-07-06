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

            // Generate application number and tracking number
            $applicationNumber = $this->generateApplicationNumber();
            $trackingNumber = $this->generateTrackingNumber();

            // Prepare application data
            $applicationData = [
                'application_number' => $applicationNumber,
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
                'submitted_at' => date('Y-m-d H:i:s')
            ];

            // Insert application
            $sql = "INSERT INTO birth_applications (" . implode(', ', array_keys($applicationData)) . ") 
                    VALUES (" . str_repeat('?,', count($applicationData) - 1) . "?)";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute(array_values($applicationData));
            
            $applicationId = $this->db->lastInsertId();

            // Create tracking record
            $this->createTrackingRecord($applicationId, $trackingNumber);

            // Create initial progress entry
            $this->createProgressEntry($applicationId, 'submitted', 'Application submitted successfully');

            // Send notification (if notification system exists)
            $this->sendApplicationNotification($applicationId, 'submitted');

            return [
                'success' => true,
                'message' => "Application submitted successfully! Your tracking number is: {$trackingNumber}",
                'application_id' => $applicationId,
                'tracking_number' => $trackingNumber
            ];

        } catch (Exception $e) {
            error_log("Application submission error: " . $e->getMessage());
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
     * Generate unique tracking number
     */
    private function generateTrackingNumber()
    {
        $prefix = 'TRK';
        $timestamp = time();
        $random = strtoupper(substr(md5(uniqid()), 0, 6));
        return $prefix . $timestamp . $random;
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
