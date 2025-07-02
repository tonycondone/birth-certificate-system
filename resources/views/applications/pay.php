<?php
$pageTitle = 'Payment - Application ' . htmlspecialchars($application['tracking_number']);
require_once __DIR__ . '/../layouts/base.php';
?>

<div class="container my-5">
    <h2>Application Payment</h2>
    <p>Please pay <strong>$<?= number_format($amount, 2) ?></strong> to complete your application.</p>
    <button id="payButton" class="btn btn-success">Pay Now</button>
</div>

<script>
// Simulate payment process and webhook callback
document.getElementById('payButton').addEventListener('click', () => {
    fetch('/applications/<?= $application['id'] ?>/payment-callback', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ application_id: <?= $application['id'] ?>, status: 'completed', transaction_id: 'TXN' + Date.now() })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            window.location.href = '/track/<?= $application['tracking_number'] ?>';
        } else {
            alert('Payment failed: ' + data.error);
        }
    });
});
</script> 