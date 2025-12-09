# EduTrack - University Attendance & Academic Management System

EduTrack is a comprehensive web-based system designed to streamline university academic and attendance management. It provides role-based dashboards for **Admins**, **Lecturers**, and **Students**, ensuring effective monitoring, reporting, and management of academic activities.

---

## 🚀 Features

### 🔑 Authentication & Security
- Secure login system with role-based access (Admin, Lecturer, Student).
- Password reset and email verification.
- Role-based access control (RBAC).
- Account activation/deactivation.

### 👨‍💼 Admin Dashboard
- **User Management:** Add, edit, delete, and manage Students, Lecturers, and Admins.
- **Academic Structure:** Manage Departments, Programmes, and Courses.
- **Enrollment Management:** Enroll students manually or in bulk, manage transfers, and withdrawals.
- **Attendance Control:** Configure attendance policies, approve correction requests, override attendance with audit trails.
- **Reporting & Analytics:** Generate attendance reports (PDF/Excel/CSV), visualize trends, identify low attendance.

### 👩‍🏫 Lecturer Dashboard
- View and manage assigned courses, students, and departments.
- Mark and track student attendance (QR code-based system).
- Manage class times and schedules.
- Access attendance reports for courses.

### 🎓 Student Dashboard
- View enrolled courses and programmes.
- Scan QR codes to mark attendance.
- Track personal attendance records.
- Receive notifications for low attendance.

### 📊 QR Code Attendance Tracking
- Unique QR code generated for each course session.
- Students scan to mark attendance.
- Logs attendance in real-time.

---

## 🛠️ Tech Stack
- **Frontend:** HTML, CSS (`style.css` with EduTrack branding), JavaScript (vanilla JS).
- **Backend:** PHP (MVC structured).
- **Database:** MySQL.
- **Reports:** PDF/Excel/CSV export support.

---

## 📂 Project Structure

Key directories and entry points:

- **Root entry:** `index.php` – public landing page and marketing site.
- **Front controller:** `public/index.php` – central router; all HTTP requests should be rewritten here by `.htaccess` or the web server.
- **Routing targets (allow‑listed in `public/index.php`):**
  - Root pages: `about.php`, `help.php`, `contact.php`, `privacy-policy.php`, `terms-of-service.php`, `send_message.php`, `send_messages.php`, `logout.php`.
  - Auth: `auth/` (login, register, password reset, etc.).
  - Dashboards & pages:
    - Admin: `pages/admin/*.php` (manage students, lecturers, programmes, courses, attendance reports, announcements, messages, etc.).
    - Lecturer: `pages/lecturer/*.php` (my courses, sessions, attendance, reports, profile, announcements).
    - Student: `pages/student/*.php` (dashboard, announcements, help, policies).
  - Controllers: `controllers/student/*.php` (e.g. `mark_attendance.php`).

- **Configuration & bootstrap:**
  - `config/bootstrap.php` – loads Composer autoloader, `.env` via `vlucas/phpdotenv`, registers error handlers, and bootstraps the database (`config/database.php`).
  - `config/database.php` – database credentials and connection bootstrap (used by `config/bootstrap.php`).
  - `includes/config.php` – defines constants like `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS` consumed by `includes/db.php`.

- **Includes & infrastructure:**
  - `includes/preload.php` – shared preload for entry scripts; registers error handlers, security headers, and includes `config/bootstrap.php`.
  - `includes/error_handlers.php` – global error/exception/shutdown handlers and helpers like `et_simple_error_page()`.
  - `includes/security_headers.php` – common security‑related HTTP headers.
  - `includes/db.php` – PDO MySQL bootstrap using constants from `includes/config.php`.
  - `includes/session.php`, `includes/auth_check.php`, `includes/csrf.php`, `includes/functions.php` – session, auth, CSRF protection, and helper utilities.
  - `includes/header.php`, `includes/admin_header.php`, `includes/footer.php`, `includes/unauthorized.php` – shared layout and access control views.

- **Vendor & tests:**
  - `vendor/` – Composer dependencies (autoloaded by `config/bootstrap.php`).
  - `tests/` – PHPUnit tests (e.g. `AuthTest.php`, `DbTest.php`) and `tests/bootstrap.php` for test environment setup.

---

## 🌐 Routing Overview

All web traffic is routed through the front controller:

- Web server document root → `public/`
- `.htaccess` (or equivalent) rewrites all requests to `public/index.php`.

High‑level flow:

```text path=null start=null
Browser Request           Web Server Rewrite          Front Controller & Router
-----------------   ---------------------------   -----------------------------
https://.../          → public/index.php           → includes/preload.php
  /                    (document root = public/)     (error handlers, security,
                                                     bootstrap, env, DB)
                                                  → inspect ?path=...
                                                    (e.g. auth/login,
                                                     pages/admin/dashboard)
                                                  → map to allowed PHP file
                                                    or show 404 via
                                                    et_simple_error_page()
```

1. **User hits URL** → `public/index.php` receives the request.
2. `includes/preload.php` is loaded (error handlers, security headers, bootstrap).
3. The `path` query parameter is read (e.g. `/auth/login`, `/pages/admin/dashboard`).
4. Router checks against allow‑listed routes:
   - Root pages like `/about`, `/help`, `/contact` → corresponding `*.php` at the project root.
   - `/auth/...` → `auth/*.php` (authentication screens).
   - `/pages/admin/...` → `pages/admin/*.php` (admin dashboard and management pages).
   - `/pages/lecturer/...` → `pages/lecturer/*.php`.
   - `/pages/student/...` → `pages/student/*.php`.
   - `/controllers/student/...` → `controllers/student/*.php` (e.g. marking attendance).
