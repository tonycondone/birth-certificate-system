@echo off
echo 🗄️ Setting up Birth Certificate Database
echo ========================================

echo 📝 Creating database...
mysql -u root -p -e "CREATE DATABASE IF NOT EXISTS birth_certificate_system CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

echo 📝 Running migrations...
echo Running migration 1/10: Users table...
mysql -u root -p birth_certificate_system < database\migrations\001_create_users_table.sql

echo Running migration 2/10: Birth applications table...
mysql -u root -p birth_certificate_system < database\migrations\002_create_birth_applications_table.sql

echo Running migration 3/10: Certificates table...
mysql -u root -p birth_certificate_system < database\migrations\003_create_certificates_table.sql

echo Running migration 4/10: Certificate verification view...
mysql -u root -p birth_certificate_system < database\migrations\004_create_certificate_verification_view.sql

echo Running migration 5/10: Notifications table...
mysql -u root -p birth_certificate_system < database\migrations\005_create_notifications_table.sql

echo Running migration 6/10: Blockchain hashes table...
mysql -u root -p birth_certificate_system < database\migrations\006_create_blockchain_hashes_table.sql

echo Running migration 7/10: System settings table...
mysql -u root -p birth_certificate_system < database\migrations\007_create_system_settings_table.sql

echo Running migration 8/10: Activity log table...
mysql -u root -p birth_certificate_system < database\migrations\008_create_activity_log_table.sql

echo Running migration 9/10: Auth security tables...
mysql -u root -p birth_certificate_system < database\migrations\009_create_auth_security_tables.sql

echo Running migration 10/10: Rate limits table...
mysql -u root -p birth_certificate_system < database\migrations\010_create_rate_limits_table.sql

echo ✅ Database setup complete!
echo.
echo 📝 Next steps:
echo 1. Configure your .env file
echo 2. Start the server: php -S localhost:8000 -t public
echo 3. Visit: http://localhost:8000
echo.
pause 