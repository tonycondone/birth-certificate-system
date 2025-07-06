-- Create system_settings table
CREATE TABLE system_settings (
    setting_key VARCHAR(100) PRIMARY KEY,
    setting_value TEXT NOT NULL,
    description VARCHAR(255),
    updated_by INT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (updated_by) REFERENCES users(id) ON DELETE SET NULL
);

-- Create system_configuration_audit table
CREATE TABLE system_configuration_audit (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    action VARCHAR(100) NOT NULL,
    details TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Create user_roles table
CREATE TABLE user_roles (
    role_name VARCHAR(50) PRIMARY KEY,
    description VARCHAR(255),
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_by INT,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (updated_by) REFERENCES users(id) ON DELETE SET NULL
);

-- Insert default system settings
INSERT INTO system_settings (setting_key, setting_value, description) VALUES
('system_name', 'Birth Certificate Management System', 'Official system name'),
('registration_enabled', 'true', 'Whether new user registrations are allowed'),
('max_daily_certificates', '100', 'Maximum number of certificates that can be issued per day'),
('certificate_validity_years', '5', 'Number of years a birth certificate remains valid'),
('notification_email', 'admin@birthcertificatesystem.gov', 'Email for system notifications'),
('backup_frequency', 'daily', 'How often system backups are performed');

-- Insert default user roles
INSERT INTO user_roles (role_name, description, created_by) VALUES
('admin', 'System administrator with full access', 1),
('registrar', 'Birth certificate registration officer', 1),
('viewer', 'Read-only access to system', 1); 