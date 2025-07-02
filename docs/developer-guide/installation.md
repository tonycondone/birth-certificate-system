# Developer Setup Guide

This guide provides step-by-step instructions for setting up the Birth Certificate System project for local development.

## Prerequisites

Before you begin, ensure you have the following installed on your system:
*   **PHP**: Version 8.1 or higher.
*   **MySQL**: Version 8.0 or higher.
*   **Composer**: For PHP dependency management.
*   **Git**: For version control.
*   **A web server**: (e.g., Apache, Nginx, or use PHP's built-in server).

## Installation Steps

1.  **Clone the Repository**
    ```bash
    git clone <repository-url>
    cd birth-certificate-system
    ```

2.  **Install PHP Dependencies**
    *There are currently no composer dependencies, but if they are added, you would run:*
    ```bash
    # composer install
    ```

3.  **Database Setup**
    a. Create a new MySQL database named `birth_certificate_system`.
    ```sql
    CREATE DATABASE birth_certificate_system;
    ```
    b. Import the database schema and initial data. The migration files are located in the `database/migrations/` directory. You can import them sequentially using a MySQL client.
    ```bash
    # Example using mysql command-line client:
    mysql -u your_user -p birth_certificate_system < database/migrations/001_create_users_table.sql
    mysql -u your_user -p birth_certificate_system < database/migrations/002_create_applications_table.sql
    # ... and so on for all migration files.
    ```

4.  **Configure Environment Variables**
    a. Copy the example environment file:
    ```bash
    cp .env.example .env
    ```
    b. Open the `.env` file and update the following settings to match your local environment:
    ```ini
    DB_HOST=127.0.0.1
    DB_NAME=birth_certificate_system
    DB_USER=your_mysql_user
    DB_PASS=your_mysql_password
    ```

5.  **Run the Application**
    You can use PHP's built-in web server for local development.
    ```bash
    php -S localhost:8000 -t public
    ```
    The application will be available at `http://localhost:8000`.

## Default Login Credentials

After setting up the database, you can use the following default accounts to log in:

*   **Administrator:**
    *   **Email:** `admin@gov.cert`
    *   **Password:** `Admin@123`
*   **Citizen:**
    *   **Email:** `citizen@example.com`
    *   **Password:** `Citizen@123`

These credentials can be found in the `001_create_users_table.sql` migration if you need to verify or change them. 