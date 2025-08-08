<?php

namespace App\Controllers;

use App\Database\Database;
use App\Services\EmailService;
use App\Services\LoggingService;
use Exception;

class PaymentController
{
    private $paystackPublicKey;
    private $paystackSecretKey;
    private $paymentAmount;

    public function __construct()
    {
        // Initialize Paystack configuration
        $this->paystackPublicKey = $_ENV['PAYSTACK_PUBLIC_KEY'] ?? 'pk_test_xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx';
        $this->paystackSecretKey = $_ENV['PAYSTACK_SECRET_KEY'] ?? 'sk_test_xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx';
        $this->paymentAmount = $_ENV['PAYMENT_AMOUNT'] ?? 15000; // GH₵150.00 in kobo/pesewas
    }

    /**
     * Display payment page for the given application.
     *
     * @param int $applicationId
     * @return void
     */
    public function pay($applicationId): void
    {
        // Ensure user is logged in
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }

        $pdo = Database::getConnection();

        // Prefer birth_applications, fallback to applications for backward compatibility
        $stmt = $pdo->prepare('SELECT * FROM birth_applications WHERE id = ?');
        $stmt->execute([$applicationId]);
        $application = $stmt->fetch();

        if (!$application) {
            $stmt = $pdo->prepare('SELECT * FROM applications WHERE id = ?');
            $stmt->execute([$applicationId]);
            $application = $stmt->fetch();
        }

        if (!$application) {
            header('Location: /applications/submit');
            exit;
        }

        // Determine payment amount (from config or default)
        $amount = $this->paymentAmount / 100; // Convert to GH₵
        $paystackPublicKey = $this->paystackPublicKey;
        
        // Get user details for payment
        $stmt = $pdo->prepare('SELECT * FROM users WHERE id = ?');
        $stmt->execute([$application['user_id']]);
        $user = $stmt->fetch();
        
