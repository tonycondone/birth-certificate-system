# Official Documentation: Digital Birth Certificate System

---

## 1. Project Overview & Architecture

### 1.1. Executive Summary

This document provides the official technical documentation for the Digital Birth Certificate System, a government-standard platform designed to modernize the registration, issuance, and verification of birth certificates. The system replaces an inefficient, paper-based process with a secure, efficient, and transparent digital workflow for citizens, healthcare providers, and government registrars.

**Core Objectives:**
-   **Accessibility & Efficiency:** Provide a 24/7 online portal to drastically reduce processing times from months to hours.
-   **Security & Integrity:** Leverage modern security practices, including data encryption and process integrity checks, to create a tamper-proof system of record.
-   **Accuracy & Reliability:** Minimize human error by integrating directly with data sources and validating information against official databases.
-   **Inclusivity & Support:** Ensure all citizens can access the system through multi-channel support and intuitive design.

### 1.2. System Architecture

The system is designed using a classic Model-View-Controller (MVC) pattern, which provides a clear separation of concerns between business logic, data handling, and presentation.

```mermaid
graph TD;
    subgraph "User's Browser"
        A[User Interface <br/>(HTML, CSS, JS, Bootstrap 5)]
    end

    subgraph "Web Server (Apache/Nginx)"
        B[Public Assets <br/>(CSS, JS, Images)]
        C[index.php <br/>(Central Router)]
    end

    subgraph "Application Core (PHP 8.1+)"
        D[Controllers <br/>(Handles HTTP Requests)]
        E[Models <br/>(Database Interaction via PDO)]
        F[Views <br/>(Renders HTML Templates)]
        G[Services <br/>(Business & Validation Logic)]
    end

    subgraph "Database Server"
        H[MySQL Database]
    end

    A -- HTTP Request --> C;
    C -- Routes to --> D;
    D -- Uses --> G;
    D -- Interacts with --> E;
    E -- Sends SQL Queries --> H;
    H -- Returns Data --> E;
    E -- Returns Data to --> D;
    D -- Passes Data to & Renders --> F;
    F -- Generates HTML --> A;
    A -- Requests Static Assets --> B;
```

### 1.3. Request Lifecycle

1.  **Entry Point:** All public requests are directed to `public/index.php`.
2.  **Routing:** The router parses the URL and dispatches the request to the appropriate `Controller@method`.
3.  **Processing:** The Controller handles the request, using `Models` to interact with the database and `Services` for complex logic like data validation or user authentication.
4.  **Response:** The Controller passes the resulting data to a `View`, which renders the final HTML template.
5.  **Output:** The generated HTML is sent back to the user's browser.

---

## 2. System Requirements & Installation

### 2.1. Server Requirements
-   **Operating System:** Linux (recommended), Windows, or macOS.
-   **Web Server:** Apache 2.4+ or Nginx 1.18+.
-   **PHP:** Version 8.1 or higher.
-   **PHP Extensions:** `PDO`, `pdo_mysql`, `mbstring`, `openssl`.
-   **Database:** MySQL 8.0+ or MariaDB 10.6+.
-   **Version Control:** Git.

### 2.2. Installation Guide

1.  **Clone Repository:**
    ```bash
    git clone <repository-url>
    cd birth-certificate-system
    ```
2.  **Configure Environment:**
    -   Copy the environment file: `cp .env.example .env`
    -   Edit the `.env` file with your database credentials and application URL.
3.  **Database Setup:**
    -   Create a MySQL database: `CREATE DATABASE birth_certificate_system;`
    -   Import all SQL migration files from the `database/migrations/` directory in sequential order.
4.  **Run the Application:**
    -   Use the PHP built-in server for local development:
        ```bash
        php -S localhost:8000 -t public
        ```
    -   Access the system at `http://localhost:8000`.

---

## 3. User Roles & Authentication

The system defines three distinct user roles with specific permissions.

### 3.1. Role Definitions

-   **Citizen:** The primary user. Can register, log in, apply for certificates, track application status, and view their issued certificates.
-   **Registrar:** A government employee. Can review, approve, or reject applications submitted by citizens. They have access to a dedicated dashboard to manage the application queue.
-   **Administrator:** A system superuser. Can manage all user accounts, configure system-wide settings, view audit logs, and oversee the health of the entire platform.

### 3.2. Authentication Flow

-   **Registration:** New citizens create an account using a unique email and a strong password.
-   **Login:** Registered users log in with their email and password. The system establishes a secure session.
-   **Role-Based Redirect:** Upon successful login, users are redirected to their respective dashboards (`/dashboard`). The `DashboardController` determines the user's role and displays the appropriate view and data.
-   **Logout:** Users can terminate their session at any time via the logout link.

---

## 4. Certificate Application Process

The core workflow of the system is the application for and issuance of a birth certificate.

