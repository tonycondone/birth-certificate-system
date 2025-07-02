<?php

namespace App\Controllers;

use App\Database\Database;

class TrackingController
{
    /**
     * Display tracking status for a given tracking number.
     *
     * @param string $trackingNumber
     * @return void
     */
    public function show($trackingNumber): void
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare(
            'SELECT a.*, p.status as payment_status, p.transaction_id, p.created_at as payment_date
             FROM applications a
             LEFT JOIN payments p ON a.id = p.application_id
             WHERE a.tracking_number = ?'
        );
        $stmt->execute([$trackingNumber]);
        $application = $stmt->fetch();

        if (!$application) {
            http_response_code(404);
            include BASE_PATH . '/resources/views/errors/404.php';
            exit;
        }

        include BASE_PATH . '/resources/views/applications/track.php';
    }

    /**
     * Display tracking lookup form
     * @return void
     */
    public function form(): void
    {
        include BASE_PATH . '/resources/views/applications/track_form.php';
    }

    /**
     * Handle tracking lookup form submission
     * @return void
     */
    public function search(): void
    {
        $trackingNumber = trim($_POST['tracking_number'] ?? '');
        if (empty($trackingNumber)) {
            $_SESSION['error_message'] = 'Please enter a tracking number.';
            header('Location: /track');
            exit;
        }
        header('Location: /track/' . urlencode($trackingNumber));
        exit;
    }
} 