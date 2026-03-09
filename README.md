# EduTrack

EduTrack is a web-based university attendance and academic management system with role-based workflows for admins, lecturers, and students.

## What It Covers

- Authentication with role-based access control.
- Admin management for students, lecturers, programmes, courses, and reports.
- Lecturer tools for sessions, attendance, announcements, and reporting.
- Student views for dashboard, announcements, and attendance tracking.
- QR-supported attendance flow and reporting/export features.
- Embedded AI assistant widget on the landing page (Jotform Agent).

## Tech Stack

- PHP 8+ (server-side app)
- MySQL (database)
- Composer (PHP dependencies)
- Vite + vanilla JavaScript (frontend asset build)
- Jotform Agent embed (floating AI assistant on landing page)

## Project Structure

- `public/index.php`: front controller and route entrypoint.
- `index.php`: landing page rendered for `/`.
- `auth/`: authentication pages.
- `pages/admin/`, `pages/lecturer/`, `pages/student/`: role-specific pages.
- `controllers/student/`: student-facing controller actions (for example attendance marking).
- `config/bootstrap.php`: app bootstrap (autoload, env load, DB bootstrap).
- `config/database.php`: shared PDO connection setup.
- `includes/`: shared config, session, auth checks, CSRF, helpers, layout includes.
- `tests/`: PHPUnit tests.
- `edutrack_db.sql`: database schema/data bootstrap script.

## Requirements

- PHP `>=8.0`
- MySQL or MariaDB
- Composer
- Node.js + npm
- PHP extension: `pdo_mysql`

## Setup

1. Clone into your web root (example: `C:\xampp\htdocs\edutrack`).
2. Install PHP dependencies:

```bash
composer install
```

3. Install frontend dependencies:

```bash
npm install
```

4. Create and configure `.env` in project root:

```env
APP_ENV=development
APP_DEBUG=1
APP_URL=http://localhost/edutrack/
DB_HOST=127.0.0.1
DB_NAME=edutrack
DB_USER=root
DB_PASS=
```

5. Create database (example: `edutrack`) and import:

```bash
mysql -u root -p edutrack < edutrack_db.sql
```

6. Ensure your web server points to `public/` as document root (recommended).

## Running the App

### Option 1: Local PHP server

```bash
composer start
```

Then open `http://localhost:8000`.

### Option 2: Apache/XAMPP

- Place project in `htdocs`.
- Set virtual host/document root to `public/`.
- Open your configured local URL (for example `http://localhost/edutrack/public/` if no vhost is set).

## Frontend Assets

- Development watcher:

```bash
npm run dev
```

- Production build:

```bash
npm run build
```

## Testing and Quality

- Run PHPUnit:

```bash
composer test
```

- Optional code style check/fix (if using dev dependencies):

```bash
vendor/bin/php-cs-fixer fix
```

## Routing Notes

- Requests are handled by `public/index.php`.
- Allowed root pages include: `about`, `help`, `contact`, `privacy-policy`, `terms-of-service`.
- Allowed route prefixes include:
  - `auth`
  - `pages/admin`
  - `pages/lecturer`
  - `pages/student`
  - `controllers/student`

## AI Agent

- A floating Jotform AI agent is embedded in `index.php` for visitor assistance.
- Embed source: `https://cdn.jotfor.ms/agent/embedjs/.../embed.js`.
- CSP and permissions allowlist for this integration is configured in `includes/security_headers.php` (including `agent.jotform.com` and related endpoints).

## Troubleshooting

- If you see autoload errors, run `composer install`.
- If DB connection fails, verify `.env` values and that MySQL is running.
- If MySQL driver errors appear, enable `pdo_mysql` in PHP.
- Use `APP_ENV=development` and `APP_DEBUG=1` during local debugging.

## Additional Docs

- [API Documentation](API_DOCUMENTATION.md)
- [User Manual](USER_MANUAL.md)
- [Contributing](CONTRIBUTING.md)
- [License Agreement](LICENCE.md)

## License

See [LICENCE.md](LICENCE.md) for the project license terms.

## Contact

- Developer: Kundananji Simukonda
- Email: kundananjisimukonda@gmail.com
- Phone: +260 967 591 264 / +260 971 863 462
