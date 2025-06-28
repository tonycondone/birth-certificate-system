# How to Make It Work - Digital Birth Certificate System

A comprehensive guide to setting up, configuring, running, and implementing features in the Digital Birth Certificate Registration Platform with modern UI/UX standards.

## Quick Start

1. **Environment Setup**
```bash
# Clone repository
git clone https://github.com/your-org/birth-certificate-system.git
cd birth-certificate-system

# Install dependencies
composer install
npm install

# Configure environment
cp .env.example .env
# Edit .env with your settings

# Set up database
mysql -u root -p birth_certificate_system < database/migrations/*.sql

# Build assets
npm run build

# Start servers
php -S localhost:8000 -t public/    # PHP development server
node websocket-server.js            # WebSocket server
```

2. **Default Credentials**
```
Admin:
Email: admin@example.com
Password: password

Test Hospital:
Email: hospital@example.com
Password: password

Test Registrar:
Email: registrar@example.com
Password: password
```

## Table of Contents

1. [System Requirements](#system-requirements)
2. [Initial Setup](#initial-setup)
3. [Database Setup](#database-setup)
4. [Configuration](#configuration)
5. [UI/UX Implementation](#ui-ux-implementation)
6. [Frontend Components](#frontend-components)
7. [Accessibility Guidelines](#accessibility-guidelines)
8. [Mobile Development](#mobile-development)
9. [Running the Application](#running-the-application)
10. [Using the System](#using-the-system)
11. [Testing Guidelines](#testing-guidelines)
12. [Troubleshooting](#troubleshooting)

## UI/UX Implementation

### Design System

1. **Color Scheme**
```scss
// Primary Colors
$primary: #0d6efd;
$secondary: #6c757d;
$success: #198754;
$info: #0dcaf0;
$warning: #ffc107;
$danger: #dc3545;

// Neutral Colors
$gray-100: #f8f9fa;
$gray-200: #e9ecef;
$gray-900: #212529;

// Semantic Colors
$border-color: $gray-200;
$text-muted: $gray-600;
```

2. **Typography**
```scss
// Font Families
$font-family-sans: 'Inter', system-ui, sans-serif;
$font-family-mono: 'JetBrains Mono', monospace;

// Font Sizes
$font-size-base: 1rem;
$font-size-sm: 0.875rem;
$font-size-lg: 1.125rem;

// Line Heights
$line-height-base: 1.5;
$line-height-sm: 1.25;
$line-height-lg: 1.75;
```

3. **Spacing System**
```scss
$spacer: 1rem;
$spacers: (
  0: 0,
  1: $spacer * .25,
  2: $spacer * .5,
  3: $spacer,
  4: $spacer * 1.5,
  5: $spacer * 3,
);
```

4. **Breakpoints**
```scss
$grid-breakpoints: (
  xs: 0,
  sm: 576px,
  md: 768px,
  lg: 992px,
  xl: 1200px,
  xxl: 1400px
);
```

### Component Guidelines

1. **Buttons**
```html
<!-- Primary Button -->
<button class="btn btn-primary">
  <span class="btn-text">Submit</span>
  <div class="btn-loader spinner-border spinner-border-sm d-none"></div>
</button>

<!-- Secondary Button -->
<button class="btn btn-secondary">Cancel</button>

<!-- Loading State -->
<script>
function showLoading(btn) {
  btn.disabled = true;
  btn.querySelector('.btn-text').classList.add('d-none');
  btn.querySelector('.btn-loader').classList.remove('d-none');
}
</script>
```

2. **Forms**
```html
<!-- Form Group with Validation -->
<div class="form-group">
  <label for="email" class="form-label">Email Address</label>
  <input type="email" 
         class="form-control" 
         id="email"
         required
         pattern="[^@]+@[^@]+\.[a-zA-Z]{2,6}"
         data-error="Please enter a valid email address">
  <div class="invalid-feedback">
    Please enter a valid email address
  </div>
  <div class="form-text">
    We'll never share your email with anyone else.
  </div>
</div>
```

3. **Alerts and Notifications**
```html
<!-- Success Alert -->
<div class="alert alert-success alert-dismissible fade show" role="alert">
  <i class="fas fa-check-circle me-2"></i>
  <strong>Success!</strong> Your changes have been saved.
  <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>

<!-- Error Alert -->
<div class="alert alert-danger" role="alert">
  <i class="fas fa-exclamation-circle me-2"></i>
  <strong>Error!</strong> Please fix the following issues:
  <ul class="mt-2 mb-0">
    <li>Invalid email format</li>
    <li>Password is required</li>
  </ul>
</div>
```

4. **Loading States**
```html
<!-- Content Loader -->
<div class="content-loader">
  <div class="spinner-border text-primary" role="status">
    <span class="visually-hidden">Loading...</span>
  </div>
</div>

<!-- Skeleton Loading -->
<div class="skeleton-loader">
  <div class="skeleton-item"></div>
  <div class="skeleton-item w-75"></div>
  <div class="skeleton-item w-50"></div>
</div>
```

### Accessibility Guidelines

1. **ARIA Labels**
```html
<!-- Interactive Elements -->
<button aria-label="Close modal">×</button>
<input aria-describedby="passwordHelpText" type="password">
<div id="passwordHelpText">Password must be at least 8 characters</div>

<!-- Dynamic Content -->
<div role="alert" aria-live="polite">
  Form submitted successfully
</div>
```

2. **Keyboard Navigation**
```javascript
// Trap focus in modals
function trapFocus(element) {
  const focusableElements = element.querySelectorAll(
    'button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])'
  );
  const firstFocusableElement = focusableElements[0];
  const lastFocusableElement = focusableElements[focusableElements.length - 1];

  element.addEventListener('keydown', function(e) {
    if (e.key === 'Tab') {
      if (e.shiftKey) {
        if (document.activeElement === firstFocusableElement) {
          lastFocusableElement.focus();
          e.preventDefault();
        }
      } else {
        if (document.activeElement === lastFocusableElement) {
          firstFocusableElement.focus();
          e.preventDefault();
        }
      }
    }
  });
}
```

### Mobile Development

1. **Viewport Configuration**
```html
<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
```

2. **Touch Targets**
```scss
// Minimum touch target size
.touch-target {
  min-height: 44px;
  min-width: 44px;
  padding: 12px;
}
```

3. **Mobile Navigation**
```html
<!-- Mobile Menu Button -->
<button class="navbar-toggler" 
        type="button" 
        data-bs-toggle="collapse" 
        data-bs-target="#navbarNav"
        aria-controls="navbarNav"
        aria-expanded="false"
        aria-label="Toggle navigation">
  <span class="navbar-toggler-icon"></span>
</button>

<!-- Mobile Menu -->
<div class="collapse navbar-collapse" id="navbarNav">
  <!-- Navigation items -->
</div>
```

### Real-time Features Implementation

1. **WebSocket Configuration**
```php
// config/websocket.php
return [
    'server' => [
        'host' => env('WEBSOCKET_HOST', 'localhost'),
        'port' => env('WEBSOCKET_PORT', 6001),
        'options' => [
            'tls' => [
                'verify_peer' => false,
            ],
        ],
    ],
    'ssl' => [
        'local' => false,
        'production' => true
    ],
    'auth' => [
        'timeout' => 30000
    ]
];
```

2. **WebSocket Client Setup**
```javascript
// resources/js/websocket.js
class WebSocketClient {
    constructor() {
        this.connect();
        this.setupReconnection();
    }

    connect() {
        const protocol = window.location.protocol === 'https:' ? 'wss:' : 'ws:';
        const host = window.location.hostname;
        const port = WEBSOCKET_PORT;
        
        this.ws = new WebSocket(`${protocol}//${host}:${port}`);
        this.setupEventHandlers();
    }

    setupEventHandlers() {
        this.ws.onopen = () => {
            console.log('Connected to WebSocket');
            this.heartbeat();
        };

        this.ws.onmessage = (event) => {
            const data = JSON.parse(event.data);
            this.handleMessage(data);
        };

        this.ws.onclose = () => {
            console.log('Disconnected from WebSocket');
            this.reconnect();
        };
    }

    handleMessage(data) {
        switch (data.type) {
            case 'notification':
                this.showNotification(data);
                break;
            case 'status_update':
                this.updateStatus(data);
                break;
            case 'certificate_ready':
                this.notifyCertificateReady(data);
                break;
        }
    }

    showNotification(data) {
        const toast = new bootstrap.Toast(document.getElementById('notificationToast'));
        document.getElementById('toastTitle').textContent = data.title;
        document.getElementById('toastMessage').textContent = data.message;
        toast.show();
    }

    heartbeat() {
        setInterval(() => {
            if (this.ws.readyState === WebSocket.OPEN) {
                this.ws.send(JSON.stringify({ type: 'ping' }));
            }
        }, 30000);
    }

    reconnect() {
        setTimeout(() => this.connect(), 5000);
    }
}

// Initialize WebSocket client
const wsClient = new WebSocketClient();
```

3. **Real-time Notifications**
```javascript
// resources/js/notifications.js
class NotificationManager {
    constructor(wsClient) {
        this.wsClient = wsClient;
        this.unreadCount = 0;
        this.setupEventListeners();
    }

    setupEventListeners() {
        // Listen for new notifications
        this.wsClient.on('notification', (data) => {
            this.handleNewNotification(data);
        });

        // Handle notification clicks
        document.querySelectorAll('.notification-item').forEach(item => {
            item.addEventListener('click', () => this.markAsRead(item.dataset.id));
        });
    }

    handleNewNotification(data) {
        // Update notification badge
        this.unreadCount++;
        this.updateBadge();

        // Show toast notification
        const toast = new bootstrap.Toast(document.getElementById('notificationToast'));
        document.getElementById('toastTitle').textContent = data.title;
        document.getElementById('toastMessage').textContent = data.message;
        toast.show();

        // Add to notification list
        const list = document.getElementById('notificationsList');
        if (list) {
            list.insertAdjacentHTML('afterbegin', this.createNotificationHTML(data));
        }
    }

    createNotificationHTML(data) {
        return `
            <div class="notification-item unread" data-id="${data.id}">
                <div class="notification-icon bg-${data.type}">
                    <i class="fas ${this.getIconClass(data.type)}"></i>
                </div>
                <div class="notification-content">
                    <h6 class="notification-title">${data.title}</h6>
                    <p class="notification-message">${data.message}</p>
                    <small class="notification-time">Just now</small>
                </div>
            </div>
        `;
    }

    getIconClass(type) {
        const icons = {
            success: 'fa-check-circle',
            warning: 'fa-exclamation-triangle',
            info: 'fa-info-circle',
            error: 'fa-times-circle'
        };
        return icons[type] || 'fa-bell';
    }

    markAsRead(id) {
        fetch(`/api/notifications/${id}/read`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.querySelector(`[data-id="${id}"]`).classList.remove('unread');
                this.unreadCount = Math.max(0, this.unreadCount - 1);
                this.updateBadge();
            }
        });
    }

    updateBadge() {
        const badge = document.getElementById('notificationBadge');
        if (badge) {
            badge.textContent = this.unreadCount;
            badge.classList.toggle('d-none', this.unreadCount === 0);
        }
    }
}

// Initialize notification manager
const notificationManager = new NotificationManager(wsClient);
```

4. **Real-time Status Updates**
```javascript
// resources/js/status-updates.js
class StatusUpdater {
    constructor(wsClient) {
        this.wsClient = wsClient;
        this.setupEventListeners();
    }

    setupEventListeners() {
        this.wsClient.on('status_update', (data) => {
            this.updateApplicationStatus(data);
        });
    }

    updateApplicationStatus(data) {
        const statusElement = document.querySelector(`[data-application-id="${data.id}"] .status`);
        if (statusElement) {
            statusElement.textContent = data.status;
            statusElement.className = `status badge bg-${this.getStatusColor(data.status)}`;
            
            // Show toast notification
            this.showStatusUpdateToast(data);
        }
    }

    getStatusColor(status) {
        const colors = {
            pending: 'warning',
            approved: 'success',
            rejected: 'danger',
            processing: 'info'
        };
        return colors[status] || 'secondary';
    }

    showStatusUpdateToast(data) {
        const toast = new bootstrap.Toast(document.getElementById('statusToast'));
        document.getElementById('statusTitle').textContent = 'Status Update';
        document.getElementById('statusMessage').textContent = 
            `Application #${data.id} status changed to ${data.status}`;
        toast.show();
    }
}

// Initialize status updater
const statusUpdater = new StatusUpdater(wsClient);
```

5. **Certificate Generation Updates**
```javascript
// resources/js/certificate-updates.js
class CertificateManager {
    constructor(wsClient) {
        this.wsClient = wsClient;
        this.setupEventListeners();
    }

    setupEventListeners() {
        this.wsClient.on('certificate_ready', (data) => {
            this.handleCertificateReady(data);
        });
    }

    handleCertificateReady(data) {
        // Update UI
        const statusElement = document.querySelector(`[data-certificate-id="${data.id}"] .status`);
        if (statusElement) {
            statusElement.textContent = 'Ready';
            statusElement.className = 'status badge bg-success';
        }

        // Show download button
        const downloadButton = document.querySelector(`[data-certificate-id="${data.id}"] .download-btn`);
        if (downloadButton) {
            downloadButton.classList.remove('d-none');
            downloadButton.href = `/certificates/download/${data.id}`;
        }

        // Show notification
        const toast = new bootstrap.Toast(document.getElementById('certificateToast'));
        document.getElementById('certificateTitle').textContent = 'Certificate Ready';
        document.getElementById('certificateMessage').textContent = 
            `Your certificate #${data.id} is ready for download`;
        toast.show();
    }
}

// Initialize certificate manager
const certificateManager = new CertificateManager(wsClient);
```
```html
<!-- Mobile Menu Button -->
<button class="navbar-toggler" 
        type="button" 
        data-bs-toggle="collapse" 
        data-bs-target="#navbarNav"
        aria-controls="navbarNav"
        aria-expanded="false"
        aria-label="Toggle navigation">
  <span class="navbar-toggler-icon"></span>
</button>

<!-- Mobile Menu -->
<div class="collapse navbar-collapse" id="navbarNav">
  <!-- Navigation items -->
</div>
```

### Testing Guidelines

1. **Cross-browser Testing**
- Chrome (latest)
- Firefox (latest)
- Safari (latest)
- Edge (latest)
- Mobile Safari
- Chrome for Android

2. **Responsive Testing**
- Extra small (<576px)
- Small (≥576px)
- Medium (≥768px)
- Large (≥992px)
- Extra large (≥1200px)
- Extra extra large (≥1400px)

3. **Accessibility Testing**
- WAVE Web Accessibility Evaluation Tool
- aXe DevTools
- Keyboard navigation testing
- Screen reader testing (NVDA, VoiceOver)

4. **Performance Testing**
- Lighthouse audits
- WebPageTest
- GTmetrix

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
