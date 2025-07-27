-- Add tracking number, status, and submitted_at to applications table
 
ALTER TABLE `applications`
    ADD COLUMN IF NOT EXISTS `tracking_number` VARCHAR(255) UNIQUE DEFAULT NULL,
    MODIFY COLUMN `status` ENUM('draft','pending_payment','submitted','under_review','pending','approved','rejected') NOT NULL DEFAULT 'draft',
    ADD COLUMN IF NOT EXISTS `submitted_at` DATETIME DEFAULT NULL;
