CREATE TABLE rate_limits (
    id INT IDENTITY(1,1) PRIMARY KEY,
    route NVARCHAR(255) NOT NULL,
    ip_address NVARCHAR(45) NOT NULL,
    timestamp DATETIME DEFAULT GETDATE(),
    CONSTRAINT idx_route_ip UNIQUE (route, ip_address)
);

CREATE INDEX idx_timestamp ON rate_limits (timestamp);

-- Add indexes to login_attempts table for better rate limiting performance
CREATE INDEX idx_email_time ON login_attempts (email, attempted_at);
CREATE INDEX idx_ip_time ON login_attempts (ip_address, attempted_at);