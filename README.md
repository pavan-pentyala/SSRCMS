# ServiceDesk Pro — Smart Service Request & Complaint Management System (SSRCMS)

A full-stack Progressive Web App (PWA) for reporting and tracking service complaints with a real-time administrative management dashboard.

## 1. Project Title and One-Line Description
ServiceDesk Pro — Smart Service Request & Complaint Management System (SSRCMS). A system for reporting service issues and managing their resolution via a role-based web interface.

## 2. Why I Built This
This project was developed as a learning exercise to master full-stack development using the PHP/MySQL stack. The goal was to implement modern frontend aesthetics and Progressive Web App features in a functional business utility.

## 3. Tech Stack
- PHP: Server-side logic and session management.
- MySQL/MariaDB: Relational database for persistent storage.
- HTML5/CSS3: UI structure and glassmorphism design.
- JavaScript/jQuery: Asynchronous API communication and dynamic UI updates.
- Bootstrap 5: Responsive layout and component framework.
- Service Workers: Offline functionality and app installability.

## 4. Features
- User registration and secure login.
- Role-based workflows for regular users and administrators.
- Complaint submission with priority levels and categories.
- Real-time status tracking for active requests.
- Admin dashboard with resolution controls and summary statistics.
- Offline access to previously viewed pages via Service Worker.

## 5. What I Learned
- Implemented a Service Worker (`sw.js`) using a cache-first strategy to ensure the application remains functional without internet connectivity.
- Developed a Role-Based Access Control (RBAC) system in PHP to enforce security across administrative and user-level routes.
- Utilized jQuery AJAX to handle form submissions and dashboard data fetching without full page reloads.
- Applied advanced CSS techniques including `backdrop-filter` and custom gradients to achieve a premium glassmorphism interface.

## 6. How to Run It
- **Prerequisites**: XAMPP or any environment with PHP 7.4+ and MySQL/MariaDB.
- **Installation**: Clone the repository to your local server directory (e.g., `C:/xampp/htdocs/SSRCMS`).
- **Configuration**: Rename `config/config.php.example` to `config/config.php` and `install.php.example` to `install.php`. Fill in your local MySQL credentials in both files.
- **Database Setup**: Open `http://localhost/SSRCMS/install.php` in your browser. This will automatically create the `ssrcms_proj_db` database and necessary tables.
- **Access**: Navigate to `http://localhost/SSRCMS/index.php`. Default credentials for testing are provided on the login page.

## 7. Known Limitations
- Database credentials are currently stored in plain text within PHP files.
- The system lacks an automated email or SMS notification engine for status updates.
- File attachments (images/documents) are not supported in complaint submissions.
- The administrator's data tables have minor responsive overflow issues on small mobile screens.

## 8. Course Context
Built as part of Mini Project (LLJ component for Web Technologies Course) during MTech in CSE at SRMIST.
