# API Documentation - Digital Birth Certificate System

## Overview

The Digital Birth Certificate System provides a comprehensive RESTful API for managing birth certificate applications, user accounts, notifications, and system administration. All endpoints require proper authentication and follow role-based access control.

## Base URL

```
Development: http://localhost:8000
Production: https://your-domain.com
```

## Authentication

### Session-Based Authentication
The system uses session-based authentication. Users must log in to receive a session cookie.

```http
POST /auth/login
Content-Type: application/x-www-form-urlencoded

email=user@example.com&password=password123
```

### Response
```json
{
  "success": true,
  "message": "Login successful",
  "user": {
    "id": 1,
    "username": "john_doe",
    "email": "user@example.com",
    "role": "parent",
    "first_name": "John",
    "last_name": "Doe"
  },
  "redirect": "/dashboard"
}
```

## User Roles

- **parent**: Can submit applications and manage personal data
- **hospital**: Can verify birth records and upload medical documents
- **registrar**: Can review, approve, and reject applications
- **admin**: Full system administration capabilities

## Core Endpoints

### Authentication Endpoints

#### POST /auth/login
Authenticate user and create session.

**Request Body:**
```json
{
  "email": "user@example.com",
  "password": "password123"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Login successful",
  "user": {
    "id": 1,
    "role": "parent",
    "first_name": "John",
    "last_name": "Doe"
  }
}
```

#### POST /auth/register
Register a new user account.

**Request Body:**
```json
{
  "username": "john_doe",
  "email": "john@example.com",
  "password": "securePassword123!",
  "first_name": "John",
  "last_name": "Doe",
  "role": "parent",
  "phone_number": "+1234567890",
  "national_id": "ID123456789"
}
```

#### POST /auth/logout
End user session.

**Response:**
```json
{
  "success": true,
  "message": "Logout successful"
}
```

### Application Management

#### GET /applications
List user's applications with pagination and filtering.

**Query Parameters:**
- `page` (int): Page number (default: 1)
- `limit` (int): Items per page (default: 10)
- `status` (string): Filter by status
- `search` (string): Search term

**Response:**
```json
{
  "success": true,
  "applications": [
    {
      "id": 1,
      "application_number": "APP-2024-0001",
      "tracking_number": "TRK-2024-0001",
      "status": "submitted",
      "child_first_name": "Jane",
      "child_last_name": "Doe",
      "date_of_birth": "2024-01-01",
      "submitted_at": "2024-01-05 10:30:00",
      "days_pending": 5
    }
  ],
  "pagination": {
    "current_page": 1,
    "total_pages": 3,
    "total_items": 25,
    "items_per_page": 10
  }
}
```

#### POST /applications
Create a new birth certificate application.

**Request Body:**
```json
{
  "child_first_name": "Jane",
  "child_last_name": "Doe",
  "child_middle_name": "Marie",
  "date_of_birth": "2024-01-01",
  "time_of_birth": "14:30:00",
  "place_of_birth": "General Hospital",
  "gender": "female",
  "weight_at_birth": 3.2,
  "length_at_birth": 50.0,
  "father_first_name": "John",
  "father_last_name": "Doe",
  "father_national_id": "ID123456789",
  "mother_first_name": "Mary",
  "mother_last_name": "Doe",
  "mother_national_id": "ID987654321",
  "hospital_name": "General Hospital",
  "attending_physician": "Dr. Smith"
}
```

#### GET /applications/{id}
Get detailed information about a specific application.

**Response:**
```json
{
  "success": true,
  "application": {
    "id": 1,
    "application_number": "APP-2024-0001",
    "status": "approved",
    "child_first_name": "Jane",
    "child_last_name": "Doe",
    "date_of_birth": "2024-01-01",
    "place_of_birth": "General Hospital",
    "father_name": "John Doe",
    "mother_name": "Mary Doe",
    "submitted_at": "2024-01-05 10:30:00",
    "reviewed_at": "2024-01-10 15:45:00",
    "reviewed_by": "Registrar Name",
    "review_notes": "All documents verified successfully"
  }
}
```

