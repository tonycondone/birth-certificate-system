-- Add tracking number, status, and submitted_at to applications table
 
ALTER TABLE `applications`
    ADD COLUMN `tracking_number` VARCHAR(255) UNIQUE DEFAULT NULL,
    ADD COLUMN `status` ENUM('draft','pending_payment','submitted','under_review','approved','rejected') NOT NULL DEFAULT 'draft',
    ADD COLUMN `submitted_at` DATETIME DEFAULT NULL; 