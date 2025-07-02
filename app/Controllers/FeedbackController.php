<?php

namespace App\Controllers;

use App\Database\Database;
use Exception;

class FeedbackController
{
    /**
     * Display feedback form for a given application.
     *
     * @param int $applicationId
     * @return void
     */
    public function create($applicationId): void
    {
        if (!isset($_SESSION['user'])) {
            header('Location: /login');
            exit;
        }

        include BASE_PATH . '/resources/views/applications/feedback.php';
    }

    /**
     * Store submitted feedback.
     *
     * @return void
     */
    public function store(): void
    {
        if (!isset($_SESSION['user'])) {
            header('Location: /login');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /');
            exit;
        }

        $userId = $_SESSION['user']['id'];
        $applicationId = $_POST['application_id'] ?? null;
        $rating = intval($_POST['rating'] ?? 0);
        $comments = trim($_POST['comments'] ?? '');

        try {
            $pdo = Database::getConnection();
            $stmt = $pdo->prepare(
                'INSERT INTO feedback (user_id, application_id, rating, comments) VALUES (?, ?, ?, ?)'
            );
            $stmt->execute([$userId, $applicationId, $rating, $comments]);

            $_SESSION['success_message'] = 'Thank you for your feedback!';
        } catch (Exception $e) {
            error_log('Feedback error: ' . $e->getMessage());
            $_SESSION['error_message'] = 'Failed to submit feedback.';
        }

        header('Location: /applications/' . $applicationId);
        exit;
    }
} 