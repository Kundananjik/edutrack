# EduTrack API Documentation

Version 1.0.0  
Last Updated: December 2025

---

## Table of Contents

- [Overview](#overview)
- [Authentication](#authentication)
- [Base URL](#base-url)
- [Response Format](#response-format)
- [Error Handling](#error-handling)
- [Rate Limiting](#rate-limiting)
- [Endpoints](#endpoints)
  - [Authentication](#authentication-endpoints)
  - [Users](#users)
  - [Students](#students)
  - [Lecturers](#lecturers)
  - [Programmes](#programmes)
  - [Courses](#courses)
  - [Attendance](#attendance)
  - [Sessions](#sessions)
  - [Announcements](#announcements)
  - [Reports](#reports)

---

## Overview

The EduTrack API provides programmatic access to the attendance and academic management system. Currently, the API is primarily used internally by the web application. Future versions will expose RESTful endpoints for external integrations.

**Current Version:** 1.0.0  
**Protocol:** HTTP/HTTPS  
**Format:** JSON, HTML Forms (POST)

---

## Authentication

### Session-Based Authentication

EduTrack uses PHP sessions for authentication. All authenticated requests must include a valid session cookie.

**Login Flow:**
1. Send credentials to `/auth/login.php`
2. Receive session cookie on successful authentication
3. Include cookie in subsequent requests
4. Session expires after 30 minutes of inactivity

**CSRF Protection:**
All POST requests must include a valid CSRF token:
```html
<input type="hidden" name="csrf_token" value="<?= get_csrf_token() ?>">
```

---

## Base URL

**Development:**
```
http://localhost/edutrack/public/
```

**Production:**
```
https://yourdomain.com/
```

---

## Response Format

### Success Response (JSON)
```json
{
  "success": true,
  "data": {
    "id": 123,
    "name": "John Doe"
  },
  "message": "Operation completed successfully"
}
```

### Error Response (JSON)
```json
{
  "success": false,
  "error": {
    "code": "INVALID_INPUT",
    "message": "Student ID is required"
  }
}
```

### HTML Form Responses
Many endpoints return HTML redirects with session flash messages:
```php
$_SESSION['success_message'] = "Student added successfully";
$_SESSION['error_message'] = "Failed to add student";
```

---

## Error Handling

### HTTP Status Codes

| Code | Meaning                  | Description                           |
|------|--------------------------|---------------------------------------|
| 200  | OK                       | Request succeeded                     |
| 201  | Created                  | Resource created successfully         |
| 400  | Bad Request              | Invalid input or missing parameters   |
| 401  | Unauthorized             | Authentication required               |
| 403  | Forbidden                | Insufficient permissions              |
| 404  | Not Found                | Resource does not exist               |
| 422  | Unprocessable Entity     | Validation failed                     |
| 500  | Internal Server Error    | Server encountered an error           |

### Common Error Codes

| Error Code          | Description                        |
|---------------------|------------------------------------|
| INVALID_CSRF        | CSRF token missing or invalid      |
| UNAUTHORIZED        | User not authenticated             |
| FORBIDDEN           | Insufficient permissions           |
| NOT_FOUND           | Resource not found                 |
| VALIDATION_ERROR    | Input validation failed            |
| DATABASE_ERROR      | Database operation failed          |
| DUPLICATE_ENTRY     | Record already exists              |

---

## Rate Limiting

Currently, there are no explicit rate limits. However, the following best practices are recommended:

- Maximum 100 requests per minute per IP
- Avoid concurrent requests from the same session
- Use bulk operations when available

---

## Endpoints

### Authentication Endpoints

#### POST /auth/login.php
**Description:** Authenticate user and create session

**Request:**
```http
POST /auth/login.php
Content-Type: application/x-www-form-urlencoded

email=student@example.com&password=securepassword
```

**Response (Success):**
```
HTTP/1.1 302 Found
Location: /pages/student/dashboard.php
Set-Cookie: PHPSESSID=...
```

**Response (Failure):**
```
HTTP/1.1 302 Found
Location: /auth/login.php
```
With `$_SESSION['error_message']` set.

---

#### POST /auth/register.php
**Description:** Register new student account

**Request:**
```http
POST /auth/register.php
Content-Type: application/x-www-form-urlencoded

name=John Doe
email=john@example.com
password=SecurePass123
student_number=2024001
programme_id=1
csrf_token=...
```

**Response:**
- Success: Redirect to verification page
- Failure: Return to registration form with error

---

#### POST /logout.php
**Description:** Destroy session and logout

**Request:**
```http
POST /logout.php
```

**Response:**
```
HTTP/1.1 302 Found
Location: /auth/login.php
```

---

### Users

#### GET /pages/admin/manage_students.php
**Description:** List all students (Admin only)

**Required Role:** Admin

**Response:** HTML page with student table

**Data Structure:**
```php
[
  {
    'user_id' => 1,
    'name' => 'John Doe',
    'email' => 'john@example.com',
    'student_number' => '2024001',
    'programme_name' => 'Computer Science',
    'status' => 'active'
  },
  ...
]
```

---

#### POST /pages/admin/add_student.php
**Description:** Add new student

**Required Role:** Admin

**Request Parameters:**
| Parameter        | Type   | Required | Description              |
|------------------|--------|----------|--------------------------|
| name             | string | Yes      | Student full name        |
| email            | string | Yes      | Valid email address      |
| password         | string | Yes      | Min 8 characters         |
| student_number   | string | Yes      | Unique student ID        |
| programme_id     | int    | Yes      | Programme ID             |
| phone            | string | No       | Contact number           |
| csrf_token       | string | Yes      | CSRF protection token    |

**Response:**
- Success: Redirect to manage_students.php with success message
- Failure: Return to form with error message

---

#### POST /pages/admin/delete_student.php
**Description:** Delete student account

**Required Role:** Admin

**Request Parameters:**
```http
POST /pages/admin/delete_student.php

id=123
csrf_token=...
```

**Response:**
- Success: `$_SESSION['success_message'] = "Student deleted"`
- Failure: `$_SESSION['error_message'] = "Failed to delete"`

---

### Lecturers

#### GET /pages/admin/manage_lecturers.php
**Description:** List all lecturers (Admin only)

**Required Role:** Admin

**Response:** HTML page with lecturer table

---

#### POST /pages/admin/add_lecturer.php
**Description:** Add new lecturer

**Required Role:** Admin

**Request Parameters:**
| Parameter   | Type   | Required | Description           |
|-------------|--------|----------|-----------------------|
| name        | string | Yes      | Lecturer full name    |
| email       | string | Yes      | Valid email address   |
| password    | string | Yes      | Min 8 characters      |
| phone       | string | No       | Contact number        |
| csrf_token  | string | Yes      | CSRF token            |

---

### Programmes

#### GET /pages/admin/manage_programmes.php
**Description:** List all academic programmes

**Required Role:** Admin

**Query Parameters:**
- `department` (optional): Filter by department

**Response:** HTML page with programme table

---

#### POST /pages/admin/add_programme.php
**Description:** Create new programme

**Required Role:** Admin

**Request Parameters:**
| Parameter   | Type   | Required | Description                  |
|-------------|--------|----------|------------------------------|
| name        | string | Yes      | Programme name               |
| code        | string | Yes      | Unique programme code        |
| department  | string | Yes      | Department name              |
| duration    | int    | Yes      | Duration in years            |
| csrf_token  | string | Yes      | CSRF token                   |

---

### Courses

#### GET /pages/admin/manage_courses.php
**Description:** List all courses

**Required Role:** Admin

**Query Parameters:**
- `programme` (optional): Filter by programme ID

**Response:** HTML page with courses table

**Data Structure:**
```php
[
  {
    'id' => 1,
    'name' => 'Data Structures',
    'course_code' => 'CS201',
    'programme_name' => 'Computer Science',
    'lecturer_name' => 'Dr. Smith',
    'status' => 'active'
  },
  ...
]
```

---

#### POST /pages/admin/add_course.php
**Description:** Create new course

**Required Role:** Admin

**Request Parameters:**
| Parameter      | Type   | Required | Description              |
|----------------|--------|----------|--------------------------|
| name           | string | Yes      | Course name              |
| course_code    | string | Yes      | Unique course code       |
| programme_id   | int    | Yes      | Programme ID             |
| description    | string | No       | Course description       |
| credits        | int    | No       | Credit hours             |
| csrf_token     | string | Yes      | CSRF token               |

---

### Attendance

#### POST /controllers/student/mark_attendance.php
**Description:** Mark student attendance via QR code

**Required Role:** Student

**Request Parameters:**
| Parameter   | Type   | Required | Description                 |
|-------------|--------|----------|-----------------------------|
| session_id  | int    | Yes      | Active session ID           |
| csrf_token  | string | Yes      | CSRF token                  |

**Response (JSON):**
```json
{
  "success": true,
  "message": "Attendance marked successfully"
}
```

**Error Responses:**
```json
{
  "success": false,
  "error": "Session has expired"
}
```

```json
{
  "success": false,
  "error": "Attendance already marked"
}
```

---

#### GET /pages/lecturer/attendance_reports.php
**Description:** View attendance statistics

**Required Role:** Lecturer

**Query Parameters:**
- `course_id` (optional): Filter by course
- `date_from` (optional): Start date
- `date_to` (optional): End date

**Response:** HTML page with attendance charts and tables

---

### Sessions

#### POST /pages/lecturer/start_session.php
**Description:** Start new attendance session

**Required Role:** Lecturer

**Request Parameters:**
| Parameter     | Type     | Required | Description                 |
|---------------|----------|----------|-----------------------------|
| course_id     | int      | Yes      | Course ID                   |
| session_date  | date     | Yes      | Session date (YYYY-MM-DD)   |
| start_time    | time     | Yes      | Start time (HH:MM)          |
| end_time      | time     | Yes      | End time (HH:MM)            |
| location      | string   | No       | Class location              |
| csrf_token    | string   | Yes      | CSRF token                  |

**Response:**
- Success: Redirect with QR code display
- Failure: Return with error message

**Generated QR Code:**
Contains encoded session ID that students scan to mark attendance.

---

#### POST /pages/lecturer/stop_session.php
**Description:** End active attendance session

**Required Role:** Lecturer

**Request Parameters:**
```http
POST /pages/lecturer/stop_session.php

session_id=123
csrf_token=...
```

---

#### GET /pages/lecturer/active_sessions.php
**Description:** View all active sessions

**Required Role:** Lecturer

**Response:** HTML page listing current sessions with QR codes

---

### Announcements

#### POST /pages/admin/send_announcement.php
**Description:** Broadcast announcement to users

**Required Role:** Admin or Lecturer

**Request Parameters:**
| Parameter   | Type   | Required | Description                        |
|-------------|--------|----------|------------------------------------|
| title       | string | Yes      | Announcement title                 |
| message     | text   | Yes      | Announcement content               |
| recipient   | enum   | Yes      | 'all', 'students', 'lecturers'     |
| programme_id| int    | No       | Target specific programme          |
| course_id   | int    | No       | Target specific course             |
| csrf_token  | string | Yes      | CSRF token                         |

**Response:**
- Success: `$_SESSION['success_message']`
- Failure: `$_SESSION['error_message']`

---

#### GET /pages/student/view_announcements.php
**Description:** View announcements for logged-in user

**Required Role:** Student, Lecturer, Admin

**Response:** HTML page with announcements list

**Data Structure:**
```php
[
  {
    'id' => 1,
    'title' => 'Exam Schedule Released',
    'message' => 'Check the portal for...',
    'created_at' => '2025-01-15 10:30:00',
    'sender_name' => 'Admin'
  },
  ...
]
```

---

### Reports

#### POST /pages/admin/generate_report.php
**Description:** Generate attendance report

**Required Role:** Admin or Lecturer

**Request Parameters:**
| Parameter     | Type   | Required | Description                    |
|---------------|--------|----------|--------------------------------|
| report_type   | enum   | Yes      | 'course', 'student', 'programme'|
| course_id     | int    | No       | For course reports             |
| student_id    | int    | No       | For student reports            |
| programme_id  | int    | No       | For programme reports          |
| date_from     | date   | Yes      | Start date (YYYY-MM-DD)        |
| date_to       | date   | Yes      | End date (YYYY-MM-DD)          |
| format        | enum   | Yes      | 'pdf', 'excel', 'csv'          |
| csrf_token    | string | Yes      | CSRF token                     |

**Response:**
File download with appropriate Content-Type header:
- PDF: `application/pdf`
- Excel: `application/vnd.openxmlformats-officedocument.spreadsheetml.sheet`
- CSV: `text/csv`

---

### Contact Messages

#### POST /send_message.php
**Description:** Send contact message (public endpoint)

**Request Parameters:**
| Parameter   | Type   | Required | Description              |
|-------------|--------|----------|--------------------------|
| name        | string | Yes      | Sender name              |
| email       | string | Yes      | Sender email             |
| subject     | string | Yes      | Message subject          |
| message     | text   | Yes      | Message content          |
| csrf_token  | string | Yes      | CSRF token               |

**Response:**
- Success: Redirect with success message
- Failure: Return to form with error

---

#### POST /pages/admin/reply_message.php
**Description:** Reply to contact message

**Required Role:** Admin

**Request Parameters:**
| Parameter        | Type   | Required | Description              |
|------------------|--------|----------|--------------------------|
| message_id       | int    | Yes      | Original message ID      |
| reply_message    | text   | Yes      | Reply content            |
| recipient_email  | string | Yes      | Recipient email          |
| csrf_token       | string | Yes      | CSRF token               |

**Response (JSON):**
```json
{
  "success": true,
  "message": "Reply sent successfully"
}
```

---

## Webhooks (Planned for v2.0)

Future versions will support webhook notifications for:
- Low attendance alerts
- Session start/end events
- New announcements
- Account activations

---

## SDK Support (Planned)

Official SDKs planned for:
- PHP
- JavaScript/Node.js
- Python
- Mobile (iOS/Android)

---

## API Versioning

The API follows semantic versioning. Breaking changes will increment the major version number. The API version is specified in:
- URL path: `/api/v1/...` (future)
- Header: `Accept: application/vnd.edutrack.v1+json` (future)

---

## Support

For API questions or issues:

**Developer:** Kundananji Simukonda  
**Email:** kundananjisimukonda@gmail.com  
**Phone:** +260 967 591 264 / +260 971 863 462

---

## Change Log

### v1.0.0 (2025-01-01)
- Initial API documentation
- Session-based authentication
- Basic CRUD operations for all entities
- Attendance marking and reporting
- Announcement system