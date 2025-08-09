<?php

namespace App\Controllers;

use App\Database\Database;
use Exception;

class AdminAuditController
{
    private $db;

    public function __construct()
    {
        try { $this->db = Database::getConnection(); } catch (Exception $e) { $this->db = null; }
    }

    public function overrides()
    {
        if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
            header('Location: /login');
            exit;
        }
        $pageTitle = 'Override & Refund Audit';
        $rows = [];
        try {
            // activity_log entries for overrides and refunds
            $stmt = $this->db->prepare("SELECT * FROM activity_log WHERE action IN ('override_reinstate_approve','override_reject_refund','payment_refund') ORDER BY created_at DESC LIMIT 500");
            $stmt->execute();
            $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            $rows = [];
        }
        include BASE_PATH . '/resources/views/admin/audit_overrides.php';
    }
} 