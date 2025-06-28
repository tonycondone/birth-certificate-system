<?php

namespace App\Controllers;

class CertificateController
{
    public function showVerify()
    {
        include __DIR__ . '/../../resources/views/verify.php';
    }
    
    public function verify($certificateId = null)
    {
        if (!$certificateId) {
            $certificateId = $_GET['id'] ?? null;
        }
        
        if (!$certificateId) {
            $error = "Certificate ID is required";
            include __DIR__ . '/../../resources/views/verify.php';
            return;
        }
        
        try {
            $pdo = \App\Database\Database::getConnection();
            
            $stmt = $pdo->prepare("
                SELECT c.*, ba.child_name, ba.date_of_birth, ba.place_of_birth
                FROM certificates c
                JOIN birth_applications ba ON c.application_id = ba.id
                WHERE c.certificate_number = ?
            ");
            $stmt->execute([$certificateId]);
            $certificate = $stmt->fetch();
            
            if ($certificate) {
                $isValid = true;
                $message = "Certificate is valid and verified";
            } else {
                $isValid = false;
                $message = "Certificate not found or invalid";
            }
            
        } catch (\Exception $e) {
            $isValid = false;
            $message = "Error verifying certificate";
            error_log($e->getMessage());
        }
        
        include __DIR__ . '/../../resources/views/verify.php';
    }
} 