<?php
require_once __DIR__ . '/../partials/header.php';
require_once __DIR__ . '/../../app/Services/AuthService.php';

$authService = new \App\Services\AuthService();
$user = $authService->getCurrentUser();

if (!$user) {
    header('Location: /login');
    exit;
}
?>

<div class="container mt-5">
    <div class="row">
        <div class="col-md-6 offset-md-3">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h3>User Profile</h3>
                </div>
                <div class="card-body">
                    <form id="profileForm" method="POST" action="/update-profile">
                        <div class="form-group mb-3">
                            <label for="fullName">Full Name</label>
                            <input 
                                type="text" 
                                class="form-control" 
                                id="fullName" 
                                name="full_name" 
                                value="<?= htmlspecialchars($user['full_name'] ?? '') ?>"
                            >
                        </div>

                        <div class="form-group mb-3">
                            <label for="email">Email Address</label>
                            <input 
                                type="email" 
                                class="form-control" 
                                id="email" 
                                name="email" 
                                value="<?= htmlspecialchars($user['email'] ?? '') ?>"
                                readonly
                            >
                        </div>

                        <div class="form-group mb-3">
                            <label for="phoneNumber">Phone Number</label>
                            <input 
                                type="tel" 
                                class="form-control" 
                                id="phoneNumber" 
                                name="phone_number" 
                                value="<?= htmlspecialchars($user['phone_number'] ?? '') ?>"
                            >
                        </div>

                        <div class="form-group mb-3">
                            <label for="address">Address</label>
                            <textarea 
                                class="form-control" 
                                id="address" 
                                name="address" 
                                rows="3"
                            ><?= htmlspecialchars($user['address'] ?? '') ?></textarea>
                        </div>

                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">Update Profile</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>

<script src="/js/profile.js"></script> 