#### DELETE /applications/{id}
Delete an application (only draft or rejected applications).

**Response:**
```json
{
  "success": true,
  "message": "Application deleted successfully"
}
```

### Certificate Management

#### GET /certificates
List certificates accessible to the current user.

**Query Parameters:**
- `page` (int): Page number
- `status` (string): Filter by certificate status
- `search` (string): Search by certificate number or child name

**Response:**
```json
{
  "success": true,
  "certificates": [
    {
      "id": 1,
      "certificate_number": "BC202508D7C911",
      "child_first_name": "Jane",
      "child_last_name": "Doe",
      "date_of_birth": "2024-01-01",
      "issued_at": "2024-01-10 16:00:00",
      "issued_by": "Registrar Name",
      "status": "active",
      "verification_count": 3
    }
  ]
}
```

#### GET /certificates/{id}
Get detailed certificate information.

**Response:**
```json
{
  "success": true,
  "certificate": {
    "id": 1,
    "certificate_number": "BC202508D7C911",
    "qr_code_hash": "abc123def456",
    "child_first_name": "Jane",
    "child_last_name": "Doe",
    "date_of_birth": "2024-01-01",
    "place_of_birth": "General Hospital",
    "father_name": "John Doe",
    "mother_name": "Mary Doe",
    "issued_at": "2024-01-10 16:00:00",
    "issued_by": "Registrar Name",
    "status": "active"
  }
}
```

#### GET /certificates/{id}/download
Download certificate as PDF.

**Response:** PDF file download

#### POST /certificates/{id}/verify
Verify certificate authenticity.

**Request Body:**
```json
{
  "certificate_number": "BC202508D7C911"
}
```

**Response:**
```json
{
  "success": true,
  "valid": true,
  "certificate": {
    "certificate_number": "BC202508D7C911",
    "child_name": "Jane Marie Doe",
    "date_of_birth": "2024-01-01",
    "issued_at": "2024-01-10 16:00:00",
    "status": "active"
  },
  "verification_count": 4
}
```

### Notification System

#### GET /notifications
List user notifications with pagination.

**Query Parameters:**
- `page` (int): Page number
- `filter` (string): all, unread, read
- `type` (string): Filter by notification type

**Response:**
```json
{
  "success": true,
  "notifications": [
    {
      "id": 1,
      "title": "Application Approved",
      "message": "Your birth certificate application has been approved",
      "type": "success",
      "priority": "high",
      "is_read": false,
      "created_at": "2024-01-10 14:30:00"
    }
  ],
  "stats": {
    "total": 15,
    "unread": 3,
    "read": 12
  }
}
```

#### GET /notifications/poll
Real-time notification polling endpoint.

**Query Parameters:**
- `since` (timestamp): Get notifications since this timestamp

**Response:**
```json
{
  "success": true,
  "new_notifications": [
    {
      "id": 2,
      "title": "Payment Pending",
      "message": "Please complete payment for application #BC2024001",
      "type": "warning",
      "priority": "normal",
      "created_at": "2024-01-10 15:00:00",
      "timestamp": 1641826800
    }
  ],
  "unread_count": 4,
  "server_time": 1641826900
}
```

#### GET /notifications/get-unread-count
Get count of unread notifications.

**Response:**
```json
{
  "success": true,
  "count": 5
}
```

#### GET /notifications/get-recent
Get recent notifications for dropdown display.

**Response:**
```json
{
  "success": true,
  "notifications": [
    {
      "id": 1,
      "title": "Application Approved",
      "message": "Your birth certificate application has been approved",
      "type": "success",
      "created_at": "2024-01-10 14:30:00",
      "time_ago": "2 hours ago"
    }
  ]
}
```

