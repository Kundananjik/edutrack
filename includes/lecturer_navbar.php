<?php
// Reusable lecturer navbar include
$lecturerHomeHref = $lecturerHomeHref ?? '../../index.php';
$lecturerDashboardHref = $lecturerDashboardHref ?? 'dashboard.php';
$lecturerCoursesHref = $lecturerCoursesHref ?? 'my_courses.php';
$lecturerSessionsHref = $lecturerSessionsHref ?? 'active_sessions.php';
$lecturerAnnouncementsHref = $lecturerAnnouncementsHref ?? 'view_announcements.php';
$lecturerChangePasswordHref = $lecturerChangePasswordHref ?? '../account/change_password.php';
$lecturerLogoutHref = $lecturerLogoutHref ?? '../../logout.php';
?>
<nav class="navbar navbar-expand-lg navbar-dark bg-success role-navbar">
    <div class="container">
        <a class="navbar-brand" href="<?= htmlspecialchars($lecturerHomeHref) ?>">
            <img src="<?= asset_url('assets/logo.png') ?>" alt="EduTrack Logo" height="40">
        </a>
        <span class="role-badge">Lecturer</span>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#lecturerNavbar" aria-controls="lecturerNavbar" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="lecturerNavbar">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item"><a class="nav-link" href="<?= htmlspecialchars($lecturerHomeHref) ?>">Home</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= htmlspecialchars($lecturerDashboardHref) ?>">Dashboard</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= htmlspecialchars($lecturerCoursesHref) ?>">Courses</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= htmlspecialchars($lecturerSessionsHref) ?>">Sessions</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= htmlspecialchars($lecturerAnnouncementsHref) ?>">Announcements</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= htmlspecialchars($lecturerChangePasswordHref) ?>">Change Password</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= htmlspecialchars($lecturerLogoutHref) ?>">Logout</a></li>
            </ul>
        </div>
    </div>
</nav>
