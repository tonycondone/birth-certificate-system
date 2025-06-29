<?php
/**
 * Router for PHP Development Server
 * This file handles all requests when using php -S localhost:8000 -t public
 */

// Get the requested URI
$uri = $_SERVER['REQUEST_URI'];

// Remove query string
$uri = parse_url($uri, PHP_URL_PATH);

// If the file exists and is not a directory, serve it directly
if (is_file(__DIR__ . $uri)) {
    return false; // Let the server handle it
}

// If it's a directory, serve index.php
if (is_dir(__DIR__ . $uri)) {
    $uri = rtrim($uri, '/') . '/index.php';
    if (is_file(__DIR__ . $uri)) {
        return false; // Let the server handle it
    }
}

// For all other requests, route through index.php
if (!is_file(__DIR__ . $uri)) {
    // Set the correct script name and request URI
    $_SERVER['SCRIPT_NAME'] = '/index.php';
    $_SERVER['REQUEST_URI'] = $uri;
    
    // Change to the public directory
    chdir(__DIR__);
    
    // Include the index.php file
    require __DIR__ . '/index.php';
    return true;
}

return false; 