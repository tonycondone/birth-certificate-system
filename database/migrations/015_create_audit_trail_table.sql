-- Audit Trail Table for Birth Certificate System
CREATE TABLE IF NOT EXISTS audit_trails (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT UNSIGNED,
    action_type ENUM('CREATE', 'UPDATE', 'DELETE', 'LOGIN', 'LOGOUT', 'ACCESS', 'ERROR') NOT NULL,
    table_name VARCHAR(100),
    record_id BIGINT UNSIGNED,
    old_data JSON,
    new_data JSON,
    ip_address VARCHAR(45),
    user_agent TEXT,
    blockchain_hash VARCHAR(64),
    digital_signature VARCHAR(255),
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_user_id (user_id),
    INDEX idx_action_type (action_type),
    INDEX idx_timestamp (timestamp),
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Create a trigger to automatically log changes
DELIMITER //

CREATE TRIGGER trg_audit_certificate_changes
AFTER INSERT ON birth_certificates
FOR EACH ROW
BEGIN
    INSERT INTO audit_trails (
        user_id, 
        action_type, 
        table_name, 
        record_id, 
        new_data, 
        ip_address, 
        user_agent,
        blockchain_hash
    ) VALUES (
        @current_user_id, 
        'CREATE', 
        'birth_certificates', 
        NEW.id, 
        JSON_OBJECT(
            'certificate_number', NEW.certificate_number,
            'first_name', NEW.first_name,
            'last_name', NEW.last_name,
            'date_of_birth', NEW.date_of_birth
        ),
        @current_ip_address,
        @current_user_agent,
        SHA2(CONCAT(NEW.id, NEW.certificate_number), 256)
    );
END;//

CREATE TRIGGER trg_audit_certificate_updates
AFTER UPDATE ON birth_certificates
FOR EACH ROW
BEGIN
    INSERT INTO audit_trails (
        user_id, 
        action_type, 
        table_name, 
        record_id, 
        old_data, 
        new_data, 
        ip_address, 
        user_agent,
        blockchain_hash
    ) VALUES (
        @current_user_id, 
        'UPDATE', 
        'birth_certificates', 
        NEW.id, 
        JSON_OBJECT(
            'certificate_number', OLD.certificate_number,
            'first_name', OLD.first_name,
            'last_name', OLD.last_name,
            'date_of_birth', OLD.date_of_birth
        ),
        JSON_OBJECT(
            'certificate_number', NEW.certificate_number,
            'first_name', NEW.first_name,
            'last_name', NEW.last_name,
            'date_of_birth', NEW.date_of_birth
        ),
        @current_ip_address,
        @current_user_agent,
        SHA2(CONCAT(NEW.id, NEW.certificate_number), 256)
    );
END;//

DELIMITER ; 