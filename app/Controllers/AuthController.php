<?php

namespace App\Controllers;

use App\Auth\Authentication;
use App\Database\Database;
use Exception;
use PDOException;

class AuthController
{
    private Authentication $auth;

    public function __construct()
    {
        try {
            $this->auth = new Authentication(Database::getConnection());
        } catch (PDOException $e) {
            // Log error and redirect to error page
            error_log($e->getMessage());
            $_SESSION['error'] = 'System error. Please try again later.';
            header('Location: /error');
            exit;
        }
    }

    public function showLogin()
    {
        require_once __DIR__ . '/../../resources/views/auth/login.php';
    }

    public function showRegister()
    {
        require_once __DIR__ . '/../../resources/views/auth/register.php';
    }

    public function login()
    {
        try {
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';
            $rememberMe = isset($_POST['remember_me']);

            if (empty($email) || empty($password)) {
                throw new Exception('Email and password are required');
            }

            $user = $this->auth->login($email, $password, $rememberMe);
            
            $_SESSION['success'] = 'Welcome back, ' . htmlspecialchars($user['first_name']);
            
            // Redirect based on role
            switch ($user['role']) {
                case 'parent':
                    header('Location: /dashboard/parent');
                    break;
                case 'hospital':
                    header('Location: /dashboard/hospital');
                    break;
                case 'registrar':
                    header('Location: /dashboard/registrar');
                    break;
                case 'admin':
                    header('Location: /dashboard/admin');
                    break;
                default:
                    header('Location: /dashboard');
            }
            exit;
        } catch (Exception $e) {
            $_SESSION['error'] = $e->getMessage();
            header('Location: /login');
            exit;
        }
    }

    public function register()
    {
        try {
            $userData = [
                'username' => $_POST['username'] ?? '',
                'email' => $_POST['email'] ?? '',
                'password' => $_POST['password'] ?? '',
                'first_name' => $_POST['first_name'] ?? '',
                'last_name' => $_POST['last_name'] ?? '',
                'role' => $_POST['role'] ?? '',
                'phone_number' => $_POST['phone_number'] ?? '',
            ];

            // Add role-specific fields
            if ($userData['role'] === 'parent') {
                $userData['national_id'] = $_POST['national_id'] ?? '';
            } elseif ($userData['role'] === 'hospital') {
                $userData['hospital_id'] = $_POST['hospital_id'] ?? '';
            }

            $this->auth->register($userData);
            
            $_SESSION['success'] = 'Registration successful. Please login.';
            header('Location: /login');
            exit;
        } catch (Exception $e) {
            $_SESSION['error'] = $e->getMessage();
            header('Location: /register');
            exit;
        }
    }

    public function logout()
    {
        $this->auth->logout();
        $_SESSION['success'] = 'You have been logged out successfully.';
        header('Location: /login');
        exit;
    }
}