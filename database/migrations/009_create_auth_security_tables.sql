-- Create password history table
CREATE TABLE password_history (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Create failed login attempts table
CREATE TABLE login_attempts (
    id INT PRIMARY KEY AUTO_INCREMENT,
    email VARCHAR(255) NOT NULL,
    ip_address VARCHAR(45) NOT NULL,
    attempted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    success BOOLEAN DEFAULT FALSE
);

-- Create two factor auth table
CREATE TABLE two_factor_auth (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT NOT NULL,
    secret_key VARCHAR(32) NOT NULL,
    backup_codes JSON,
    enabled BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Add new columns to users table
ALTER TABLE users
ADD COLUMN password_expires_at TIMESTAMP NULL,
ADD COLUMN account_locked BOOLEAN DEFAULT FALSE,
ADD COLUMN lock_expires_at TIMESTAMP NULL,
ADD COLUMN last_password_change TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
ADD COLUMN require_password_change BOOLEAN DEFAULT FALSE;