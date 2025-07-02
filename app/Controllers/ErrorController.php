<?php

namespace App\Controllers;

class ErrorController
{
    public function showError()
    {
        $pageTitle = 'System Error - Birth Certificate System';
        require_once __DIR__ . '/../../resources/views/errors/error.php';
    }
} 