#### POST /notifications/{id}/read
Mark notification as read.

**Response:**
```json
{
  "success": true,
  "message": "Notification marked as read"
}
```

#### DELETE /notifications/{id}
Delete a notification.

**Response:**
```json
{
  "success": true,
  "message": "Notification deleted successfully"
}
```

#### POST /notifications/mark-all-read
Mark all user notifications as read.

**Response:**
```json
{
  "success": true,
  "message": "All notifications marked as read",
  "count": 5
}
```

### User Settings & Profile Management

#### GET /settings
Get user settings page (returns HTML view).

#### POST /settings/update-profile
Update user profile information.

**Request Body:**
```json
{
  "first_name": "John",
  "last_name": "Doe",
  "email": "john.doe@example.com",
  "phone_number": "+1234567890"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Profile updated successfully"
}
```

#### POST /settings/change-password
Change user password.

**Request Body:**
```json
{
  "current_password": "oldPassword123",
  "new_password": "newSecurePassword456!",
  "confirm_password": "newSecurePassword456!"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Password changed successfully"
}
```

#### POST /settings/delete-account
Delete user account.

**Request Body:**
```json
{
  "confirmation": "DELETE",
  "password": "currentPassword123"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Account deleted successfully"
}
```

#### GET /settings/applications
Get user's application history.

**Response:**
```json
{
  "success": true,
  "applications": [
    {
      "id": 1,
      "application_number": "APP-2024-0001",
      "status": "approved",
      "child_name": "Jane Doe",
      "submitted_at": "2024-01-05 10:30:00",
      "can_delete": false
    }
  ]
}
```

#### DELETE /settings/applications/{id}
Delete a specific application from user's history.

**Response:**
```json
{
  "success": true,
  "message": "Application deleted successfully"
}
```

#### GET /settings/export-data
Export user's personal data (GDPR compliance).

**Response:** JSON file download with user's data

### Application Tracking

#### GET /tracking
Get application tracking form (returns HTML view).

#### POST /tracking
Track application by tracking number.

**Request Body:**
```json
{
  "tracking_number": "TRK-2024-0001"
}
```

**Response:** Redirect to `/tracking/show/{tracking_number}`

#### GET /tracking/show/{tracking_number}
Get detailed tracking information.

**Response:**
```json
{
  "success": true,
  "application": {
    "id": 1,
    "application_number": "APP-2024-0001",
    "tracking_number": "TRK-2024-0001",
    "status": "approved",
    "child_name": "Jane Marie Doe",
    "submitted_at": "2024-01-05 10:30:00",
    "last_updated": "2024-01-10 15:45:00"
  },
  "status_history": [
    {
      "status": "submitted",
      "timestamp": "2024-01-05 10:30:00",
      "description": "Application submitted for review"
    },
    {
      "status": "under_review",
      "timestamp": "2024-01-08 09:00:00",
      "description": "Application under review by registrar"
    },
    {
      "status": "approved",
      "timestamp": "2024-01-10 15:45:00",
      "description": "Application approved and certificate generated"
    }
  ]
}
```

### Registrar-Specific Endpoints

#### GET /registrar/pending
List pending applications for review.

**Query Parameters:**
- `page` (int): Page number
- `search` (string): Search term
- `days_pending` (int): Filter by days pending

**Response:**
```json
{
  "success": true,
  "applications": [
    {
      "id": 1,
      "application_number": "APP-2024-0001",
      "child_name": "Jane Doe",
      "submitted_at": "2024-01-05 10:30:00",
      "days_pending": 5,
      "status": "submitted"
    }
  ]
}
```

#### GET /registrar/approved
List approved applications.

**Response:**
```json
{
  "success": true,
  "applications": [
    {
      "id": 1,
      "application_number": "APP-2024-0001",
      "child_name": "Jane Doe",
      "approved_at": "2024-01-10 15:45:00",
      "certificate_number": "BC202508D7C911"
    }
  ]
}
```

