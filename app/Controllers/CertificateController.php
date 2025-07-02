<?php

namespace App\Controllers;

use App\Database\Database;

/**
 * Class CertificateController
 *
 * Handles birth certificate verification, issuance, listing,
 * approval, and PDF download functionality.
 */
class CertificateController
{
    /**
     * Display the certificate verification form.
     * Generates CSRF token and includes the verify view.
     *
     * @return void
     */
    public function showVerify()
    {
        $pageTitle = 'Verify Certificate - Digital Birth Certificate System';
        
        // Generate CSRF token if not exists
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        
        // Initialize certificate variable to avoid undefined variable errors
        $certificate = null;
        
        include BASE_PATH . '/resources/views/verify.php';
    }
    
    /**
     * Process certificate verification by certificate number.
     * Validates input, enforces rate limits, queries database,
     * updates verification count, and prepares certificate data.
     *
     * @param string|null $certificateId Optional certificate number from URL.
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
        
        // Validate certificate number format (12 characters, alphanumeric)
        if (!preg_match('/^[A-Z0-9]{12}$/', $certificateId)) {
            $error = "Invalid certificate number format. Please enter a 12-character alphanumeric code.";
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
                    CONCAT(u.first_name, ' ', u.last_name) as registrar_name,
                    u.email as registrar_email,
                    h.name as hospital_name,
                    h.address as hospital_address
                FROM certificates c
                JOIN birth_applications ba ON c.application_id = ba.id
                JOIN users u ON c.issued_by = u.id
                LEFT JOIN hospitals h ON ba.hospital_id = h.id
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
                    SELECT c.*, ba.child_name, ba.date_of_birth, ba.gender, ba.place_of_birth,
                           ba.mother_name, ba.father_name
                    FROM certificates c 
                    JOIN birth_applications ba ON c.application_id = ba.id 
                    WHERE c.certificate_number = ?
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
    
    private function generatePDFCertificate($certificate) {
        // In a real implementation, you would use a PDF library like TCPDF or FPDF
        // For now, we'll just output the certificate data as HTML
        
        header('Content-Type: text/html');
        header('Content-Disposition: inline; filename="certificate_' . $certificate['certificate_number'] . '.html"');
        
        echo '<!DOCTYPE html>
        <html>
        <head>
            <title>Birth Certificate</title>
            <style>
                body { font-family: Arial, sans-serif; margin: 40px; }
                .certificate { border: 2px solid #000; padding: 30px; text-align: center; }
                .header { font-size: 24px; font-weight: bold; margin-bottom: 30px; }
                .content { text-align: left; line-height: 1.6; }
                .field { margin-bottom: 15px; }
                .label { font-weight: bold; }
            </style>
        </head>
        <body>
            <div class="certificate">
                <div class="header">BIRTH CERTIFICATE</div>
                <div class="content">
                    <div class="field">
                        <span class="label">Certificate Number:</span> ' . htmlspecialchars($certificate['certificate_number']) . '
                    </div>
                    <div class="field">
                        <span class="label">Child Name:</span> ' . htmlspecialchars($certificate['child_name']) . '
                    </div>
                    <div class="field">
                        <span class="label">Date of Birth:</span> ' . htmlspecialchars($certificate['date_of_birth']) . '
                    </div>
                    <div class="field">
                        <span class="label">Gender:</span> ' . htmlspecialchars($certificate['gender']) . '
                    </div>
                    <div class="field">
                        <span class="label">Place of Birth:</span> ' . htmlspecialchars($certificate['place_of_birth']) . '
                    </div>
                    <div class="field">
                        <span class="label">Mother Name:</span> ' . htmlspecialchars($certificate['mother_name']) . '
                    </div>
                    <div class="field">
                        <span class="label">Father Name:</span> ' . htmlspecialchars($certificate['father_name']) . '
                    </div>
                    <div class="field">
                        <span class="label">Issue Date:</span> ' . htmlspecialchars($certificate['issued_at']) . '
                    </div>
                </div>
            </div>
        </body>
        </html>';
    }
} 