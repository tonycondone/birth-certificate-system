# System Architecture Overview

This document provides a high-level overview of the Birth Certificate System's architecture. The system is designed using a traditional Model-View-Controller (MVC) pattern, which separates application logic from the user interface.

## Core Components

1.  **Web Server (Apache/Nginx)**: Serves the PHP application and static assets.
2.  **PHP-FPM**: Manages the PHP processes and executes the application code.
3.  **MySQL Database**: Stores all application data, including user information, applications, and certificates.
4.  **Frontend (Browser)**: The user interface, built with HTML, CSS, and JavaScript.

## Architectural Diagram

```mermaid
graph TD;
    subgraph "User's Browser"
        A[User Interface <br/>(HTML, CSS, JS)]
    end

    subgraph "Web Server"
        B[Public Assets <br/>(CSS, JS, Images)]
        C[index.php <br/>(Router)]
    end

    subgraph "Application Core (PHP)"
        D[Controllers <br/>(Handles HTTP Requests)]
        E[Models <br/>(Database Interaction)]
        F[Views <br/>(Renders HTML)]
        G[Services <br/>(Business Logic)]
    end

    subgraph "Database"
        H[MySQL Database]
    end

    A -- HTTP Request --> C;
    C -- Routes to --> D;
    D -- Uses --> G;
    D -- Interacts with --> E;
    E -- Queries --> H;
    H -- Returns Data --> E;
    E -- Returns Data to --> D;
    D -- Renders --> F;
    F -- Generates HTML --> A;
    A -- Requests --> B;
```

## Request Lifecycle

1.  A user's request hits the `public/index.php` file.
2.  The router maps the URL to a specific `Controller` method.
3.  The `Controller` processes the request, interacts with `Models` to fetch or save data, and may use `Services` for complex business logic.
4.  Data is passed to a `View`, which renders the final HTML.
5.  The HTML is returned to the user's browser. 