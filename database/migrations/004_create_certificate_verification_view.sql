-- Drop the view if it exists
DROP VIEW IF EXISTS certificate_verification_view;

-- Create certificate verification view for efficient lookups
CREATE VIEW certificate_verification_view AS
SELECT 
    c.id as certificate_id,
    c.certificate_number,
    CONCAT(ba.child_first_name, ' ', IFNULL(ba.child_middle_name, ''), ' ', ba.child_last_name) as child_name,
    ba.date_of_birth,
    ba.place_of_birth,
    ba.gender,
    c.issued_at as issue_date,
    c.status,
    c.blockchain_hash,
    u.first_name as parent_first_name,
    u.last_name as parent_last_name,
    u.email as parent_email,
    ba.id as application_id,
    ba.created_at as application_date
FROM certificates c
LEFT JOIN birth_applications ba ON c.application_id = ba.id
LEFT JOIN users u ON ba.user_id = u.id
WHERE c.status = 'active';