1.  **Initiation:** A logged-in `Citizen` starts a new application from their dashboard.
2.  **Data Entry:** The citizen fills out a multi-step form with child and parent details. The form includes real-time, client-side validation.
3.  **Document Upload:** The citizen uploads required supporting documents (e.g., hospital birth summary, parental IDs).
4.  **Submission:** Upon submission, the application is saved to the `birth_applications` table with a `Pending` status.
5.  **Registrar Review:** The application appears in the queue on the `Registrar` dashboard. The registrar reviews the details and documents.
6.  **Decision:**
    -   **Approve:** The registrar approves the application. A new entry is created in the `certificates` table, a unique certificate number is generated, and the citizen is notified.
    -   **Reject:** The registrar rejects the application, providing a reason for the rejection. The citizen is notified.
7.  **Issuance:** The `Citizen` can now view and download their digitally-signed certificate from their dashboard.

---

## 5. Database Schema & API Endpoints

### 5.1. Core Database Schema

| Table | Purpose | Key Columns |
|---|---|---|
| `users` | Stores all user accounts and their roles. | `id`, `email`, `password`, `role` |
| `birth_applications` | Stores all application data submitted by citizens. | `id`, `user_id`, `child_first_name`, `status` |
| `certificates` | Stores final, issued certificates after approval. | `id`, `application_id`, `certificate_number` |
| `system_settings`| Key-value store for global application settings. | `setting_key`, `setting_value` |

### 5.2. Core API Endpoints (Web Routes)

The system uses a simple routing mechanism defined in `public/index.php`.

| Method | URI | Controller@Method | Description |
|---|---|---|---|
| `GET` | `/` | `HomeController@index` | Displays the home page. |
| `GET` | `/login` | `AuthController@showLoginForm` | Displays the login page. |
| `POST`| `/login` | `AuthController@login` | Handles user login attempt. |
| `GET` | `/register`| `AuthController@showRegisterForm`| Displays the registration page. |
| `POST`| `/register`| `AuthController@register` | Handles new user registration. |
| `GET` | `/dashboard`| `DashboardController@index` | Displays the role-specific dashboard. |
| `GET` | `/certificate/apply` | `CertificateController@create` | Displays the application form. |
| `POST`| `/certificate/apply` | `CertificateController@store` | Stores a new application. |
| `GET` | `/verify` | `CertificateController@showVerify` | Displays the certificate verification page. |

---

## 6. Security Protocols & Compliance

### 6.1. Technical Security Protocols

-   **SQL Injection Prevention:** All database queries are executed using `PDO` prepared statements with parameterized inputs.
-   **Cross-Site Scripting (XSS) Prevention:** All dynamic data rendered in views is escaped using `htmlspecialchars()`.
-   **CSRF Protection:** All forms that perform state-changing actions are protected with CSRF tokens stored in the user's session.
-   **Secure File Uploads:** Uploaded files are validated by type and size and stored in a non-public directory. Access is granted via a secure script.
-   **Strong Password Hashing:** User passwords are securely hashed using the `password_hash()` function with the `BCRYPT` algorithm.

### 6.2. Compliance and Auditing
-   **Data Privacy:** The system is designed to comply with standard data protection regulations by limiting data access based on user roles.
-   **Audit Trails:** Key activities (logins, application status changes, certificate issuance) are logged to provide a trail for security reviews and to ensure accountability.

---

## 7. Troubleshooting & Maintenance

### 7.1. Common Troubleshooting
-   **500 Internal Server Error:** Check the PHP error logs (`php_error.log` in the root directory) for detailed error messages. Common causes include database connection failures or syntax errors.
-   **Database Connection Failed:** Verify that the `DB_HOST`, `DB_NAME`, `DB_USER`, and `DB_PASS` credentials in the `.env` file are correct and that the MySQL server is running.
-   **404 Not Found:** Ensure the requested route is defined in `public/index.php` and that the corresponding Controller and method exist.

### 7.2. System Maintenance
-   **Log Rotation:** Regularly review and clear application and server logs to monitor for issues and prevent them from consuming excessive disk space.
-   **Database Backups:** Implement a regular backup schedule for the MySQL database to prevent data loss.
-   **Dependency Updates:** Periodically check for updates to all libraries and frameworks to apply security patches and performance improvements.

---

## 8. User Guides for Each Role

### 8.1. Citizen Guide
-   **Dashboard:** View your existing applications and issued certificates.
-   **Apply:** Start a new application for a birth certificate.
-   **Track:** See the real-time status of your submitted applications.
-   **Profile:** Update your contact information and password.

### 8.2. Registrar Guide
-   **Dashboard:** View the queue of pending applications and key statistics.
-   **Review:** Examine application details and uploaded documents.
-   **Approve/Reject:** Make a final decision on an application. The system handles certificate generation and user notification automatically.
-   **Search:** Find specific applications or certificates.

### 8.3. Administrator Guide
-   **Dashboard:** Get a high-level overview of system-wide activity and health.
-   **User Management:** Create, edit, and manage all user accounts and roles.
-   **System Settings:** Configure global application parameters without changing code.
-   **Audit & Reporting:** View activity logs and generate system-wide reports. 