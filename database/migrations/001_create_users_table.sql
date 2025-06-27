-- Drop table if exists
DROP TABLE IF EXISTS users;

-- Create Users Table
CREATE TABLE users (
    id BIGINT IDENTITY(1,1) PRIMARY KEY,
    role VARCHAR(20) NOT NULL,
    username VARCHAR(50) NOT NULL,
    email VARCHAR(255) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    phone_number VARCHAR(20),
    national_id VARCHAR(50),
    hospital_id VARCHAR(100), -- Only for hospital role
    registration_number VARCHAR(100), -- For hospital/registrar roles
    status VARCHAR(20) DEFAULT 'active',
    email_verified_at DATETIME NULL,
    phone_verified_at DATETIME NULL,
    remember_token VARCHAR(100),
    created_at DATETIME DEFAULT GETDATE(),
    updated_at DATETIME DEFAULT GETDATE(),
    deleted_at DATETIME NULL,
    
    CONSTRAINT UQ_username UNIQUE (username),
    CONSTRAINT UQ_email UNIQUE (email),
    CONSTRAINT UQ_national_id UNIQUE (national_id),
    CONSTRAINT CHK_role CHECK (role IN ('parent', 'hospital', 'registrar', 'admin')),
    CONSTRAINT CHK_status CHECK (status IN ('active', 'inactive', 'suspended'))
);

-- Create indexes
CREATE INDEX idx_role ON users(role);
CREATE INDEX idx_email ON users(email);
CREATE INDEX idx_national_id ON users(national_id);
CREATE INDEX idx_hospital_id ON users(hospital_id);

-- Create initial admin user
INSERT INTO users (
    role, 
    username,
    email,
    password_hash,
    first_name,
    last_name,
    status
) VALUES (
    'admin',
    'admin',
    'admin@system.local',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', -- password: password
    'System',
    'Administrator',
    'active'
);