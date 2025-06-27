-- Drop table if exists
DROP TABLE IF EXISTS certificates;

-- Create Certificates Table
CREATE TABLE certificates (
    id BIGINT IDENTITY(1,1) PRIMARY KEY,
    certificate_number VARCHAR(50) NOT NULL,
    application_id BIGINT NOT NULL,
    
    -- Certificate Details
    issued_date DATETIME NOT NULL DEFAULT GETDATE(),
    expiry_date DATETIME,
    certificate_path VARCHAR(255) NOT NULL,
    qr_code_path VARCHAR(255) NOT NULL,
    
    -- Verification Data
    verification_hash VARCHAR(255) NOT NULL, -- For blockchain/digital verification
    blockchain_transaction_id VARCHAR(255),  -- Optional, for blockchain implementation
    verification_url VARCHAR(255) NOT NULL,  -- Public verification URL
    
    -- Certificate Status
    status VARCHAR(20) NOT NULL,
    is_valid BIT NOT NULL DEFAULT 1,
    revoked_at DATETIME,
    revoked_by BIGINT,
    revocation_reason TEXT,
    
    -- Access Control
    download_count INT NOT NULL DEFAULT 0,
    last_downloaded_at DATETIME,
    last_downloaded_by BIGINT,
    
    -- Metadata
    issued_by BIGINT NOT NULL,           -- Reference to registrar who issued
    registrar_signature VARCHAR(255),     -- Digital signature of the registrar
    registrar_seal_path VARCHAR(255),     -- Path to the digital seal image
    
    created_at DATETIME DEFAULT GETDATE(),
    updated_at DATETIME DEFAULT GETDATE(),
    deleted_at DATETIME,
    
    CONSTRAINT UQ_certificate_number UNIQUE (certificate_number),
    CONSTRAINT UQ_verification_hash UNIQUE (verification_hash),
    CONSTRAINT CHK_status CHECK (status IN ('active', 'expired', 'revoked')),
    CONSTRAINT FK_application FOREIGN KEY (application_id) REFERENCES birth_applications(id),
    CONSTRAINT FK_issued_by FOREIGN KEY (issued_by) REFERENCES users(id),
    CONSTRAINT FK_revoked_by FOREIGN KEY (revoked_by) REFERENCES users(id),
    CONSTRAINT FK_last_downloaded_by FOREIGN KEY (last_downloaded_by) REFERENCES users(id)
);

-- Create indexes
CREATE INDEX idx_certificate_number ON certificates(certificate_number);
CREATE INDEX idx_application_id ON certificates(application_id);
CREATE INDEX idx_verification_hash ON certificates(verification_hash);
CREATE INDEX idx_status ON certificates(status);
CREATE INDEX idx_issued_date ON certificates(issued_date);