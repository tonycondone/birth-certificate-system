-- Drop view if exists
DROP VIEW IF EXISTS certificate_verification_view;

-- Create Certificate Verification View
CREATE VIEW certificate_verification_view AS
SELECT 
    c.certificate_number,
    c.status as certificate_status,
    c.issue_date,
    c.expiry_date,
    c.blockchain_hash,
    c.qr_code_data,
    
    ba.child_first_name,
    ba.child_middle_name,
    ba.child_last_name,
    ba.date_of_birth,
    ba.gender,
    ba.place_of_birth,
    
    CONCAT(ba.child_first_name, ' ', ba.child_last_name) as full_name,
    
    CASE 
        WHEN c.status = 'active' AND (c.expiry_date IS NULL OR c.expiry_date > CURRENT_TIMESTAMP) THEN 1
        ELSE 0
    END as is_valid
FROM certificates c
JOIN birth_applications ba ON c.application_id = ba.id
WHERE c.deleted_at IS NULL AND ba.deleted_at IS NULL;