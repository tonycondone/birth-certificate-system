-- Create activity_log table
CREATE TABLE activity_log (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED,
    action VARCHAR(100) NOT NULL,
    details TEXT,
    ip_address VARCHAR(45),
    user_agent VARCHAR(255),
    entity_type VARCHAR(50),
    entity_id BIGINT UNSIGNED,
    status VARCHAR(20) DEFAULT 'success',
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) 
        REFERENCES users(id) 
        ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add indexes for common queries
CREATE INDEX idx_activity_user ON activity_log (user_id);
CREATE INDEX idx_activity_action ON activity_log (action);
CREATE INDEX idx_activity_timestamp ON activity_log (timestamp);
CREATE INDEX idx_activity_entity ON activity_log (entity_type, entity_id);
CREATE INDEX idx_activity_status ON activity_log (status);

-- Create trigger to automatically log user creation
DELIMITER //
CREATE TRIGGER after_user_create 
AFTER INSERT ON users
FOR EACH ROW
BEGIN
    INSERT INTO activity_log (
        user_id,
        action,
        details,
        entity_type,
        entity_id
    ) VALUES (
        NEW.id,
        'user_created',
        CONCAT('New user created: ', NEW.email),
        'user',
        NEW.id
    );
END;
//
DELIMITER ;

-- Create trigger to log user status changes
DELIMITER //
CREATE TRIGGER after_user_update 
AFTER UPDATE ON users
FOR EACH ROW
BEGIN
    IF NEW.status != OLD.status THEN
        INSERT INTO activity_log (
            user_id,
            action,
            details,
            entity_type,
            entity_id
        ) VALUES (
            NEW.id,
            'user_status_changed',
            CONCAT('User status changed from ', OLD.status, ' to ', NEW.status),
            'user',
            NEW.id
        );
    END IF;
END;
//
DELIMITER ;

-- Create trigger to log application status changes
DELIMITER //
CREATE TRIGGER after_application_update 
AFTER UPDATE ON birth_applications
FOR EACH ROW
BEGIN
    IF NEW.status != OLD.status THEN
        INSERT INTO activity_log (
            user_id,
            action,
            details,
            entity_type,
            entity_id
        ) VALUES (
            NEW.parent_id,
            'application_status_changed',
            CONCAT('Application status changed from ', OLD.status, ' to ', NEW.status),
            'application',
            NEW.id
        );
    END IF;
END;
//
DELIMITER ;

-- Create trigger to log certificate generation
DELIMITER //
CREATE TRIGGER after_certificate_create 
AFTER INSERT ON certificates
FOR EACH ROW
BEGIN
    INSERT INTO activity_log (
        action,
        details,
        entity_type,
        entity_id
    ) VALUES (
        'certificate_generated',
        CONCAT('Certificate generated: ', NEW.certificate_number),
        'certificate',
        NEW.id
    );
END;
//
DELIMITER ;