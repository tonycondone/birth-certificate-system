-- Create system settings table
CREATE TABLE IF NOT EXISTS system_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) NOT NULL UNIQUE,
    setting_value TEXT,
    setting_type ENUM('string', 'integer', 'boolean', 'json') DEFAULT 'string',
    description TEXT,
    is_public BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_setting_key (setting_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default settings
INSERT INTO system_settings (setting_key, setting_value, setting_type, description, is_public) VALUES
('app_name', 'Digital Birth Certificate System', 'string', 'Application name', TRUE),
('app_version', '1.0.0', 'string', 'Application version', TRUE),
('maintenance_mode', 'false', 'boolean', 'Maintenance mode status', TRUE),
('max_file_size', '5242880', 'integer', 'Maximum file upload size in bytes', TRUE),
('allowed_file_types', 'jpg,jpeg,png,pdf', 'string', 'Allowed file types for uploads', TRUE),
('session_lifetime', '7200', 'integer', 'Session lifetime in seconds', FALSE),
('password_min_length', '8', 'integer', 'Minimum password length', TRUE),
('login_attempts_limit', '5', 'integer', 'Maximum login attempts before lockout', FALSE),
('lockout_duration', '900', 'integer', 'Account lockout duration in seconds', FALSE),
('certificate_prefix', 'BC', 'string', 'Certificate number prefix', FALSE),
('qr_code_size', '300', 'integer', 'QR code size in pixels', TRUE);
