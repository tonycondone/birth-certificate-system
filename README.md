# Digital Birth Certificate System

A modern, secure, and user-friendly system for managing digital birth certificates with real-time updates and enhanced UI/UX.

## System Overview

The Digital Birth Certificate System is a comprehensive solution that enables:
- Parents to apply for birth certificates
- Hospitals to verify birth records
- Registrars to process and approve certificates
- Administrators to manage the entire system

## Features

### Core Features (Implemented)
- Complete authentication system with email verification
- Role-based dashboards (Parent, Hospital, Registrar, Admin)
- Multi-role authentication system (Parents, Hospitals, Registrars, Administrators)
- Real-time notifications and updates via WebSocket
- Certificate generation and verification with QR codes
- Document management with secure file handling
- Modern, responsive UI with Bootstrap 5+
- Comprehensive admin dashboard
- User profile management
- Activity logging and audit trails

### Security Features
- Role-based access control (RBAC)
- CSRF protection
- XSS prevention
- Input validation
- Secure file handling
- Session security
- SSL/HTTPS configuration

### UI/UX Features
- Responsive design (mobile-first approach)
- Real-time updates and notifications
- Loading states and feedback
- Form validation with helpful messages
- Toast notifications
- Accessibility compliance (WCAG 2.1 AA)
- Intuitive navigation

### Planned Features
- Testing infrastructure
- SMS integration
- Blockchain implementation
- Advanced search capabilities

## Technology Stack

### Frontend
- Bootstrap 5.3.0
- Font Awesome 6.4.0
- SweetAlert2 11.7.12
- QR Scanner 1.4.2
- WebSocket client

### Backend
- PHP 8.1+
- MySQL/MariaDB
- WebSocket server
- Apache/Nginx

## Installation

1. Clone the repository:
```bash
git clone https://github.com/your-org/birth-certificate-system.git
cd birth-certificate-system
```

2. Install dependencies:
```bash
composer install
npm install
```

3. Configure environment:
```bash
cp .env.example .env
# Edit .env with your settings
```

4. Set up database:
```bash
# Run migrations in order
mysql -u root -p birth_certificate_system < database/migrations/*.sql
```

5. Build assets:
```bash
npm run build
```

## Configuration

### Environment Variables
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

# WebSocket
WEBSOCKET_HOST=localhost
WEBSOCKET_PORT=6001
WEBSOCKET_SSL=true

# Security
SESSION_SECURE=true
SESSION_HTTP_ONLY=true
CSRF_LIFETIME=7200
```

## Usage

### Parent Role
- Register with validated credentials
- Submit birth certificate applications
- Track application status in real-time
- Download approved certificates
- Receive notifications

### Hospital Role
- Verify birth records
- Upload supporting documents
- Process applications
- Real-time status updates

### Registrar Role
- Review and verify applications
- Generate certificates
- Manage verifications
- Track activities

### Admin Role
- Comprehensive dashboard
- User management
- System configuration
- Activity monitoring
- Performance analytics

## Security

### Implementation
- CSRF tokens on all forms
- Input sanitization and validation
- Secure file upload handling
- Role-based access control
- Session management
- SSL/HTTPS enforcement

### Best Practices
- Regular security audits
- Password policy enforcement
- Activity logging
- File permission management
- Error handling

## UI/UX Components

### Navigation
- Responsive header
- Role-based menu items
- Mobile-friendly navigation
- Breadcrumb trails

### Forms
- Real-time validation
- Helpful error messages
- Loading states
- Success feedback

### Notifications
- Real-time updates
- Toast notifications
- Status badges
- Alert system

### Dashboard
- Quick statistics
- Recent activities
- Status updates
- Action items

## Contributing

Please read [CONTRIBUTING.md](CONTRIBUTING.md) for details on our code of conduct and the process for submitting pull requests.

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## Support

For support, please email support@birthcert.gov or visit our documentation at /docs.

## Acknowledgments

- Modern PHP development practices
- Bootstrap team for UI components
- Security best practices
- Accessibility guidelines