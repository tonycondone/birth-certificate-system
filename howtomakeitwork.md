# How to Make It Work - Digital Birth Certificate System

A comprehensive guide to setting up, configuring, and running the Digital Birth Certificate Registration Platform.

## Table of Contents

1. [System Requirements](#system-requirements)
2. [Initial Setup](#initial-setup)
3. [Database Setup](#database-setup)
4. [Configuration](#configuration)
5. [Running the Application](#running-the-application)
6. [Using the System](#using-the-system)
7. [Troubleshooting](#troubleshooting)

## System Requirements

### Software Prerequisites

- PHP 8.1 or higher with required extensions
- SQL Server (or MySQL/MariaDB 10.4+ with syntax adjustments)
- Node.js 16.x or higher (required by package.json)
- npm 8.x or higher (required by package.json)
- Apache/Nginx web server
- Composer (PHP package manager)
- npm (Node.js package manager)

### PHP Extensions

```bash
# Required PHP extensions
php-pdo
php-mysql
php-json
php-gd
php-mbstring
php-xml
php-tokenizer
php-fileinfo
```

### Server Requirements

- Minimum 2GB RAM
- 20GB storage space
- Modern CPU (2+ cores recommended)

## Initial Setup

1. Clone the repository:

```bash
git clone https://github.com/your-org/birth-certificate-system.git
cd birth-certificate-system
```

2. Install PHP dependencies:

```bash
composer install
```

3. Frontend Setup:

```bash
# Install Node.js dependencies
npm install

# Development build with watch mode
npm run dev

# Production build
npm run build
```

4. Frontend Dependencies:

- Bootstrap 5.3.0 (UI framework)
- Font Awesome 6.4.0 (Icons)
- SweetAlert2 11.7.12 (Notifications)
- QR Scanner 1.4.2 (Certificate verification)

5. Create storage directories:

```bash
mkdir -p storage/logs
mkdir -p storage/cache
mkdir -p public/uploads
chmod -R 775 storage
chmod -R 775 public/uploads
```

## Database Setup

1. Create the database:

```sql
-- SQL Server syntax
CREATE DATABASE birth_certificate_system;

-- MySQL syntax (if using MySQL)
CREATE DATABASE birth_certificate_system CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

Note: The migration files use SQL Server syntax (IDENTITY, DATETIME). For MySQL:

- Replace IDENTITY with AUTO_INCREMENT
- Replace DATETIME with TIMESTAMP
- Adjust CHECK constraints syntax

2. Run migrations in order:

```bash
# Using MySQL CLI
mysql -u root -p birth_certificate_system < database/migrations/001_create_users_table.sql
mysql -u root -p birth_certificate_system < database/migrations/002_create_birth_applications_table.sql
mysql -u root -p birth_certificate_system < database/migrations/003_create_certificates_table.sql
mysql -u root -p birth_certificate_system < database/migrations/004_create_certificate_verification_view.sql
mysql -u root -p birth_certificate_system < database/migrations/005_create_notifications_table.sql
mysql -u root -p birth_certificate_system < database/migrations/006_create_blockchain_hashes_table.sql
```

3. Create initial admin user:

```sql
INSERT INTO users (username, email, password, role, first_name, last_name) 
VALUES ('admin', 'admin@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'System', 'Administrator');
-- Default password: password
```

## Configuration

1. Create environment file:

```bash
cp .env.example .env
```

2. Configure environment variables:

```ini
# Application
APP_NAME="Digital Birth Certificate System"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com

# Database
DB_HOST=localhost
DB_NAME=birth_certificate_system
DB_USER=your_database_user
DB_PASS=your_database_password

# Mail
MAIL_HOST=smtp.your-provider.com
MAIL_PORT=587
MAIL_USERNAME=your_username
MAIL_PASSWORD=your_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@your-domain.com
MAIL_FROM_NAME="${APP_NAME}"

# SMS (if using Twilio)
SMS_PROVIDER=twilio
SMS_ACCOUNT_SID=your_account_sid
SMS_AUTH_TOKEN=your_auth_token
SMS_FROM_NUMBER=your_twilio_number

# Security
SESSION_SECURE=true
SESSION_HTTP_ONLY=true
CSRF_LIFETIME=7200

# File Upload Configuration
UPLOAD_MAX_SIZE=5242880 # 5MB in bytes
ALLOWED_FILE_TYPES=jpg,jpeg,png,pdf
UPLOAD_PATH=public/uploads

# Certificate Generation
CERTIFICATE_PREFIX=BC
QR_CODE_SIZE=300
WATERMARK_PATH=resources/watermark.png

# Role Configuration
ALLOWED_ROLES=parent,hospital,registrar,admin
PARENT_REQUIRES_NATIONAL_ID=true
HOSPITAL_REQUIRES_LICENSE=true

# Development
DEV_MODE=true
LOG_LEVEL=debug
ERROR_REPORTING=E_ALL
```

3. Configure web server:

### Apache (.htaccess already included)

```apache
<VirtualHost *:80>
    ServerName your-domain.com
    DocumentRoot /path/to/birth-certificate-system/public
    
    <Directory /path/to/birth-certificate-system/public>
        AllowOverride All
        Require all granted
    </Directory>
    
    ErrorLog ${APACHE_LOG_DIR}/birth-cert-error.log
    CustomLog ${APACHE_LOG_DIR}/birth-cert-access.log combined
</VirtualHost>
```

### Nginx

```nginx
server {
    listen 80;
    server_name your-domain.com;
    root /path/to/birth-certificate-system/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

## Running the Application

1. Start the web server:

```bash
# Apache
sudo service apache2 start

# OR Nginx
sudo service nginx start
```

2. Start PHP-FPM:

```bash
sudo service php8.1-fpm start
```

3. Ensure proper permissions:

```bash
sudo chown -R www-data:www-data storage
sudo chown -R www-data:www-data public/uploads
```

4. Access the application:

- Frontend: <https://your-domain.com>
- Admin panel: <https://your-domain.com/admin> (use admin credentials)

## Using the System

### Parent Role

1. Register as a parent:
   - Visit /register
   - Select "Parent" role
   - Required fields:
     - National ID (validated)
     - Full name
     - Email (verified)
     - Phone number (optional)
     - Password (min 8 chars, requires numbers and symbols)
   - Complete registration

2. Submit birth certificate application:
   - Login to dashboard
   - Click "New Application"
   - Fill in child's details
   - Upload required documents
   - Submit for review

3. Track application:
   - View status in dashboard
   - Check notifications
   - Download certificate when approved

### Hospital Role

1. Register hospital account:
   - Contact admin for registration
   - Required fields:
     - Hospital registration number (validated)
     - Hospital name
     - Email (verified)
     - Phone number
     - Address details
     - License information
   - Complete verification process

2. Verify birth records:
   - Login to hospital dashboard
   - Review pending applications
   - Verify birth details
   - Upload supporting documents
   - Approve/reject applications

### Registrar Role

1. Access registrar dashboard:
   - Login with registrar credentials
   - View pending applications
   - Review submitted documents

2. Process applications:
   - Verify all documents
   - Check hospital verification
   - Generate certificate
   - Approve/reject application

### Admin Role

1. System management:
   - Manage users
   - Monitor system activity
   - Configure settings
   - View audit logs

2. User management:
   - Create/edit users
   - Assign roles
   - Reset passwords
   - Disable accounts

## Troubleshooting

### Common Issues

1. **Database Connection Errors**
   - Verify database credentials in .env
   - Check database service is running
   - Ensure proper permissions

   ```bash
   sudo service mysql status
   mysql -u your_user -p
   ```

2. **File Permission Issues**
   - Reset storage permissions

   ```bash
   sudo chown -R www-data:www-data storage
   sudo chmod -R 775 storage
   ```

3. **Session Issues**
   - Clear session storage
   - Verify PHP session configuration

   ```bash
   sudo rm -rf storage/framework/sessions/*
   php -i | grep session
   ```

4. **Upload Problems**
   - Check upload directory permissions
   - Verify PHP upload limits

   ```bash
   sudo chown -R www-data:www-data public/uploads
   php -i | grep upload
   ```

### Security Checks

1. **SSL/HTTPS**
   - Verify SSL certificate installation
   - Check SSL configuration
   - Force HTTPS redirection

2. **File Permissions**
   - Secure configuration files
   - Protect upload directory
   - Set proper ownership

3. **Error Reporting**
   - Disable debug mode in production
   - Configure error logging
   - Monitor error logs

### Performance Optimization

1. **Cache Configuration**
   - Enable PHP OPcache
   - Configure browser caching
   - Optimize database queries

2. **Asset Optimization**
   - Compress images
   - Minify CSS/JS
   - Enable gzip compression

### Monitoring

1. **System Logs**
   - Check application logs

   ```bash
   tail -f storage/logs/app.log
   ```

2. **Server Logs**
   - Monitor web server logs

   ```bash
   tail -f /var/log/apache2/error.log
   # OR
   tail -f /var/log/nginx/error.log
   ```

3. **Database Logs**
   - Check MySQL logs

   ```bash
   tail -f /var/log/mysql/error.log
   ```

## Support and Resources

For additional help:

- Technical Support: <support@birthcert.gov>
- Documentation: /docs
- Issue Tracker: GitHub Issues
- Security Reports: <security@birthcert.gov>

Remember to regularly:

- Backup the database
- Update dependencies
- Monitor system logs
- Check for security updates
