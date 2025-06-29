# 🚀 Complete Birth Certificate System Setup Guide

## 📋 Table of Contents
1. [System Requirements](#system-requirements)
2. [Installation Steps](#installation-steps)
3. [Database Setup](#database-setup)
4. [Configuration](#configuration)
5. [Testing](#testing)
6. [Troubleshooting](#troubleshooting)
7. [Alternative Database Tools](#alternative-database-tools)

## 🖥️ System Requirements

### **Minimum Requirements:**
- **PHP:** 8.1 or higher
- **Web Server:** Apache 2.4+ or Nginx 1.18+
- **Database:** MySQL 8.0+ or MariaDB 10.5+
- **Memory:** 512MB RAM
- **Storage:** 1GB free space

### **Recommended Requirements:**
- **PHP:** 8.2 or higher
- **Web Server:** Apache 2.4+ with mod_rewrite
- **Database:** MySQL 8.0+ with InnoDB
- **Memory:** 2GB RAM
- **Storage:** 5GB free space
- **SSL Certificate:** For production use

## 🔧 Installation Steps

### **Step 1: Clone the Repository**
```bash
# Clone the repository
git clone https://github.com/your-org/birth-certificate-system.git
cd birth-certificate-system

# Or if you have the files locally, navigate to your project directory
cd /path/to/your/birth-certificate-system
```

### **Step 2: Install PHP Dependencies**
```bash
# Install Composer (if not already installed)
# Windows: Download from https://getcomposer.org/download/
# Mac/Linux: curl -sS https://getcomposer.org/installer | php

# Install PHP dependencies
composer install --no-dev --optimize-autoloader
```

### **Step 3: Install Node.js Dependencies**
```bash
# Install Node.js dependencies
npm install

# Build frontend assets
npm run build
```

### **Step 4: Set Up Environment**
```bash
# Copy environment file
cp env.example .env

# Edit the environment file with your settings
# Use any text editor: nano .env, vim .env, or your preferred editor
```

## 🗄️ Database Setup

### **Option 1: Using Command Line (Recommended)**

#### **Create Database:**
```bash
# Connect to MySQL
mysql -u root -p

# Create database
CREATE DATABASE birth_certificate_system CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

# Create user (optional but recommended)
CREATE USER 'birthcert_user'@'localhost' IDENTIFIED BY 'your_secure_password';
GRANT ALL PRIVILEGES ON birth_certificate_system.* TO 'birthcert_user'@'localhost';
FLUSH PRIVILEGES;

# Exit MySQL
EXIT;
```

#### **Run Migrations:**
```bash
# Navigate to your project directory
cd /path/to/birth-certificate-system

# Run all migration files in order
mysql -u root -p birth_certificate_system < database/migrations/001_create_users_table.sql
mysql -u root -p birth_certificate_system < database/migrations/002_create_birth_applications_table.sql
mysql -u root -p birth_certificate_system < database/migrations/003_create_certificates_table.sql
mysql -u root -p birth_certificate_system < database/migrations/004_create_certificate_verification_view.sql
mysql -u root -p birth_certificate_system < database/migrations/005_create_notifications_table.sql
mysql -u root -p birth_certificate_system < database/migrations/006_create_blockchain_hashes_table.sql
mysql -u root -p birth_certificate_system < database/migrations/007_create_system_settings_table.sql
mysql -u root -p birth_certificate_system < database/migrations/008_create_activity_log_table.sql
mysql -u root -p birth_certificate_system < database/migrations/009_create_auth_security_tables.sql
mysql -u root -p birth_certificate_system < database/migrations/010_create_rate_limits_table.sql
```

### **Option 2: Using phpMyAdmin**

1. **Access phpMyAdmin** (usually at `http://localhost/phpmyadmin`)
2. **Create Database:**
   - Click "New" in the left sidebar
   - Enter database name: `birth_certificate_system`
   - Select collation: `utf8mb4_unicode_ci`
   - Click "Create"
3. **Import Migrations:**
   - Select your database
   - Click "Import" tab
   - Upload each `.sql` file from `database/migrations/` folder
   - Import them in numerical order (001, 002, 003, etc.)

### **Option 3: Using DBeaver (Free Alternative to MySQL Workbench)**

1. **Download DBeaver** from https://dbeaver.io/
2. **Create Connection:**
   - Click "New Database Connection"
   - Select "MySQL"
   - Enter connection details:
     - Host: `localhost`
     - Port: `3306`
     - Database: `birth_certificate_system`
     - Username: `root` (or your custom user)
     - Password: `your_password`
3. **Create Database:**
   - Right-click on connection → "Create" → "Database"
   - Name: `birth_certificate_system`
   - Collation: `utf8mb4_unicode_ci`
4. **Run Migrations:**
   - Open SQL Editor (Ctrl+Shift+SQL)
   - Copy and paste each migration file content
   - Execute in order

## ⚙️ Configuration

### **Step 1: Configure Environment File**
Edit your `.env` file with your specific settings:

```ini
# Application Configuration
APP_NAME="Digital Birth Certificate System"
APP_ENV=development
APP_DEBUG=true
APP_URL=http://localhost:8000

# Database Configuration
DB_HOST=127.0.0.1
DB_NAME=birth_certificate_system
DB_USER=root
DB_PASS=1212

# Mail Configuration (for email notifications)
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@birthcert.gov
MAIL_FROM_NAME="Digital Birth Certificate System"

# Security Configuration
SESSION_SECURE=false
SESSION_HTTP_ONLY=true
CSRF_LIFETIME=7200
JWT_SECRET=your-super-secret-jwt-key-change-this-in-production

# File Upload Configuration
UPLOAD_MAX_SIZE=5242880
ALLOWED_FILE_TYPES=jpg,jpeg,png,pdf
UPLOAD_PATH=public/uploads

# Development Configuration
DEV_MODE=true
LOG_LEVEL=debug
ERROR_REPORTING=E_ALL
```

### **Step 2: Set File Permissions**
```bash
# Set proper permissions for storage and uploads
chmod -R 755 storage/
chmod -R 755 public/uploads/
chmod 644 .env

# Create required directories
mkdir -p storage/logs
mkdir -p public/uploads
mkdir -p public/uploads/certificates
mkdir -p public/uploads/documents
```

### **Step 3: Configure Web Server**

#### **Apache Configuration:**
Create or edit `.htaccess` file in the `public/` directory:

```apache
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [QSA,L]

# Security headers
Header always set X-Content-Type-Options nosniff
Header always set X-Frame-Options DENY
Header always set X-XSS-Protection "1; mode=block"
```

#### **Nginx Configuration:**
Add to your nginx site configuration:

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
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.ht {
        deny all;
    }
}
```

## 🧪 Testing

### **Step 1: Start Development Server**
```bash
# Using PHP's built-in server (for development)
php -S localhost:8000 -t public/

# Or using your configured web server
# Access via: http://localhost/birth-certificate-system
```

### **Step 2: Test the System**
1. **Visit Homepage:** `http://localhost:8000`
2. **Test Registration:** Click "Get Started" → Register as a parent
3. **Test Login:** Use the credentials you just created
4. **Test Features:** Explore the dashboard, profile, and other features

### **Step 3: Test API Endpoints**
```bash
# Test system statistics API
curl -X GET "http://localhost:8000/api/statistics" \
  -H "Authorization: demo_api_key_123"

# Test certificate verification API
curl -X GET "http://localhost:8000/api/verify-certificate?certificate_number=BC2024001"
```

## 🔧 Alternative Database Tools

### **1. phpMyAdmin (Web-based)**
- **Pros:** Easy to use, no installation required
- **Cons:** Limited features, security concerns
- **Best for:** Quick database management

### **2. DBeaver (Free, Cross-platform)**
- **Pros:** Free, powerful, supports multiple databases
- **Cons:** Can be complex for beginners
- **Best for:** Professional database management

### **3. HeidiSQL (Windows)**
- **Pros:** Lightweight, fast, Windows-native
- **Cons:** Windows only
- **Best for:** Windows users

### **4. Sequel Pro (Mac)**
- **Pros:** Mac-native, clean interface
- **Cons:** Mac only, limited features
- **Best for:** Mac users

### **5. Command Line (mysql client)**
- **Pros:** Fast, powerful, always available
- **Cons:** Learning curve, no GUI
- **Best for:** Advanced users, automation

## 🚨 Troubleshooting

### **Common Issues:**

#### **1. Database Connection Error**
```bash
# Check if MySQL is running
sudo systemctl status mysql

# Start MySQL if not running
sudo systemctl start mysql

# Check database credentials in .env file
# Ensure database exists
mysql -u root -p -e "SHOW DATABASES;"
```

#### **2. Permission Errors**
```bash
# Fix file permissions
chmod -R 755 storage/
chmod -R 755 public/uploads/
chown -R www-data:www-data storage/
chown -R www-data:www-data public/uploads/
```

#### **3. Composer Issues**
```bash
# Clear Composer cache
composer clear-cache

# Reinstall dependencies
rm -rf vendor/
composer install
```

#### **4. Node.js Issues**
```bash
# Clear npm cache
npm cache clean --force

# Reinstall dependencies
rm -rf node_modules/
npm install
```

#### **5. Web Server Issues**
```bash
# Check Apache/Nginx status
sudo systemctl status apache2
sudo systemctl status nginx

# Check error logs
sudo tail -f /var/log/apache2/error.log
sudo tail -f /var/log/nginx/error.log
```

### **Debug Mode:**
Enable debug mode in `.env`:
```ini
APP_DEBUG=true
LOG_LEVEL=debug
```

Check logs in `storage/logs/` directory.

## 🎯 Quick Setup Script

Create a setup script for automated installation:

```bash
#!/bin/bash
# setup.sh

echo "🚀 Setting up Birth Certificate System..."

# Install dependencies
echo "📦 Installing PHP dependencies..."
composer install --no-dev --optimize-autoloader

echo "📦 Installing Node.js dependencies..."
npm install
npm run build

# Set up environment
echo "⚙️ Setting up environment..."
cp env.example .env

# Create directories
echo "📁 Creating directories..."
mkdir -p storage/logs
mkdir -p public/uploads/certificates
mkdir -p public/uploads/documents

# Set permissions
echo "🔐 Setting permissions..."
chmod -R 755 storage/
chmod -R 755 public/uploads/
chmod 644 .env

echo "✅ Setup complete!"
echo "📝 Next steps:"
echo "1. Edit .env file with your database credentials"
echo "2. Set up your database using one of the methods above"
echo "3. Start your web server"
echo "4. Visit http://localhost:8000"
```

Make it executable: `chmod +x setup.sh`

## 🎉 Success!

Your Birth Certificate System is now ready! You've learned:

✅ **Complete Web Development** - From basics to advanced APIs  
✅ **Database Management** - Multiple tools and approaches  
✅ **System Administration** - Setup, configuration, troubleshooting  
✅ **Modern Web Technologies** - RESTful APIs, JSON, security  
✅ **Professional Development** - Best practices and real-world applications  

**You're now a full-stack web developer!** 🚀

---

## 📚 Next Steps

1. **Deploy to Production** - Set up SSL, optimize performance
2. **Add Features** - Implement additional functionality
3. **Mobile App** - Use the API to build mobile applications
4. **Advanced Security** - Implement additional security measures
5. **Monitoring** - Set up logging and monitoring systems

**Congratulations on completing this comprehensive learning journey!** 🌟 