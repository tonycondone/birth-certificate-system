-- Migration 027: Fix Certificate Verification System
-- Date: 2025-08-09
-- Purpose: Document and ensure certificate verification system fixes

-- Ensure application_documents table exists (required for delete functionality)
CREATE TABLE IF NOT EXISTS application_documents (
    id INT AUTO_INCREMENT PRIMARY KEY,
    application_id INT NOT NULL,
    document_type VARCHAR(255) NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (application_id) REFERENCES birth_applications(id) ON DELETE CASCADE
);

-- Ensure application_progress table exists (required for status tracking)
CREATE TABLE IF NOT EXISTS application_progress (
    id INT AUTO_INCREMENT PRIMARY KEY,
    application_id INT NOT NULL,
    status VARCHAR(50) NOT NULL,
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    notes TEXT,
    FOREIGN KEY (application_id) REFERENCES birth_applications(id) ON DELETE CASCADE
);

-- Ensure application_tracking table exists (required for tracking functionality)
CREATE TABLE IF NOT EXISTS application_tracking (
    id INT AUTO_INCREMENT PRIMARY KEY,
    application_id INT NOT NULL,
    tracking_number VARCHAR(255) UNIQUE NOT NULL,
    status VARCHAR(50) NOT NULL,
    last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (application_id) REFERENCES birth_applications(id) ON DELETE CASCADE
);

-- Ensure certificates table has proper structure for verification
ALTER TABLE certificates 
ADD COLUMN IF NOT EXISTS qr_code_hash VARCHAR(255) DEFAULT NULL,
ADD COLUMN IF NOT EXISTS blockchain_hash VARCHAR(255) DEFAULT NULL,
ADD COLUMN IF NOT EXISTS verification_count INT DEFAULT 0,
ADD COLUMN IF NOT EXISTS last_verified_at TIMESTAMP NULL;

-- Update certificate format validation (BC + 14 characters)
-- This is handled in the controller code, no SQL changes needed

-- Ensure all approved applications have tracking numbers
UPDATE birth_applications 
SET tracking_number = CONCAT('TRK', DATE_FORMAT(NOW(), '%Y%m%d'), UPPER(SUBSTRING(MD5(RAND()), 1, 8)))
WHERE tracking_number IS NULL OR tracking_number = '';

-- Sync approved certificates to certificates table
INSERT IGNORE INTO certificates (certificate_number, application_id, issued_by, issued_at, status, verification_count, qr_code_hash)
SELECT 
    ba.certificate_number,
    ba.id,
    COALESCE(ba.verified_by, 1),
    COALESCE(ba.certificate_generated_at, ba.verified_at, NOW()),
    'active',
    0,
    MD5(CONCAT(ba.certificate_number, UNIX_TIMESTAMP()))
FROM birth_applications ba
WHERE ba.status = 'approved' 
AND ba.certificate_number IS NOT NULL 
AND ba.certificate_number != ''
AND NOT EXISTS (
    SELECT 1 FROM certificates c 
    WHERE c.certificate_number = ba.certificate_number
);

-- Add indexes for better performance
CREATE INDEX IF NOT EXISTS idx_certificates_number_status ON certificates(certificate_number, status);
CREATE INDEX IF NOT EXISTS idx_birth_applications_status ON birth_applications(status);
CREATE INDEX IF NOT EXISTS idx_birth_applications_certificate ON birth_applications(certificate_number);
CREATE INDEX IF NOT EXISTS idx_application_tracking_number ON application_tracking(tracking_number);

-- Migration completed successfully
SELECT 'Certificate verification system fixes applied successfully' as result; 