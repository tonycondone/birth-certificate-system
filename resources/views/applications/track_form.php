<?php
$pageTitle = 'Track Your Application';
require_once __DIR__ . '/../layouts/base.php';
?>

<div class="container my-5">
    <h2>Track Your Application</h2>
    <?php if (!empty($_SESSION['error_message'])): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($_SESSION['error_message']); unset($_SESSION['error_message']); ?></div>
    <?php endif; ?>
    <form action="/track/search" method="POST" class="row g-3">
        <div class="col-md-6">
            <label for="tracking_number" class="form-label">Tracking Number</label>
            <input type="text" name="tracking_number" id="tracking_number" class="form-control" placeholder="Enter your tracking number" required>
        </div>
        <div class="col-md-6 align-self-end">
            <button type="submit" class="btn btn-primary mb-3">Track</button>
        </div>
    </form>
</div> 