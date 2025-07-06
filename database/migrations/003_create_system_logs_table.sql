-- System Logs Table Migration
-- Provides a robust logging mechanism for tracking system events

-- Create main system logs table
CREATE TABLE IF NOT EXISTS system_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    level ENUM('debug', 'info', 'warning', 'error', 'critical') NOT NULL DEFAULT 'info',
    category VARCHAR(100) NOT NULL,
    message TEXT NOT NULL,
    context JSON NULL,
    ip_address VARCHAR(45) NULL,
    user_agent VARCHAR(255) NULL,
    user_id BIGINT UNSIGNED NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_level (level),
    INDEX idx_category (category),
    INDEX idx_user_id (user_id),
    INDEX idx_created_at (created_at),
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create logs archive table for log rotation
CREATE TABLE IF NOT EXISTS system_logs_archive (
    id BIGINT UNSIGNED PRIMARY KEY,
    level ENUM('debug', 'info', 'warning', 'error', 'critical') NOT NULL DEFAULT 'info',
    category VARCHAR(100) NOT NULL,
    message TEXT NOT NULL,
    context JSON NULL,
    ip_address VARCHAR(45) NULL,
    user_agent VARCHAR(255) NULL,
    user_id BIGINT UNSIGNED NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    archived_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create index for efficient archiving
CREATE INDEX idx_archive_created_at ON system_logs_archive (created_at); 