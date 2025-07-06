-- Add certificate-related columns to birth_applications table
ALTER TABLE birth_applications
ADD COLUMN certificate_filename VARCHAR(255) NULL,
ADD COLUMN certificate_filepath VARCHAR(500) NULL,
ADD COLUMN certificate_number VARCHAR(50) NULL UNIQUE,
ADD COLUMN certificate_generated_at TIMESTAMP NULL;

-- Create index for faster certificate lookups
CREATE INDEX idx_certificate_number ON birth_applications(certificate_number);

-- Optional: Add a trigger to generate unique certificate number
DELIMITER //
CREATE TRIGGER generate_certificate_number 
BEFORE UPDATE ON birth_applications
FOR EACH ROW
BEGIN
    IF NEW.status = 'approved' AND OLD.certificate_number IS NULL THEN
        SET NEW.certificate_number = CONCAT(
            YEAR(CURRENT_DATE), 
            LPAD(NEW.id, 6, '0'), 
            SUBSTRING(MD5(NEW.full_name), 1, 4)
        );
    END IF;
END;//
DELIMITER ; 