        include BASE_PATH . '/resources/views/applications/pay.php';
    }

    /**
     * Handle payment initialization for Paystack
     * 
     * @param int $applicationId
     * @return void
     */
    public function initializePayment($applicationId): void 
    {
        try {
            // Ensure user is owner or admin/registrar
            if (!isset($_SESSION['user_id'])) {
                http_response_code(401);
                echo json_encode(['success' => false, 'error' => 'Unauthorized']);
                return;
            }

            $pdo = Database::getConnection();

            // Try birth_applications first
            $stmt = $pdo->prepare('SELECT a.*, u.email, u.first_name, u.last_name FROM birth_applications a JOIN users u ON a.user_id = u.id WHERE a.id = ?');
            $stmt->execute([$applicationId]);
            $application = $stmt->fetch();

            if (!$application) {
                // Fallback to legacy applications table
                $stmt = $pdo->prepare('SELECT a.*, u.email, u.first_name, u.last_name FROM applications a JOIN users u ON a.user_id = u.id WHERE a.id = ?');
                $stmt->execute([$applicationId]);
                $application = $stmt->fetch();
            }

            if (!$application) {
                http_response_code(404);
                echo json_encode(['success' => false, 'error' => 'Application not found']);
                return;
            }

            // Generate a unique reference
            $reference = 'BCS-'.time().'-'.uniqid();
            
            // Prepare the Paystack API request
            $url = "https://api.paystack.co/transaction/initialize";
            $fields = [
                'email' => $application['email'],
                'amount' => $this->paymentAmount,
                'reference' => $reference,
                'callback_url' => ($_ENV['APP_URL'] ?? (isset($_SERVER['HTTP_HOST']) ? 'http://' . $_SERVER['HTTP_HOST'] : '')) . "/applications/{$applicationId}/payment-callback",
                'metadata' => [
                    'application_id' => $applicationId,
                    'user_id' => $application['user_id'],
                    'full_name' => $application['first_name'] . ' ' . $application['last_name']
                ]
            ];

            // Optional: nudge Paystack to show preferred method first
            $input = json_decode(file_get_contents('php://input'), true);
            $paymentMethod = $input['payment_method'] ?? null;
            if ($paymentMethod === 'mobile-money') {
                $fields['channels'] = ['mobile_money'];
            } elseif ($paymentMethod === 'card') {
                $fields['channels'] = ['card'];
            }
            
            $headers = [
                'Authorization: Bearer ' . $this->paystackSecretKey,
                'Content-Type: application/json',
            ];
            
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            
            $response = curl_exec($ch);
            
            if (curl_error($ch)) {
                throw new Exception(curl_error($ch));
            }
            
            curl_close($ch);
            $result = json_decode($response, true);
            
            if (!$result['status']) {
                throw new Exception($result['message']);
            }
            
            // Store payment reference in database
            $stmt = $pdo->prepare(
                'INSERT INTO payments (application_id, amount, currency, transaction_id, status, payment_gateway) 
                VALUES (?, ?, ?, ?, ?, ?)'
            );
            $stmt->execute([$applicationId, $this->paymentAmount / 100, 'GHS', $reference, 'pending', 'paystack']);
            
            // Return the authorization URL
            echo json_encode(['success' => true, 'data' => $result['data']]);
            
        } catch (Exception $e) {
            error_log('Payment initialization error: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    /**
     * Handle payment gateway webhook callback.
     *
     * @param int $applicationId
     * @return void
     */
    public function callback($applicationId): void
    {
        try {
            // For Paystack, verify the transaction using the reference
            $reference = $_GET['reference'] ?? '';
            if (empty($reference)) {
                throw new Exception('No reference supplied');
            }
            
            // Verify the transaction
            $result = $this->verifyPaystackTransaction($reference);
            
            if (!$result['status']) {
                throw new Exception('Payment verification failed');
            }
            
            // Payment was successful, update database
            $pdo = Database::getConnection();
            
            // Update payment record
            $stmt = $pdo->prepare(
                'UPDATE payments SET status = ?, transaction_id = ?, updated_at = NOW() 
                WHERE transaction_id = ?'
            );
            $stmt->execute(['completed', $reference, $reference]);
            
            // Determine which applications table to update
            $trackingNumber = 'APP-' . strtoupper(uniqid());

            $updated = false;
            $stmt = $pdo->prepare('SELECT id FROM birth_applications WHERE id = ?');
            $stmt->execute([$applicationId]);
            if ($stmt->fetch()) {
                $stmt = $pdo->prepare(
                    'UPDATE birth_applications SET status = "submitted", tracking_number = ?, submitted_at = NOW() WHERE id = ?'
                );
                $stmt->execute([$trackingNumber, $applicationId]);
                $updated = true;
            }

            if (!$updated) {
                $stmt = $pdo->prepare(
                    'UPDATE applications SET status = "submitted", tracking_number = ?, submitted_at = NOW() WHERE id = ?'
                );
                $stmt->execute([$trackingNumber, $applicationId]);
            }
            
            // Fetch user details
            $stmt = $pdo->prepare(
                'SELECT u.email, u.first_name, u.last_name FROM users u JOIN (
                    SELECT user_id FROM birth_applications WHERE id = ?
                    UNION ALL
                    SELECT user_id FROM applications WHERE id = ?
                ) a ON u.id = a.user_id LIMIT 1'
            );
            $stmt->execute([$applicationId, $applicationId]);
            $user = $stmt->fetch();
            
            // Send confirmation email
            $emailService = new EmailService(
                new \App\Services\BlockchainService(),
                \App\Services\LoggingService::getInstance()
            );
            $fullName = $user['first_name'] . ' ' . $user['last_name'];
            $emailService->sendApplicationStatusEmail(
                $user['email'], $fullName, $trackingNumber, 'submitted'
            );
            
            // Redirect to tracking page
            $_SESSION['success'] = 'Payment successful! Your application has been submitted.';
            header("Location: /track/{$trackingNumber}");
            exit;
            
        } catch (Exception $e) {
            error_log('Payment callback error: ' . $e->getMessage());
            $_SESSION['error'] = 'Payment verification failed: ' . $e->getMessage();
            header("Location: /applications/{$applicationId}/pay");
            exit;
        }
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
        $headers = [
            'Authorization: Bearer ' . $this->paystackSecretKey
        ];
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        
        $response = curl_exec($ch);
        
        if (curl_error($ch)) {
            throw new Exception(curl_error($ch));
        }
        
        curl_close($ch);
        return json_decode($response, true);
    }
    
    /**
     * Handle Paystack webhook (for server-to-server confirmation)
     * 
     * @return void
     */
    public function webhook(): void
    {
        // Retrieve the request body and parse it as JSON
        $input = file_get_contents('php://input');
        $event = json_decode($input, true);
        
        // Verify that this is a Paystack webhook
        if (!$event || !isset($event['event'])) {
            http_response_code(400);
            exit();
        }
        
        http_response_code(200);
        
        // Handle the webhook event
        if ($event['event'] === 'charge.success') {
            $reference = $event['data']['reference'];
            $metadata = $event['data']['metadata'] ?? [];
            $applicationId = $metadata['application_id'] ?? null;
            
            if ($applicationId) {
                try {
                    // Update payment and application status
                    $pdo = Database::getConnection();
                    
                    // Update payment record
                    $stmt = $pdo->prepare(
                        'UPDATE payments SET status = ?, updated_at = NOW() 
                        WHERE transaction_id = ?'
                    );
                    $stmt->execute(['completed', $reference]);
                    
                    // Generate tracking number and mark application submitted
                    $trackingNumber = 'APP-' . strtoupper(uniqid());

                    $updated = false;
                    $stmt = $pdo->prepare('SELECT id FROM birth_applications WHERE id = ?');
                    $stmt->execute([$applicationId]);
                    if ($stmt->fetch()) {
                        $stmt = $pdo->prepare(
                            'UPDATE birth_applications SET status = "submitted", tracking_number = ?, submitted_at = NOW() WHERE id = ?'
                        );
                        $stmt->execute([$trackingNumber, $applicationId]);
                        $updated = true;
                    }

                    if (!$updated) {
                        $stmt = $pdo->prepare(
                            'UPDATE applications SET status = "submitted", tracking_number = ?, submitted_at = NOW() WHERE id = ?'
                        );
                        $stmt->execute([$trackingNumber, $applicationId]);
                    }

                    // Log success
                    LoggingService::getInstance()->logInfo(
                        'Paystack webhook processed successfully',
                        ['reference' => $reference, 'application_id' => $applicationId]
                    );
                } catch (Exception $e) {
                    LoggingService::getInstance()->logError(
                        'Paystack webhook processing error',
                        ['message' => $e->getMessage(), 'reference' => $reference]
                    );
                }
            }
        }
        
        exit();
    }
} 