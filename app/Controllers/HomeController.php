<?php

namespace App\Controllers;

class HomeController
{
    public function index()
    {
        // Check if user is logged in
        $isLoggedIn = isset($_SESSION['user_id']);
        $userRole = $_SESSION['user_role'] ?? null;
        
        // Include the home view
        include __DIR__ . '/../../resources/views/home.php';
    }
} 