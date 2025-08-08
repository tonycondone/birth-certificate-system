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
        // Start output buffer to capture stray warnings/notices
        ob_start();
        $prevDisplay = ini_get('display_errors');
        ini_set('display_errors', '0');
        
        $safeJson = function (int $httpCode, array $data) use ($prevDisplay) {
            $payload = json_encode($data);
            if (ob_get_length()) { ob_end_clean(); } else { ob_end_clean(); }
            if (!headers_sent()) { header('Content-Type: application/json'); }
            http_response_code($httpCode);
            echo $payload;
            // Restore display_errors
            ini_set('display_errors', $prevDisplay);
            exit;
        };
        
        // basic file logging for diagnostics (very first line)
        $logDir = BASE_PATH . '/storage/logs';
        if (!is_dir($logDir)) { @mkdir($logDir, 0775, true); }
        @file_put_contents($logDir . '/payments.log', "HIT initializePayment app={$applicationId} time=".date('c')."\n", FILE_APPEND);
        try {
            
            if (!isset($_SESSION['user_id'])) {
                $safeJson(401, ['success' => false, 'error' => 'Unauthorized']);
            }

            $pdo = Database::getConnection();
            
            // Fetch from birth_applications first, fallback to applications
            $stmt = $pdo->prepare('SELECT a.*, u.email, u.first_name, u.last_name FROM birth_applications a JOIN users u ON a.user_id = u.id WHERE a.id = ?');
            $stmt->execute([$applicationId]);
            $application = $stmt->fetch();
            if (!$application) {
                $stmt = $pdo->prepare('SELECT a.*, u.email, u.first_name, u.last_name FROM applications a JOIN users u ON a.user_id = u.id WHERE a.id = ?');
                $stmt->execute([$applicationId]);
                $application = $stmt->fetch();
            }

            if (!$application) {
                $safeJson(404, ['success' => false, 'error' => 'Application not found']);
            }

            $reference = 'BCS-'.time().'-'.uniqid();
            $appUrl = $_ENV['APP_URL'] ?? (isset($_SERVER['HTTP_HOST']) ? 'http://' . $_SERVER['HTTP_HOST'] : 'http://localhost:8000');

            $url = "https://api.paystack.co/transaction/initialize";
            $fields = [
                'email' => $application['email'],
                'amount' => $this->paymentAmount,
                'currency' => 'GHS',
                'reference' => $reference,
                'callback_url' => rtrim($appUrl, '/') . "/applications/{$applicationId}/payment-callback",
                'metadata' => [
                    'application_id' => $applicationId,
                    'user_id' => $application['user_id'],
                    'full_name' => trim(($application['first_name'] ?? '') . ' ' . ($application['last_name'] ?? ''))
                ]
            ];

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
                'Accept: application/json',
            ];

            if (!function_exists('curl_init')) {
                @file_put_contents($logDir . '/payments.log', "ERROR: PHP cURL extension not enabled\n", FILE_APPEND);
                $safeJson(500, ['success' => false, 'error' => 'Server missing PHP cURL extension. Please enable php_curl in php.ini and restart the server.']);
            }

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $response = curl_exec($ch);

            // Sanity check keys
            if (empty($this->paystackSecretKey) || stripos($this->paystackSecretKey, 'sk_') !== 0) {
                error_log('Paystack secret key looks invalid or missing');
            }

            // basic file logging for diagnostics
            if (@file_put_contents($logDir . '/payments.log', "INIT fields=" . json_encode($fields) . "\n", FILE_APPEND) === false) {
                error_log('payments.log write failed: ' . json_encode($fields));
            }

            if ($response === false) {
                $err = curl_error($ch);
                if (@file_put_contents($logDir . '/payments.log', "cURL error=" . $err . "\n", FILE_APPEND) === false) {
                    error_log('Paystack init cURL error: ' . $err);
                }
                error_log('Paystack init cURL error: ' . $err);
                curl_close($ch);
                $safeJson(502, ['success' => false, 'error' => 'Network error initializing payment']);
            }

            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            $result = json_decode($response, true);
            if (@file_put_contents($logDir . '/payments.log', "HTTP=".$httpCode." RESP=".$response."\n", FILE_APPEND) === false) {
                error_log('Paystack init HTTP='.$httpCode.' RESP='.$response);
            }

            if ($httpCode >= 400 || !$result || ($result['status'] ?? false) !== true) {
                $message = $result['message'] ?? ('Paystack error (' . $httpCode . ')');
                // Include any buffered PHP warnings to help diagnose
                $buffered = trim(ob_get_contents() ?: '');
                if ($buffered !== '') { $message .= ' | debug: ' . strip_tags($buffered); }
                error_log('Paystack init error: ' . $message);
                $safeJson(500, ['success' => false, 'error' => $message]);
            }

            // Persist payment reference (support both legacy and unified schemas)
            try {
                $stmt = $pdo->prepare(
                    'INSERT INTO payments (application_id, user_id, amount, currency, transaction_id, status, payment_gateway) 
                     VALUES (?, ?, ?, ?, ?, ?, ?)'
                );
                $stmt->execute([$applicationId, $application['user_id'], $this->paymentAmount / 100, 'GHS', $reference, 'pending', 'paystack']);
            } catch (Exception $e) {
                // Fallback to legacy schema without user_id
                error_log('Payments insert with user_id failed, retrying legacy schema: ' . $e->getMessage());
                $stmt = $pdo->prepare(
                    'INSERT INTO payments (application_id, amount, currency, transaction_id, status, payment_gateway) 
                     VALUES (?, ?, ?, ?, ?, ?)'
                );
                $stmt->execute([$applicationId, $this->paymentAmount / 100, 'GHS', $reference, 'pending', 'paystack']);
            }

            $safeJson(200, ['success' => true, 'data' => $result['data']]);
        } catch (Exception $e) {
            error_log('Payment initialization exception: ' . $e->getMessage());
            $buffered = trim(ob_get_contents() ?: '');
            $safeJson(500, ['success' => false, 'error' => 'Initialization failed' . ($buffered ? (' | debug: ' . strip_tags($buffered)) : '')]);
        } finally {
            // nothing; handled in $safeJson
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