-- Create table for tracking application payments

CREATE TABLE IF NOT EXISTS `payments` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `application_id` INT NOT NULL,
    `amount` DECIMAL(10,2) NOT NULL,
    `currency` VARCHAR(10) NOT NULL DEFAULT 'GHS',
    `transaction_id` VARCHAR(255) DEFAULT NULL,
    `status` ENUM('pending','completed','failed') NOT NULL DEFAULT 'pending',
    `payment_gateway` VARCHAR(50) DEFAULT 'paystack',
    `payment_method` VARCHAR(50) DEFAULT NULL,
    `metadata` JSON DEFAULT NULL,
    `gateway_response` JSON DEFAULT NULL,
    `paid_at` TIMESTAMP NULL DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`application_id`) REFERENCES `applications`(`id`) ON DELETE CASCADE
);
