<?php
// Test repository instantiation

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/vendor/autoload.php';

// Check if autoloader is working
spl_autoload_register(function ($class) {
    echo "Attempting to load class: $class\n";
});

use App\Database\Database;
use App\Repositories\BirthApplicationRepository;

try {
    // Verify class exists
    if (!class_exists(BirthApplicationRepository::class)) {
        echo "Class BirthApplicationRepository does not exist!\n";
        echo "Namespace: " . __NAMESPACE__ . "\n";
        echo "Current file: " . __FILE__ . "\n";
        
        // Try to manually include the file
        $expectedPath = __DIR__ . '/app/Repositories/BirthApplicationRepository.php';
        if (file_exists($expectedPath)) {
            echo "File exists at: $expectedPath\n";
            require_once $expectedPath;
        } else {
            echo "File does not exist at: $expectedPath\n";
        }
        
        exit(1);
    }

    // Get database connection
    $db = Database::getConnection();

    // Try to instantiate repository
    $repository = new BirthApplicationRepository($db);

    echo "Repository instantiation successful!\n";
    
    // Try a method to further verify
    $result = $repository->findByStatus('PENDING', 1);
    
    echo "Method call test:\n";
    print_r($result);
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
} catch (Error $e) {
    echo "Fatal Error: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
} 