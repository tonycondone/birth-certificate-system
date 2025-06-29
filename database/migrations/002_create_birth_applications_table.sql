-- Create birth_applications table
CREATE TABLE IF NOT EXISTS birth_applications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    application_number VARCHAR(50) UNIQUE NOT NULL,
    user_id INT NOT NULL,
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
    
    -- Application Status
    status ENUM('draft', 'submitted', 'under_review', 'approved', 'rejected', 'certificate_issued') DEFAULT 'draft',
    submitted_at TIMESTAMP NULL,
    reviewed_at TIMESTAMP NULL,
    reviewed_by INT NULL,
    review_notes TEXT,
    
    -- Documents
    birth_notification_document VARCHAR(255),
    parent_id_documents VARCHAR(255),
    medical_records VARCHAR(255),
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (reviewed_by) REFERENCES users(id) ON DELETE SET NULL,
    
    INDEX idx_application_number (application_number),
    INDEX idx_user_id (user_id),
    INDEX idx_status (status),
    INDEX idx_date_of_birth (date_of_birth)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
