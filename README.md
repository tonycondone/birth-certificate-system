# 🏥 Digital Birth Certificate Management System

A comprehensive, secure, and user-friendly digital birth certificate management system built with PHP 8.4, featuring real-time notifications, elegant certificate templates, and robust verification capabilities.

![System Status](https://img.shields.io/badge/Status-Production%20Ready-green)
![PHP Version](https://img.shields.io/badge/PHP-8.4-blue)
![Database](https://img.shields.io/badge/Database-MySQL%208.0+-orange)
![Frontend](https://img.shields.io/badge/Frontend-Bootstrap%205-purple)

## 🚀 Quick Start

```bash
# Clone the repository
git clone https://github.com/your-org/birth-certificate-system.git
cd birth-certificate-system

# Install dependencies
composer install
npm install

# Configure environment
cp .env.example .env
# Edit .env with your database credentials

# Start the development server
php -S localhost:8000 -t public public/router.php
```

**Default Admin Credentials:**

- Email: `admin@example.com`
- Password: `password`

## 📋 Table of Contents

- [Features](#-features)
- [System Requirements](#-system-requirements)
- [Installation](#-installation)
- [User Roles](#-user-roles)
- [API Documentation](#-api-documentation)
- [Security Features](#-security-features)
- [Testing](#-testing)
- [Troubleshooting](#-troubleshooting)
- [Contributing](#-contributing)

## ✨ Features

### 🎯 Core Features

- **Multi-Role User System**: Parent, Hospital, Registrar, and Admin roles
- **Application Management**: Complete birth certificate application workflow
- **Certificate Generation**: Professional, government-style certificate templates
- **Real-time Verification**: QR code and number-based instant verification
- **Document Management**: Secure file upload and storage system

### 🔔 Notification System

- **Live Notifications**: Real-time notification bell with polling updates
- **Browser Notifications**: Native browser notification support
- **In-app Toasts**: Elegant toast notifications with sound alerts
- **Email Notifications**: Automated email alerts for status changes
- **Admin Broadcasting**: System-wide announcements and maintenance alerts

### 👤 User Management

- **Profile Management**: Complete user profile editing
- **Password Security**: Secure password change functionality
- **Account Deletion**: Self-service account deletion with safeguards
- **Application History**: View and manage personal applications
- **Data Export**: GDPR-compliant data export functionality

### 📊 Dashboard & Reports

- **Role-based Dashboards**: Customized dashboards for each user role
- **Advanced Reporting**: Daily, weekly, and monthly reports with charts
- **Application Tracking**: Real-time application status tracking
- **Batch Processing**: Bulk approve/reject applications
- **Statistics & Analytics**: Comprehensive system statistics

### 🎨 Modern UI/UX

- **Responsive Design**: Mobile-first, fully responsive interface
- **Bootstrap 5**: Modern, accessible UI components
- **Dark/Light Theme**: User preference-based theming
- **Accessibility**: WCAG 2.1 AA compliant
- **Progressive Web App**: PWA capabilities for mobile devices

## 🛠 System Requirements

### Server Requirements

- **PHP**: 8.4 or higher
- **Database**: MySQL 8.0+ or MariaDB 10.4+
- **Web Server**: Apache 2.4+ or Nginx 1.18+
- **Memory**: 2GB RAM minimum
- **Storage**: 20GB available space

### PHP Extensions

```bash
php-pdo
php-mysql
php-json
php-gd
php-mbstring
php-xml
php-fileinfo
php-zip
php-curl
```

### Frontend Dependencies

- Bootstrap 5.3.0
- Font Awesome 6.4.0
- Chart.js 3.9.1
- SweetAlert2 11.7.12

## 🔧 Installation

### 1. Environment Setup

```bash
# Clone repository
git clone https://github.com/your-org/birth-certificate-system.git
cd birth-certificate-system

# Install PHP dependencies
composer install

# Install Node.js dependencies
npm install

# Build frontend assets
npm run build
```

### 2. Database Configuration

```bash
# Create database
mysql -u root -p -e "CREATE DATABASE birth_certificate_system CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# Configure environment
cp .env.example .env
```

Edit `.env` file:

```ini
DB_HOST=localhost
DB_NAME=birth_certificate_system
DB_USER=your_username
DB_PASS=your_password

APP_URL=http://localhost:8000
APP_DEBUG=true
```

### 3. Initialize Database

The system uses dynamic table creation, so tables will be created automatically when needed. For manual setup:

```bash
# Run the setup script
php setup_database.php
```

### 4. Start Development Server

```bash
# Start PHP development server
php -S localhost:8000 -t public public/router.php

# Access the application
open http://localhost:8000
```

## 👥 User Roles

### Parent

- Register and submit birth certificate applications
- Upload required documents (birth notification, ID documents)
- Track application status in real-time
- Download approved certificates
- Manage personal profile and applications
- Delete rejected applications

### Hospital

- Verify birth records and medical information
- Upload supporting medical documents
- Review and validate birth applications
- Communicate with registrars about applications

### Registrar

- Review and process birth certificate applications
- Approve or reject applications with detailed comments
- Generate official birth certificates
- Batch process multiple applications
- Generate comprehensive reports
- Manage application workflows

### Admin

- Full system administration capabilities
- User management (create, edit, disable accounts)
- System monitoring and audit logs
- Broadcast system-wide notifications
- Generate system reports and analytics
- Configure system settings

## 🔒 Security Features

### Authentication & Authorization

- **Bcrypt Password Hashing**: Secure password storage
- **Role-Based Access Control**: Granular permission system
- **Session Management**: Secure session handling
- **CSRF Protection**: Cross-site request forgery prevention



```bash
# Run PHP unit tests
composer test

# Run frontend tests
npm test

# Run integration tests
php run_tests.php
```




## System Architecture

```
┌─────────────────┐    ┌─────────────────┐    ┌─────────────────┐
│   Frontend      │    │   Backend       │    │   Database      │
│                 │    │                 │    │                 │
│ • Bootstrap 5   │◄──►│ • PHP 8.4       │◄──►│ • MySQL 8.0+    │
│ • JavaScript    │    │ • MVC Pattern   │    │ • InnoDB Engine │
│ • Chart.js      │    │ • RESTful API   │    │ • Full-text     │
│ • PWA Support   │    │ • Real-time     │    │   Search        │
└─────────────────┘    └─────────────────┘    └─────────────────┘
```




```bash
# Check database service
sudo service mysql status

# Test connection
mysql -u username -p database_name
```

**File Permission Issues**

```bash
# Fix storage permissions
chmod -R 755 storage/
chmod -R 755 public/uploads/
```

**Session Problems**

```bash
# Clear sessions
rm -rf storage/framework/sessions/*

# Check PHP session configuration
php -i | grep session
```

### Error Logs

```bash
jjjjjjjJJJJJjjJJJJJj# Application logs
tail -f storage/logs/app.log

# PHP error log
tail -f php_error.log

# Web server logs
tail -f /var/log/apache2/error.log
```




### Database Schema

- **Migration 027** applied: All certificate verification fixes
- **19 active certificates** ready for verification
- **10 approved applications** with valid certificate numbers
- **All tracking numbers** generated and functional



### Code Standards

- PSR-12 coding standards
- Comprehensive PHPDoc comments
- Unit test coverage
- Security best practices

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

##  Support

- **Documentation**: [Full Documentation](docs/)
- **Issues**: [GitHub Issues](https://github.com/your-org/birth-certificate-system/issues)
- **Security**: [Security Policy](SECURITY.md)
- **Email**: support@birthcert.system


---

**Built with ❤️ for secure and efficient birth certificate management**
