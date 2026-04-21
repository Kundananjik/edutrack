<?php
// Reusable student navbar include
$studentHomeHref = $studentHomeHref ?? '../../index.php';
$studentDashboardHref = $studentDashboardHref ?? 'dashboard.php';
$studentAnnouncementsHref = $studentAnnouncementsHref ?? 'view_announcements.php';
$studentChangePasswordHref = $studentChangePasswordHref ?? '../account/change_password.php';
$studentLogoutHref = $studentLogoutHref ?? '../../logout.php';
?>
<nav class="navbar navbar-expand-lg navbar-dark bg-success role-navbar">
    <div class="container">
        <a class="navbar-brand" href="<?= htmlspecialchars($studentHomeHref) ?>">
            <img src="<?= asset_url('assets/logo.png') ?>" alt="EduTrack Logo" height="40">
        </a>
        <span class="role-badge">Student</span>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#studentNavbar" aria-controls="studentNavbar" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="studentNavbar">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item"><a class="nav-link" href="<?= htmlspecialchars($studentHomeHref) ?>">Home</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= htmlspecialchars($studentDashboardHref) ?>">Dashboard</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= htmlspecialchars($studentAnnouncementsHref) ?>">Announcements</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= htmlspecialchars($studentChangePasswordHref) ?>">Change Password</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= htmlspecialchars($studentLogoutHref) ?>">Logout</a></li>
            </ul>
        </div>
    </div>
</nav>