#### GET /registrar/batch-process
Get batch processing form (returns HTML view).

#### POST /registrar/batch-process
Process multiple applications in batch.

**Request Body:**
```json
{
  "application_ids": [1, 2, 3],
  "action": "approve",
  "comments": "All documents verified successfully"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Batch processing completed. 3 successful, 0 errors.",
  "results": [
    {
      "success": true,
      "message": "Application approved successfully",
      "application_id": 1
    }
  ]
}
```

#### GET /registrar/reports
Get reports dashboard with filters.

**Query Parameters:**
- `type` (string): daily, weekly, monthly, performance
- `start_date` (date): Start date for report
- `end_date` (date): End date for report

**Response:**
```json
{
  "success": true,
  "report_type": "daily",
  "date_range": {
    "start": "2024-01-01",
    "end": "2024-01-10"
  },
  "summary": {
    "total_applications": 150,
    "approved": 120,
    "rejected": 20,
    "pending": 10
  },
  "chart_data": {
    "labels": ["Jan 1", "Jan 2", "Jan 3"],
    "datasets": [
      {
        "label": "Applications",
        "data": [15, 20, 18]
      }
    ]
  }
}
```

### Admin-Specific Endpoints

#### POST /notifications/create
Create system notification (Admin only).

**Request Body:**
```json
{
  "user_id": 1,
  "title": "System Maintenance",
  "message": "System will be under maintenance from 2-4 AM",
  "type": "warning",
  "priority": "high"
}
```

#### POST /notifications/broadcast
Broadcast notification to all users (Admin only).

**Request Body:**
```json
{
  "title": "System Update",
  "message": "New features have been added to the system",
  "type": "info",
  "priority": "normal",
  "roles": ["parent", "registrar"]
}
```

## Error Responses

All endpoints return consistent error responses:

```json
{
  "success": false,
  "error": "Error message",
  "code": "ERROR_CODE",
  "details": {
    "field": "Specific field error message"
  }
}
```

### Common Error Codes

- `UNAUTHORIZED`: User not authenticated
- `FORBIDDEN`: User lacks required permissions
- `VALIDATION_ERROR`: Input validation failed
- `NOT_FOUND`: Resource not found
- `RATE_LIMITED`: Too many requests
- `SERVER_ERROR`: Internal server error

## Rate Limiting

API endpoints are rate-limited to prevent abuse:

- **Authentication**: 5 requests per minute
- **General API**: 100 requests per minute
- **File uploads**: 10 requests per minute

Rate limit headers are included in responses:
```
X-RateLimit-Limit: 100
X-RateLimit-Remaining: 95
X-RateLimit-Reset: 1641826800
```

## WebSocket Support (Future)

Real-time features currently use polling but will be migrated to WebSocket:

```javascript
// Future WebSocket implementation
const ws = new WebSocket('wss://your-domain.com/ws');
ws.onmessage = function(event) {
  const data = JSON.parse(event.data);
  handleNotification(data);
};
```

## SDK Examples

### JavaScript/Node.js
```javascript
// Authentication
const response = await fetch('/auth/login', {
  method: 'POST',
  headers: { 'Content-Type': 'application/json' },
  body: JSON.stringify({
    email: 'user@example.com',
    password: 'password123'
  })
});

// Get notifications
const notifications = await fetch('/notifications?page=1&filter=unread');
const data = await notifications.json();
```

### PHP
```php
// Using cURL for API requests
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'http://localhost:8000/applications');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_COOKIE, $sessionCookie);
$response = curl_exec($ch);
$data = json_decode($response, true);
```

## Testing

API endpoints can be tested using:
- Postman collection (available in `/docs/postman/`)
- cURL commands
- Built-in test scripts (`php run_tests.php`)

## Changelog

See [CHANGELOG.md](CHANGELOG.md) for API version history and breaking changes. 