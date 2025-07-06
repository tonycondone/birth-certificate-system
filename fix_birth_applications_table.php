<?php
require_once __DIR__ . '/app/Database/Database.php';

use App\Database\Database;

try {
    $pdo = Database::getConnection();
    
    echo "=== FIXING BIRTH_APPLICATIONS TABLE ===\n\n";
    
    // Check if birth_applications table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'birth_applications'");
    $tableExists = $stmt->rowCount() > 0;
    
    if ($tableExists) {
        echo "birth_applications table exists. Checking structure...\n";
        
        // Get current table structure
        $stmt = $pdo->query("DESCRIBE birth_applications");
        $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        echo "Current columns: " . implode(', ', $columns) . "\n\n";
        
        // Check if child_first_name exists
        if (!in_array('child_first_name', $columns)) {
            echo "child_first_name column missing. Dropping and recreating table...\n";
            
            // Drop the table and recreate with proper structure
            $pdo->exec("DROP TABLE IF EXISTS birth_applications");
        } else {
            echo "Table structure looks correct.\n";
            return;
        }
    }
    
    echo "Creating birth_applications table with proper structure...\n";
    
    // Create the birth_applications table with the correct structure
    $createTableSQL = "
    CREATE TABLE birth_applications (
        id INT AUTO_INCREMENT PRIMARY KEY,
        application_number VARCHAR(50) UNIQUE NOT NULL,
        user_id INT NOT NULL,
        
        -- Child Information
        child_first_name VARCHAR(50) NOT NULL,
        child_last_name VARCHAR(50) NOT NULL,
        child_middle_name VARCHAR(50),
        date_of_birth DATE NOT NULL,
        time_of_birth TIME,
        place_of_birth VARCHAR(200) NOT NULL,
        gender ENUM('male', 'female', 'other') NOT NULL,
        weight_at_birth DECIMAL(4,2),
        length_at_birth DECIMAL(4,2),
        
        -- Parent Information
        father_first_name VARCHAR(50),
        father_last_name VARCHAR(50),
        father_national_id VARCHAR(20),
        father_phone VARCHAR(20),
        father_email VARCHAR(100),
        
        mother_first_name VARCHAR(50) NOT NULL,
        mother_last_name VARCHAR(50) NOT NULL,
        mother_national_id VARCHAR(20),
        mother_phone VARCHAR(20),
        mother_email VARCHAR(100),
        
        -- Hospital Information
        hospital_name VARCHAR(200),
        attending_physician VARCHAR(100),
        physician_license VARCHAR(50),
        
        -- Application Status
        status ENUM('draft', 'submitted', 'under_review', 'pending', 'approved', 'rejected', 'certificate_issued') DEFAULT 'pending',
        submitted_at TIMESTAMP NULL,
        reviewed_at TIMESTAMP NULL,
        approved_at TIMESTAMP NULL,
        rejected_at TIMESTAMP NULL,
        reviewed_by INT NULL,
        approved_by INT NULL,
        rejected_by INT NULL,
        review_notes TEXT,
        rejection_reason TEXT,
        
        -- Documents
        birth_notification_document VARCHAR(255),
        parent_id_documents VARCHAR(255),
        medical_records VARCHAR(255),
        supporting_documents TEXT,
        
        -- Timestamps
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        
        -- Foreign Keys
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (reviewed_by) REFERENCES users(id) ON DELETE SET NULL,
        FOREIGN KEY (approved_by) REFERENCES users(id) ON DELETE SET NULL,
        FOREIGN KEY (rejected_by) REFERENCES users(id) ON DELETE SET NULL,
        
        -- Indexes
        INDEX idx_application_number (application_number),
        INDEX idx_user_id (user_id),
        INDEX idx_status (status),
        INDEX idx_date_of_birth (date_of_birth),
        INDEX idx_created_at (created_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    $pdo->exec($createTableSQL);
    echo "birth_applications table created successfully!\n\n";
    
    // Verify the table structure
    $stmt = $pdo->query("DESCRIBE birth_applications");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "=== VERIFIED TABLE STRUCTURE ===\n";
    foreach ($columns as $column) {
        echo "- {$column['Field']} ({$column['Type']}) - {$column['Null']} - {$column['Key']}\n";
    }
    
    echo "\nTable structure fixed successfully!\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
