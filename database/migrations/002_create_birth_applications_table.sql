-- Drop table if exists
DROP TABLE IF EXISTS birth_applications;

-- Create Birth Applications Table
CREATE TABLE birth_applications (
    id BIGINT IDENTITY(1,1) PRIMARY KEY,
    application_number VARCHAR(50) NOT NULL,
    parent_id BIGINT NOT NULL,
    hospital_id BIGINT,
    
    -- Child Details
    child_first_name VARCHAR(100) NOT NULL,
    child_middle_name VARCHAR(100),
    child_last_name VARCHAR(100) NOT NULL,
    date_of_birth DATETIME NOT NULL,
    time_of_birth TIME NOT NULL,
    gender VARCHAR(20) NOT NULL,
    weight_in_grams INT,
    place_of_birth VARCHAR(255) NOT NULL,
    
    -- Parents Details
    father_national_id VARCHAR(50),
    father_first_name VARCHAR(100),
    father_last_name VARCHAR(100),
    father_nationality VARCHAR(100),
    
    mother_national_id VARCHAR(50) NOT NULL,
    mother_first_name VARCHAR(100) NOT NULL,
    mother_last_name VARCHAR(100) NOT NULL,
    mother_nationality VARCHAR(100) NOT NULL,
    
    -- Address Information
    address_line1 VARCHAR(255) NOT NULL,
    address_line2 VARCHAR(255),
    city VARCHAR(100) NOT NULL,
    state_province VARCHAR(100) NOT NULL,
    postal_code VARCHAR(20),
    country VARCHAR(100) NOT NULL,
    
    -- Application Status
    status VARCHAR(50) NOT NULL,
    submitted_at DATETIME NOT NULL DEFAULT GETDATE(),
    hospital_verified_at DATETIME,
    hospital_verified_by BIGINT,
    registrar_verified_at DATETIME,
    registrar_verified_by BIGINT,
    rejected_at DATETIME,
    rejected_by BIGINT,
    rejection_reason TEXT,
    
    -- Document References
    supporting_documents TEXT, -- JSON array of document paths
    hospital_documents TEXT,   -- JSON array of hospital-provided document paths
    
    created_at DATETIME DEFAULT GETDATE(),
    updated_at DATETIME DEFAULT GETDATE(),
    deleted_at DATETIME,
    
    CONSTRAINT UQ_application_number UNIQUE (application_number),
    CONSTRAINT CHK_gender CHECK (gender IN ('male', 'female', 'other')),
    CONSTRAINT CHK_status CHECK (status IN ('draft', 'submitted', 'hospital_verified', 'registrar_verified', 'rejected', 'approved')),
    CONSTRAINT FK_parent_user FOREIGN KEY (parent_id) REFERENCES users(id),
    CONSTRAINT FK_hospital_verified_by FOREIGN KEY (hospital_verified_by) REFERENCES users(id),
    CONSTRAINT FK_registrar_verified_by FOREIGN KEY (registrar_verified_by) REFERENCES users(id),
    CONSTRAINT FK_rejected_by FOREIGN KEY (rejected_by) REFERENCES users(id)
);

-- Create indexes
CREATE INDEX idx_application_number ON birth_applications(application_number);
CREATE INDEX idx_parent_id ON birth_applications(parent_id);
CREATE INDEX idx_hospital_id ON birth_applications(hospital_id);
CREATE INDEX idx_status ON birth_applications(status);
CREATE INDEX idx_mother_national_id ON birth_applications(mother_national_id);
CREATE INDEX idx_submitted_at ON birth_applications(submitted_at);