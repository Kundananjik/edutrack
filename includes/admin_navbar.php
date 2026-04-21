<?php
// Reusable admin navbar include
$adminHomeHref = $adminHomeHref ?? '../../index.php';
$adminDashboardHref = $adminDashboardHref ?? 'dashboard.php';
$adminStudentsHref = $adminStudentsHref ?? 'manage_students.php';
$adminCoursesHref = $adminCoursesHref ?? 'manage_courses.php';
$adminReportsHref = $adminReportsHref ?? 'attendance_reports.php';
$adminAuditHref = $adminAuditHref ?? 'audit_overview.php';
$adminAnnouncementsHref = $adminAnnouncementsHref ?? 'view_announcements.php';
$adminLogoutHref = $adminLogoutHref ?? '../../logout.php';
$adminLoginHref = $adminLoginHref ?? '../../auth/login.php';
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
            <ul class="navbar-nav ms-auto">
                <li class="nav-item"><a class="nav-link" href="<?= htmlspecialchars($adminHomeHref) ?>">Home</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= htmlspecialchars($adminDashboardHref) ?>">Dashboard</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= htmlspecialchars($adminStudentsHref) ?>">Students</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= htmlspecialchars($adminCoursesHref) ?>">Courses</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= htmlspecialchars($adminReportsHref) ?>">Reports</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= htmlspecialchars($adminAuditHref) ?>">Audit</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= htmlspecialchars($adminAnnouncementsHref) ?>">Announcements</a></li>
                <?php if (function_exists('is_logged_in') && is_logged_in()): ?>
                    <li class="nav-item"><a class="nav-link" href="<?= htmlspecialchars($adminLogoutHref) ?>">Logout</a></li>
                <?php else: ?>
                    <li class="nav-item"><a class="nav-link" href="<?= htmlspecialchars($adminLoginHref) ?>">Login</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>
