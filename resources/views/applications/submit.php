<?php
$pageTitle = 'Submit Application';
require_once __DIR__ . '/../layouts/base.php';
?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h2>Submit a New Application</h2>
                </div>
                <div class="card-body">
                    <?php if (isset($_SESSION['success_message'])): ?>
                        <div class="alert alert-success">
                            <?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?>
                        </div>
                    <?php endif; ?>
                    <?php if (isset($_SESSION['error_message'])): ?>
                        <div class="alert alert-danger">
                            <?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?>
                        </div>
                    <?php endif; ?>
                    <form action="/applications/submit/store" method="POST">
                        <div class="mb-3">
                            <label for="purpose" class="form-label">Purpose of Application</label>
                            <input type="text" class="form-control" id="purpose" name="purpose" required>
                        </div>
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="5" required></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">Submit Application</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div> 