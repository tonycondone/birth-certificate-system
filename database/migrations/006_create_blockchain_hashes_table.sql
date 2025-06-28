-- Drop table if exists
DROP TABLE IF EXISTS blockchain_hashes;

-- Create Blockchain Hashes Table
CREATE TABLE blockchain_hashes (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    
    -- Hash Information
    hash_value VARCHAR(255) NOT NULL,
    hash_type VARCHAR(50) NOT NULL, -- 'sha256', 'sha512', etc.
    
    -- Related Records
    certificate_id BIGINT,
    application_id BIGINT,
    document_id BIGINT,
    
    -- Blockchain Information
    blockchain_name VARCHAR(100), -- 'ethereum', 'bitcoin', 'custom'
    transaction_id VARCHAR(255),
    block_number BIGINT,
    block_hash VARCHAR(255),
    
    -- Verification Data
    verification_url VARCHAR(500),
    verification_status VARCHAR(20) DEFAULT 'pending',
    verified_at TIMESTAMP NULL,
    verification_attempts INT DEFAULT 0,
    
    -- Metadata
    description TEXT,
    tags JSON, -- Additional metadata as JSON
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    
    CONSTRAINT UQ_blockchain_hash_value UNIQUE (hash_value),
    CONSTRAINT CHK_blockchain_hash_type CHECK (hash_type IN ('sha256', 'sha512', 'md5', 'ripemd160')),
    CONSTRAINT CHK_blockchain_verification_status CHECK (verification_status IN ('pending', 'verified', 'failed', 'expired')),
    CONSTRAINT FK_blockchain_hashes_certificate FOREIGN KEY (certificate_id) REFERENCES certificates(id),
    CONSTRAINT FK_blockchain_hashes_application FOREIGN KEY (application_id) REFERENCES birth_applications(id)
);

-- Create indexes
CREATE INDEX idx_hash_value ON blockchain_hashes(hash_value);
CREATE INDEX idx_certificate_id ON blockchain_hashes(certificate_id);
CREATE INDEX idx_application_id ON blockchain_hashes(application_id);
CREATE INDEX idx_transaction_id ON blockchain_hashes(transaction_id);
CREATE INDEX idx_verification_status ON blockchain_hashes(verification_status);
CREATE INDEX idx_created_at ON blockchain_hashes(created_at);