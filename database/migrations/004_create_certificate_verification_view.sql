-- Drop view if exists
IF EXISTS (SELECT * FROM sys.views WHERE name = 'vw_certificate_verification')
    DROP VIEW vw_certificate_verification;
GO

-- Create view for public certificate verification
CREATE VIEW vw_certificate_verification AS
SELECT 
    c.certificate_number,
    c.verification_hash,
    c.issued_date,
    c.status,
    c.is_valid,
    ba.child_first_name,
    ba.child_last_name,
    ba.date_of_birth,
    ba.mother_national_id
FROM 
    certificates c
    INNER JOIN birth_applications ba ON c.application_id = ba.id
WHERE 
    c.deleted_at IS NULL;