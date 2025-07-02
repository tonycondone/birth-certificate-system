<?php

namespace App\Controllers;

use App\Database\Database;

class GenericApplicationController
{
    /**
     * Show the application submission form.
     */
    public function create()
    {
        // User must be logged in to submit an application
        if (!isset($_SESSION['user'])) {
            header('Location: /login');
            exit;
        }

        require_once BASE_PATH . '/resources/views/applications/submit.php';
    }

    /**
     * Store a new application.
     */
    public function store()
    {
        // User must be logged in
        if (!isset($_SESSION['user'])) {
            header('Location: /login');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo 'Method Not Allowed';
            exit;
        }

        $purpose = trim($_POST['purpose'] ?? '');
        $description = trim($_POST['description'] ?? '');

        if (empty($purpose) || empty($description)) {
            $_SESSION['error_message'] = 'Purpose and description are required.';
            header('Location: /applications/submit');
            exit;
        }

        try {
            $pdo = Database::getConnection();
            $user_id = $_SESSION['user']['id'];
            $reference_number = 'APP-' . strtoupper(uniqid());

            $stmt = $pdo->prepare(
                'INSERT INTO applications (user_id, reference_number, purpose, description) VALUES (?, ?, ?, ?)'
            );
            $stmt->execute([$user_id, $reference_number, $purpose, $description]);
            $applicationId = $pdo->lastInsertId();

            // Set application to pending payment and record submitted timestamp
            $stmt = $pdo->prepare(
                'UPDATE applications SET status = "pending_payment", submitted_at = NOW(), tracking_number = CONCAT("APP-", UPPER(uniqid())) WHERE id = ?'
            );
            $stmt->execute([$applicationId]);

            // Redirect to payment page
            header('Location: /applications/' . $applicationId . '/pay');
            exit;

        } catch (\PDOException $e) {
            // Log the actual error to the server's error log for debugging
            error_log('PDOException: ' . $e->getMessage());
            // Set a user-friendly error message
            $_SESSION['error_message'] = 'There was a critical error submitting your application. Please contact support. Details: ' . $e->getMessage();
        } catch (\Exception $e) {
            error_log('Exception: ' . $e->getMessage());
            $_SESSION['error_message'] = 'An unexpected error occurred. Please try again. Details: ' . $e->getMessage();
        }

        // Redirect back to the submission form
        header('Location: /applications/submit');
        exit;
    }
} 