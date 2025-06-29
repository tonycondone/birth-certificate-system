-- Create certificate verification view for efficient lookups
CREATE OR REPLACE VIEW certificate_verification_view AS
SELECT 
    c.id as certificate_id,
    c.certificate_number,
    c.child_name,
    c.date_of_birth,
    c.place_of_birth,
    c.gender,
    c.issue_date,
    c.status,
    c.blockchain_hash,
    u.first_name as parent_first_name,
    u.last_name as parent_last_name,
    u.email as parent_email,
    ba.id as application_id,
    ba.created_at as application_date
FROM certificates c
LEFT JOIN birth_applications ba ON c.application_id = ba.id
LEFT JOIN users u ON ba.parent_id = u.id
WHERE c.status = 'active';
