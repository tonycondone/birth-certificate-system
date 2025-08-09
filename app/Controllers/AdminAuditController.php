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

    public function schema()
    {
        if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
            header('Location: /login');
            exit;
        }
        $pageTitle = 'Audit Schema Inspector';
        $tables = ['payments', 'activity_log'];
        $schema = [];
        try {
            foreach ($tables as $t) {
                $stmt = $this->db->query("SHOW TABLES LIKE '" . $t . "'");
                $exists = $stmt && $stmt->rowCount() > 0;
                $schema[$t] = ['exists' => $exists, 'columns' => []];
                if ($exists) {
                    $desc = $this->db->query("DESCRIBE `{$t}`");
                    $cols = $desc ? $desc->fetchAll(\PDO::FETCH_ASSOC) : [];
                    foreach ($cols as $c) {
                        $schema[$t]['columns'][$c['Field']] = [
                            'type' => $c['Type'],
                            'null' => $c['Null'],
                            'key' => $c['Key'],
                            'default' => $c['Default'],
                            'extra' => $c['Extra'],
                        ];
                    }
                }
            }
        } catch (Exception $e) {
            // ignore
        }
        include BASE_PATH . '/resources/views/admin/audit_schema.php';
    }
} 