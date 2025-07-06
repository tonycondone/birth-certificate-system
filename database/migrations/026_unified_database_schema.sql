-- Unified Database Schema for Birth Certificate System
-- This migration consolidates and fixes all database inconsistencies

-- Drop existing conflicting tables if they exist
DROP TABLE IF EXISTS certificates;
DROP TABLE IF EXISTS applications;
DROP TABLE IF EXISTS birth_applications;

-- Create birth_applications table (used by ApplicationController)
CREATE TABLE IF NOT EXISTS birth_applications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    application_number VARCHAR(50) UNIQUE NOT NULL,
    reference_number VARCHAR(255) UNIQUE NOT NULL,
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
    hospital_id VARCHAR(50),
    attending_physician VARCHAR(100),
    physician_license VARCHAR(50),
    
    -- Application Details
    purpose VARCHAR(255) NOT NULL DEFAULT 'Birth Certificate',
    description TEXT,
    
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
    
    -- Tracking
    tracking_number VARCHAR(100) UNIQUE,
    priority ENUM('normal', 'urgent', 'emergency') DEFAULT 'normal',
    
    -- Payment
    payment_status ENUM('pending', 'paid', 'failed', 'refunded') DEFAULT 'pending',
    payment_amount DECIMAL(10,2) DEFAULT 0.00,
    payment_reference VARCHAR(100),
    payment_date TIMESTAMP NULL,
    
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
    INDEX idx_reference_number (reference_number),
    INDEX idx_tracking_number (tracking_number),
    INDEX idx_user_id (user_id),
    INDEX idx_status (status),
    INDEX idx_date_of_birth (date_of_birth),
    INDEX idx_created_at (created_at),
    INDEX idx_payment_status (payment_status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create certificates table
CREATE TABLE IF NOT EXISTS certificates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    certificate_number VARCHAR(50) UNIQUE NOT NULL,
    application_id INT NOT NULL,
    qr_code_hash VARCHAR(255) NOT NULL,
    qr_code_data TEXT,
    blockchain_hash VARCHAR(255),
    digital_signature TEXT,
    
    -- Certificate Details
    certificate_type ENUM('birth', 'death', 'marriage') DEFAULT 'birth',
    template_version VARCHAR(20) DEFAULT '1.0',
    
    -- Issuance Information
    issued_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    issued_by INT NOT NULL,
    issuing_authority VARCHAR(200) DEFAULT 'Civil Registration Authority',
    
    -- Validity
    valid_until DATE NULL,
    status ENUM('active', 'revoked', 'expired', 'suspended') DEFAULT 'active',
    revocation_reason TEXT,
    revoked_at TIMESTAMP NULL,
    revoked_by INT NULL,
    
    -- Security
    security_features JSON,
    verification_count INT DEFAULT 0,
    last_verified_at TIMESTAMP NULL,
    
    -- File Information
    pdf_path VARCHAR(500),
    pdf_size INT,
    pdf_hash VARCHAR(255),
    
    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Foreign Keys
    FOREIGN KEY (application_id) REFERENCES birth_applications(id) ON DELETE CASCADE,
    FOREIGN KEY (issued_by) REFERENCES users(id) ON DELETE RESTRICT,
    FOREIGN KEY (revoked_by) REFERENCES users(id) ON DELETE SET NULL,
    
    -- Indexes
    INDEX idx_certificate_number (certificate_number),
    INDEX idx_application_id (application_id),
    INDEX idx_status (status),
    INDEX idx_qr_code_hash (qr_code_hash),
    INDEX idx_issued_at (issued_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create notifications table
CREATE TABLE IF NOT EXISTS notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    type ENUM('info', 'success', 'warning', 'error') DEFAULT 'info',
    category ENUM('application', 'certificate', 'payment', 'system', 'security') DEFAULT 'application',
    
    -- Related entities
    application_id INT NULL,
    certificate_id INT NULL,
    
    -- Status
    is_read BOOLEAN DEFAULT FALSE,
    read_at TIMESTAMP NULL,
    
    -- Delivery
    delivery_method ENUM('web', 'email', 'sms', 'push') DEFAULT 'web',
    delivered_at TIMESTAMP NULL,
    
    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Foreign Keys
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (application_id) REFERENCES birth_applications(id) ON DELETE SET NULL,
    FOREIGN KEY (certificate_id) REFERENCES certificates(id) ON DELETE SET NULL,
    
    -- Indexes
    INDEX idx_user_id (user_id),
    INDEX idx_is_read (is_read),
    INDEX idx_created_at (created_at),
    INDEX idx_type (type),
    INDEX idx_category (category)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create system_settings table
CREATE TABLE IF NOT EXISTS system_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) UNIQUE NOT NULL,
    setting_value TEXT,
    setting_type ENUM('string', 'integer', 'boolean', 'json', 'text') DEFAULT 'string',
    category VARCHAR(50) DEFAULT 'general',
    description TEXT,
    is_public BOOLEAN DEFAULT FALSE,
    
    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Indexes
    INDEX idx_setting_key (setting_key),
    INDEX idx_category (category),
    INDEX idx_is_public (is_public)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create activity_logs table
CREATE TABLE IF NOT EXISTS activity_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    action VARCHAR(100) NOT NULL,
    entity_type VARCHAR(50),
    entity_id INT NULL,
    description TEXT,
    ip_address VARCHAR(45),
    user_agent TEXT,
    
    -- Additional data
    old_values JSON,
    new_values JSON,
    metadata JSON,
    
    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    -- Foreign Keys
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    
    -- Indexes
    INDEX idx_user_id (user_id),
    INDEX idx_action (action),
    INDEX idx_entity_type (entity_type),
    INDEX idx_entity_id (entity_id),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create verification_logs table
CREATE TABLE IF NOT EXISTS verification_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    certificate_id INT NOT NULL,
    verifier_ip VARCHAR(45),
    verifier_user_agent TEXT,
    verification_method ENUM('qr_code', 'certificate_number', 'api') NOT NULL,
    verification_result ENUM('valid', 'invalid', 'expired', 'revoked') NOT NULL,
    verification_details JSON,
    
    -- Timestamps
    verified_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    -- Foreign Keys
    FOREIGN KEY (certificate_id) REFERENCES certificates(id) ON DELETE CASCADE,
    
    -- Indexes
    INDEX idx_certificate_id (certificate_id),
    INDEX idx_verification_result (verification_result),
    INDEX idx_verified_at (verified_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create payments table
CREATE TABLE IF NOT EXISTS payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    application_id INT NOT NULL,
    user_id INT NOT NULL,
    
    -- Payment Details
    amount DECIMAL(10,2) NOT NULL,
    currency VARCHAR(3) DEFAULT 'USD',
    payment_method ENUM('credit_card', 'debit_card', 'bank_transfer', 'mobile_money', 'cash') NOT NULL,
    
    -- Payment Gateway
    gateway VARCHAR(50),
    gateway_transaction_id VARCHAR(255),
    gateway_reference VARCHAR(255),
    gateway_response JSON,
    
    -- Status
    status ENUM('pending', 'processing', 'completed', 'failed', 'cancelled', 'refunded') DEFAULT 'pending',
    
    -- Timestamps
    initiated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    completed_at TIMESTAMP NULL,
    failed_at TIMESTAMP NULL,
    
    -- Additional Info
    failure_reason TEXT,
    notes TEXT,
    
    -- Foreign Keys
    FOREIGN KEY (application_id) REFERENCES birth_applications(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    
    -- Indexes
    INDEX idx_application_id (application_id),
    INDEX idx_user_id (user_id),
    INDEX idx_status (status),
    INDEX idx_gateway_transaction_id (gateway_transaction_id),
    INDEX idx_initiated_at (initiated_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create feedback table
CREATE TABLE IF NOT EXISTS feedback (
    id INT AUTO_INCREMENT PRIMARY KEY,
    application_id INT NOT NULL,
    user_id INT NOT NULL,
    
    -- Feedback Details
    rating INT CHECK (rating >= 1 AND rating <= 5),
    subject VARCHAR(255),
    message TEXT NOT NULL,
    category ENUM('service_quality', 'processing_time', 'staff_behavior', 'system_usability', 'other') DEFAULT 'other',
    
    -- Status
    status ENUM('pending', 'reviewed', 'resolved', 'closed') DEFAULT 'pending',
    admin_response TEXT,
    responded_by INT NULL,
    responded_at TIMESTAMP NULL,
    
    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Foreign Keys
    FOREIGN KEY (application_id) REFERENCES birth_applications(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (responded_by) REFERENCES users(id) ON DELETE SET NULL,
    
    -- Indexes
    INDEX idx_application_id (application_id),
    INDEX idx_user_id (user_id),
    INDEX idx_status (status),
    INDEX idx_rating (rating),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default system settings
INSERT INTO system_settings (setting_key, setting_value, setting_type, category, description, is_public) VALUES
('app_name', 'Digital Birth Certificate System', 'string', 'general', 'Application name', true),
('app_version', '1.0.0', 'string', 'general', 'Application version', true),
('certificate_fee', '25.00', 'string', 'payment', 'Standard certificate fee', true),
('urgent_fee', '50.00', 'string', 'payment', 'Urgent processing fee', true),
('max_file_size', '5242880', 'integer', 'upload', 'Maximum file upload size in bytes', false),
('allowed_file_types', 'jpg,jpeg,png,pdf,doc,docx', 'string', 'upload', 'Allowed file types for upload', false),
('certificate_validity_years', '0', 'integer', 'certificate', 'Certificate validity in years (0 = permanent)', false),
('auto_approve_hospital', 'false', 'boolean', 'workflow', 'Auto-approve applications from verified hospitals', false),
('email_notifications', 'true', 'boolean', 'notification', 'Enable email notifications', false),
('sms_notifications', 'false', 'boolean', 'notification', 'Enable SMS notifications', false),
('maintenance_mode', 'false', 'boolean', 'system', 'Enable maintenance mode', false),
('registration_enabled', 'true', 'boolean', 'system', 'Enable user registration', true);

-- Create triggers for automatic tracking number generation
DELIMITER $$

CREATE TRIGGER generate_tracking_number 
BEFORE INSERT ON birth_applications 
FOR EACH ROW 
BEGIN 
    IF NEW.tracking_number IS NULL THEN
        SET NEW.tracking_number = CONCAT('TRK', YEAR(NOW()), LPAD(MONTH(NOW()), 2, '0'), LPAD(DAY(NOW()), 2, '0'), LPAD(NEW.id, 6, '0'));
    END IF;
    
    IF NEW.application_number IS NULL THEN
        SET NEW.application_number = CONCAT('APP', YEAR(NOW()), LPAD(MONTH(NOW()), 2, '0'), LPAD(DAY(NOW()), 2, '0'), LPAD(NEW.id, 6, '0'));
    END IF;
    
    IF NEW.reference_number IS NULL THEN
        SET NEW.reference_number = CONCAT('REF', YEAR(NOW()), LPAD(MONTH(NOW()), 2, '0'), LPAD(DAY(NOW()), 2, '0'), LPAD(NEW.id, 6, '0'));
    END IF;
END$$

CREATE TRIGGER generate_certificate_number 
BEFORE INSERT ON certificates 
FOR EACH ROW 
BEGIN 
    IF NEW.certificate_number IS NULL THEN
        SET NEW.certificate_number = CONCAT('BC', YEAR(NOW()), LPAD(MONTH(NOW()), 2, '0'), LPAD(DAY(NOW()), 2, '0'), LPAD(NEW.id, 6, '0'));
    END IF;
END$$

DELIMITER ;
