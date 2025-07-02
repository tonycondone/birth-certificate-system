-- Add email verification columns to users table

ALTER TABLE `users`
    ADD COLUMN `email_verified` TINYINT(1) NOT NULL DEFAULT 0,
    ADD COLUMN `email_verified_at` DATETIME NULL; 