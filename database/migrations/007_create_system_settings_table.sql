-- Create system_settings table
CREATE TABLE system_settings (
    setting_key VARCHAR(50) PRIMARY KEY,
    setting_value TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default settings
INSERT INTO system_settings (setting_key, setting_value) VALUES
('system_name', 'Digital Birth Certificate System'),
('smtp_host', 'smtp.mailtrap.io'),
('smtp_port', '587'),
('smtp_encryption', 'tls'),
('sms_provider', 'twilio'),
('certificate_prefix', 'BC'),
('upload_max_size', '5242880'),
('allowed_file_types', 'jpg,jpeg,png,pdf'),
('session_lifetime', '7200'),
('maintenance_mode', 'false'),
('enable_notifications', 'true'),
('enable_sms', 'false'),
('enable_email', 'true'),
('support_email', 'support@birthcert.gov'),
('support_phone', '+1234567890');

-- Add indexes
CREATE INDEX idx_settings_updated ON system_settings (updated_at);