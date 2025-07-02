-- Create table for tracking certificate deliveries

CREATE TABLE IF NOT EXISTS `deliveries` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `certificate_id` INT NOT NULL,
    `carrier` VARCHAR(255) DEFAULT NULL,
    `tracking_url` VARCHAR(255) DEFAULT NULL,
    `status` ENUM('pending','shipped','delivered') NOT NULL DEFAULT 'pending',
    `delivered_at` DATETIME DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`certificate_id`) REFERENCES `certificates`(`id`) ON DELETE CASCADE
); 