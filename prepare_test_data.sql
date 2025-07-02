-- Ensure a test user exists
INSERT IGNORE INTO users (
    username, 
    email, 
    password, 
    role, 
    first_name, 
    last_name
) VALUES (
    'test_registrar', 
    'registrar@example.com', 
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 
    'registrar', 
    'Test', 
    'Registrar'
);

-- Ensure a test application exists
INSERT IGNORE INTO birth_applications (
    user_id, 
    child_first_name, 
    child_last_name, 
    status, 
    created_at
) VALUES (
    (SELECT id FROM users WHERE email = 'test_registrar@example.com' LIMIT 1), 
    'Test', 
    'Child', 
    'pending', 
    NOW()
);

-- Verify the data
SELECT 
    u.id AS user_id, 
    u.username, 
    u.role, 
    ba.id AS application_id, 
    ba.status 
FROM users u
LEFT JOIN birth_applications ba ON u.id = ba.user_id
WHERE u.email = 'test_registrar@example.com'; 