-- Add email_verified column to users table
ALTER TABLE users ADD COLUMN email_verified TINYINT(1) NOT NULL DEFAULT 0;
ALTER TABLE users ADD COLUMN email_verified_at TIMESTAMP NULL; 