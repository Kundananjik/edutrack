<?php
// Preload (auto-locate includes/preload.php)
$__et = __DIR__;
for ($__i = 0;$__i < 6;$__i++) {
    $__p = $__et . '/includes/preload.php';
    if (file_exists($__p)) {
        require_once $__p;
        break;
    }
    $__et = dirname($__et);
}
unset($__et,$__i,$__p);
// pages/admin/dashboard.php

require_once '../../includes/auth_check.php';
require_once '../../includes/config.php';
require_once '../../includes/db.php';
require_once '../../includes/functions.php';
require_once '../../includes/admin_report_helper.php';

// Ensure only admins access
require_login();
require_role(['admin']);

$metrics = admin_fetch_dashboard_metrics($pdo);

// Admin name
$adminName = htmlspecialchars($_SESSION['name'] ?? 'Admin');

// Timestamp
$lastUpdated = date('F j, Y, g:i a');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    
    <link rel="icon" type="image/png" href="<?= asset_url('assets/favicon.png') ?>">
    <title>EduTrack - Admin Dashboard</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

    <!-- Custom EduTrack CSS -->
    <link rel="stylesheet" href="css/dashboard.css">
</head>
<body>

<?php require_once '../../includes/admin_navbar.php'; ?>

<main class="dashboard-container container mt-4">
    <h1 class="text-success mb-4">Welcome, <?= $adminName ?>!</h1>

    <section class="mb-5">
        <h2 class="mb-3">System Overview</h2>
        <div class="row g-4">
            <div class="col-md-3 col-sm-6"><div class="dashboard-card text-center d-block"><i class="bi bi-mortarboard fs-2 mb-2"></i><h3>Students</h3><p><?= (int) $metrics['students'] ?></p></div></div>
            <div class="col-md-3 col-sm-6"><div class="dashboard-card text-center d-block"><i class="bi bi-easel fs-2 mb-2"></i><h3>Lecturers</h3><p><?= (int) $metrics['lecturers'] ?></p></div></div>
            <div class="col-md-3 col-sm-6"><div class="dashboard-card text-center d-block"><i class="bi bi-book fs-2 mb-2"></i><h3>Courses</h3><p><?= (int) $metrics['courses'] ?></p></div></div>
            <div class="col-md-3 col-sm-6"><div class="dashboard-card text-center d-block"><i class="bi bi-journal-text fs-2 mb-2"></i><h3>Programmes</h3><p><?= (int) $metrics['programmes'] ?></p></div></div>
        </div>
    </section>

    <section class="mb-5">
        <h2 class="mb-3">Attendance Oversight</h2>
        <div class="row g-4">
            <div class="col-md-3 col-sm-6"><div class="dashboard-card text-center d-block"><i class="bi bi-person-check fs-2 mb-2"></i><h3>Enrollments</h3><p><?= (int) $metrics['enrollments'] ?></p></div></div>
            <div class="col-md-3 col-sm-6"><div class="dashboard-card text-center d-block"><i class="bi bi-broadcast-pin fs-2 mb-2"></i><h3>Active Sessions</h3><p><?= (int) $metrics['active_sessions'] ?></p></div></div>
            <div class="col-md-3 col-sm-6"><div class="dashboard-card text-center d-block"><i class="bi bi-calendar-check fs-2 mb-2"></i><h3>Attendance Today</h3><p><?= (int) $metrics['attendance_today'] ?></p></div></div>
            <div class="col-md-3 col-sm-6">
                <a href="attendance_reports.php?risk=at_risk" class="dashboard-card text-center d-block">
                    <i class="bi bi-exclamation-triangle fs-2 mb-2"></i>
                    <h3>Low Attendance View</h3>
                    <p><?= count($metrics['at_risk_courses']) ?></p>
                </a>
            </div>
        </div>
    </section>

    <section class="mb-5">
        <h2 class="mb-3">Risk Monitoring</h2>
        <div class="row g-4">
            <div class="col-lg-4">
                <div class="card h-100">
                    <div class="card-body">
                        <h3 class="h5">Students Below 75%</h3>
                        <?php if (empty($metrics['at_risk_students'])): ?>
                            <p class="text-muted mb-0">No at-risk students found.</p>
                        <?php else: ?>
                            <ul class="list-unstyled mb-0">
                                <?php foreach ($metrics['at_risk_students'] as $student): ?>
                                    <li class="mb-2">
                                        <strong><?= htmlspecialchars($student['name']) ?></strong><br>
                                        <small><?= htmlspecialchars($student['student_number']) ?> • <?= htmlspecialchars(number_format((float) $student['attendance_rate'], 1)) ?>%</small>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card h-100">
                    <div class="card-body">
                        <h3 class="h5">Courses With Weak Attendance</h3>
                        <?php if (empty($metrics['at_risk_courses'])): ?>
                            <p class="text-muted mb-0">No weak courses found.</p>
                        <?php else: ?>
                            <ul class="list-unstyled mb-0">
                                <?php foreach ($metrics['at_risk_courses'] as $course): ?>
                                    <li class="mb-2">
                                        <strong><?= htmlspecialchars($course['course_code']) ?></strong><br>
                                        <small><?= htmlspecialchars($course['name']) ?> • <?= htmlspecialchars(number_format((float) $course['attendance_rate'], 1)) ?>%</small>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card h-100">
                    <div class="card-body">
                        <h3 class="h5">Courses Without Lecturer</h3>
                        <?php if (empty($metrics['courses_without_lecturer'])): ?>
                            <p class="text-muted mb-0">All courses have lecturer assignments.</p>
                        <?php else: ?>
                            <ul class="list-unstyled mb-0">
                                <?php foreach ($metrics['courses_without_lecturer'] as $course): ?>
                                    <li class="mb-2">
                                        <strong><?= htmlspecialchars($course['course_code']) ?></strong><br>
                                        <small><?= htmlspecialchars($course['name']) ?></small>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- User Management -->
    <section class="mb-5">
        <h2 class="mb-3">User Management</h2>
        <div class="row g-4">
            <div class="col-md-3 col-sm-6">
                <a href="add_student.php" class="dashboard-card text-center d-block">
                    <i class="bi bi-person-plus fs-2 mb-2"></i>
                    <h3>Add Student</h3>
                </a>
            </div>
            <div class="col-md-3 col-sm-6">
                <a href="manage_students.php" class="dashboard-card text-center d-block">
                    <i class="bi bi-mortarboard fs-2 mb-2"></i>
                    <h3>Manage Students</h3>
                    <p><?= (int) $metrics['students'] ?></p>
                </a>
            </div>
            <div class="col-md-3 col-sm-6">
                <a href="add_lecturer.php" class="dashboard-card text-center d-block">
                    <i class="bi bi-person-plus fs-2 mb-2"></i>
                    <h3>Add Lecturer</h3>
                </a>
            </div>
            <div class="col-md-3 col-sm-6">
                <a href="manage_lecturers.php" class="dashboard-card text-center d-block">
                    <i class="bi bi-easel fs-2 mb-2"></i>
                    <h3>Manage Lecturers</h3>
                    <p><?= (int) $metrics['lecturers'] ?></p>
                </a>
            </div>
        </div>
    </section>

    <!-- Academic Management -->
    <section class="mb-5">
        <h2 class="mb-3">Academic Management</h2>
        <div class="row g-4">
            <div class="col-md-3 col-sm-6">
                <a href="manage_programmes.php" class="dashboard-card text-center d-block">
                    <i class="bi bi-book fs-2 mb-2"></i>
                    <h3>Programmes</h3>
                    <p><?= (int) $metrics['programmes'] ?></p>
                </a>
            </div>
            <div class="col-md-3 col-sm-6">
                <a href="manage_courses.php" class="dashboard-card text-center d-block">
                    <i class="bi bi-book fs-2 mb-2"></i>
                    <h3>Courses</h3>
                    <p><?= (int) $metrics['courses'] ?></p>
                </a>
            </div>
            <div class="col-md-3 col-sm-6">
                <a href="enrollment.php" class="dashboard-card text-center d-block">
                    <i class="bi bi-person-plus fs-2 mb-2"></i>
                    <h3>Enrollments</h3>
                    <p><?= (int) $metrics['enrollments'] ?></p>
                </a>
            </div>
        </div>
    </section>

    <!-- Reporting -->
    <section class="mb-5">
        <h2 class="mb-3">Reporting</h2>
        <div class="row g-4">
            <div class="col-md-3 col-sm-6">
                <a href="attendance_reports.php" class="dashboard-card text-center d-block">
                    <i class="bi bi-graph-up fs-2 mb-2"></i>
                    <h3>Attendance Reports</h3>
                </a>
            </div>
            <div class="col-md-3 col-sm-6">
                <a href="audit_overview.php" class="dashboard-card text-center d-block">
                    <i class="bi bi-shield-check fs-2 mb-2"></i>
                    <h3>Audit Overview</h3>
                </a>
            </div>
        </div>
    </section>

    <!-- Communication -->
    <section class="mb-5">
        <h2 class="mb-3">Communication</h2>
        <div class="row g-4">
            <div class="col-md-3 col-sm-6">
                <a href="send_announcement.php" class="dashboard-card text-center d-block">
                    <i class="bi bi-megaphone fs-2 mb-2"></i>
                    <h3>Send Announcement</h3>
                </a>
            </div>
            <div class="col-md-3 col-sm-6">
                <a href="view_announcements.php" class="dashboard-card text-center d-block">
                    <i class="bi bi-eye fs-2 mb-2"></i>
                    <h3>View Announcements</h3>
                </a>
            </div>
            <div class="col-md-3 col-sm-6">
                <a href="view_messages.php" class="dashboard-card text-center d-block">
                    <i class="bi bi-envelope fs-2 mb-2"></i>
                    <h3>Messages</h3>
                </a>
            </div>
            <div class="col-md-3 col-sm-6">
                <a href="notifications.php" class="dashboard-card text-center d-block">
                    <i class="bi bi-bell fs-2 mb-2"></i>
                    <h3>Notifications</h3>
                </a>
            </div>
        </div>
    </section>

    <div class="last-updated text-muted mt-4">
        Last updated: <?= $lastUpdated ?>
    </div>
</main>

<?php require_once '../../includes/footer.php'; ?>

<!-- Bootstrap JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>


