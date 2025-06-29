-- Create verification_attempts table for rate limiting
CREATE TABLE IF NOT EXISTS verification_attempts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ip_address VARCHAR(45) NOT NULL,
    certificate_number VARCHAR(50) NOT NULL,
    action VARCHAR(50) NOT NULL DEFAULT 'verify',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_ip_address (ip_address),
    INDEX idx_action (action),
    INDEX idx_created_at (created_at),
    INDEX idx_ip_action_time (ip_address, action, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create verification_log table for tracking verification history
CREATE TABLE IF NOT EXISTS verification_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    certificate_number VARCHAR(50) NOT NULL,
    ip_address VARCHAR(45) NOT NULL,
    status ENUM('success', 'failed', 'invalid') NOT NULL,
    user_agent TEXT,
    verification_details JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_certificate_number (certificate_number),
    INDEX idx_ip_address (ip_address),
    INDEX idx_status (status),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add verification tracking fields to certificates table (ignore errors if already exist)
ALTER TABLE certificates ADD COLUMN verification_count INT DEFAULT 0;
ALTER TABLE certificates ADD COLUMN last_verified_at TIMESTAMP NULL;
ALTER TABLE certificates ADD COLUMN first_verified_at TIMESTAMP NULL;

-- Create index for verification performance (ignore errors if already exist)
CREATE INDEX idx_certificates_verification ON certificates (verification_count, last_verified_at);

-- Create user_activity_log table (if not exists from previous migration)
CREATE TABLE IF NOT EXISTS user_activity_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    action VARCHAR(100) NOT NULL,
    description TEXT,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    
    INDEX idx_user_id (user_id),
    INDEX idx_action (action),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add reset token fields to users table (ignore errors if already exist)
ALTER TABLE users ADD COLUMN reset_token VARCHAR(255) NULL;
ALTER TABLE users ADD COLUMN reset_token_expires TIMESTAMP NULL;

-- Create index for reset tokens (ignore errors if already exist)
CREATE INDEX idx_users_reset_token ON users (reset_token, reset_token_expires);

-- NOTE: If you re-run this migration and get errors about duplicate columns or indexes, it is safe to ignore them. 