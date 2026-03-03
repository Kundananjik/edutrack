<?php
// Reusable lecturer navbar include
?>
<nav class="navbar navbar-expand-lg navbar-dark bg-success role-navbar">
    <div class="container">
        <a class="navbar-brand" href="../../index.php">
            <img src="<?= asset_url('assets/logo.png') ?>" alt="EduTrack Logo" height="40">
        </a>
        <span class="role-badge">Lecturer</span>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#lecturerNavbar" aria-controls="lecturerNavbar" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="lecturerNavbar">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item"><a class="nav-link" href="../../index.php">Home</a></li>
                <li class="nav-item"><a class="nav-link" href="dashboard.php">Dashboard</a></li>
                <li class="nav-item"><a class="nav-link" href="my_courses.php">Courses</a></li>
                <li class="nav-item"><a class="nav-link" href="active_sessions.php">Sessions</a></li>
                <li class="nav-item"><a class="nav-link" href="view_announcements.php">Announcements</a></li>
                <li class="nav-item"><a class="nav-link" href="../../logout.php">Logout</a></li>
            </ul>
        </div>
    </div>
</nav>
