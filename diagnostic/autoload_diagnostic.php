<?php
// Autoloader diagnostic script

// Load Composer's autoloader
require_once __DIR__ . '/vendor/autoload.php';

// List of classes to check
$classesToCheck = [
    'App\Repositories\BirthApplicationRepository',
    'App\Repositories\CertificateRepository',
    'App\Repositories\UserRepository',
    'App\Services\DependencyContainer'
];

echo "Autoloader Diagnostic\n";
echo "====================\n\n";

foreach ($classesToCheck as $className) {
    echo "Checking class: $className ... ";
    
    if (class_exists($className)) {
        echo "✓ FOUND\n";
    } else {
        echo "✗ NOT FOUND\n";
        
        // Additional diagnostic information
        $expectedPath = str_replace('\\', DIRECTORY_SEPARATOR, str_replace('App\\', 'app/', $className)) . '.php';
        echo "  Expected file path: $expectedPath\n";
        
        if (file_exists($expectedPath)) {
            echo "  File exists, but class not loaded. Check namespace and class name.\n";
        } else {
            echo "  File does not exist.\n";
        }
    }
    echo "\n";
}

echo "Diagnostic complete.\n"; 