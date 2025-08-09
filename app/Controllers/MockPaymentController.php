<?php

namespace App\Controllers;

use App\Database\Database;
use Exception;

/**
 * Mock Payment Controller for development testing when Paystack is unreachable
 * This should ONLY be used in development mode
 */
class MockPaymentController
{
    /**
     * Display mock payment page
     * 
     * @param int $applicationId
     * @param string $reference
     * @return void
     */
    public function showPaymentPage($applicationId, $reference): void
    {
        // Ensure we're in development mode
        if (!$this->isDevelopmentMode()) {
            header('Location: /applications/' . $applicationId);
            exit;
        }
        
        try {
            $pdo = Database::getConnection();
            
            // Get application details
            $stmt = $pdo->prepare('SELECT ba.*, u.email, u.first_name, u.last_name 
                                  FROM birth_applications ba 
                                  JOIN users u ON ba.user_id = u.id 
                                  WHERE ba.id = ?');
            $stmt->execute([$applicationId]);
            $application = $stmt->fetch();
            
            if (!$application) {
                $stmt = $pdo->prepare('SELECT a.*, u.email, u.first_name, u.last_name 
                                      FROM applications a 
                                      JOIN users u ON a.user_id = u.id 
                                      WHERE a.id = ?');
                $stmt->execute([$applicationId]);
                $application = $stmt->fetch();
            }
            
            if (!$application) {
                $_SESSION['error'] = 'Application not found';
                header('Location: /applications');
                exit;
            }
            
            // Get payment details
            $stmt = $pdo->prepare('SELECT * FROM payments WHERE transaction_id = ?');
            $stmt->execute([$reference]);
            $payment = $stmt->fetch();
            
            if (!$payment) {
                $amount = $_ENV['PAYMENT_AMOUNT'] ?? 15000;
                $amount = $amount / 100; // Convert to GH₵
            } else {
                $amount = $payment['amount'];
            }
            
            // Display mock payment page
            include BASE_PATH . '/resources/views/mock-payment/payment.php';
            
        } catch (Exception $e) {
            error_log('Mock payment error: ' . $e->getMessage());
            $_SESSION['error'] = 'An error occurred while processing your payment. Please try again.';
            header('Location: /applications/' . $applicationId);
            exit;
        }
    }
    
    /**
     * Process mock payment
     * 
     * @param int $applicationId
     * @param string $reference
     * @return void
     */
    public function processPayment($applicationId, $reference): void
    {
        // Ensure we're in development mode
        if (!$this->isDevelopmentMode()) {
            header('Location: /applications/' . $applicationId);
            exit;
        }
        
        try {
            $pdo = Database::getConnection();
            
            // Update payment status
            $stmt = $pdo->prepare('UPDATE payments SET status = ?, updated_at = NOW() WHERE transaction_id = ?');
            $stmt->execute(['completed', $reference]);
            
            // Generate tracking number
            $trackingNumber = 'MOCK-' . strtoupper(uniqid());
            
            // Update application status
            $updated = false;
            $stmt = $pdo->prepare('SELECT id FROM birth_applications WHERE id = ?');
            $stmt->execute([$applicationId]);
            if ($stmt->fetch()) {
                $stmt = $pdo->prepare('UPDATE birth_applications SET status = "submitted", tracking_number = ?, submitted_at = NOW() WHERE id = ?');
                $stmt->execute([$trackingNumber, $applicationId]);
                $updated = true;
            }
            
            if (!$updated) {
                $stmt = $pdo->prepare('UPDATE applications SET status = "submitted", tracking_number = ?, submitted_at = NOW() WHERE id = ?');
                $stmt->execute([$trackingNumber, $applicationId]);
            }
            
            // Set success message
            $_SESSION['success'] = 'Mock payment successful! Your application has been submitted.';
            
            // Redirect to tracking page
            header('Location: /track/' . $trackingNumber);
            exit;
            
        } catch (Exception $e) {
            error_log('Mock payment processing error: ' . $e->getMessage());
            $_SESSION['error'] = 'An error occurred while processing your payment. Please try again.';
            header('Location: /applications/' . $applicationId);
            exit;
        }
    }
    
    /**
     * Check if we're in development mode
     * 
     * @return bool
     */
    private function isDevelopmentMode(): bool
    {
        return (($_ENV['APP_DEBUG'] ?? '') && strtolower($_ENV['APP_DEBUG']) !== '0' && ($_ENV['DEV_MODE'] ?? '') === 'true');
    }
} 