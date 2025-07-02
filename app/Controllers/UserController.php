<?php
namespace App\Controllers;

use App\Database\Database;
use Exception;

/**
 * Class UserController
 *
 * Handles user profile display and updates, settings management,
 * password changes, and account deletion.
 */
class UserController
{
    /**
     * Display the user's profile and allow updating profile information.
     *
     * @return void
     */
    public function profile()
    {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }
        
        $pdo = Database::getConnection();
        $user = null;
        $success = null;
        $error = null;
        
        try {
            // Get user information
            $stmt = $pdo->prepare('SELECT * FROM users WHERE id = ?');
            $stmt->execute([$_SESSION['user_id']]);
            $user = $stmt->fetch();
            
            if (!$user) {
                session_destroy();
                header('Location: /login');
                exit;
            }
            
            // Handle profile update
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $firstName = trim($_POST['first_name'] ?? '');
                $lastName = trim($_POST['last_name'] ?? '');
                $phone = trim($_POST['phone'] ?? '');
                
                if (empty($firstName) || empty($lastName)) {
                    $error = 'First name and last name are required.';
                } else {
                    $stmt = $pdo->prepare('UPDATE users SET first_name = ?, last_name = ?, phone = ? WHERE id = ?');
                    $stmt->execute([$firstName, $lastName, $phone, $_SESSION['user_id']]);
                    
                    $success = 'Profile updated successfully!';
                    
                    // Refresh user data
                    $stmt = $pdo->prepare('SELECT * FROM users WHERE id = ?');
                    $stmt->execute([$_SESSION['user_id']]);
                    $user = $stmt->fetch();
                }
            }
        } catch (Exception $e) {
            $error = 'Error loading profile. Please try again.';
            error_log("Profile error: " . $e->getMessage());
        }
        
        include BASE_PATH . '/resources/views/profile.php';
    }

    /**
     * Render an edit profile placeholder page.
     *
     * @return void
     */
    public function editProfile()
    {
        // Render a simple edit profile page (not used, handled in profile.php)
        echo '<div class="container py-5"><h1>Edit Profile</h1><p>This is the edit profile page. Implement profile editing here.</p></div>';
    }

    /**
     * Process profile updates submitted via POST.
     * Validates input and updates user data in the database.
     *
     * @return void
     */
    public function updateProfile()
    {
        session_start();
        if (!isset($_SESSION['user'])) {
            header('Location: /login');
            exit;
        }
        $userId = $_SESSION['user']['id'];
        $firstName = trim($_POST['first_name'] ?? '');
        $lastName = trim($_POST['last_name'] ?? '');
        $phone = trim($_POST['phone_number'] ?? '');
        $errors = [];
        if (empty($firstName) || strlen($firstName) < 2) {
            $errors[] = 'First name is required and must be at least 2 characters.';
        }
        if (empty($lastName) || strlen($lastName) < 2) {
            $errors[] = 'Last name is required and must be at least 2 characters.';
        }
        if (empty($phone)) {
            $errors[] = 'Phone number is required.';
        }
        if ($errors) {
            $_SESSION['error'] = implode(' ', $errors);
            header('Location: /profile');
            exit;
        }
        try {
            $pdo = Database::getConnection();
            $stmt = $pdo->prepare('UPDATE users SET first_name = ?, last_name = ?, phone_number = ?, updated_at = NOW() WHERE id = ?');
            $stmt->execute([$firstName, $lastName, $phone, $userId]);
            // Update session user info
            $_SESSION['user']['first_name'] = $firstName;
            $_SESSION['user']['last_name'] = $lastName;
            $_SESSION['user']['phone_number'] = $phone;
            $_SESSION['success'] = 'Profile updated successfully!';
        } catch (Exception $e) {
            $_SESSION['error'] = 'Failed to update profile: ' . $e->getMessage();
        }
        header('Location: /profile');
        exit;
    }

    /**
     * Display or process user's password change form.
     * Placeholder implementation.
     *
     * @return void
     */
    public function changePassword()
    {
        // Placeholder: Handle password change (to be implemented)
        echo "<div class='container py-5'><h1>Password changed (placeholder)</h1></div>";
    }

    /**
     * Display user settings page and handle password changes.
     *
     * @return void
     */
    public function settings()
    {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }
        
        $pdo = Database::getConnection();
        $user = null;
        $success = null;
        $error = null;
        
        try {
            // Get user information
            $stmt = $pdo->prepare('SELECT * FROM users WHERE id = ?');
            $stmt->execute([$_SESSION['user_id']]);
            $user = $stmt->fetch();
            
            if (!$user) {
                session_destroy();
                header('Location: /login');
                exit;
            }
            
            // Handle password change
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $currentPassword = $_POST['current_password'] ?? '';
                $newPassword = $_POST['new_password'] ?? '';
                $confirmPassword = $_POST['confirm_password'] ?? '';
                
                if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
                    $error = 'All password fields are required.';
                } elseif ($newPassword !== $confirmPassword) {
                    $error = 'New passwords do not match.';
                } elseif (strlen($newPassword) < 8) {
                    $error = 'New password must be at least 8 characters long.';
                } elseif (!password_verify($currentPassword, $user['password'])) {
                    $error = 'Current password is incorrect.';
                } else {
                    $hash = password_hash($newPassword, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare('UPDATE users SET password = ? WHERE id = ?');
                    $stmt->execute([$hash, $_SESSION['user_id']]);
                    
                    $success = 'Password changed successfully!';
                }
            }
        } catch (Exception $e) {
            $error = 'Error loading settings. Please try again.';
            error_log("Settings error: " . $e->getMessage());
        }
        
        include BASE_PATH . '/resources/views/settings.php';
    }

    /**
     * Process settings updates submitted via POST.
     * Placeholder implementation.
     *
     * @return void
     */
    public function updateSettings()
    {
        // Placeholder: Handle settings update
        echo "<div class='container py-5'><h1>Settings updated (placeholder)</h1></div>";
    }

    /**
     * Delete the user's account after verifying their password.
     *
     * @return void
     */
    public function deleteAccount() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $pdo = Database::getConnection();
            $password = $_POST['password'] ?? '';
            
            try {
                // Verify password
                $stmt = $pdo->prepare('SELECT password FROM users WHERE id = ?');
                $stmt->execute([$_SESSION['user_id']]);
                $user = $stmt->fetch();
                
                if ($user && password_verify($password, $user['password'])) {
                    // Delete user account
                    $stmt = $pdo->prepare('DELETE FROM users WHERE id = ?');
                    $stmt->execute([$_SESSION['user_id']]);
                    
                    session_destroy();
                    header('Location: /login?message=account_deleted');
                    exit;
                } else {
                    $error = 'Incorrect password.';
                }
            } catch (Exception $e) {
                $error = 'Error deleting account. Please try again.';
                error_log("Delete account error: " . $e->getMessage());
            }
        }
        
        header('Location: /settings');
        exit;
    }
} 