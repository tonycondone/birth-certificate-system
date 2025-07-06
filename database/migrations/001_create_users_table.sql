-- Create users table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    role ENUM('admin', 'registrar', 'hospital', 'parent') NOT NULL DEFAULT 'parent',
    status ENUM('active', 'inactive', 'suspended', 'deleted') NOT NULL DEFAULT 'active',
    email_verified BOOLEAN DEFAULT FALSE,
    phone VARCHAR(20),
    last_login TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    
    -- Indexes
    INDEX idx_email (email),
    INDEX idx_username (username),
    INDEX idx_role (role),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default admin user
-- Password: admin123 (hashed)
INSERT INTO users (username, email, password, first_name, last_name, role, status, email_verified) 
VALUES (
    'admin',
    'admin@birthcert.gov',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    'System',
    'Administrator',
    'admin',
    'active',
    TRUE
);
