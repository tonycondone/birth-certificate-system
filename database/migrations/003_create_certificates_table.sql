-- Create certificates table
CREATE TABLE IF NOT EXISTS certificates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    certificate_number VARCHAR(50) UNIQUE NOT NULL,
    application_id INT NOT NULL,
    qr_code_hash VARCHAR(255) NOT NULL,
    blockchain_hash VARCHAR(255),
    issued_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    issued_by INT NOT NULL,
    valid_until DATE NULL,
    status ENUM('active', 'revoked', 'expired') DEFAULT 'active',
    revocation_reason TEXT,
    revoked_at TIMESTAMP NULL,
    revoked_by INT NULL,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (application_id) REFERENCES birth_applications(id) ON DELETE CASCADE,
    FOREIGN KEY (issued_by) REFERENCES users(id) ON DELETE RESTRICT,
    FOREIGN KEY (revoked_by) REFERENCES users(id) ON DELETE SET NULL,
    
    INDEX idx_certificate_number (certificate_number),
    INDEX idx_application_id (application_id),
    INDEX idx_status (status),
    INDEX idx_qr_code_hash (qr_code_hash)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
