-- Drop table if exists
DROP TABLE IF EXISTS blockchain_hashes;

-- Create Blockchain Hashes Table
CREATE TABLE blockchain_hashes (
    id BIGINT IDENTITY(1,1) PRIMARY KEY,
    certificate_id BIGINT NOT NULL,
    
    -- Hash Information
    certificate_hash VARCHAR(255) NOT NULL,    -- Hash of the certificate content
    blockchain_hash VARCHAR(255) NOT NULL,     -- Hash stored on blockchain
    
    -- Blockchain Transaction Details
    transaction_id VARCHAR(255),               -- Blockchain transaction ID
    block_number BIGINT,                       -- Block number where hash is stored
    block_timestamp DATETIME,                  -- When the block was mined
    network_id VARCHAR(50),                    -- Blockchain network identifier
    
    -- Verification History
    last_verified_at DATETIME,                 -- Last successful verification
    verification_count INT DEFAULT 0,          -- Number of verifications
    last_verification_status VARCHAR(20),      -- Result of last verification
    
    -- Metadata
    stored_by BIGINT NOT NULL,                -- User who initiated storage
    verification_endpoint VARCHAR(255),        -- API endpoint for verification
    
    created_at DATETIME DEFAULT GETDATE(),
    updated_at DATETIME DEFAULT GETDATE(),
    deleted_at DATETIME,
    
    CONSTRAINT UQ_certificate_hash UNIQUE (certificate_hash),
    CONSTRAINT UQ_blockchain_hash UNIQUE (blockchain_hash),
    CONSTRAINT CHK_verification_status CHECK (last_verification_status IN ('pending', 'verified', 'failed', 'invalid')),
    CONSTRAINT FK_blockchain_certificate FOREIGN KEY (certificate_id) REFERENCES certificates(id),
    CONSTRAINT FK_blockchain_stored_by FOREIGN KEY (stored_by) REFERENCES users(id)
);

-- Create indexes
CREATE INDEX idx_certificate_id ON blockchain_hashes(certificate_id);
CREATE INDEX idx_certificate_hash ON blockchain_hashes(certificate_hash);
CREATE INDEX idx_blockchain_hash ON blockchain_hashes(blockchain_hash);
CREATE INDEX idx_transaction_id ON blockchain_hashes(transaction_id);
CREATE INDEX idx_last_verified_at ON blockchain_hashes(last_verified_at);