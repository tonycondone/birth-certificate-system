<?php
$pageTitle = 'Provide Feedback';
require_once __DIR__ . '/../layouts/base.php';
?>

<div class="container my-5">
    <h2>Leave Feedback</h2>
    <?php if (!empty($_SESSION['success_message'])): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($_SESSION['success_message']); unset($_SESSION['success_message']); ?></div>
    <?php endif; ?>
    <?php if (!empty($_SESSION['error_message'])): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($_SESSION['error_message']); unset($_SESSION['error_message']); ?></div>
    <?php endif; ?>
    <form action="/applications/feedback/store" method="POST">
        <input type="hidden" name="application_id" value="<?= htmlspecialchars($applicationId) ?>">
        <div class="mb-3">
            <label for="rating" class="form-label">Rating (1–5)</label>
            <select class="form-select" id="rating" name="rating" required>
                <option value="">Select rating</option>
                <?php for ($i = 1; $i <= 5; $i++): ?>
                    <option value="<?= $i ?>"><?= $i ?></option>
                <?php endfor; ?>
            </select>
        </div>
        <div class="mb-3">
            <label for="comments" class="form-label">Comments</label>
            <textarea class="form-control" id="comments" name="comments" rows="4"></textarea>
        </div>
        <button type="submit" class="btn btn-primary">Submit Feedback</button>
    </form>
</div> 