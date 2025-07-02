<?php
$pageTitle = 'Track Application ' . htmlspecialchars($application['tracking_number']);
require_once __DIR__ . '/../layouts/base.php';
?>

<div class="container my-5">
    <h2>Track Application</h2>
    <table class="table">
        <tr><th>Tracking Number</th><td><?= htmlspecialchars($application['tracking_number']) ?></td></tr>
        <tr><th>Status</th><td><?= htmlspecialchars(ucfirst($application['status'])) ?></td></tr>
        <tr><th>Submitted</th><td><?= htmlspecialchars($application['submitted_at'] ?? 'N/A') ?></td></tr>
        <tr><th>Payment Status</th><td><?= htmlspecialchars(ucfirst($application['payment_status'] ?? 'pending')) ?></td></tr>
        <tr><th>Transaction ID</th><td><?= htmlspecialchars($application['transaction_id'] ?? 'N/A') ?></td></tr>
    </table>
    <?php if ($application['status'] === 'approved'): ?>
        <a href="/certificates/download/<?= htmlspecialchars($application['id']) ?>" class="btn btn-primary">Download Certificate</a>
    <?php endif; ?>
</div> 