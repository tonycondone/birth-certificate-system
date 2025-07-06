<?php

namespace App\Controllers;

use App\Database\Database;
use Exception;

/**
 * UserController
 * 
 * Handles user profile and account management
 */
class UserController
{
    /**
     * Show user profile
     */
    public function profile()
    {
        // Check if user is logged in
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }
        
        try {
            $pdo = Database::getConnection();
            $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
            $stmt->execute([$_SESSION['user_id']]);
            $user = $stmt->fetch();
            
            if (!$user) {
                $_SESSION['error'] = 'User not found';
                header('Location: /dashboard');
                exit;
            }
            
            $pageTitle = 'Profile - Digital Birth Certificate System';
            
            // Handle profile update
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $this->updateProfile($pdo, $user);
                return;
            }
            
            // Include profile view or show fallback
            $viewPath = BASE_PATH . '/resources/views/profile.php';
            if (file_exists($viewPath)) {
                include $viewPath;
            } else {
                echo $this->getProfileForm($user);
            }
            
        } catch (Exception $e) {
            error_log("Profile error: " . $e->getMessage());
            $_SESSION['error'] = 'Unable to load profile';
            header('Location: /dashboard');
            exit;
        }
    }
    
    /**
     * Show user settings
     */
    public function settings()
    {
        // Check if user is logged in
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }
        
        $pageTitle = 'Settings - Digital Birth Certificate System';
        
        // Simple settings page
        echo "<!DOCTYPE html>
        <html>
        <head>
            <title>$pageTitle</title>
            <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css' rel='stylesheet'>
        </head>
        <body class='bg-light'>
            <div class='container mt-5'>
                <div class='row'>
                    <div class='col-md-8'>
                        <div class='card'>
                            <div class='card-header'>
                                <h4>Account Settings</h4>
                            </div>
                            <div class='card-body'>
                                <div class='list-group'>
                                    <a href='/profile' class='list-group-item list-group-item-action'>
                                        <h5>Profile Information</h5>
                                        <p class='mb-1'>Update your personal information</p>
                                    </a>
                                    <a href='#' class='list-group-item list-group-item-action'>
                                        <h5>Change Password</h5>
                                        <p class='mb-1'>Update your account password</p>
                                    </a>
                                    <a href='#' class='list-group-item list-group-item-action'>
                                        <h5>Notification Preferences</h5>
                                        <p class='mb-1'>Manage your notification settings</p>
                                    </a>
                                    <a href='/user/delete-account' class='list-group-item list-group-item-action text-danger'>
                                        <h5>Delete Account</h5>
                                        <p class='mb-1'>Permanently delete your account</p>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class='mt-3'>
                    <a href='/dashboard' class='btn btn-secondary'>Back to Dashboard</a>
                </div>
            </div>
        </body>
        </html>";
    }
    
    /**
     * Delete user account
     */
    public function deleteAccount()
    {
        // Check if user is logged in
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }
        
        $pageTitle = 'Delete Account - Digital Birth Certificate System';
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->processAccountDeletion();
            return;
        }
        
        // Show confirmation form
        echo "<!DOCTYPE html>
        <html>
        <head>
            <title>$pageTitle</title>
            <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css' rel='stylesheet'>
        </head>
        <body class='bg-light'>
            <div class='container mt-5'>
                <div class='row justify-content-center'>
                    <div class='col-md-6'>
                        <div class='card border-danger'>
                            <div class='card-header bg-danger text-white'>
                                <h4>Delete Account</h4>
                            </div>
                            <div class='card-body'>
                                <div class='alert alert-warning'>
                                    <strong>Warning!</strong> This action cannot be undone. All your data will be permanently deleted.
                                </div>
                                <form method='POST'>
                                    <div class='mb-3'>
                                        <label for='password' class='form-label'>Enter your password to confirm:</label>
                                        <input type='password' class='form-control' id='password' name='password' required>
                                    </div>
                                    <div class='mb-3 form-check'>
                                        <input type='checkbox' class='form-check-input' id='confirm' name='confirm' required>
                                        <label class='form-check-label' for='confirm'>
                                            I understand that this action is permanent and cannot be undone
                                        </label>
                                    </div>
                                    <button type='submit' class='btn btn-danger w-100'>Delete My Account</button>
                                </form>
                                <div class='mt-3 text-center'>
                                    <a href='/settings' class='btn btn-secondary'>Cancel</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </body>
        </html>";
    }
    
    /**
     * Update user profile
     */
    private function updateProfile($pdo, $user)
    {
        try {
            $firstName = $_POST['first_name'] ?? '';
            $lastName = $_POST['last_name'] ?? '';
            $phone = $_POST['phone'] ?? '';
            $email = $_POST['email'] ?? '';
            
            // Validate input
            if (empty($firstName) || empty($lastName) || empty($email)) {
                $_SESSION['error'] = 'Please fill in all required fields';
                header('Location: /profile');
                exit;
            }
            
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $_SESSION['error'] = 'Please enter a valid email address';
                header('Location: /profile');
                exit;
            }
            
            // Check if email is already taken by another user
            if ($email !== $user['email']) {
                $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
                $stmt->execute([$email, $user['id']]);
                if ($stmt->fetch()) {
                    $_SESSION['error'] = 'Email address is already in use';
                    header('Location: /profile');
                    exit;
                }
            }
            
            // Update user profile
            $stmt = $pdo->prepare("
                UPDATE users 
                SET first_name = ?, last_name = ?, phone = ?, email = ?, updated_at = NOW() 
                WHERE id = ?
            ");
            $stmt->execute([$firstName, $lastName, $phone, $email, $user['id']]);
            
            // Update session data
            $_SESSION['first_name'] = $firstName;
            $_SESSION['last_name'] = $lastName;
            $_SESSION['email'] = $email;
            
            $_SESSION['success'] = 'Profile updated successfully';
            header('Location: /profile');
            exit;
            
        } catch (Exception $e) {
            error_log("Profile update error: " . $e->getMessage());
            $_SESSION['error'] = 'Failed to update profile';
            header('Location: /profile');
            exit;
        }
    }
    
    /**
     * Process account deletion
     */
    private function processAccountDeletion()
    {
        try {
            $password = $_POST['password'] ?? '';
            $confirm = isset($_POST['confirm']);
            
            if (!$confirm) {
                $_SESSION['error'] = 'You must confirm account deletion';
                header('Location: /user/delete-account');
                exit;
            }
            
            // Verify password
            $pdo = Database::getConnection();
            $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
            $stmt->execute([$_SESSION['user_id']]);
            $user = $stmt->fetch();
            
            if (!$user || !password_verify($password, $user['password'])) {
                $_SESSION['error'] = 'Invalid password';
                header('Location: /user/delete-account');
                exit;
            }
            
            // Delete user account (soft delete)
            $stmt = $pdo->prepare("UPDATE users SET status = 'deleted', deleted_at = NOW() WHERE id = ?");
            $stmt->execute([$_SESSION['user_id']]);
            
            // Log out user
            session_destroy();
            
            // Redirect with message
            session_start();
            $_SESSION['success'] = 'Your account has been deleted successfully';
            header('Location: /');
            exit;
            
        } catch (Exception $e) {
            error_log("Account deletion error: " . $e->getMessage());
            $_SESSION['error'] = 'Failed to delete account';
            header('Location: /user/delete-account');
            exit;
        }
    }
    
    /**
     * Get profile form HTML
     */
    private function getProfileForm($user)
    {
        $error = $_SESSION['error'] ?? '';
        $success = $_SESSION['success'] ?? '';
        unset($_SESSION['error'], $_SESSION['success']);
        
        return "<!DOCTYPE html>
        <html>
        <head>
            <title>Profile - Digital Birth Certificate System</title>
            <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css' rel='stylesheet'>
        </head>
        <body class='bg-light'>
            <div class='container mt-5'>
                <div class='row justify-content-center'>
                    <div class='col-md-8'>
                        <div class='card'>
                            <div class='card-header'>
                                <h4>Profile Information</h4>
                            </div>
                            <div class='card-body'>
                                " . ($error ? "<div class='alert alert-danger'>$error</div>" : '') . "
                                " . ($success ? "<div class='alert alert-success'>$success</div>" : '') . "
                                <form method='POST'>
                                    <div class='row'>
                                        <div class='col-md-6 mb-3'>
                                            <label for='first_name' class='form-label'>First Name</label>
                                            <input type='text' class='form-control' id='first_name' name='first_name' value='" . htmlspecialchars($user['first_name']) . "' required>
                                        </div>
                                        <div class='col-md-6 mb-3'>
                                            <label for='last_name' class='form-label'>Last Name</label>
                                            <input type='text' class='form-control' id='last_name' name='last_name' value='" . htmlspecialchars($user['last_name']) . "' required>
                                        </div>
                                    </div>
                                    <div class='mb-3'>
                                        <label for='email' class='form-label'>Email</label>
                                        <input type='email' class='form-control' id='email' name='email' value='" . htmlspecialchars($user['email']) . "' required>
                                    </div>
                                    <div class='mb-3'>
                                        <label for='phone' class='form-label'>Phone</label>
                                        <input type='tel' class='form-control' id='phone' name='phone' value='" . htmlspecialchars($user['phone'] ?? '') . "'>
                                    </div>
                                    <div class='mb-3'>
                                        <label class='form-label'>Role</label>
                                        <input type='text' class='form-control' value='" . ucfirst($user['role']) . "' readonly>
                                    </div>
                                    <div class='mb-3'>
                                        <label class='form-label'>Member Since</label>
                                        <input type='text' class='form-control' value='" . date('F j, Y', strtotime($user['created_at'])) . "' readonly>
                                    </div>
                                    <button type='submit' class='btn btn-primary'>Update Profile</button>
                                    <a href='/dashboard' class='btn btn-secondary'>Cancel</a>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </body>
        </html>";
    }
}
