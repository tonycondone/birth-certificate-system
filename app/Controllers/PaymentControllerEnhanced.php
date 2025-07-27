<?php

namespace App\Controllers;

use App\Database\Database;
use App\Services\EmailService;
use App\Services\LoggingService;
use App\Services\ValidationService;
use Exception;

class PaymentControllerEnhanced
{
    private $paystackPublicKey;
    private $paystackSecretKey;
    private $paymentAmount;
    private $logger;

    public function __construct()
    {
        $this->paystackPublicKey = $_ENV['PAYSTACK_PUBLIC_KEY'] ?? 'pk_test_xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx';
        $this->paystackSecretKey = $_ENV['PAYSTACK_SECRET_KEY'] ?? 'sk_test_xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx';
        $this->paymentAmount = $_ENV['PAYMENT_AMOUNT'] ?? 15000;
        $this->logger = LoggingService::getInstance();
    }

    /**
     * Display enhanced payment page
     *
     * @param int $applicationId
     * @return void
     */
    public function showPaymentPage($applicationId): void
    {
        try {
            // Ensure user is authenticated
            if (!isset($_SESSION['user'])) {
                $_SESSION['redirect_after_login'] = "/applications/$applicationId/pay";
                header('Location: /login');
                exit;
            }

            $pdo = Database::getConnection();
            
            // Fetch application with user details
            $stmt = $pdo->prepare('
                SELECT a.*, u.email, u.first_name, u.last_name, u.phone 
                FROM applications a 
                JOIN users u ON a.user_id = u.id 
                WHERE a.id = ? AND (a.user_id = ? OR ? = 1)
            ');
            $stmt->execute([$applicationId, $_SESSION['user']['id'], $_SESSION['user']['role'] ?? 'user']);
            $application = $stmt->fetch();

            if (!$application) {
                $_SESSION['error'] = 'Application not found or access denied';
                header('Location: /applications');
                exit;
            }

            // Check if payment already exists
            $stmt = $pdo->prepare('SELECT * FROM payments WHERE application_id = ? ORDER BY created_at DESC LIMIT 1');
            $stmt->execute([$applicationId]);
            $existingPayment = $stmt->fetch();

            // Calculate amounts
            $baseAmount = $this->paymentAmount / 100;
            $processingFee = 5.00;
            $totalAmount = $baseAmount + $processingFee;

            // Check payment status
            $paymentStatus = $this->getPaymentStatus($applicationId);
            
            // Redirect if already paid
            if ($paymentStatus === 'completed') {
                $_SESSION['success'] = 'Payment already completed for this application';
                header("Location: /track/{$application['tracking_number']}");
                exit;
            }

            include BASE_PATH . '/resources/views/applications/payment-enhanced.php';

        } catch (Exception $e) {
            $this->logger->logError('Payment page error', ['error' => $e->getMessage()]);
            $_SESSION['error'] = 'Unable to load payment page';
            header('Location: /applications');
            exit;
        }
    }

    /**
     * Enhanced payment initialization with validation
     *
     * @param int $applicationId
     * @return void
     */
    public function initializePayment($applicationId): void
    {
        try {
            // Validate request
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                http_response_code(405);
                echo json_encode(['success' => false, 'error' => 'Method not allowed']);
                return;
            }

            // Ensure user is authenticated
            if (!isset($_SESSION['user'])) {
                http_response_code(401);
                echo json_encode(['success' => false, 'error' => 'Authentication required']);
                return;
            }

            // Validate application ownership
            $pdo = Database::getConnection();
            $stmt = $pdo->prepare('
                SELECT a.*, u.email, u.first_name, u.last_name, u.phone 
                FROM applications a 
                JOIN users u ON a.user_id = u.id 
                WHERE a.id = ? AND (a.user_id = ? OR ? = 1)
            ');
            $stmt->execute([$applicationId, $_SESSION['user']['id'], $_SESSION['user']['role'] ?? 'user']);
            $application = $stmt->fetch();

            if (!$application) {
                http_response_code(404);
                echo json_encode(['success' => false, 'error' => 'Application not found']);
                return;
            }

            // Validate payment method
            $input = json_decode(file_get_contents('php://input'), true);
            $paymentMethod = $input['payment_method'] ?? 'paystack';
            
            if (!in_array($paymentMethod, ['paystack', 'mobile-money'])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Invalid payment method']);
                return;
            }

            // Check for existing pending payment
            $stmt = $pdo->prepare('
                SELECT * FROM payments 
                WHERE application_id = ? AND status = "pending" 
                AND created_at > DATE_SUB(NOW(), INTERVAL 30 MINUTE)
            ');
            $stmt->execute([$applicationId]);
            $existingPayment = $stmt->fetch();

            if ($existingPayment) {
                // Return existing payment URL
                echo json_encode([
                    'success' => true, 
                    'data' => [
                        'authorization_url' => $existingPayment['transaction_id'],
                        'reference' => $existingPayment['transaction_id']
                    ]
                ]);
                return;
            }

            // Generate unique reference
            $reference = 'BCS-' . date('YmdHis') . '-' . strtoupper(uniqid());
            
            // Calculate amounts
            $amount = $this->paymentAmount + 500; // Add processing fee
            
            // Prepare metadata
            $metadata = [
                'application_id' => $applicationId,
                'user_id' => $application['user_id'],
                'full_name' => $application['first_name'] . ' ' . $application['last_name'],
                'payment_method' => $paymentMethod,
                'cancel_action' => $_ENV['APP_URL'] . "/applications/$applicationId/pay"
            ];

            // Create payment record
            $stmt = $pdo->prepare('
                INSERT INTO payments (application_id, amount, currency, transaction_id, 
                status, payment_gateway, payment_method, metadata) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ');
            $stmt->execute([
                $applicationId,
                $amount / 100,
                'GHS',
                $reference,
                'pending',
                'paystack',
                $paymentMethod,
                json_encode($metadata)
            ]);

            // Initialize Paystack transaction
            $result = $this->initializePaystackTransaction([
                'email' => $application['email'],
                'amount' => $amount,
                'reference' => $reference,
                'callback_url' => $_ENV['APP_URL'] . "/applications/{$applicationId}/payment-callback",
                'metadata' => $metadata
            ]);

            echo json_encode(['success' => true, 'data' => $result]);

        } catch (Exception $e) {
            $this->logger->logError('Payment initialization error', [
                'error' => $e->getMessage(),
                'application_id' => $applicationId
            ]);
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Payment initialization failed']);
        }
    }

    /**
     * Enhanced payment callback with better error handling
     *
     * @param int $applicationId
     * @return void
     */
    public function handleCallback($applicationId): void
    {
        try {
            $reference = $_GET['reference'] ?? '';
            $trxref = $_GET['trxref'] ?? $reference;

            if (empty($reference)) {
                throw new Exception('No payment reference provided');
            }

            // Verify transaction
            $verification = $this->verifyPaystackTransaction($reference);
            
            if (!$verification['status']) {
                throw new Exception($verification['message'] ?? 'Payment verification failed');
            }

            $transactionData = $verification['data'];
            $pdo = Database::getConnection();

            // Update payment record
            $stmt = $pdo->prepare('
                UPDATE payments 
                SET status = ?, transaction_id = ?, gateway_response = ?, 
                    paid_at = NOW(), updated_at = NOW()
                WHERE transaction_id = ?
            ');
            $stmt->execute([
                'completed',
                $transactionData['reference'],
                json_encode($transactionData),
                $reference
            ]);

            // Update application status
            $trackingNumber = 'APP-' . strtoupper(uniqid());
            $stmt = $pdo->prepare('
                UPDATE applications 
                SET status = "submitted", tracking_number = ?, submitted_at = NOW() 
                WHERE id = ?
            ');
            $stmt->execute([$trackingNumber, $applicationId]);

            // Send confirmation email
            $this->sendPaymentConfirmation($applicationId, $transactionData);

            // Log successful payment
            $this->logger->logInfo('Payment completed successfully', [
                'application_id' => $applicationId,
                'reference' => $reference,
                'amount' => $transactionData['amount']
            ]);

            $_SESSION['success'] = 'Payment successful! Your application has been submitted.';
            header("Location: /track/{$trackingNumber}");
            exit;

        } catch (Exception $e) {
            $this->logger->logError('Payment callback error', [
                'error' => $e->getMessage(),
                'application_id' => $applicationId,
                'reference' => $reference ?? 'unknown'
            ]);
            
            $_SESSION['error'] = 'Payment processing failed: ' . $e->getMessage();
            header("Location: /applications/{$applicationId}/pay");
            exit;
        }
    }

    /**
     * Enhanced webhook handler with security validation
     *
     * @return void
     */
    public function handleWebhook(): void
    {
        try {
            // Validate webhook signature
            $input = file_get_contents('php://input');
            $signature = $_SERVER['HTTP_X_PAYSTACK_SIGNATURE'] ?? '';
            
            if (!$this->validateWebhookSignature($input, $signature)) {
                http_response_code(401);
                exit('Unauthorized');
            }

            $event = json_decode($input, true);
            
            if (!$event || !isset($event['event'])) {
                http_response_code(400);
                exit('Invalid webhook data');
            }

            // Handle successful payment
            if ($event['event'] === 'charge.success') {
                $this->processSuccessfulPayment($event);
            }

            // Handle failed payment
            if ($event['event'] === 'charge.failed') {
                $this->processFailedPayment($event);
            }

            http_response_code(200);
            exit('OK');

        } catch (Exception $e) {
            $this->logger->logError('Webhook processing error', ['error' => $e->getMessage()]);
            http_response_code(500);
            exit('Error');
        }
    }

    /**
     * Get payment status for an application
     *
     * @param int $applicationId
     * @return string
     */
    private function getPaymentStatus($applicationId): string
    {
        try {
            $pdo = Database::getConnection();
            $stmt = $pdo->prepare('SELECT status FROM payments WHERE application_id = ? ORDER BY created_at DESC LIMIT 1');
            $stmt->execute([$applicationId]);
            $payment = $stmt->fetch();
            
            return $payment['status'] ?? 'none';
        } catch (Exception $e) {
            return 'error';
        }
    }

    /**
     * Initialize Paystack transaction
     *
     * @param array $data
     * @return array
     */
    private function initializePaystackTransaction(array $data): array
    {
        $url = "https://api.paystack.co/transaction/initialize";
        $headers = [
            'Authorization: Bearer ' . $this->paystackSecretKey,
            'Content-Type: application/json',
        ];

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYPEER => true
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        if (curl_errno($ch)) {
            throw new Exception('CURL error: ' . curl_error($ch));
        }
        
        curl_close($ch);
        
        $result = json_decode($response, true);
        
        if ($httpCode !== 200 || !$result['status']) {
            throw new Exception($result['message'] ?? 'Paystack API error');
        }

        return $result['data'];
    }

    /**
     * Verify Paystack transaction
     *
     * @param string $reference
     * @return array
     */
    private function verifyPaystackTransaction($reference): array
    {
        $url = "https://api.paystack.co/transaction/verify/" . rawurlencode($reference);
        $headers = ['Authorization: Bearer ' . $this->paystackSecretKey];

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYPEER => true
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        if (curl_errno($ch)) {
            throw new Exception('CURL error: ' . curl_error($ch));
        }
        
        curl_close($ch);
        
        return json_decode($response, true);
    }

    /**
     * Validate webhook signature
     *
     * @param string $payload
     * @param string $signature
     * @return bool
     */
    private function validateWebhookSignature($payload, $signature): bool
    {
        $computedSignature = hash_hmac('sha512', $payload, $this->paystackSecretKey);
        return hash_equals('sha512=' . $computedSignature, $signature);
    }

    /**
     * Process successful payment
     *
     * @param array $event
     * @return void
     */
    private function processSuccessfulPayment(array $event): void
    {
        $reference = $event['data']['reference'];
        $metadata = $event['data']['metadata'] ?? [];
        $applicationId = $metadata['application_id'] ?? null;

        if (!$applicationId) {
            throw new Exception('Application ID not found in metadata');
        }

        $pdo = Database::getConnection();
        
        // Update payment record
        $stmt = $pdo->prepare('
            UPDATE payments 
            SET status = ?, gateway_response = ?, paid_at = NOW(), updated_at = NOW()
            WHERE transaction_id = ?
        ');
        $stmt->execute([
            'completed',
            json_encode($event['data']),
            $reference
        ]);

        // Update application status
        $trackingNumber = 'APP-' . strtoupper(uniqid());
        $stmt = $pdo->prepare('
            UPDATE applications 
            SET status = "submitted", tracking_number = ?, submitted_at = NOW() 
            WHERE id = ?
        ');
        $stmt->execute([$trackingNumber, $applicationId]);

        $this->logger->logInfo('Payment processed via webhook', [
            'application_id' => $applicationId,
            'reference' => $reference
        ]);
    }

    /**
     * Process failed payment
     *
     * @param array $event
     * @return void
     */
    private function processFailedPayment(array $event): void
    {
        $reference = $event['data']['reference'];
        
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('
            UPDATE payments 
            SET status = ?, gateway_response = ?, updated_at = NOW()
            WHERE transaction_id = ?
        ');
        $stmt->execute(['failed', json_encode($event['data']), $reference]);

        $this->logger->logWarning('Payment failed', ['reference' => $reference]);
    }

    /**
     * Send payment confirmation email
     *
     * @param int $applicationId
     * @param array $transactionData
     * @return void
     */
    private function sendPaymentConfirmation($applicationId, $transactionData): void
    {
        try {
            $pdo = Database::getConnection();
            $stmt = $pdo->prepare('
                SELECT u.email, u.first_name, u.last_name, a.tracking_number
                FROM users u
                JOIN applications a ON u.id = a.user_id
                WHERE a.id = ?
            ');
            $stmt->execute([$applicationId]);
            $user = $stmt->fetch();

            if ($user) {
                $emailService = new EmailService(
                    new \App\Services\BlockchainService(),
                    LoggingService::getInstance()
                );
                
                $fullName = $user['first_name'] . ' ' . $user['last_name'];
                $emailService->sendApplicationStatusEmail(
                    $user['email'],
                    $fullName,
                    $user['tracking_number'],
                    'payment_completed'
                );
            }
        } catch (Exception $e) {
            $this->logger->logError('Failed to send payment confirmation', [
                'error' => $e->getMessage(),
                'application_id' => $applicationId
            ]);
        }
    }
}