5. If the route is unknown or the file does not exist, a 404 is returned via `et_simple_error_page()`.

### Common URLs → PHP files

| URL path                          | PHP file                             | Role       |
|-----------------------------------|--------------------------------------|-----------|
| `/`                               | `index.php`                          | Public    |
| `/about`                          | `about.php`                          | Public    |
| `/help`                           | `help.php`                           | Public    |
| `/contact`                        | `contact.php`                        | Public    |
| `/auth/login`                     | `auth/login.php`                     | All       |
| `/auth/register`                  | `auth/register.php`                  | Student   |
| `/pages/admin/dashboard`          | `pages/admin/dashboard.php`          | Admin     |
| `/pages/admin/manage_students`    | `pages/admin/manage_students.php`    | Admin     |
| `/pages/admin/attendance_reports` | `pages/admin/attendance_reports.php` | Admin     |
| `/pages/lecturer/dashboard`       | `pages/lecturer/dashboard.php`       | Lecturer  |
| `/pages/lecturer/my_courses`      | `pages/lecturer/my_courses.php`      | Lecturer  |
| `/pages/student/dashboard`        | `pages/student/dashboard.php`        | Student   |
| `/pages/student/view_announcements` | `pages/student/view_announcements.php` | Student |
| `/controllers/student/mark_attendance` | `controllers/student/mark_attendance.php` | Student/Lecturer |

### Role dashboards → files

**Admin** (under `pages/admin/`):

- `dashboard.php` – admin overview.
- `manage_students.php`, `add_student.php`, `edit_student.php`, `delete_student.php`.
- `manage_lecturers.php`, `add_lecturer.php`, `edit_lecturer.php`, `delete_lecturer.php`.
- `manage_programmes.php`, `add_programme.php`, `edit_programme.php`, `delete_programme.php`, `view_programme.php`.
- `manage_courses.php`, `add_course.php`, `edit_course.php`, `delete_course.php`, `view_course.php`.
- `attendance_reports.php`, `generate_report.php`.
- `send_announcement.php`, `view_announcements.php`, `notifications.php`.
- `contact_messages.php`, `view_messages.php`, `reply_message.php`, `send_message.php`.
- `help.php`, `privacy-policy.php`, `terms-of-service.php`.

**Lecturer** (under `pages/lecturer/`):

- `dashboard.php` – lecturer overview.
- `my_courses.php`, `view_course.php`.
- `start_session.php`, `stop_session.php`, `active_sessions.php`, `view_session.php`.
- `attendance_reports.php`, `generate_report.php`.
- `my_students.php`.
- `profile.php`, `update_profile.php`.
- `send_announcement.php`, `view_announcements.php`.
- `contact.php`, `privacy-policy.php`, `terms-of-service.php`.

**Student** (under `pages/student/`):

- `dashboard.php` – student overview.
- `index.php` – student landing/entry.
- `view_announcements.php`.
- `contact.php`, `help.php`, `privacy-policy.php`, `terms-of-service.php`.

---

## ⚙️ Installation & Setup

1. **Clone the repository** into your web server directory (e.g. `htdocs/edutrack`).
2. **Install PHP dependencies:**
   ```bash path=null start=null
   composer install
   ```
3. **Create a database** in MySQL (e.g. `edutrack`). Import the provided SQL schema if available.
4. **Configure environment variables:** create a `.env` file in the project root:
   ```dotenv path=null start=null
   APP_ENV=development
   APP_DEBUG=1
   DB_HOST=127.0.0.1
   DB_NAME=edutrack
   DB_USER=root
   DB_PASS=
   ```
5. **Configure PHP constants** in `includes/config.php` (these should match your `.env` values):
   ```php path=null start=null
   <?php
   define('DB_HOST', '127.0.0.1');
   define('DB_NAME', 'edutrack');
   define('DB_USER', 'root');
   define('DB_PASS', '');
   ```
6. **Ensure extensions are enabled:** PHP must have `pdo_mysql` enabled for `includes/db.php` to work.
7. **Set the document root / virtual host:** point your web server to the `public/` directory so that all requests go through `public/index.php`.

---

## 🐞 Error Handling & Debugging

- Global error/exception/fatal handlers are registered via `includes/error_handlers.php` and are always loaded by `includes/preload.php` or `config/bootstrap.php`.
- Configure verbosity in `.env`:
  - Development (detailed errors): `APP_ENV=development` and `APP_DEBUG=1`
  - Production (friendly messages): `APP_ENV=production` and `APP_DEBUG=0`

### Quick manual tests

- Visit the landing page via the front controller (recommended): `http://localhost/edutrack/public/`.
- Or hit the root directly (if your document root is the project root): `http://localhost/edutrack/`.
- To verify error handling and DB connectivity, you can temporarily create files under a `debug/` folder, for example:
  - `debug/handler_test.php`
    ```php path=null start=null
    <?php
    require_once __DIR__ . '/../includes/preload.php';
    throw new Exception('Test exception from handler_test.php');
    ```
  - `debug/db_test.php`
    ```php path=null start=null
    <?php
    require_once __DIR__ . '/../includes/preload.php';
    $ok = $pdo->query('SELECT 1')->fetchColumn();
    echo $ok ? 'DB OK' : 'DB query failed';
    ```
- Remove these temporary debug files after testing.
