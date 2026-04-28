# EduTrack

EduTrack is a web-based attendance and academic workflow system for universities/colleges with role-based interfaces for admin, lecturer, and student users.

## Features

- Authentication and role-based access control.
- Admin modules for users, programmes, courses, enrollment, announcements, reports, contact messages, and audit monitoring.
- Lecturer modules for course management, starting/stopping attendance sessions, active session monitoring, analytics, and reports.
- Student modules for attendance marking (QR/manual), attendance history/summary, and announcements.
- Shared public pages (`about`, `help`, `contact`, policies/terms).
- Embedded Jotform AI assistant on the landing page.

## Latest Improvement

- Added an admin audit overview for operational checks such as active sessions, sessions without attendance, courses without lecturer assignments, and inactive students with enrollments.
- Added dedicated admin profile views for students and lecturers with quick access to enrolments, assigned courses, and key account details.
- Upgraded the lecturer dashboard with teaching metrics, attendance trend highlights, at-risk student/course visibility, and active session summaries.

## Attendance Security (Current)

- **Rotating signed QR tokens** for active lecturer sessions.
- QR tokens are short-lived and validated server-side before recording attendance.
- CSRF protection enforced on attendance POST requests.
- Duplicate attendance checks (same student + same session).
- Optional **device-binding strict mode** controlled by env var.
- Security headers (CSP, Permissions-Policy, etc.) applied globally.

## Tech Stack

- PHP `>=8.0`
- MySQL/MariaDB
- Composer dependencies:
  - `vlucas/phpdotenv`
  - `phpmailer/phpmailer`
  - `dompdf/dompdf`
- Frontend tooling: Vite (plus project CSS/JS and CDN assets)

## Project Layout

- `public/index.php`: front controller + allowlisted router.
- `index.php`: landing page.
- `auth/`: auth and account flows.
- `pages/admin/`, `pages/lecturer/`, `pages/student/`: role pages.
- `controllers/student/mark_attendance.php`: student attendance capture endpoint.
- `pages/lecturer/session_qr_token.php`: signed QR token endpoint for lecturer active sessions.
- `includes/`: shared bootstrap, security headers, helpers, session/auth/CSRF, nav/footer partials.
- `config/bootstrap.php`, `config/database.php`: core bootstrap and DB bootstrap.
- `edutrack_db.sql`: base database schema/data.
- `scripts/migrations/`: incremental schema migrations.

## Requirements

- PHP `>=8.0` with `pdo_mysql`
- MySQL or MariaDB
- Composer
- Node.js + npm (for frontend build tooling)

## Setup

1. Clone into web root, e.g. `C:\xampp\htdocs\edutrack`.
2. Install PHP dependencies:
```bash
composer install
```
3. Install frontend dependencies:
```bash
npm install
```
4. Create/update `.env`:
```env
APP_ENV=development
APP_DEBUG=1
APP_URL=http://localhost/edutrack/
DB_HOST=127.0.0.1
DB_NAME=edutrack_db
DB_USER=root
DB_PASS=

# Optional security hardening
QR_TOKEN_SECRET=change-this-to-a-long-random-secret
ATTENDANCE_DEVICE_STRICT=0
```
5. Import base schema:
```bash
mysql -u root -p edutrack_db < edutrack_db.sql
```
6. Apply migration(s):
```sql
-- run file: scripts/migrations/2026-04-05-attendance-device-binding.sql
```

## Run

### Option 1: Built-in PHP server

```bash
composer start
```
Open: `http://localhost:8000`

### Option 2: XAMPP / Apache

- Place project in `htdocs`.
- Keep/enable project `.htaccess` (root rewrite to `public/index.php`).
- Open: `http://localhost/edutrack/`

## Frontend Commands

```bash
npm run dev
npm run build
```

## Routing Notes

The front controller allowlists:

- Root pages:
  - `about`, `help`, `contact`, `privacy-policy`, `terms-of-service`
  - `send_message`, `send_messages`, `logout`
- Prefix routes:
  - `auth/*`
  - `pages/admin/*`
  - `pages/lecturer/*`
  - `pages/student/*`
  - `controllers/student/*`

## Mobile QR Scanning (Phone Testing)

Camera scanning requires a secure context (`https://` or localhost on same device).

Recommended for phone testing:

1. Start Apache locally (`http://localhost/edutrack/` works on laptop).
2. Start ngrok tunnel to port 80:
```bash
ngrok http 80
```
3. Set:
```env
APP_URL=https://<your-ngrok-domain>/edutrack/
```
4. Restart Apache, open the same HTTPS URL on phone, allow camera permission.

## Testing / Quality

```bash
composer test
vendor/bin/php-cs-fixer fix
```

## AI Agent Integration

- Jotform Agent script is embedded on landing page (`index.php`).
- CSP and permissions are configured in `includes/security_headers.php` to allow required Jotform domains.

## Troubleshooting

- `composer` autoload errors: run `composer install`.
- DB connection errors: verify `.env` DB values and MySQL service.
- Camera `NotAllowedError` on phone:
  - Use HTTPS URL.
  - Confirm browser + OS camera permissions.
  - Ensure `Permissions-Policy` allows `self` (already configured in this project).
- Missing CSS on phone/LAN: verify `APP_URL` and open the exact `/edutrack/` path.

## Additional Docs

- [API Documentation](API_DOCUMENTATION.md)
- [User Manual](USER_MANUAL.md)
- [Contributing](CONTRIBUTING.md)
- [License Agreement](LICENCE.md)

## License

See [LICENCE.md](LICENCE.md).

## Contact

- Developer: Kundananji Simukonda
- Email: kundananjisimukonda@gmail.com
- Phone: +260 967 591 264 / +260 971 863 462
