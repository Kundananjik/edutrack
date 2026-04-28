<?php
// Reusable admin navbar include
$adminHomeHref = $adminHomeHref ?? '../../index.php';
$adminDashboardHref = $adminDashboardHref ?? 'dashboard.php';
$adminStudentsHref = $adminStudentsHref ?? 'manage_students.php';
$adminLecturersHref = $adminLecturersHref ?? 'manage_lecturers.php';
$adminProgrammesHref = $adminProgrammesHref ?? 'manage_programmes.php';
$adminCoursesHref = $adminCoursesHref ?? 'manage_courses.php';
$adminEnrollmentHref = $adminEnrollmentHref ?? 'enrollment.php';
$adminReportsHref = $adminReportsHref ?? 'attendance_reports.php';
$adminAuditHref = $adminAuditHref ?? 'audit_overview.php';
$adminAnnouncementsHref = $adminAnnouncementsHref ?? 'view_announcements.php';
$adminMessagesHref = $adminMessagesHref ?? 'view_messages.php';
$adminContactMessagesHref = $adminContactMessagesHref ?? 'contact_messages.php';
$adminNotificationsHref = $adminNotificationsHref ?? 'notifications.php';
$adminHelpHref = $adminHelpHref ?? 'help.php';
$adminLogoutHref = $adminLogoutHref ?? '../../logout.php';
$adminLoginHref = $adminLoginHref ?? '../../auth/login.php';

$currentAdminPage = basename(parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?? '');
$adminNavigationGroups = [
    'dashboard' => ['dashboard.php'],
    'people' => ['manage_students.php', 'add_student.php', 'edit_student.php', 'manage_lecturers.php', 'add_lecturer.php', 'edit_lecturer.php', 'view_lecturer.php'],
    'academics' => ['manage_programmes.php', 'add_programme.php', 'edit_programme.php', 'view_programme.php', 'manage_courses.php', 'add_course.php', 'edit_course.php', 'view_course.php', 'enrollment.php'],
    'reports' => ['attendance_reports.php', 'generate_report.php', 'download_report.php', 'print_report.php', 'audit_overview.php'],
    'communication' => ['view_announcements.php', 'send_announcement.php', 'view_messages.php', 'reply_message.php', 'contact_messages.php', 'contact.php', 'notifications.php'],
    'support' => ['help.php', 'privacy-policy.php', 'terms-of-service.php'],
];

