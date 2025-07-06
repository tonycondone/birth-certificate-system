-- Add phone number and address to users table
ALTER TABLE users 
ADD COLUMN phone_number VARCHAR(20) NULL AFTER email,
ADD COLUMN address TEXT NULL AFTER phone_number;

-- Optional: Add index for phone number for faster lookups
CREATE INDEX idx_phone_number ON users(phone_number);

-- Optional: Add a timestamp for profile updates
ALTER TABLE users 
ADD COLUMN profile_updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP; 