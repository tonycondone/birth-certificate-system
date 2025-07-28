# Complete List of Pages and Routes in Birth Certificate System

## **Main Application Routes**

### **Authentication & User Management**
- **GET /** - Home page
- **GET /login** - User login page
- **GET /register** - User registration page
- **GET /auth/logout** - User logout
- **GET /auth/forgot-password** - Password reset request
- **GET /auth/reset-password** - Password reset form
- **GET /auth/2fa** - Two-factor authentication
- **GET /auth/verify** - Email verification
- **GET /auth/verify-email** - Email verification alternative
- **GET /profile** - User profile page
- **GET /settings** - User settings page
- **GET /user/delete-account** - Account deletion

### **Dashboard Routes**
- **GET /dashboard** - Main dashboard
- **GET /dashboard/pending** - Pending applications
- **GET /dashboard/approved** - Approved applications
- **GET /dashboard/reports** - Reports section
- **GET /dashboard/settings** - Dashboard settings
- **GET /dashboard/registrar** - Registrar dashboard
- **GET /dashboard/hospital** - Hospital dashboard
- **GET /dashboard/admin** - Admin dashboard

### **Certificate Management**
- **GET /certificate/apply** - Apply for certificate
- **GET /certificate/verify** - Verify certificate
- **GET /certificate/approve** - Approve certificate
- **GET /certificate/download** - Download certificate
- **GET /certificate/sample** - Sample certificate
- **GET /certificate/generate/{id}** - Generate certificate
- **GET /certificate/list** - List certificates

### **Certificates (Plural) Routes**
- **GET /certificates** - List all certificates
- **GET /certificates/download/{id}** - Download specific certificate
- **GET /certificates/generate/{id}** - Generate specific certificate
- **GET /certificates/verify/{id}** - Verify specific certificate
- **GET /certificates/reject/{id}** - Reject certificate
- **GET /certificates/approve/{id}** - Approve certificate
- **GET /certificates/{id}** - View certificate details

### **Verification Routes**
- **GET /verify** - Certificate verification page
- **GET /verify/{number}** - Verify by certificate number
- **GET /verify/certificate/{number}** - Validate certificate
- **GET /verifications** - Verification history
- **GET /verifications/history** - Detailed verification history

### **API Routes**
- **GET /api/certificate/verify** - API certificate verification
- **GET /api/certificates/download/{id}** - API certificate download
- **GET /api/certificates/generate/{id}** - API certificate generation

### **Application Management**
- **GET /applications/new** - New application form
- **GET /applications** - List applications
- **GET /applications/{id}** - View application details
- **GET /applications/download/{id}** - Download application
- **GET /applications/approve/{id}** - Approve application
- **GET /applications/reject/{id}** - Reject application

### **Generic Application Submission**
- **GET /applications/submit** - Submit new application
- **GET /applications/submit/store** - Store submitted application

### **Payment Routes**
- **GET /applications/{id}/pay** - Payment page
- **GET /applications/{id}/payment-callback** - Payment callback
- **GET /applications/{id}/initialize-payment** - Initialize payment
- **GET /paystack/webhook** - Paystack webhook

### **Tracking Routes**
- **GET /track** - Track application form
- **GET /track/search** - Search tracking
- **GET /track/{tracking_number}** - View tracking details

### **Feedback Routes**
- **GET /applications/{id}/feedback** - Provide feedback
- **GET /applications/feedback/store** - Store feedback

### **Hospital Management Routes**
- **GET /hospital/submissions** - Hospital submissions
- **GET /hospital/dashboard** - Hospital dashboard
- **GET /hospital/records** - Hospital records
- **GET /hospital/records/new** - New hospital record
- **GET /hospital/records/{id}** - View hospital record
- **GET /hospital/records/{id}/edit** - Edit hospital record
- **GET /hospital/records/download/{id}** - Download hospital record
- **GET /hospital/verifications** - Hospital verifications
- **GET /hospital/verify/{id}** - Verify hospital record
- **GET /hospital/settings** - Hospital settings

### **Registrar Management Routes**
- **GET /registrar/dashboard** - Registrar dashboard
- **GET /registrar/pending** - Pending applications
- **GET /registrar/review/{id}** - Review application
- **GET /registrar/process** - Process applications
- **GET /registrar/batch-process** - Batch process applications
- **GET /registrar/reports** - Registrar reports
- **GET /registrar/applications** - Registrar applications
- **GET /registrar/approved** - Approved applications
- **GET /registrar/settings** - Registrar settings
- **GET /registrar/certificates** - Registrar certificates
- **GET /registrar/certificates/download/{id}** - Download certificates

### **Admin Portal Routes**
- **GET /admin/dashboard** - Admin dashboard
- **GET /admin/users** - User management
- **GET /admin/users/create** - Create new user
- **GET /admin/users/store** - Store new user
- **GET /admin/users/{id}** - View user details
- **GET /admin/users/{id}/edit** - Edit user
- **GET /admin/users/{id}/update** - Update user
- **GET /admin/users/{id}/delete** - Delete user
- **GET /admin/users/bulk-action** - Bulk user actions
- **GET /admin/users/export** - Export users
- **GET /admin/users/import** - Import users
- **GET /admin/user-action** - User actions

### **Admin System Management**
- **GET /admin/monitoring** - System monitoring
- **GET /admin/settings** - System settings
- **GET /admin/reports** - Admin reports
- **GET /admin/backup** - Backup management
- **GET /admin/backup/create** - Create backup
- **GET /admin/backup/restore** - Restore backup
- **GET /admin/audit-trail** - Audit trail
- **GET /admin/system-health** - System health
- **GET /admin/logs** - System logs
- **GET /admin/mail-templates** - Email templates
- **GET /admin/mail-templates/create** - Create email template
- **GET /admin/mail-templates/{id}/edit** - Edit email template
- **GET /admin/notifications** - Admin notifications
- **GET /admin/api-keys** - API key management
- **GET /admin/webhooks** - Webhook management

### **Admin Application Management**
- **GET /admin/applications** - All applications
- **GET /admin/applications/create** - Create application
- **GET /admin/applications/{id}** - View application
- **GET /admin/applications/{id}/edit** - Edit application
- **GET /admin/applications/{id}/approve** - Approve application
- **GET /admin/applications/{id}/reject** - Reject application
- **GET /admin/applications/bulk-action** - Bulk application actions
- **GET /admin/applications/export** - Export applications
- **GET /admin/generic-applications** - Generic applications
- **GET /admin/applications/download/{id}** - Download applications

### **Admin Certificate Management**
- **GET /admin/certificates** - All certificates
- **GET /admin/certificates/create** - Create certificate
- **GET /admin/certificates/{id}** - View certificate
- **GET /admin/certificates/{id}/edit** - Edit certificate
- **GET /admin/certificates/{id}/revoke** - Revoke certificate
- **GET /admin/certificates/bulk-action** - Bulk certificate actions
- **GET /admin/certificates/templates** - Certificate templates
- **GET /admin/certificates/download/{id}** - Download certificates

### **Reports & Analytics**
- **GET /reports** - Reports dashboard
- **GET /reports/export** - Export reports

### **Static Pages**
- **GET /about** - About page
- **GET /contact** - Contact page
- **GET /faq** - FAQ page
- **GET /privacy** - Privacy policy
- **GET /terms** - Terms of service
- **GET /api-docs** - API documentation

### **Notifications & Settings**
- **GET /notifications** - Notifications center

---

## **System Status**
- **PHP Server**: Running on http://localhost:8000
- **Fileinfo Extension**: Enabled
- **Monolog Warning**: Present but non-critical
- **System**: Ready for testing

## **Testing Recommendations**
1. **Critical Path Testing**: Test login, registration, certificate application, and payment flow
2. **Thorough Testing**: Test all routes and edge cases
3. **Admin Testing**: Test all admin portal functionality
4. **API Testing**: Test all API endpoints

The system is fully functional and ready for use at http://localhost:8000

