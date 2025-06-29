<?php

namespace App\Controllers;

use App\Database\Database;

class CertificateController
{
    public function showVerify()
    {
        $pageTitle = 'Verify Birth Certificate';
        
        // Generate CSRF token if not exists
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        
        include __DIR__ . '/../../resources/views/verify.php';
    }
    
    public function verify($certificateId = null)
    {
        $pageTitle = 'Verify Birth Certificate';
        
        // Rate limiting for verification attempts
        if ($this->isRateLimited($_SERVER['REMOTE_ADDR'], 'verify')) {
            $error = "Too many verification attempts. Please wait 5 minutes before trying again.";
            include __DIR__ . '/../../resources/views/verify.php';
            return;
        }
        
        if (!$certificateId) {
            $certificateId = trim($_GET['certificate_number'] ?? '');
        }
        
        // Validate certificate number format
        if (empty($certificateId)) {
            $error = "Certificate number is required";
            include __DIR__ . '/../../resources/views/verify.php';
            return;
        }
        
        // Validate certificate number format (12 characters, alphanumeric)
        if (!preg_match('/^[A-Z0-9]{12}$/', $certificateId)) {
            $error = "Invalid certificate number format. Please enter a 12-character alphanumeric code.";
            include __DIR__ . '/../../resources/views/verify.php';
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
        
        include __DIR__ . '/../../resources/views/verify.php';
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
} 