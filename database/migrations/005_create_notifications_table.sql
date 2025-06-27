-- Drop table if exists
DROP TABLE IF EXISTS notifications;

-- Create Notifications Table
CREATE TABLE notifications (
    id BIGINT IDENTITY(1,1) PRIMARY KEY,
    user_id BIGINT NOT NULL,
    
    -- Notification Content
    type VARCHAR(50) NOT NULL,
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    
    -- Related Records
    application_id BIGINT,
    certificate_id BIGINT,
    
    -- Notification Status
    status VARCHAR(20) NOT NULL,
    priority VARCHAR(20) NOT NULL,
    
    -- Delivery Details
    notification_method VARCHAR(20) NOT NULL, -- 'email', 'sms', 'both'
    email_sent_at DATETIME,
    email_status VARCHAR(50),
    sms_sent_at DATETIME,
    sms_status VARCHAR(50),
    
    -- Read Status
    read_at DATETIME,
    
    created_at DATETIME DEFAULT GETDATE(),
    updated_at DATETIME DEFAULT GETDATE(),
    deleted_at DATETIME,
    
    CONSTRAINT CHK_notification_type CHECK (type IN (
        'application_submitted',
        'application_updated',
        'hospital_verification',
        'registrar_verification',
        'document_request',
        'certificate_issued',
        'certificate_downloaded',
        'certificate_verified',
        'application_rejected'
    )),
    CONSTRAINT CHK_notification_status CHECK (status IN ('pending', 'processing', 'sent', 'failed', 'cancelled')),
    CONSTRAINT CHK_notification_priority CHECK (priority IN ('low', 'medium', 'high', 'urgent')),
    CONSTRAINT CHK_notification_method CHECK (notification_method IN ('email', 'sms', 'both')),
    CONSTRAINT FK_notification_user FOREIGN KEY (user_id) REFERENCES users(id),
    CONSTRAINT FK_notification_application FOREIGN KEY (application_id) REFERENCES birth_applications(id),
    CONSTRAINT FK_notification_certificate FOREIGN KEY (certificate_id) REFERENCES certificates(id)
);

-- Create indexes
CREATE INDEX idx_user_id ON notifications(user_id);
CREATE INDEX idx_application_id ON notifications(application_id);
CREATE INDEX idx_certificate_id ON notifications(certificate_id);
CREATE INDEX idx_type ON notifications(type);
CREATE INDEX idx_status ON notifications(status);
CREATE INDEX idx_created_at ON notifications(created_at);