$isCurrentAdminGroup = static function (string $groupKey) use ($adminNavigationGroups, $currentAdminPage): bool {
    return in_array($currentAdminPage, $adminNavigationGroups[$groupKey] ?? [], true);
};
?>
<nav class="navbar navbar-expand-lg navbar-dark bg-success role-navbar">
    <div class="container">
        <a class="navbar-brand" href="<?= htmlspecialchars($adminHomeHref) ?>">
            <img src="<?= asset_url('assets/logo.png') ?>" alt="EduTrack Logo" height="40">
        </a>
        <span class="role-badge">Admin</span>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#adminNavbar" aria-controls="adminNavbar" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="adminNavbar">
            <ul class="navbar-nav ms-auto align-items-lg-center">
                <li class="nav-item">
                    <a class="nav-link <?= $isCurrentAdminGroup('dashboard') ? 'active' : '' ?>" href="<?= htmlspecialchars($adminDashboardHref) ?>" <?= $isCurrentAdminGroup('dashboard') ? 'aria-current="page"' : '' ?>>Dashboard</a>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle <?= $isCurrentAdminGroup('people') ? 'active' : '' ?>" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">People</a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item <?= $currentAdminPage === 'manage_students.php' ? 'active' : '' ?>" href="<?= htmlspecialchars($adminStudentsHref) ?>">Students</a></li>
                        <li><a class="dropdown-item <?= $currentAdminPage === 'add_student.php' ? 'active' : '' ?>" href="add_student.php">Add Student</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item <?= $currentAdminPage === 'manage_lecturers.php' ? 'active' : '' ?>" href="<?= htmlspecialchars($adminLecturersHref) ?>">Lecturers</a></li>
                        <li><a class="dropdown-item <?= $currentAdminPage === 'add_lecturer.php' ? 'active' : '' ?>" href="add_lecturer.php">Add Lecturer</a></li>
                    </ul>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle <?= $isCurrentAdminGroup('academics') ? 'active' : '' ?>" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">Academics</a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item <?= $currentAdminPage === 'manage_programmes.php' ? 'active' : '' ?>" href="<?= htmlspecialchars($adminProgrammesHref) ?>">Programmes</a></li>
                        <li><a class="dropdown-item <?= $currentAdminPage === 'add_programme.php' ? 'active' : '' ?>" href="add_programme.php">Add Programme</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item <?= $currentAdminPage === 'manage_courses.php' ? 'active' : '' ?>" href="<?= htmlspecialchars($adminCoursesHref) ?>">Courses</a></li>
                        <li><a class="dropdown-item <?= $currentAdminPage === 'add_course.php' ? 'active' : '' ?>" href="add_course.php">Add Course</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item <?= $currentAdminPage === 'enrollment.php' ? 'active' : '' ?>" href="<?= htmlspecialchars($adminEnrollmentHref) ?>">Enrollment</a></li>
                    </ul>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle <?= $isCurrentAdminGroup('reports') ? 'active' : '' ?>" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">Reports</a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item <?= $currentAdminPage === 'attendance_reports.php' ? 'active' : '' ?>" href="<?= htmlspecialchars($adminReportsHref) ?>">Attendance Reports</a></li>
                        <li><a class="dropdown-item <?= $currentAdminPage === 'audit_overview.php' ? 'active' : '' ?>" href="<?= htmlspecialchars($adminAuditHref) ?>">Audit Overview</a></li>
                    </ul>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle <?= $isCurrentAdminGroup('communication') ? 'active' : '' ?>" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">Communication</a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item <?= $currentAdminPage === 'view_announcements.php' ? 'active' : '' ?>" href="<?= htmlspecialchars($adminAnnouncementsHref) ?>">Announcements</a></li>
                        <li><a class="dropdown-item <?= $currentAdminPage === 'send_announcement.php' ? 'active' : '' ?>" href="send_announcement.php">Send Announcement</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item <?= $currentAdminPage === 'view_messages.php' ? 'active' : '' ?>" href="<?= htmlspecialchars($adminMessagesHref) ?>">Messages</a></li>
                        <li><a class="dropdown-item <?= $currentAdminPage === 'contact_messages.php' ? 'active' : '' ?>" href="<?= htmlspecialchars($adminContactMessagesHref) ?>">Contact Messages</a></li>
                        <li><a class="dropdown-item <?= $currentAdminPage === 'notifications.php' ? 'active' : '' ?>" href="<?= htmlspecialchars($adminNotificationsHref) ?>">Notifications</a></li>
                    </ul>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle <?= $isCurrentAdminGroup('support') ? 'active' : '' ?>" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">Support</a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item <?= $currentAdminPage === 'help.php' ? 'active' : '' ?>" href="<?= htmlspecialchars($adminHelpHref) ?>">Help</a></li>
                        <li><a class="dropdown-item <?= $currentAdminPage === 'privacy-policy.php' ? 'active' : '' ?>" href="privacy-policy.php">Privacy Policy</a></li>
                        <li><a class="dropdown-item <?= $currentAdminPage === 'terms-of-service.php' ? 'active' : '' ?>" href="terms-of-service.php">Terms of Service</a></li>
                    </ul>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?= htmlspecialchars($adminHomeHref) ?>">Home</a>
                </li>
                <?php if (function_exists('is_logged_in') && is_logged_in()): ?>
                    <li class="nav-item"><a class="nav-link" href="<?= htmlspecialchars($adminLogoutHref) ?>">Logout</a></li>
                <?php else: ?>
                    <li class="nav-item"><a class="nav-link" href="<?= htmlspecialchars($adminLoginHref) ?>">Login</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>
