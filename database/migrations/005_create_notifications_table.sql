-- Drop table if exists
DROP TABLE IF EXISTS notifications;

-- Create Notifications Table
CREATE TABLE notifications (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT NOT NULL,
    
    -- Notification Details
    type VARCHAR(50) NOT NULL,
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    data JSON, -- Additional data for the notification
    
    -- Delivery Status
    email_sent BOOLEAN DEFAULT FALSE,
    email_sent_at TIMESTAMP NULL,
    sms_sent BOOLEAN DEFAULT FALSE,
    sms_sent_at TIMESTAMP NULL,
    push_sent BOOLEAN DEFAULT FALSE,
    push_sent_at TIMESTAMP NULL,
    
    -- Read Status
    read_at TIMESTAMP NULL,
    read_by_user_id BIGINT,
    
    -- Priority and Scheduling
    priority VARCHAR(20) DEFAULT 'normal', -- low, normal, high, urgent
    scheduled_at TIMESTAMP NULL,
    
    -- Retry Logic
    retry_count INT DEFAULT 0,
    max_retries INT DEFAULT 3,
    last_retry_at TIMESTAMP NULL,
    
    -- Status
    status VARCHAR(20) DEFAULT 'pending', -- pending, sent, failed, cancelled
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    
    CONSTRAINT CHK_notifications_type CHECK (type IN ('application_submitted', 'application_approved', 'application_rejected', 'certificate_issued', 'document_requested', 'system_alert')),
    CONSTRAINT CHK_notifications_priority CHECK (priority IN ('low', 'normal', 'high', 'urgent')),
    CONSTRAINT CHK_notifications_status CHECK (status IN ('pending', 'sent', 'failed', 'cancelled')),
    CONSTRAINT FK_user FOREIGN KEY (user_id) REFERENCES users(id),
    CONSTRAINT FK_read_by_user FOREIGN KEY (read_by_user_id) REFERENCES users(id)
);

-- Create indexes
CREATE INDEX idx_user_id ON notifications(user_id);
CREATE INDEX idx_type ON notifications(type);
CREATE INDEX idx_status ON notifications(status);
CREATE INDEX idx_priority ON notifications(priority);
CREATE INDEX idx_created_at ON notifications(created_at);
CREATE INDEX idx_read_at ON notifications(read_at);
CREATE INDEX idx_scheduled_at ON notifications(scheduled_at);