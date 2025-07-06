<?php
// Advanced Diagnostic Script

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Print PHP and system information
echo "=== System Information ===\n";
echo "PHP Version: " . PHP_VERSION . "\n";
echo "PHP Executable: " . PHP_BINARY . "\n";
echo "PHP Include Path: " . get_include_path() . "\n";
echo "Current Directory: " . getcwd() . "\n";

// Check Composer autoloader
echo "\n=== Composer Autoloader ===\n";
$autoloaderPath = __DIR__ . '/vendor/autoload.php';
if (file_exists($autoloaderPath)) {
    require_once $autoloaderPath;
    echo "Composer Autoloader: Loaded successfully\n";
} else {
    echo "Composer Autoloader: NOT FOUND at $autoloaderPath\n";
    exit(1);
}

// Detailed class loading investigation
echo "\n=== Class Loading Investigation ===\n";

// List of classes to check
$classesToCheck = [
    'App\Repositories\BirthApplicationRepository',
    'App\Services\DependencyContainer',
    'App\Repositories\UserRepository',
];

foreach ($classesToCheck as $className) {
    echo "\nChecking $className:\n";
    try {
        if (class_exists($className)) {
            $reflection = new ReflectionClass($className);
            echo "  Status: FOUND ✓\n";
            echo "  File Location: " . $reflection->getFileName() . "\n";
            
            // Check file existence
            $classFile = $reflection->getFileName();
            if (file_exists($classFile)) {
                echo "  File Exists: Yes\n";
                
                // Check file contents
                $fileContents = file_get_contents($classFile);
                echo "  Namespace Check: " . (strpos($fileContents, "namespace $className") !== false ? "✓" : "✗") . "\n";
            } else {
                echo "  File Exists: NO ✗\n";
            }
        } else {
            echo "  Status: NOT FOUND ✗\n";
        }
    } catch (Exception $e) {
        echo "  Error: " . $e->getMessage() . "\n";
    }
}

// Namespace and autoload path investigation
echo "\n=== Namespace Investigation ===\n";
$namespaceRoots = [
    'App' => __DIR__ . '/app',
];

foreach ($namespaceRoots as $namespace => $path) {
    echo "Namespace: $namespace\n";
    echo "Path: $path\n";
    
    if (is_dir($path)) {
        echo "Directory Exists: Yes\n";
        
        // Recursive file listing
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );
        
        echo "PHP Files:\n";
        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                echo "  " . $file->getPathname() . "\n";
            }
        }
    } else {
        echo "Directory Exists: NO ✗\n";
    }
}

echo "\n=== Diagnostic Complete ===\n"; 