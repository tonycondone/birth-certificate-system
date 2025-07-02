<?php

namespace App\Controllers;

use App\Database\Database;
use App\Services\EmailService;
use App\Services\LoggingService;
use Exception;

class PaymentController
{
    /**
     * Display payment page for the given application.
     *
     * @param int $applicationId
     * @return void
     */
    public function pay($applicationId): void
    {
        // Ensure user is owner or admin/registrar
        if (!isset($_SESSION['user'])) {
            header('Location: /login');
            exit;
        }

        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('SELECT * FROM applications WHERE id = ?');
        $stmt->execute([$applicationId]);
        $application = $stmt->fetch();

        if (!$application) {
            header('Location: /applications/submit');
            exit;
        }

        // Determine payment amount (from config or default)
        $amount = $_ENV['APPLICATION_FEE'] ?? 0;
        
        include BASE_PATH . '/resources/views/applications/pay.php';
    }

    /**
     * Handle payment gateway webhook callback.
     *
     * @return void
     */
    public function callback(): void
    {
        // Read JSON payload
        $payload = json_decode(file_get_contents('php://input'), true);
        $applicationId = $payload['application_id'] ?? null;
        $status = $payload['status'] ?? 'failed';
        $transactionId = $payload['transaction_id'] ?? '';

        if (!$applicationId) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Invalid payload']);
            return;
        }

        try {
            $pdo = Database::getConnection();
            // Update or insert payment record
            $stmt = $pdo->prepare('SELECT * FROM payments WHERE application_id = ?');
            $stmt->execute([$applicationId]);
            $payment = $stmt->fetch();

            $amount = $_ENV['APPLICATION_FEE'] ?? 0;
            if ($payment) {
                $stmt = $pdo->prepare(
                    'UPDATE payments SET status = ?, transaction_id = ?, updated_at = NOW() WHERE id = ?'
                );
                $stmt->execute([$status, $transactionId, $payment['id']]);
            } else {
                $stmt = $pdo->prepare(
                    'INSERT INTO payments (application_id, amount, currency, transaction_id, status) VALUES (?, ?, ?, ?, ?)'
                );
                $stmt->execute([$applicationId, $amount, 'USD', $transactionId, $status]);
            }

            if ($status === 'completed') {
                // Generate tracking number and mark application submitted
                $trackingNumber = 'APP-' . strtoupper(uniqid());
                $stmt = $pdo->prepare(
                    'UPDATE applications SET status = "submitted", tracking_number = ?, submitted_at = NOW() WHERE id = ?'
                );
                $stmt->execute([$trackingNumber, $applicationId]);

                // Fetch user details
                $stmt = $pdo->prepare(
                    'SELECT u.email, u.first_name, u.last_name FROM users u JOIN applications a ON u.id = a.user_id WHERE a.id = ?'
                );
                $stmt->execute([$applicationId]);
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
            }

            echo json_encode(['success' => true]);
        } catch (Exception $e) {
            error_log('Payment callback error: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    }
} 