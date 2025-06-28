-- Drop table if exists
DROP TABLE IF EXISTS users;

-- Create Users Table
CREATE TABLE users (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
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
    email_verified_at TIMESTAMP NULL,
    phone_verified_at TIMESTAMP NULL,
    remember_token VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    
    CONSTRAINT UQ_users_username UNIQUE (username),
    CONSTRAINT UQ_users_email UNIQUE (email),
    CONSTRAINT UQ_users_national_id UNIQUE (national_id),
    CONSTRAINT CHK_users_role CHECK (role IN ('parent', 'hospital', 'registrar', 'admin')),
    CONSTRAINT CHK_users_status CHECK (status IN ('active', 'inactive', 'suspended'))
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