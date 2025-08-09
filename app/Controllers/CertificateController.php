<?php

namespace App\Controllers;

use App\Database\Database;
use App\Models\Certificate;
use App\Models\BirthApplication;
use App\Models\User;
use App\Services\AuthService;
use App\Services\CertificateVerificationService;
use App\Middleware\AuthMiddleware;
use App\Middleware\RoleMiddleware;
use PDO;
use Exception;

/**
 * Class CertificateController
 *
 * Handles birth certificate verification, issuance, listing,
 * approval, and PDF download functionality.
 */
class CertificateController
{
    private $db;
    private $authService;
    private $verificationService;

    public function __construct($db = null, $authService = null, $verificationService = null) {
        // Initialize database connection - defer to when actually needed
        $this->db = $db;
        $this->authService = $authService;
        $this->verificationService = $verificationService;
    }

    /**
     * Display the certificate verification page
     * Accessible only to registrars and admins
     */
    public function showVerify() {
        // Get the current user
        $user = $this->authService->getCurrentUser();
        
        // Fetch pending applications for verification
        $pendingApplications = $this->getPendingApplications($user);

        // Render the verification view
        require_once __DIR__ . '/../Views/certificates/verify.php';
    }

    /**
     * Retrieve pending applications based on user role
     * 
     * @param User $user
     * @return array
     */
    private function getPendingApplications(User $user): array {
        $query = "
            SELECT 
                ba.id, 
                ba.user_id, 
                ba.child_first_name, 
                ba.child_last_name, 
                ba.status, 
                u.first_name AS parent_first_name, 
                u.last_name AS parent_last_name,
                ba.created_at
            FROM 
                birth_applications ba
            JOIN 
                users u ON ba.user_id = u.id
            WHERE 
                ba.status = 'pending'
            ORDER BY 
                ba.created_at ASC
        ";

        $stmt = $this->db->prepare($query);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Verify a specific certificate/application
     * 
     * @param int $applicationId
     */
    public function verifyCertificate(int $applicationId) {
        // Validate input
        if (!$applicationId) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid application ID']);
            exit;
        }

        try {
            // Get the current user
            $user = $this->authService->getCurrentUser();

            // Verify the application
            $verificationResult = $this->verificationService->verifyApplication(
                $applicationId, 
                $user->getId()
            );

            // Return verification result
            echo json_encode([
                'status' => 'success',
                'message' => 'Application verified successfully',
                'details' => $verificationResult
            ]);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Reject a certificate/application
     * 
     * @param int $applicationId
     * @param string $reason
     */
    public function rejectCertificate(int $applicationId, string $reason = '') {
        try {
            // Get the current user
            $user = $this->authService->getCurrentUser();

            // Reject the application
            $rejectionResult = $this->verificationService->rejectApplication(
                $applicationId, 
                $user->getId(),
                $reason
            );

            // Return rejection result
            echo json_encode([
                'status' => 'success',
                'message' => 'Application rejected successfully',
                'details' => $rejectionResult
            ]);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Validate a certificate's authenticity
     * 
     * @param string $certificateNumber
     */
    public function validateCertificate(string $certificateNumber) {
        try {
            // Validate certificate
            $validationResult = $this->verificationService->validateCertificate($certificateNumber);

            // Return validation result
            echo json_encode([
                'status' => 'success',
                'valid' => $validationResult['valid'],
                'details' => $validationResult['details']
            ]);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Display the certificate verification form.
     * Generates CSRF token and includes the verify view.
     *
     * @return void
     */
    public function verify($certificateId = null)
    {
        $pageTitle = 'Verify Birth Certificate';
        
        // Rate limiting for verification attempts
        if ($this->isRateLimited($_SERVER['REMOTE_ADDR'], 'verify')) {
            $error = "Too many verification attempts. Please wait 5 minutes before trying again.";
            include BASE_PATH . '/resources/views/verify.php';
            return;
        }
        
        if (!$certificateId) {
            $certificateId = trim($_GET['certificate_number'] ?? '');
        }
        
        // Validate certificate number format
        if (empty($certificateId)) {
            $error = "Certificate number is required";
            include BASE_PATH . '/resources/views/verify.php';
            return;
        }
        
        // Validate certificate number format (BC followed by 14 alphanumeric characters)
        if (!preg_match('/^BC[A-Z0-9]{14}$/', $certificateId)) {
            $error = "Invalid certificate number format. Please enter a valid certificate number (e.g., BC202508D7C911).";
            include BASE_PATH . '/resources/views/verify.php';
            return;
        }
        
        try {
            $pdo = Database::getConnection();
            
            // Log verification attempt
            $this->logVerificationAttempt($_SERVER['REMOTE_ADDR'], $certificateId);
            
            // Query for certificate with comprehensive details
            $stmt = $pdo->prepare("
                SELECT 
                    c.certificate_number,
                    c.issued_at,
                    c.status,
                    c.blockchain_hash,
                    c.verification_count,
                    c.last_verified_at,
                    ba.child_first_name,
                    ba.child_last_name,
                    ba.child_middle_name,
                    ba.date_of_birth,
                    ba.place_of_birth,
                    ba.gender,
                    ba.father_first_name,
                    ba.father_last_name,
                    ba.mother_first_name,
                    ba.mother_last_name,
                    COALESCE(CONCAT(u.first_name, ' ', u.last_name), 'System') as registrar_name,
                    COALESCE(u.email, 'system@birthcerts.gov') as registrar_email,
                    ba.hospital_name,
                    '' as hospital_address
                FROM certificates c
                JOIN birth_applications ba ON c.application_id = ba.id
                LEFT JOIN users u ON c.issued_by = u.id
                WHERE c.certificate_number = ? AND c.status = 'active'
            ");
            $stmt->execute([$certificateId]);
            $certificate = $stmt->fetch();
            
            if ($certificate) {
                // Update verification count and timestamp
                $stmt = $pdo->prepare("
                    UPDATE certificates 
                    SET verification_count = verification_count + 1, 
                        last_verified_at = NOW() 
                    WHERE certificate_number = ?
                ");
                $stmt->execute([$certificateId]);
                
                // Format certificate data for display
                $certificate['is_valid'] = true;
                $certificate['number'] = $certificate['certificate_number'];
                $certificate['issue_date'] = $certificate['issued_at'];
                $certificate['child_name'] = trim($certificate['child_first_name'] . ' ' . 
                                                 ($certificate['child_middle_name'] ? $certificate['child_middle_name'] . ' ' : '') . 
                                                 $certificate['child_last_name']);
                $certificate['father_name'] = trim($certificate['father_first_name'] . ' ' . $certificate['father_last_name']);
                $certificate['mother_name'] = trim($certificate['mother_first_name'] . ' ' . $certificate['mother_last_name']);
                $certificate['registrar'] = $certificate['registrar_name'];
                $certificate['message'] = "Certificate is valid and verified";
                $certificate['verification_count'] = ($certificate['verification_count'] ?? 0) + 1;
                
                // Generate QR code data
                $certificate['qr_data'] = json_encode([
                    'certificate_number' => $certificate['certificate_number'],
                    'child_name' => $certificate['child_name'],
                    'date_of_birth' => $certificate['date_of_birth'],
                    'verification_url' => 'https://' . $_SERVER['HTTP_HOST'] . '/verify/' . $certificate['certificate_number']
                ]);
                
                // Log successful verification
                $this->logSuccessfulVerification($certificateId, $_SERVER['REMOTE_ADDR']);
                
            } else {
                // Check if certificate exists but is invalid
                $stmt = $pdo->prepare("
                    SELECT certificate_number, status, created_at, updated_at 
                    FROM certificates 
                    WHERE certificate_number = ?
                ");
                $stmt->execute([$certificateId]);
                $invalidCert = $stmt->fetch();
                
                if ($invalidCert) {
                    $certificate = [
                        'is_valid' => false,
                        'number' => $certificateId,
                        'status' => $invalidCert['status'],
                        'message' => "Certificate found but status is: " . ucfirst($invalidCert['status']),
                        'details' => [
                            'created_at' => $invalidCert['created_at'],
                            'updated_at' => $invalidCert['updated_at']
                        ]
                    ];
                } else {
                    $certificate = [
                        'is_valid' => false,
                        'number' => $certificateId,
                        'message' => "Certificate not found in our database. Please verify the certificate number and try again."
                    ];
                }
                
                // Log failed verification
                $this->logFailedVerification($certificateId, $_SERVER['REMOTE_ADDR']);
            }
            
        } catch (\Exception $e) {
            error_log("Certificate verification error: " . $e->getMessage());
            $certificate = [
                'is_valid' => false,
                'number' => $certificateId,
                'message' => "Error verifying certificate. Please try again later or contact support if the problem persists."
            ];
        }
        
        include BASE_PATH . '/resources/views/verify.php';
    }
    
    /**
     * Alternative verification method for requests with query parameter.
     * Returns JSON-like structure for API usage.
     *
     * @return void
     */
    public function verifyFromRequest() 
    {
        $certificate = null;
        
        if (isset($_GET['certificate_number'])) {
            $pdo = Database::getConnection();
            $certNumber = trim($_GET['certificate_number']);
            
            try {
                $stmt = $pdo->prepare('
                    SELECT c.*, 
                           CONCAT(ba.child_first_name, " ", COALESCE(ba.child_middle_name, ""), " ", ba.child_last_name) as child_name,
                           ba.date_of_birth, 
                           ba.gender, 
                           ba.place_of_birth,
                           CONCAT(ba.mother_first_name, " ", ba.mother_last_name) as mother_name,
                           CONCAT(ba.father_first_name, " ", ba.father_last_name) as father_name
                    FROM certificates c 
                    JOIN birth_applications ba ON c.application_id = ba.id 
                    WHERE c.certificate_number = ? AND c.status = "active"
                ');
                $stmt->execute([$certNumber]);
                $certificate = $stmt->fetch();
                
                if ($certificate) {
                    $certificate['is_valid'] = true;
                } else {
                    $certificate = ['is_valid' => false, 'message' => 'Certificate not found'];
                }
            } catch (Exception $e) {
                $certificate = ['is_valid' => false, 'message' => 'Error verifying certificate'];
                error_log("Verification error: " . $e->getMessage());
            }
        }
        
        include BASE_PATH . '/resources/views/verify.php';
    }
    
    /**
     * API endpoint for certificate verification
     */
    public function apiVerify()
    {
        header('Content-Type: application/json');
        
        try {
            $input = json_decode(file_get_contents('php://input'), true);
            $certificateNumber = trim($input['certificate_number'] ?? '');
            
            if (empty($certificateNumber)) {
                http_response_code(400);
                echo json_encode(['error' => 'Certificate number is required']);
                return;
            }
            
            if (!preg_match('/^[A-Z0-9]{12}$/', $certificateNumber)) {
                http_response_code(400);
                echo json_encode(['error' => 'Invalid certificate number format']);
                return;
            }
            
            $pdo = Database::getConnection();
            
            $stmt = $pdo->prepare("
                SELECT 
                    c.certificate_number,
                    c.issued_at,
                    c.status,
                    c.blockchain_hash,
                    ba.child_first_name,
                    ba.child_last_name,
                    ba.child_middle_name,
                    ba.date_of_birth,
                    ba.place_of_birth,
                    ba.gender,
                    CONCAT(u.first_name, ' ', u.last_name) as registrar_name
                FROM certificates c
                JOIN birth_applications ba ON c.application_id = ba.id
                JOIN users u ON c.issued_by = u.id
                WHERE c.certificate_number = ? AND c.status = 'active'
            ");
            $stmt->execute([$certificateNumber]);
            $certificate = $stmt->fetch();
            
            if ($certificate) {
                echo json_encode([
                    'valid' => true,
                    'certificate' => [
                        'number' => $certificate['certificate_number'],
                        'child_name' => trim($certificate['child_first_name'] . ' ' . 
                                           ($certificate['child_middle_name'] ? $certificate['child_middle_name'] . ' ' : '') . 
                                           $certificate['child_last_name']),
                        'date_of_birth' => $certificate['date_of_birth'],
                        'place_of_birth' => $certificate['place_of_birth'],
                        'gender' => $certificate['gender'],
                        'issue_date' => $certificate['issued_at'],
                        'registrar' => $certificate['registrar_name'],
                        'blockchain_hash' => $certificate['blockchain_hash']
                    ]
                ]);
            } else {
                echo json_encode([
                    'valid' => false,
                    'message' => 'Certificate not found or invalid'
                ]);
            }
            
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Internal server error']);
        }
    }
    
    /**
     * Check if IP is rate limited for verification
     */
    private function isRateLimited($ip, $action)
    {
        try {
            $pdo = Database::getConnection();
            $stmt = $pdo->prepare("
                SELECT COUNT(*) FROM verification_attempts 
                WHERE ip_address = ? AND action = ? AND created_at > DATE_SUB(NOW(), INTERVAL 5 MINUTE)
            ");
            $stmt->execute([$ip, $action]);
            return $stmt->fetchColumn() >= 10; // Allow 10 attempts per 5 minutes
        } catch (Exception $e) {
            return false; // Don't block if we can't check
        }
    }
    
    /**
     * Log verification attempt
     */
    private function logVerificationAttempt($ip, $certificateNumber)
    {
        try {
            $pdo = Database::getConnection();
            
            // Create verification_attempts table if it doesn't exist
            $pdo->exec("
                CREATE TABLE IF NOT EXISTS verification_attempts (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    ip_address VARCHAR(45) NOT NULL,
                    certificate_number VARCHAR(50) NOT NULL,
                    action VARCHAR(20) NOT NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    INDEX idx_ip (ip_address),
                    INDEX idx_cert (certificate_number)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
            ");
            
            $stmt = $pdo->prepare("
                INSERT INTO verification_attempts (ip_address, certificate_number, action, created_at) 
                VALUES (?, ?, 'verify', NOW())
            ");
            $stmt->execute([$ip, $certificateNumber]);
        } catch (Exception $e) {
            error_log("Failed to log verification attempt: " . $e->getMessage());
        }
    }
    
    /**
     * Log successful verification
     */
    private function logSuccessfulVerification($certificateNumber, $ip)
    {
        try {
            $pdo = Database::getConnection();
            
            // Create verification_log table if it doesn't exist
            $pdo->exec("
                CREATE TABLE IF NOT EXISTS verification_log (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    certificate_number VARCHAR(50) NOT NULL,
                    ip_address VARCHAR(45) NOT NULL,
                    status VARCHAR(20) NOT NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    INDEX idx_cert (certificate_number),
                    INDEX idx_status (status)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
            ");
            
            $stmt = $pdo->prepare("
                INSERT INTO verification_log (certificate_number, ip_address, status, created_at) 
                VALUES (?, ?, 'success', NOW())
            ");
            $stmt->execute([$certificateNumber, $ip]);
        } catch (Exception $e) {
            error_log("Failed to log successful verification: " . $e->getMessage());
        }
    }
    
    /**
     * Log failed verification
     */
    private function logFailedVerification($certificateNumber, $ip)
    {
        try {
            $pdo = Database::getConnection();
            
            // Create verification_log table if it doesn't exist
            $pdo->exec("
                CREATE TABLE IF NOT EXISTS verification_log (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    certificate_number VARCHAR(50) NOT NULL,
                    ip_address VARCHAR(45) NOT NULL,
                    status VARCHAR(20) NOT NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    INDEX idx_cert (certificate_number),
                    INDEX idx_status (status)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
            ");
            
            $stmt = $pdo->prepare("
                INSERT INTO verification_log (certificate_number, ip_address, status, created_at) 
                VALUES (?, ?, 'failed', NOW())
            ");
            $stmt->execute([$certificateNumber, $ip]);
        } catch (Exception $e) {
            error_log("Failed to log failed verification: " . $e->getMessage());
        }
    }

    public function apply() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }
        
        $success = null;
        $error = null;
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $pdo = Database::getConnection();
            $childName = trim($_POST['child_name'] ?? '');
            $dob = $_POST['date_of_birth'] ?? '';
            $gender = $_POST['gender'] ?? '';
            $placeOfBirth = trim($_POST['place_of_birth'] ?? '');
            $motherName = trim($_POST['mother_name'] ?? '');
            $fatherName = trim($_POST['father_name'] ?? '');
            $parentEmail = trim($_POST['parent_email'] ?? '');
            $parentPhone = trim($_POST['parent_phone'] ?? '');
            $address = trim($_POST['address'] ?? '');
            
            if (!$childName || !$dob || !$gender || !$placeOfBirth || !$motherName) {
                $error = 'Please fill in all required fields.';
            } else {
                try {
                    $stmt = $pdo->prepare('
                        INSERT INTO birth_applications (
                            user_id, child_name, date_of_birth, gender, place_of_birth, 
                            mother_name, father_name, parent_email, parent_phone, address, status
                        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, "pending")
                    ');
                    $stmt->execute([
                        $_SESSION['user_id'], $childName, $dob, $gender, $placeOfBirth,
                        $motherName, $fatherName, $parentEmail, $parentPhone, $address
                    ]);
                    
                    $success = 'Application submitted successfully! You will be notified once it\'s processed.';
                } catch (Exception $e) {
                    $error = 'Error submitting application. Please try again.';
                    error_log("Application error: " . $e->getMessage());
                }
            }
        }
        
        include BASE_PATH . '/resources/views/certificates/apply.php';
    }

    public function listCertificates() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }
        
        $pdo = Database::getConnection();
        $certificates = [];
        $applications = [];
        
        try {
            if ($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'registrar') {
                // Admin/Registrar can see all certificates
                $stmt = $pdo->prepare('
                    SELECT c.*, ba.child_name, ba.date_of_birth, u.email as applicant_email
                    FROM certificates c 
                    JOIN birth_applications ba ON c.application_id = ba.id 
                    JOIN users u ON ba.user_id = u.id 
                    ORDER BY c.issued_at DESC
                ');
                $stmt->execute();
                $certificates = $stmt->fetchAll();
                
                // Get pending applications
                $stmt = $pdo->prepare('
                    SELECT ba.*, u.email as applicant_email
                    FROM birth_applications ba 
                    JOIN users u ON ba.user_id = u.id 
                    WHERE ba.status = "pending"
                    ORDER BY ba.created_at ASC
                ');
                $stmt->execute();
                $applications = $stmt->fetchAll();
            } else {
                // Regular users see only their certificates
                $stmt = $pdo->prepare('
                    SELECT c.*, ba.child_name, ba.date_of_birth
                    FROM certificates c 
                    JOIN birth_applications ba ON c.application_id = ba.id 
                    WHERE ba.user_id = ?
                    ORDER BY c.issued_at DESC
                ');
                $stmt->execute([$_SESSION['user_id']]);
                $certificates = $stmt->fetchAll();
                
                // Get user's applications
                $stmt = $pdo->prepare('
                    SELECT * FROM birth_applications 
                    WHERE user_id = ? 
                    ORDER BY created_at DESC
                ');
                $stmt->execute([$_SESSION['user_id']]);
                $applications = $stmt->fetchAll();
            }
        } catch (Exception $e) {
            error_log("List certificates error: " . $e->getMessage());
        }
        
        include BASE_PATH . '/resources/views/certificates/list.php';
    }
    
    public function approveApplication() {
        if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'registrar'])) {
            http_response_code(403);
            echo json_encode(['error' => 'Unauthorized']);
            return;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $pdo = Database::getConnection();
            $applicationId = $_POST['application_id'] ?? '';
            $action = $_POST['action'] ?? ''; // 'approve' or 'reject'
            $comments = trim($_POST['comments'] ?? '');
            
            try {
                if ($action === 'approve') {
                    // Generate certificate
                    $certificateNumber = $this->generateCertificateNumber();
                    
                    $stmt = $pdo->prepare('
                        INSERT INTO certificates (
                            application_id, certificate_number, issued_by, issued_at, status
                        ) VALUES (?, ?, ?, NOW(), "active")
                    ');
                    $stmt->execute([$applicationId, $certificateNumber, $_SESSION['user_id']]);
                    
                    // Update application status
                    $stmt = $pdo->prepare('UPDATE birth_applications SET status = "approved" WHERE id = ?');
                    $stmt->execute([$applicationId]);
                    
                    echo json_encode(['success' => 'Application approved', 'certificate_number' => $certificateNumber]);
                } elseif ($action === 'reject') {
                    // Update application status
                    $stmt = $pdo->prepare('UPDATE birth_applications SET status = "rejected", comments = ? WHERE id = ?');
                    $stmt->execute([$comments, $applicationId]);
                    
                    echo json_encode(['success' => 'Application rejected']);
                }
            } catch (Exception $e) {
                error_log("Approval error: " . $e->getMessage());
                echo json_encode(['error' => 'Error processing request']);
            }
        }
    }
    
    public function downloadCertificate() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }
        
        $certificateId = $_GET['id'] ?? '';
        if (empty($certificateId)) {
            header('Location: /certificates');
            exit;
        }
        
        $pdo = Database::getConnection();
        
        try {
            $stmt = $pdo->prepare('
                SELECT c.*, ba.*, u.email as applicant_email
                FROM certificates c 
                JOIN birth_applications ba ON c.application_id = ba.id 
                JOIN users u ON ba.user_id = u.id 
                WHERE c.id = ?
            ');
            $stmt->execute([$certificateId]);
            $certificate = $stmt->fetch();
            
            if (!$certificate) {
                header('Location: /certificates');
                exit;
            }
            
            // Check if user has permission to download this certificate
            if ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'registrar' && $certificate['user_id'] != $_SESSION['user_id']) {
                http_response_code(403);
                echo 'Unauthorized';
                return;
            }
            
            // Generate PDF certificate
            $this->generatePDFCertificate($certificate);
        } catch (Exception $e) {
            error_log("Download error: " . $e->getMessage());
            header('Location: /certificates');
            exit;
        }
    }
    
    // Helper methods
    private function generateCertificateNumber() {
        $prefix = 'BC';
        $year = date('Y');
        $random = strtoupper(substr(md5(uniqid()), 0, 6));
        return $prefix . $year . $random;
    }
    
    /**
     * Download certificate by ID (for URL parameter routes)
     */
    public function download($certificateId = null)
    {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }
        
        // Get certificate ID from parameter or query string
        if (!$certificateId) {
            $certificateId = $_GET['id'] ?? '';
        }
        
        if (empty($certificateId)) {
            header('Location: /certificates');
            exit;
        }
        
        try {
            $pdo = Database::getConnection();
            
            // Get certificate and application data
            $stmt = $pdo->prepare("
                SELECT c.*, a.* 
                FROM certificates c
                JOIN birth_applications a ON c.application_id = a.id
                WHERE c.id = ?
            ");
            $stmt->execute([$certificateId]);
            $data = $stmt->fetch();
            
            if (!$data) {
                error_log("Certificate download: Certificate ID $certificateId not found in database");
                $_SESSION['error'] = 'Certificate not found. Please check the certificate ID and try again.';
                header('Location: /certificates');
                exit;
            }
            
            // Check if user has permission to download this certificate
            if ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'registrar' && $data['user_id'] != $_SESSION['user_id']) {
                http_response_code(403);
                echo 'Unauthorized';
                return;
            }
            
            // Prepare data for template
            $certificate = [
                'certificate_number' => $data['certificate_number'],
                'issued_at' => $data['issued_at'],
                'qr_code_data' => $data['qr_code_data'] ?? json_encode(['certificate' => true])
            ];
            
            $application = [
                'child_first_name' => $data['child_first_name'],
                'child_middle_name' => $data['child_middle_name'],
                'child_last_name' => $data['child_last_name'],
                'date_of_birth' => $data['date_of_birth'],
                'time_of_birth' => $data['time_of_birth'],
                'place_of_birth' => $data['place_of_birth'],
                'gender' => $data['gender'],
                'weight_at_birth' => $data['weight_at_birth'],
                'length_at_birth' => $data['length_at_birth'],
                'father_first_name' => $data['father_first_name'],
                'father_last_name' => $data['father_last_name'],
                'father_national_id' => $data['father_national_id'],
                'mother_first_name' => $data['mother_first_name'],
                'mother_last_name' => $data['mother_last_name'],
                'mother_national_id' => $data['mother_national_id'],
                'hospital_name' => $data['hospital_name'],
                'attending_physician' => $data['attending_physician']
            ];
            
            // Set headers for HTML download (can be printed to PDF)
            header('Content-Type: text/html; charset=utf-8');
            header('Content-Disposition: inline; filename="birth_certificate_' . $certificate['certificate_number'] . '.html"');
            
            // Include the certificate template
            include BASE_PATH . '/resources/views/certificates/birth_certificate_template.php';
            
        } catch (Exception $e) {
            error_log("Certificate download error: " . $e->getMessage());
            error_log("Certificate ID: " . $certificateId);
            $_SESSION['error'] = 'An error occurred while downloading the certificate: ' . $e->getMessage();
            header('Location: /certificates');
            exit;
        }
    }

    /**
     * Generate a sample certificate using real data from database
     */
    public function sample()
    {
        try {
            $pdo = Database::getConnection();
            
            // Try to fetch a real certificate from the database
            $stmt = $pdo->prepare("
                SELECT c.*, a.* 
                FROM certificates c
                JOIN birth_applications a ON c.application_id = a.id
                WHERE c.status = 'active'
                ORDER BY c.issued_at DESC
                LIMIT 1
            ");
            $stmt->execute();
            $data = $stmt->fetch();
            
            if ($data) {
                // Use real data from database
                $certificate = [
                    'certificate_number' => $data['certificate_number'],
                    'issued_at' => $data['issued_at'],
                    'qr_code_data' => $data['qr_code_data'] ?? json_encode(['sample' => true])
                ];
                
                $application = [
                    'child_first_name' => $data['child_first_name'],
                    'child_middle_name' => $data['child_middle_name'],
                    'child_last_name' => $data['child_last_name'],
                    'date_of_birth' => $data['date_of_birth'],
                    'time_of_birth' => $data['time_of_birth'],
                    'place_of_birth' => $data['place_of_birth'],
                    'gender' => $data['gender'],
                    'weight_at_birth' => $data['weight_at_birth'],
                    'length_at_birth' => $data['length_at_birth'],
                    'father_first_name' => $data['father_first_name'],
                    'father_last_name' => $data['father_last_name'],
                    'father_national_id' => $data['father_national_id'],
                    'mother_first_name' => $data['mother_first_name'],
                    'mother_last_name' => $data['mother_last_name'],
                    'mother_national_id' => $data['mother_national_id'],
                    'hospital_name' => $data['hospital_name'],
                    'attending_physician' => $data['attending_physician']
                ];
            } else {
                // Fallback to sample data if no certificates exist
                $certificate = [
                    'certificate_number' => 'BC' . date('Ymd') . '000001',
                    'issued_at' => date('Y-m-d H:i:s'),
                    'qr_code_data' => json_encode(['sample' => true])
                ];
                
                $application = [
                    'child_first_name' => 'Emma',
                    'child_middle_name' => 'Grace',
                    'child_last_name' => 'Johnson',
                    'date_of_birth' => '2024-01-15',
                    'time_of_birth' => '14:30:00',
                    'place_of_birth' => 'City General Hospital, Accra',
                    'gender' => 'female',
                    'weight_at_birth' => '3.2',
                    'length_at_birth' => '50',
                    'father_first_name' => 'Robert',
                    'father_last_name' => 'Johnson',
                    'father_national_id' => 'GHA-123456789-1',
                    'mother_first_name' => 'Sarah',
                    'mother_last_name' => 'Johnson',
                    'mother_national_id' => 'GHA-987654321-2',
                    'hospital_name' => 'City General Hospital',
                    'attending_physician' => 'Dr. Sarah Mensah'
                ];
            }
            
        } catch (Exception $e) {
            error_log("Sample certificate error: " . $e->getMessage());
            
            // Fallback to sample data on error
            $certificate = [
                'certificate_number' => 'BC' . date('Ymd') . '000001',
                'issued_at' => date('Y-m-d H:i:s'),
                'qr_code_data' => json_encode(['sample' => true])
            ];
            
            $application = [
                'child_first_name' => 'Emma',
                'child_middle_name' => 'Grace',
                'child_last_name' => 'Johnson',
                'date_of_birth' => '2024-01-15',
                'time_of_birth' => '14:30:00',
                'place_of_birth' => 'City General Hospital, Accra',
                'gender' => 'female',
                'weight_at_birth' => '3.2',
                'length_at_birth' => '50',
                'father_first_name' => 'Robert',
                'father_last_name' => 'Johnson',
                'father_national_id' => 'GHA-123456789-1',
                'mother_first_name' => 'Sarah',
                'mother_last_name' => 'Johnson',
                'mother_national_id' => 'GHA-987654321-2',
                'hospital_name' => 'City General Hospital',
                'attending_physician' => 'Dr. Sarah Mensah'
            ];
        }
        
        // Set headers for HTML display
        header('Content-Type: text/html; charset=utf-8');
        
        // Include the certificate template
        include BASE_PATH . '/resources/views/certificates/birth_certificate_template.php';
    }

    /**
     * Generate certificate for an approved application (API endpoint)
     */
    public function generate($applicationId)
    {
        if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'registrar'])) {
            http_response_code(403);
            echo json_encode(['error' => 'Unauthorized']);
            return;
        }
        
        try {
            $pdo = Database::getConnection();
            
            // Get application details
            $stmt = $pdo->prepare("
                SELECT * FROM birth_applications 
                WHERE id = ? AND status = 'approved'
            ");
            $stmt->execute([$applicationId]);
            $application = $stmt->fetch();
            
            if (!$application) {
                throw new Exception('Application not found or not approved');
            }
            
            // Check if certificate already exists
            $stmt = $pdo->prepare("SELECT id FROM certificates WHERE application_id = ?");
            $stmt->execute([$applicationId]);
            if ($stmt->fetch()) {
                throw new Exception('Certificate already exists for this application');
            }
            
            // Generate certificate number
            $certificateNumber = $this->generateCertificateNumber();
            
            // Generate QR code data
            $qrData = json_encode([
                'certificate_number' => $certificateNumber,
                'application_id' => $applicationId,
                'issued_date' => date('Y-m-d'),
                'verification_url' => '/verify/' . $certificateNumber
            ]);
            
            $qrHash = hash('sha256', $qrData);
            
            // Insert certificate record
            $stmt = $pdo->prepare("
                INSERT INTO certificates (
                    certificate_number, application_id, qr_code_hash, qr_code_data,
                    issued_by, issued_at, status
                ) VALUES (?, ?, ?, ?, ?, NOW(), 'active')
            ");
            
            $issuedBy = $_SESSION['user_id'];
            $stmt->execute([
                $certificateNumber,
                $applicationId,
                $qrHash,
                $qrData,
                $issuedBy
            ]);
            
            $certificateId = $pdo->lastInsertId();
            
            // Update application status
            $stmt = $pdo->prepare("
                UPDATE birth_applications 
                SET status = 'certificate_issued' 
                WHERE id = ?
            ");
            $stmt->execute([$applicationId]);
            
            echo json_encode([
                'success' => true,
                'certificate_id' => $certificateId,
                'certificate_number' => $certificateNumber,
                'message' => 'Certificate generated successfully'
            ]);
            
        } catch (Exception $e) {
            error_log("Certificate generation error: " . $e->getMessage());
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
    
    /**
     * Show individual certificate details
     */
    public function show($certificateId)
    {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }
        
        try {
            $pdo = Database::getConnection();
            
            // Get certificate and application data
            $stmt = $pdo->prepare("
                SELECT c.*, a.*, u.email as applicant_email
                FROM certificates c
                JOIN birth_applications a ON c.application_id = a.id
                JOIN users u ON a.user_id = u.id
                WHERE c.id = ?
            ");
            $stmt->execute([$certificateId]);
            $certificate = $stmt->fetch();
            
            if (!$certificate) {
                $_SESSION['error'] = 'Certificate not found';
                header('Location: /certificates');
                exit;
            }
            
            // Check if user has permission to view this certificate
            if ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'registrar' && $certificate['user_id'] != $_SESSION['user_id']) {
                $_SESSION['error'] = 'Unauthorized to view this certificate';
                header('Location: /certificates');
                exit;
            }
            
            $pageTitle = 'Certificate Details - ' . $certificate['certificate_number'];
            
            // Include certificate details view
            include BASE_PATH . '/resources/views/certificates/show.php';
            
        } catch (Exception $e) {
            error_log("Certificate show error: " . $e->getMessage());
            $_SESSION['error'] = 'Error loading certificate details';
            header('Location: /certificates');
            exit;
        }
    }
    
    /**
     * List verifications (admin/registrar only)
     */
    public function verifications()
    {
        if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'registrar'])) {
            header('Location: /login');
            exit;
        }
        
        try {
            $pdo = Database::getConnection();
            
            // Get recent verifications
            $stmt = $pdo->prepare("
                SELECT vl.*, c.certificate_number, a.child_first_name, a.child_last_name
                FROM verification_log vl
                LEFT JOIN certificates c ON vl.certificate_number = c.certificate_number
                LEFT JOIN birth_applications a ON c.application_id = a.id
                ORDER BY vl.created_at DESC
                LIMIT 100
            ");
            $stmt->execute();
            $verifications = $stmt->fetchAll();
            
            $pageTitle = 'Certificate Verifications';
            
            // Include verifications list view
            include BASE_PATH . '/resources/views/certificates/verifications.php';
            
        } catch (Exception $e) {
            error_log("Verifications list error: " . $e->getMessage());
            $_SESSION['error'] = 'Error loading verifications';
            header('Location: /dashboard');
            exit;
        }
    }
    
    /**
     * Show verification history for a specific certificate
     */
    public function verificationHistory()
    {
        if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'registrar'])) {
            header('Location: /login');
            exit;
        }
        
        $certificateNumber = $_GET['certificate'] ?? '';
        
        if (empty($certificateNumber)) {
            $_SESSION['error'] = 'Certificate number is required';
            header('Location: /verifications');
            exit;
        }
        
        try {
            $pdo = Database::getConnection();
            
            // Get certificate details
            $stmt = $pdo->prepare("
                SELECT c.*, a.child_first_name, a.child_last_name
                FROM certificates c
                JOIN birth_applications a ON c.application_id = a.id
                WHERE c.certificate_number = ?
            ");
            $stmt->execute([$certificateNumber]);
            $certificate = $stmt->fetch();
            
            if (!$certificate) {
                $_SESSION['error'] = 'Certificate not found';
                header('Location: /verifications');
                exit;
            }
            
            // Get verification history
            $stmt = $pdo->prepare("
                SELECT * FROM verification_log 
                WHERE certificate_number = ? 
                ORDER BY created_at DESC
            ");
            $stmt->execute([$certificateNumber]);
            $history = $stmt->fetchAll();
            
            $pageTitle = 'Verification History - ' . $certificateNumber;
            
            // Include verification history view
            include BASE_PATH . '/resources/views/certificates/verification_history.php';
            
        } catch (Exception $e) {
            error_log("Verification history error: " . $e->getMessage());
            $_SESSION['error'] = 'Error loading verification history';
            header('Location: /verifications');
            exit;
        }
    }

    private function generatePDFCertificate($certificate) {
        // Use the new professional template
        $certificateData = [
            'certificate_number' => $certificate['certificate_number'] ?? 'BC' . date('Ymd') . '000001',
            'issued_at' => $certificate['issued_at'] ?? date('Y-m-d H:i:s'),
            'qr_code_data' => $certificate['qr_code_data'] ?? json_encode(['certificate' => true])
        ];
        
        $applicationData = [
            'child_first_name' => $certificate['child_first_name'] ?? '',
            'child_middle_name' => $certificate['child_middle_name'] ?? '',
            'child_last_name' => $certificate['child_last_name'] ?? '',
            'date_of_birth' => $certificate['date_of_birth'] ?? '',
            'time_of_birth' => $certificate['time_of_birth'] ?? '',
            'place_of_birth' => $certificate['place_of_birth'] ?? '',
            'gender' => $certificate['gender'] ?? '',
            'weight_at_birth' => $certificate['weight_at_birth'] ?? '',
            'length_at_birth' => $certificate['length_at_birth'] ?? '',
            'father_first_name' => $certificate['father_first_name'] ?? '',
            'father_last_name' => $certificate['father_last_name'] ?? '',
            'father_national_id' => $certificate['father_national_id'] ?? '',
            'mother_first_name' => $certificate['mother_first_name'] ?? '',
            'mother_last_name' => $certificate['mother_last_name'] ?? '',
            'mother_national_id' => $certificate['mother_national_id'] ?? '',
            'hospital_name' => $certificate['hospital_name'] ?? '',
            'attending_physician' => $certificate['attending_physician'] ?? ''
        ];
        
        // Set headers for HTML download (can be printed to PDF)
        header('Content-Type: text/html; charset=utf-8');
        header('Content-Disposition: inline; filename="birth_certificate_' . $certificateData['certificate_number'] . '.html"');
        
        // Use the variables expected by the template
        $certificate = $certificateData;
        $application = $applicationData;
        
        // Include the professional certificate template
        include BASE_PATH . '/resources/views/certificates/birth_certificate_template.php';
    }

    /**
     * Index - List all certificates accessible to the current user
     */
    public function index()
    {
        // Check if user is logged in
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }
        
        try {
            // Ensure database connection is available
            if ($this->db === null) {
                $this->db = Database::getConnection();
                if ($this->db === null) {
                    throw new Exception("Failed to establish database connection");
                }
            }
            
            // Ensure certificates table exists
            $this->ensureCertificatesTableExists();
            
            // Get current user information
            $userId = $_SESSION['user_id'];
            $role = $_SESSION['role'] ?? '';
            
            // Get search parameters
            $search = $_GET['search'] ?? '';
            $status = $_GET['status'] ?? '';
            $date = $_GET['date'] ?? '';
            $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
            $perPage = 10;
            $offset = ($page - 1) * $perPage;
            
            // Determine which certificates to show based on user role
            $certificates = [];
            $totalCertificates = 0;
            
            if (in_array($role, ['admin', 'registrar'])) {
                // Admins and Registrars can see all certificates
                list($certificates, $totalCertificates) = $this->getAllCertificates($search, $status, $date, $offset, $perPage);
            } else {
                // Regular users can only see their own certificates
                list($certificates, $totalCertificates) = $this->getUserCertificates($userId, $search, $status, $date, $offset, $perPage);
            }
            
            // Calculate pagination
            $totalPages = ceil($totalCertificates / $perPage);
            $currentPage = $page;
            
            // Page title
            $pageTitle = 'My Certificates';
            if ($role === 'admin') {
                $pageTitle = 'All Certificates';
            } elseif ($role === 'registrar') {
                $pageTitle = 'Issued Certificates';
            }
            
            // Include view
            include BASE_PATH . '/resources/views/certificates/index.php';
            
        } catch (Exception $e) {
            error_log("Certificate index error: " . $e->getMessage());
            error_log("Certificate index stack trace: " . $e->getTraceAsString());
            $_SESSION['error'] = 'An error occurred while loading certificates. Please try again. Error: ' . $e->getMessage();
            header('Location: /dashboard');
            exit;
        }
    }
    
    /**
     * Get all certificates with filtering and pagination (admin/registrar access)
     * 
     * @param string $search Search term
     * @param string $status Status filter
     * @param string $date Date filter
     * @param int $offset Pagination offset
     * @param int $limit Pagination limit
     * @return array [certificates, totalCount]
     */
    private function getAllCertificates($search = '', $status = '', $date = '', $offset = 0, $limit = 10)
    {
        $whereConditions = [];
        $params = [];
        
        if (!empty($search)) {
            $whereConditions[] = "(c.certificate_number LIKE ? OR CONCAT(ba.child_first_name, ' ', ba.child_last_name) LIKE ? OR ba.application_number LIKE ?)";
            $searchTerm = "%{$search}%";
            $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm]);
        }
        
        if (!empty($status)) {
            $whereConditions[] = "c.status = ?";
            $params[] = $status;
        }
        
        if (!empty($date)) {
            switch ($date) {
                case 'today':
                    $whereConditions[] = "DATE(c.issued_at) = CURDATE()";
                    break;
                case 'week':
                    $whereConditions[] = "c.issued_at >= DATE_SUB(NOW(), INTERVAL 1 WEEK)";
                    break;
                case 'month':
                    $whereConditions[] = "c.issued_at >= DATE_SUB(NOW(), INTERVAL 1 MONTH)";
                    break;
            }
        }
        
        $whereClause = !empty($whereConditions) ? "WHERE " . implode(' AND ', $whereConditions) : "";
        
        // Get certificates
        $query = "
            SELECT c.*, 
                   ba.child_first_name, ba.child_last_name, ba.date_of_birth, ba.place_of_birth,
                   CONCAT(ba.father_first_name, ' ', ba.father_last_name) as father_name, 
                   CONCAT(ba.mother_first_name, ' ', ba.mother_last_name) as mother_name, 
                   ba.gender,
                   u.first_name as issued_by_first_name, u.last_name as issued_by_last_name
            FROM certificates c
            LEFT JOIN birth_applications ba ON c.application_id = ba.id
            LEFT JOIN users u ON c.issued_by = u.id
            {$whereClause}
            ORDER BY c.issued_at DESC
            LIMIT ? OFFSET ?
        ";
        
        $params[] = $limit;
        $params[] = $offset;
        
        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        $certificates = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        
        // Get total count
        $countQuery = "
            SELECT COUNT(*) as total 
            FROM certificates c
            LEFT JOIN birth_applications ba ON c.application_id = ba.id
            {$whereClause}
        ";
        
        // Remove limit/offset for count query
        array_pop($params);
        array_pop($params);
        
        $countStmt = $this->db->prepare($countQuery);
        $countStmt->execute($params);
        $totalCount = $countStmt->fetchColumn();
        
        return [$certificates, $totalCount];
    }
    
    /**
     * Get certificates for a specific user with filtering and pagination
     * 
     * @param int $userId User ID
     * @param string $search Search term
     * @param string $status Status filter
     * @param string $date Date filter
     * @param int $offset Pagination offset
     * @param int $limit Pagination limit
     * @return array [certificates, totalCount]
     */
    private function getUserCertificates($userId, $search = '', $status = '', $date = '', $offset = 0, $limit = 10)
    {
        $whereConditions = ["ba.user_id = ?"];
        $params = [$userId];
        
        if (!empty($search)) {
            $whereConditions[] = "(c.certificate_number LIKE ? OR CONCAT(ba.child_first_name, ' ', ba.child_last_name) LIKE ?)";
            $searchTerm = "%{$search}%";
            $params = array_merge($params, [$searchTerm, $searchTerm]);
        }
        
        if (!empty($status)) {
            $whereConditions[] = "c.status = ?";
            $params[] = $status;
        }
        
        if (!empty($date)) {
            switch ($date) {
                case 'today':
                    $whereConditions[] = "DATE(c.issued_at) = CURDATE()";
                    break;
                case 'week':
                    $whereConditions[] = "c.issued_at >= DATE_SUB(NOW(), INTERVAL 1 WEEK)";
                    break;
                case 'month':
                    $whereConditions[] = "c.issued_at >= DATE_SUB(NOW(), INTERVAL 1 MONTH)";
                    break;
            }
        }
        
        $whereClause = "WHERE " . implode(' AND ', $whereConditions);
        
        // Get certificates
        $query = "
            SELECT c.*, 
                   ba.child_first_name, ba.child_last_name, ba.date_of_birth, ba.place_of_birth,
                   CONCAT(ba.father_first_name, ' ', ba.father_last_name) as father_name, 
                   CONCAT(ba.mother_first_name, ' ', ba.mother_last_name) as mother_name, 
                   ba.gender,
                   u.first_name as issued_by_first_name, u.last_name as issued_by_last_name
            FROM certificates c
            LEFT JOIN birth_applications ba ON c.application_id = ba.id
            LEFT JOIN users u ON c.issued_by = u.id
            {$whereClause}
            ORDER BY c.issued_at DESC
            LIMIT ? OFFSET ?
        ";
        
        $params[] = $limit;
        $params[] = $offset;
        
        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        $certificates = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        
        // Get total count
        $countQuery = "
            SELECT COUNT(*) as total 
            FROM certificates c
            LEFT JOIN birth_applications ba ON c.application_id = ba.id
            {$whereClause}
        ";
        
        // Remove limit/offset for count query
        array_pop($params);
        array_pop($params);
        
        $countStmt = $this->db->prepare($countQuery);
        $countStmt->execute($params);
        $totalCount = $countStmt->fetchColumn();
        
        return [$certificates, $totalCount];
    }

    /**
     * Email certificate to applicant (placeholder)
     */
    public function emailCertificate($id)
    {
        // Auth: registrar/admin only for emailing from registrar list; owner can request their own
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }
        try {
            $pdo = Database::getConnection();
            // Load certificate + applicant email
            $stmt = $pdo->prepare('SELECT c.id as cert_id, c.certificate_number, ba.id as application_id, u.email as applicant_email
                                   FROM certificates c 
                                   JOIN birth_applications ba ON c.application_id = ba.id 
                                   JOIN users u ON ba.user_id = u.id
                                   WHERE c.id = ? OR ba.id = ? LIMIT 1');
            $stmt->execute([$id, $id]);
            $row = $stmt->fetch();
            if (!$row) {
                $this->respondEmailResult(false, 'Certificate or application not found');
                return;
            }
            // Here we would send email via EmailService; for now simulate success
            $success = true;
            $message = 'Certificate will be emailed to ' . htmlspecialchars($row['applicant_email']);
            $this->respondEmailResult($success, $message);
        } catch (Exception $e) {
            error_log('emailCertificate error: ' . $e->getMessage());
            $this->respondEmailResult(false, 'Failed to email certificate. Please try again.');
        }
    }

    private function respondEmailResult($success, $message)
    {
        $isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH'])==='xmlhttprequest';
        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => (bool)$success, 'message' => $message]);
            return;
        }
        if ($success) {
            $_SESSION['success'] = $message;
        } else {
            $_SESSION['error'] = $message;
        }
        // Redirect back to referrer if possible
        $back = $_SERVER['HTTP_REFERER'] ?? '/certificates';
        header('Location: ' . $back);
    }
    
    /**
     * Ensure certificates table exists
     */
    private function ensureCertificatesTableExists()
    {
        try {
            $createTableSQL = "
                CREATE TABLE IF NOT EXISTS certificates (
                    id INT PRIMARY KEY AUTO_INCREMENT,
                    certificate_number VARCHAR(50) UNIQUE NOT NULL,
                    application_id INT NOT NULL,
                    issued_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    issued_by INT,
                    status ENUM('active', 'revoked', 'expired') DEFAULT 'active',
                    qr_code_data TEXT,
                    digital_signature TEXT,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    INDEX idx_certificate_number (certificate_number),
                    INDEX idx_application_id (application_id),
                    INDEX idx_issued_at (issued_at),
                    FOREIGN KEY (application_id) REFERENCES birth_applications(id) ON DELETE CASCADE,
                    FOREIGN KEY (issued_by) REFERENCES users(id) ON DELETE SET NULL
                )
            ";
            
            $this->db->exec($createTableSQL);
        } catch (Exception $e) {
            error_log("Error creating certificates table: " . $e->getMessage());
            // Don't throw exception to avoid breaking the flow
        }
    }
} 