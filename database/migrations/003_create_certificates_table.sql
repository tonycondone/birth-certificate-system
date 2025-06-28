-- Drop table if exists
DROP TABLE IF EXISTS certificates;

-- Create Certificates Table
CREATE TABLE certificates (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    certificate_number VARCHAR(50) NOT NULL,
    application_id BIGINT NOT NULL,
    
    -- Certificate Details
    issue_date TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    expiry_date TIMESTAMP NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'active',
    
    -- Digital Certificate Data
    qr_code_data TEXT NOT NULL,
    digital_signature VARCHAR(255),
    blockchain_hash VARCHAR(255),
    
    -- Certificate Content (JSON)
    certificate_data JSON NOT NULL, -- Contains all certificate information
    
    -- File References
    pdf_path VARCHAR(255),
    watermark_path VARCHAR(255),
    
    -- Audit Information
    issued_by BIGINT NOT NULL,
    issued_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    revoked_at TIMESTAMP NULL,
    revoked_by BIGINT,
    revocation_reason TEXT,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    
    CONSTRAINT UQ_certificates_certificate_number UNIQUE (certificate_number),
    CONSTRAINT CHK_certificates_status CHECK (status IN ('active', 'expired', 'revoked', 'suspended')),
    CONSTRAINT FK_certificates_application FOREIGN KEY (application_id) REFERENCES birth_applications(id),
    CONSTRAINT FK_certificates_issued_by FOREIGN KEY (issued_by) REFERENCES users(id),
    CONSTRAINT FK_certificates_revoked_by FOREIGN KEY (revoked_by) REFERENCES users(id)
);

-- Create indexes
CREATE INDEX idx_certificate_number ON certificates(certificate_number);
CREATE INDEX idx_application_id ON certificates(application_id);
CREATE INDEX idx_status ON certificates(status);
CREATE INDEX idx_issue_date ON certificates(issue_date);
CREATE INDEX idx_blockchain_hash ON certificates(blockchain_hash);