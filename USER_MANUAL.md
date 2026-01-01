# EduTrack User Manual

**Version 1.0.0**  
**Last Updated: January 2025**

---

## Table of Contents

1. [Introduction](#introduction)
2. [System Requirements](#system-requirements)
3. [Getting Started](#getting-started)
4. [User Roles](#user-roles)
5. [Admin Guide](#admin-guide)
6. [Lecturer Guide](#lecturer-guide)
7. [Student Guide](#student-guide)
8. [Troubleshooting](#troubleshooting)
9. [FAQ](#faq)
10. [Support](#support)

---

## Introduction

### What is EduTrack?

EduTrack is a comprehensive university attendance and academic management system designed to streamline attendance tracking, course management, and academic administration. The system provides specialized dashboards for three user roles:

- **Administrators** - Full system management and oversight
- **Lecturers** - Course and attendance management
- **Students** - Personal attendance tracking and course information

### Key Features

- QR code-based attendance marking
- Real-time attendance tracking
- Comprehensive reporting (PDF, Excel, CSV)
- Announcement broadcasting
- Programme and course management
- Student enrollment management
- Mobile-responsive design

---

## System Requirements

### Minimum Requirements

**For Users:**
- Modern web browser (Chrome 90+, Firefox 88+, Safari 14+, Edge 90+)
- Internet connection (minimum 1 Mbps)
- Screen resolution: 1024x768 or higher
- JavaScript enabled
- Cookies enabled

**For Mobile Users:**
- iOS 13+ or Android 8+
- Camera access (for QR code scanning)
- Mobile browser with JavaScript support

**For Administrators (Server):**
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Apache/Nginx web server
- SSL certificate (recommended)

---

## Getting Started

### First-Time Access

1. **Visit the EduTrack URL** provided by your institution
2. **Click "Login"** on the landing page
3. **Enter your credentials** provided by the administrator
4. **Change your password** on first login (recommended)

### Login Process

1. Navigate to the login page
2. Enter your email address
3. Enter your password
4. Click "Login"
5. You will be redirected to your role-specific dashboard

### Password Reset

If you forget your password:

1. Click **"Forgot Password?"** on the login page
2. Enter your registered email address
3. Check your email for reset instructions
4. Click the reset link (valid for 1 hour)
5. Enter your new password
6. Confirm the new password
7. Click "Reset Password"

---

## User Roles

### Role Overview

| Role       | Primary Function                        | Access Level |
|------------|-----------------------------------------|--------------|
| Admin      | Full system management                  | Complete     |
| Lecturer   | Course and attendance management        | Limited      |
| Student    | View attendance and courses             | Read-only    |

### Role-Based Access

Each role has access to specific features and pages based on their responsibilities:

**Admin Access:**
- All features available
- User management (create, edit, delete)
- System configuration
- Comprehensive reports

**Lecturer Access:**
- Own courses and students
- Attendance management
- Course-specific reports
- Announcements to students

**Student Access:**
- Personal attendance records
- Enrolled courses
- Programme information
- View announcements

---

## Admin Guide

### Admin Dashboard Overview

The admin dashboard provides:
- System statistics (total students, lecturers, courses)
- Recent activity logs
- Quick action buttons
- Attendance overview charts

### Managing Students

#### Adding a New Student

1. Navigate to **Manage Students**
2. Click **"Add Student"**
3. Fill in required information:
   - Full Name
   - Email Address
   - Student Number (unique)
   - Programme
   - Password (temporary)
   - Phone Number (optional)
4. Click **"Add Student"**
5. Student receives activation email

#### Editing Student Information

1. Go to **Manage Students**
2. Find the student in the table
3. Click the **Edit** icon (pencil)
4. Update the information
5. Click **"Save Changes"**

#### Deleting a Student

1. Navigate to **Manage Students**
2. Find the student
3. Click the **Delete** icon (trash)
4. Confirm deletion in the popup
5. Note: This action cannot be undone

#### Bulk Student Import (Planned)

Future feature for importing multiple students via CSV file.

### Managing Lecturers

#### Adding a New Lecturer

1. Go to **Manage Lecturers**
2. Click **"Add Lecturer"**
3. Enter details:
   - Full Name
   - Email Address
   - Password (temporary)
   - Phone Number (optional)
4. Click **"Add Lecturer"**

#### Assigning Courses to Lecturers

1. Navigate to **Manage Courses**
2. Click **"Edit"** on a course
3. Select the lecturer from dropdown
4. Click **"Save Changes"**
5. Multiple lecturers can be assigned to one course

### Managing Programmes

#### Creating a Programme

1. Go to **Manage Programmes**
2. Click **"Add Programme"**
3. Enter information:
   - Programme Name (e.g., "Computer Science")
   - Programme Code (e.g., "CS")
   - Department
   - Duration (in years)
4. Click **"Create Programme"**

#### Filtering Programmes

Use the dropdown filter to view programmes by department.

### Managing Courses

#### Adding a New Course

1. Navigate to **Manage Courses**
2. Click **"Add Course"**
3. Fill in details:
   - Course Name
   - Course Code (unique)
   - Programme
   - Description (optional)
   - Credits (optional)
4. Click **"Add Course"**

#### Viewing Course Details

1. Go to **Manage Courses**
2. Click the **View** icon (eye)
3. See enrolled students, assigned lecturers, and statistics

### Student Enrollment

#### Manual Enrollment

1. Navigate to **Enrollment Management**
2. Click **"Add Enrollment"**
3. Select:
   - Student
   - Programme
4. Click **"Enroll Student"**
5. Student is automatically enrolled in all programme courses

#### Changing Student Programme

1. Go to **Enrollment Management**
2. Find the student
3. Use the **"Change Programme"** dropdown
4. Select new programme
5. Click the **Change** button
6. Confirm the action
7. Previous enrollments are removed, new ones created

#### Removing Enrollment

1. Navigate to **Enrollment Management**
2. Find the student and programme
3. Click the **Delete** button (trash icon)
4. Confirm deletion
5. All course enrollments for that programme are removed

### Generating Reports

#### Attendance Reports

1. Go to **Attendance Reports**
2. Select report type:
   - **Course Report** - Attendance for a specific course
   - **Student Report** - Individual student attendance
   - **Programme Report** - All students in a programme
3. Set date range (From - To)
4. Choose format:
   - PDF (for printing)
   - Excel (for analysis)
   - CSV (for import to other systems)
5. Click **"Generate Report"**
6. Report downloads automatically

#### Report Contents

Reports include:
- Attendance percentages
- Present/absent counts
- Date-wise breakdown
- Student lists
- Summary statistics

### Announcements

#### Broadcasting Announcements

1. Navigate to **Send Announcement**
2. Fill in:
   - Title
   - Message content
   - Recipients:
     - All Users
     - Students Only
     - Lecturers Only
     - Specific Programme
     - Specific Course
3. Click **"Send Announcement"**
4. Recipients see it on their dashboard

#### Managing Announcements

1. Go to **View Announcements**
2. See all sent announcements
3. Edit or delete as needed

### Contact Messages

#### Viewing Messages

1. Navigate to **Contact Messages**
2. See all messages from website contact form
3. Use search to find specific messages

#### Replying to Messages

1. Find the message
2. Click **"Reply"**
3. Type your response
4. Click **"Send Reply"**
5. Reply is sent to the sender's email

---

## Lecturer Guide

### Lecturer Dashboard Overview

Your dashboard shows:
- Assigned courses count
- Total students
- Today's sessions
- Recent announcements
- Quick action buttons

### Managing Your Courses

#### Viewing Assigned Courses

1. Navigate to **My Courses**
2. See all courses assigned to you
3. Click on a course to view details

#### Course Details

Each course page shows:
- Course information
- Enrolled students list
- Past sessions
- Attendance statistics

### Attendance Sessions

#### Starting a Session

1. Go to **My Courses**
2. Click **"Start Session"** on a course
3. Fill in details:
   - Session Date (auto-filled with today)
   - Start Time
   - End Time
   - Location (optional)
4. Click **"Start Session"**
5. QR code is generated and displayed

#### Displaying the QR Code

1. After starting session, QR code appears
2. **Project the QR code** on screen/whiteboard
3. Students scan with their devices
4. Attendance marks automatically
5. Monitor attendance in real-time

#### Active Sessions

1. Navigate to **Active Sessions**
2. See all currently running sessions
3. View who has marked attendance
4. Stop session when class ends

#### Stopping a Session

1. Go to **Active Sessions**
2. Find your session
3. Click **"Stop Session"**
4. Session ends, no more attendance accepted
5. Final attendance is recorded

### Viewing Attendance

#### Course Attendance

1. Navigate to **Attendance Reports**
2. Select a course
3. Choose date range
4. Click **"View Report"**
5. See:
   - Student-wise attendance
   - Session-wise breakdown
   - Attendance percentages

#### Generating Reports

1. Go to **Attendance Reports**
2. Select course
3. Set date range
4. Choose format (PDF/Excel/CSV)
5. Click **"Generate"**
6. Download report

### Managing Students

#### Viewing Your Students

1. Navigate to **My Students**
2. See all students in your courses
3. View their attendance records
4. Filter by course or programme

### Profile Management

#### Updating Your Profile

1. Click **"Profile"** in navigation
2. Update:
   - Phone number
   - Password (optional)
   - Contact information
3. Click **"Save Changes"**

#### Changing Your Password

1. Go to **Profile**
2. Click **"Change Password"**
3. Enter current password
4. Enter new password
5. Confirm new password
6. Click **"Update Password"**

### Sending Announcements

1. Navigate to **Send Announcement**
2. Fill in:
   - Title
   - Message
   - Recipients:
     - All My Students
     - Specific Course
3. Click **"Send Announcement"**
4. Students see it immediately on login

---

## Student Guide

### Student Dashboard Overview

Your dashboard displays:
- Enrolled courses
- Recent announcements
- Attendance summary
- Quick links

### Viewing Your Courses

1. Your courses are listed on the dashboard
2. Click on a course to see:
   - Course details
   - Assigned lecturers
   - Class schedule
   - Your attendance percentage

### Marking Attendance

#### Using QR Code

1. **When lecturer starts a session:**
   - QR code is displayed in class
2. **On your mobile device:**
   - Open your phone camera OR
   - Use QR code scanner app
3. **Scan the QR code:**
   - Point camera at the QR code
   - Wait for the link to appear
4. **Click the link:**
   - Opens EduTrack attendance page
   - Confirm you're marking attendance
5. **Attendance marked:**
   - Success message appears
   - Record is saved instantly

#### Attendance Rules

- You must be logged in to mark attendance
- QR code is valid only during session time
- You can mark attendance only once per session
- Late marking may not be accepted (depends on policy)

### Viewing Your Attendance

#### Attendance Overview

1. Dashboard shows overall attendance percentage
2. Color coding:
   - Green (80%+) - Good attendance
   - Yellow (60-79%) - Warning
   - Red (<60%) - Low attendance

#### Detailed Attendance

1. Click on a course
2. View **Attendance History**:
   - All sessions
   - Present/Absent status
   - Date and time
   - Percentage calculation

### Announcements

1. Announcements appear on dashboard
2. Click to read full message
3. Announcements from:
   - Admin (important updates)
   - Your lecturers (course-specific)

### Low Attendance Alerts

If your attendance falls below threshold:
- Warning message on dashboard
- Email notification (if enabled)
- Discuss with lecturer if needed

### Profile Management

#### Viewing Your Profile

1. Click **"Profile"** in menu
2. See your information:
   - Name
   - Student Number
   - Email
   - Programme
   - Enrolled Courses

#### Updating Contact Information

1. Go to **Profile**
2. Update phone number (if allowed)
3. Click **"Save Changes"**

#### Changing Password

1. Navigate to **Profile**
2. Click **"Change Password"**
3. Enter current password
4. Enter new password (min 8 characters)
5. Confirm new password
6. Click **"Update Password"**

### Help & Support

1. Click **"Help"** in navigation
2. Read FAQ and guides
3. Use **Contact Form** for issues:
   - Enter your message
   - Admin will respond via email

---

## Troubleshooting

### Common Issues and Solutions

#### Cannot Login

**Problem:** "Invalid email or password" error

**Solutions:**
1. Check email spelling carefully
2. Ensure Caps Lock is off
3. Use password reset if forgotten
4. Contact admin if account is locked

---

#### QR Code Won't Scan

**Problem:** Camera doesn't recognize QR code

**Solutions:**
1. Ensure good lighting
2. Hold camera steady
3. Try different angle/distance
4. Clean camera lens
5. Use QR scanner app instead of camera
6. Ask lecturer to regenerate QR code

---

#### Attendance Not Marked

**Problem:** Scanned QR but attendance not recorded

**Possible Causes:**
1. Session already ended
2. Already marked for this session
3. Not enrolled in the course
4. Network connection lost

**Solutions:**
1. Refresh the page
2. Check dashboard for update
3. Contact lecturer immediately
4. Take screenshot as proof

---

#### Cannot See Dashboard

**Problem:** Blank page or error after login

**Solutions:**
1. Clear browser cache and cookies
2. Try different browser
3. Disable browser extensions
4. Check internet connection
5. Contact IT support

---

#### Forgot Password

**Solution:**
1. Click "Forgot Password" on login page
2. Enter your registered email
3. Check email inbox (and spam)
4. Click reset link within 1 hour
5. Set new password
6. If no email received, contact admin

---

#### Profile Won't Update

**Problem:** Changes not saving

**Solutions:**
1. Ensure all required fields filled
2. Check internet connection
3. Try again after refreshing
4. Contact admin if persists

---

#### Reports Won't Download

**Problem:** Generate button doesn't work

**Solutions:**
1. Check popup blocker settings
2. Enable downloads in browser
3. Try different format (PDF → Excel)
4. Clear browser cache
5. Try different browser

---

## FAQ

### General Questions

**Q: How do I get an EduTrack account?**  
A: Admins create accounts. Contact your university IT department.

**Q: Can I use EduTrack on my phone?**  
A: Yes! EduTrack is mobile-responsive and works on all devices.

**Q: Is my data secure?**  
A: Yes. We use industry-standard encryption and security measures.

**Q: Can I access EduTrack offline?**  
A: No. Internet connection is required for all features.

---

### For Students

**Q: What if I'm late to class?**  
A: You can mark attendance if the session is still active. Check with your lecturer about late attendance policies.

**Q: Can I mark attendance from home?**  
A: No. The QR code system requires you to be physically present in class.

**Q: What if my attendance is low?**  
A: Contact your lecturer to discuss. Attend regularly to improve your percentage.

**Q: How is attendance percentage calculated?**  
A: (Sessions Attended / Total Sessions) × 100

**Q: Can I see attendance from previous semesters?**  
A: Yes, if the data is retained in the system. Check with your admin.

---

### For Lecturers

**Q: How long should I keep the session active?**  
A: Keep it active for your entire class period. Stop it when class ends.

**Q: What if a student's attendance wasn't marked?**  
A: Contact admin to manually add the attendance record with justification.

**Q: Can I edit attendance records?**  
A: No. Contact admin for corrections with proper documentation.

**Q: How do I handle technical issues during class?**  
A: Have a backup plan (paper roll call). Report issue to IT immediately.

**Q: Can I see other lecturers' courses?**  
A: No. You can only access your assigned courses.

---

### For Admins

**Q: How do I backup the system?**  
A: Regular database backups should be automated. Consult technical documentation.

**Q: Can I restore deleted users?**  
A: No. Deletions are permanent. Always confirm before deleting.

**Q: How do I handle data privacy requests?**  
A: Follow your institution's GDPR/data protection policies. Export user data using reports.

**Q: What's the maximum number of users?**  
A: Depends on server capacity. System is designed to scale.

---

## Support

### Getting Help

**Priority Levels:**

1. **Critical** (System down, data loss)
   - Call immediately
   - Response within 1 hour

2. **High** (Features not working, blocking users)
   - Email or call
   - Response within 4 hours

3. **Medium** (Minor bugs, questions)
   - Email support
   - Response within 24 hours

4. **Low** (Feature requests, suggestions)
   - Submit via contact form
   - Response within 48 hours

### Contact Information

**Technical Support:**  
Email: kundananjisimukonda@gmail.com  
Phone: +260 967 591 264 / +260 971 863 462  
Hours: Monday-Friday, 8:00 AM - 5:00 PM CAT

**For Emergencies:**  
Call during business hours

### Before Contacting Support

Please have ready:
1. Your name and role
2. Browser and device info
3. Screenshot of error (if applicable)
4. Steps that led to the problem
5. What you've already tried

### Self-Help Resources

- This user manual
- In-app help pages
- FAQ section
- Video tutorials (coming soon)

---

## Appendix

### Keyboard Shortcuts

| Shortcut    | Action                |
|-------------|-----------------------|
| Ctrl + L    | Go to Login           |
| Ctrl + D    | Go to Dashboard       |
| Ctrl + P    | Print Current Page    |
| Esc         | Close Modal/Popup     |

### Glossary

**Attendance Session:** A time period during which students can mark attendance

**QR Code:** Quick Response code used for attendance marking

**Enrollment:** Registration of a student in a course

**Programme:** Academic degree program (e.g., Computer Science)

**Course:** Individual subject/module within a programme

**CSRF Token:** Security measure to prevent unauthorized form submissions

---

## Document Information

**Version:** 1.0.0  
**Last Updated:** January 2025  
**Author:** Kundananji Simukonda  
**Copyright:** © 2025 All Rights Reserved

---

**Need more help? Contact us at kundananjisimukonda@gmail.com**