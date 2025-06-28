<?php
/**
 * Digital Birth Certificate System
 * Main Entry Point
 */

// Load environment variables
if (file_exists(__DIR__ . '/../env.example')) {
    $envFile = __DIR__ . '/../env.example';
} elseif (file_exists(__DIR__ . '/../.env')) {
    $envFile = __DIR__ . '/../.env';
} else {
    die('Environment file not found. Please copy env.example to .env');
}

// Load environment variables
$envContent = file_get_contents($envFile);
$envLines = explode("\n", $envContent);
foreach ($envLines as $line) {
    $line = trim($line);
    if (empty($line) || strpos($line, '#') === 0) continue;
    
    if (strpos($line, '=') !== false) {
        list($key, $value) = explode('=', $line, 2);
        $key = trim($key);
        $value = trim($value, '"\'');
        $_ENV[$key] = $value;
        putenv("$key=$value");
    }
}

// Start session
session_start();

// Set error reporting
if ($_ENV['APP_DEBUG'] ?? false) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// Autoloader
require_once __DIR__ . '/../vendor/autoload.php';

// Load database connection
require_once __DIR__ . '/../app/Database/Database.php';

// Simple router
$requestUri = $_SERVER['REQUEST_URI'];
$requestMethod = $_SERVER['REQUEST_METHOD'];

// Remove query string
$path = parse_url($requestUri, PHP_URL_PATH);

// Remove base path if exists
$basePath = dirname($_SERVER['SCRIPT_NAME']);
if ($basePath !== '/') {
    $path = substr($path, strlen($basePath));
}

// Default route
if ($path === '' || $path === '/') {
    $path = '/home';
}

// Route definitions
$routes = [
    'GET' => [
        '/home' => 'App\Controllers\HomeController@index',
        '/login' => 'App\Controllers\AuthController@showLogin',
        '/register' => 'App\Controllers\AuthController@showRegister',
        '/dashboard' => 'App\Controllers\DashboardController@index',
        '/verify' => 'App\Controllers\CertificateController@showVerify',
        '/verify/{id}' => 'App\Controllers\CertificateController@verify',
    ],
    'POST' => [
        '/auth/login' => 'App\Controllers\AuthController@login',
        '/auth/register' => 'App\Controllers\AuthController@register',
        '/auth/logout' => 'App\Controllers\AuthController@logout',
        '/applications/submit' => 'App\Controllers\ApplicationController@submit',
    ]
];

// Route matching
$matchedRoute = null;
$params = [];

foreach ($routes[$requestMethod] ?? [] as $route => $handler) {
    $pattern = preg_replace('/\{([^}]+)\}/', '([^/]+)', $route);
    $pattern = '#^' . $pattern . '$#';
    
    if (preg_match($pattern, $path, $matches)) {
        $matchedRoute = $handler;
        array_shift($matches); // Remove full match
        $params = $matches;
        break;
    }
}

// Handle route
if ($matchedRoute) {
    list($controller, $method) = explode('@', $matchedRoute);
    
    if (class_exists($controller)) {
        $controllerInstance = new $controller();
        if (method_exists($controllerInstance, $method)) {
            call_user_func_array([$controllerInstance, $method], $params);
        } else {
            http_response_code(404);
            echo "Method $method not found in $controller";
        }
    } else {
        http_response_code(404);
        echo "Controller $controller not found";
    }
} else {
    // Default to home page
    header('Location: /home');
    exit;
} 