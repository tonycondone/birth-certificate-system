-- Verification and Audit Tables

-- Drop existing indexes if they exist (to prevent duplicate index errors)
SET @drop_idx_certificate_number := (SELECT IF(
    EXISTS(
        SELECT 1 FROM information_schema.statistics 
        WHERE table_schema = DATABASE() 
        AND table_name = 'certificates' 
        AND index_name = 'idx_certificate_number'
    ), 
    'DROP INDEX idx_certificate_number ON certificates', 
    'SELECT 1'
));
PREPARE stmt FROM @drop_idx_certificate_number;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @drop_idx_verification_logs_application := (SELECT IF(
    EXISTS(
        SELECT 1 FROM information_schema.statistics 
        WHERE table_schema = DATABASE() 
        AND table_name = 'verification_logs' 
        AND index_name = 'idx_verification_logs_application'
    ), 
    'DROP INDEX idx_verification_logs_application ON verification_logs', 
    'SELECT 1'
));
PREPARE stmt FROM @drop_idx_verification_logs_application;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @drop_idx_verification_logs_verifier := (SELECT IF(
    EXISTS(
        SELECT 1 FROM information_schema.statistics 
        WHERE table_schema = DATABASE() 
        AND table_name = 'verification_logs' 
        AND index_name = 'idx_verification_logs_verifier'
    ), 
    'DROP INDEX idx_verification_logs_verifier ON verification_logs', 
    'SELECT 1'
));
PREPARE stmt FROM @drop_idx_verification_logs_verifier;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Certificates Table
CREATE TABLE IF NOT EXISTS certificates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    application_id INT NOT NULL,
    certificate_number VARCHAR(50) UNIQUE NOT NULL,
    verifier_id INT NOT NULL,
    verification_notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (application_id) REFERENCES birth_applications(id) ON DELETE CASCADE,
    FOREIGN KEY (verifier_id) REFERENCES users(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Verification Activity Log
CREATE TABLE IF NOT EXISTS verification_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    application_id INT NOT NULL,
    verifier_id INT NOT NULL,
    action ENUM('verified', 'rejected', 'reviewed') NOT NULL,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (application_id) REFERENCES birth_applications(id) ON DELETE CASCADE,
    FOREIGN KEY (verifier_id) REFERENCES users(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Update birth_applications table to support verification workflow
DELIMITER //

CREATE PROCEDURE add_columns_to_birth_applications()
BEGIN
    -- Check and add rejection_reason column
    IF NOT EXISTS (
        SELECT 1 FROM information_schema.columns 
        WHERE table_name = 'birth_applications' 
        AND column_name = 'rejection_reason'
        AND table_schema = DATABASE()
    ) THEN
        ALTER TABLE birth_applications 
        ADD COLUMN rejection_reason TEXT;
    END IF;

    -- Check and add verification_status column
    IF NOT EXISTS (
        SELECT 1 FROM information_schema.columns 
        WHERE table_name = 'birth_applications' 
        AND column_name = 'verification_status'
        AND table_schema = DATABASE()
    ) THEN
        ALTER TABLE birth_applications 
        ADD COLUMN verification_status ENUM('pending', 'verified', 'rejected') DEFAULT 'pending';
    END IF;

    -- Check and add verified_at column
    IF NOT EXISTS (
        SELECT 1 FROM information_schema.columns 
        WHERE table_name = 'birth_applications' 
        AND column_name = 'verified_at'
        AND table_schema = DATABASE()
    ) THEN
        ALTER TABLE birth_applications 
        ADD COLUMN verified_at TIMESTAMP NULL;
    END IF;

    -- Check and add verified_by column
    IF NOT EXISTS (
        SELECT 1 FROM information_schema.columns 
        WHERE table_name = 'birth_applications' 
        AND column_name = 'verified_by'
        AND table_schema = DATABASE()
    ) THEN
        ALTER TABLE birth_applications 
        ADD COLUMN verified_by INT NULL;
    END IF;

    -- Add foreign key constraint if not exists
    IF NOT EXISTS (
        SELECT 1 
        FROM information_schema.KEY_COLUMN_USAGE 
        WHERE TABLE_SCHEMA = DATABASE() 
        AND TABLE_NAME = 'birth_applications' 
        AND REFERENCED_TABLE_NAME = 'users'
        AND CONSTRAINT_NAME = 'fk_verified_by'
    ) THEN
        ALTER TABLE birth_applications 
        ADD CONSTRAINT fk_verified_by 
        FOREIGN KEY (verified_by) REFERENCES users(id) ON DELETE SET NULL;
    END IF;
END //

DELIMITER ;

-- Call the procedure to add columns
CALL add_columns_to_birth_applications();

-- Drop the procedure after use
DROP PROCEDURE IF EXISTS add_columns_to_birth_applications;

-- Create indexes (with safe creation)
-- Certificates index
SET @create_certificate_index := (SELECT IF(
    NOT EXISTS(
        SELECT 1 FROM information_schema.statistics 
        WHERE table_schema = DATABASE() 
        AND table_name = 'certificates' 
        AND index_name = 'idx_certificate_number'
    ), 
    'CREATE INDEX idx_certificate_number ON certificates(certificate_number)', 
    'SELECT 1'
));
PREPARE stmt FROM @create_certificate_index;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Verification logs application index
SET @create_verification_logs_application_index := (SELECT IF(
    NOT EXISTS(
        SELECT 1 FROM information_schema.statistics 
        WHERE table_schema = DATABASE() 
        AND table_name = 'verification_logs' 
        AND index_name = 'idx_verification_logs_application'
    ), 
    'CREATE INDEX idx_verification_logs_application ON verification_logs(application_id)', 
    'SELECT 1'
));
PREPARE stmt FROM @create_verification_logs_application_index;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Verification logs verifier index
SET @create_verification_logs_verifier_index := (SELECT IF(
    NOT EXISTS(
        SELECT 1 FROM information_schema.statistics 
        WHERE table_schema = DATABASE() 
        AND table_name = 'verification_logs' 
        AND index_name = 'idx_verification_logs_verifier'
    ), 
    'CREATE INDEX idx_verification_logs_verifier ON verification_logs(verifier_id)', 
    'SELECT 1'
));
PREPARE stmt FROM @create_verification_logs_verifier_index;
EXECUTE stmt;
DEALLOCATE PREPARE stmt; 