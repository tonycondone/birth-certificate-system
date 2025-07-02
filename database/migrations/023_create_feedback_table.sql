-- Create table for storing user feedback on applications

CREATE TABLE IF NOT EXISTS `feedback` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `application_id` INT NOT NULL,
    `rating` TINYINT UNSIGNED NOT NULL CHECK (rating BETWEEN 1 AND 5),
    `comments` TEXT DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`application_id`) REFERENCES `applications`(`id`) ON DELETE CASCADE
); 