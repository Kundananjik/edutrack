<?php
$__et = __DIR__;
for ($__i = 0; $__i < 6; $__i++) {
    $__p = $__et . '/includes/preload.php';
    if (file_exists($__p)) {
        require_once $__p;
        break;
    }
    $__et = dirname($__et);
}
unset($__et, $__i, $__p);

require_once '../../includes/auth_check.php';
require_once '../../includes/config.php';
require_once '../../includes/db.php';
require_once '../../includes/functions.php';
require_once '../../includes/lecturer_report_helper.php';

require_login();
require_role(['lecturer']);

$lecturerId = (int) ($_SESSION['user_id'] ?? 0);
if ($lecturerId <= 0) {
    redirect('../../auth/login.php');
}

$lecturerName = htmlspecialchars($_SESSION['name'] ?? 'Lecturer');
$metrics = [
    'courses' => 0,
    'students' => 0,
    'active_sessions' => 0,
    'attendance_today' => 0,
    'courses_with_sessions' => 0,
    'at_risk_students' => [],
    'at_risk_courses' => [],
    'active_session_courses' => [],
];
$errorMessage = null;

try {
    $metrics = lecturer_fetch_dashboard_metrics($pdo, $lecturerId);
} catch (PDOException $e) {
    error_log('Lecturer dashboard error: ' . $e->getMessage());
    $errorMessage = 'Failed to load dashboard data. Please try again later.';
}

$lastUpdated = date('F j, Y, g:i a');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/png" href="<?= asset_url('assets/favicon.png') ?>">
    <title>EduTrack - Lecturer Dashboard</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="css/dashboard.css">
</head>
<body class="bg-light d-flex flex-column min-vh-100">

<?php require_once '../../includes/lecturer_navbar.php'; ?>

<main class="dashboard-container container mt-4 flex-grow-1">
    <h1 class="text-success mb-4">Welcome, <?= $lecturerName ?>!</h1>

    <?php if ($errorMessage !== null): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($errorMessage) ?></div>
    <?php else: ?>
        <section class="mb-5">
            <h2 class="mb-3">Teaching Overview</h2>
            <div class="row g-4">
                <div class="col-md-3 col-sm-6">
                    <a href="my_courses.php" class="dashboard-card text-center d-block">
                        <i class="bi bi-journal-bookmark fs-2 mb-2"></i>
                        <h3>My Courses</h3>
                        <p><?= (int) $metrics['courses'] ?></p>
                    </a>
                </div>
                <div class="col-md-3 col-sm-6">
                    <a href="my_students.php" class="dashboard-card text-center d-block">
                        <i class="bi bi-mortarboard fs-2 mb-2"></i>
                        <h3>My Students</h3>
                        <p><?= (int) $metrics['students'] ?></p>
                    </a>
                </div>
                <div class="col-md-3 col-sm-6">
                    <a href="active_sessions.php" class="dashboard-card text-center d-block">
                        <i class="bi bi-broadcast-pin fs-2 mb-2"></i>
                        <h3>Active Sessions</h3>
                        <p><?= (int) $metrics['active_sessions'] ?></p>
                    </a>
                </div>
                <div class="col-md-3 col-sm-6">
                    <a href="attendance_reports.php" class="dashboard-card text-center d-block">
                        <i class="bi bi-calendar-check fs-2 mb-2"></i>
                        <h3>Attendance Today</h3>
                        <p><?= (int) $metrics['attendance_today'] ?></p>
                    </a>
                </div>
            </div>
        </section>

        <section class="mb-5">
            <h2 class="mb-3">Attendance Oversight</h2>
            <div class="row g-4">
                <div class="col-md-4 col-sm-6">
                    <a href="attendance_reports.php" class="dashboard-card text-center d-block">
                        <i class="bi bi-graph-up fs-2 mb-2"></i>
                        <h3>Courses With Reports</h3>
                        <p><?= (int) $metrics['courses_with_sessions'] ?></p>
                    </a>
                </div>
                <div class="col-md-4 col-sm-6">
                    <a href="generate_report.php?attendance_filter=at_risk" class="dashboard-card text-center d-block">
                        <i class="bi bi-exclamation-triangle fs-2 mb-2"></i>
                        <h3>Low Attendance View</h3>
                        <p><?= count($metrics['at_risk_courses']) ?></p>
                    </a>
                </div>
                <div class="col-md-4 col-sm-6">
                    <a href="active_sessions.php" class="dashboard-card text-center d-block">
                        <i class="bi bi-qr-code-scan fs-2 mb-2"></i>
                        <h3>Open Session Tools</h3>
                        <p><?= count($metrics['active_session_courses']) ?></p>
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
                            <?php if ($metrics['at_risk_students'] === []): ?>
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
                            <?php if ($metrics['at_risk_courses'] === []): ?>
                                <p class="text-muted mb-0">No weak-attendance courses found.</p>
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
                            <h3 class="h5">Currently Active Sessions</h3>
                            <?php if ($metrics['active_session_courses'] === []): ?>
                                <p class="text-muted mb-0">No active sessions are running right now.</p>
                            <?php else: ?>
                                <ul class="list-unstyled mb-0">
                                    <?php foreach ($metrics['active_session_courses'] as $session): ?>
                                        <li class="mb-2">
                                            <strong><?= htmlspecialchars($session['course_code']) ?></strong><br>
                                            <small><?= htmlspecialchars($session['name']) ?> • <?= htmlspecialchars($session['session_code']) ?></small>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="mb-5">
            <h2 class="mb-3">Teaching Management</h2>
            <div class="row g-4">
                <div class="col-md-3 col-sm-6">
                    <a href="my_courses.php" class="dashboard-card text-center d-block">
                        <i class="bi bi-book fs-2 mb-2"></i>
                        <h3>Manage Courses</h3>
                        <p><?= (int) $metrics['courses'] ?></p>
                    </a>
                </div>
                <div class="col-md-3 col-sm-6">
                    <a href="my_students.php" class="dashboard-card text-center d-block">
                        <i class="bi bi-people fs-2 mb-2"></i>
                        <h3>Review Students</h3>
                        <p><?= (int) $metrics['students'] ?></p>
                    </a>
                </div>
                <div class="col-md-3 col-sm-6">
                    <a href="active_sessions.php" class="dashboard-card text-center d-block">
                        <i class="bi bi-play-circle fs-2 mb-2"></i>
                        <h3>Session Control</h3>
                        <p><?= (int) $metrics['active_sessions'] ?></p>
                    </a>
                </div>
                <div class="col-md-3 col-sm-6">
                    <a href="profile.php" class="dashboard-card text-center d-block">
                        <i class="bi bi-person-gear fs-2 mb-2"></i>
                        <h3>Update Profile</h3>
                    </a>
                </div>
            </div>
        </section>

        <section class="mb-5">
            <h2 class="mb-3">Reporting</h2>
            <div class="row g-4">
                <div class="col-md-3 col-sm-6">
                    <a href="attendance_reports.php" class="dashboard-card text-center d-block">
                        <i class="bi bi-bar-chart-line fs-2 mb-2"></i>
                        <h3>Attendance Reports</h3>
                    </a>
                </div>
            </div>
        </section>

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
                    <a href="contact.php" class="dashboard-card text-center d-block">
                        <i class="bi bi-envelope fs-2 mb-2"></i>
                        <h3>Contact Support</h3>
                    </a>
                </div>
            </div>
        </section>

        <section class="mb-4">
            <h2 class="mb-3">Account</h2>
            <div class="row g-4">
                <div class="col-md-3 col-sm-6">
                    <a href="profile.php" class="dashboard-card text-center d-block">
                        <i class="bi bi-person-circle fs-2 mb-2"></i>
                        <h3>My Profile</h3>
                    </a>
                </div>
                <div class="col-md-3 col-sm-6">
                    <a href="../account/change_password.php" class="dashboard-card text-center d-block">
                        <i class="bi bi-shield-lock fs-2 mb-2"></i>
                        <h3>Change Password</h3>
                    </a>
                </div>
            </div>
        </section>

        <div class="last-updated text-muted mt-4">
            Last updated: <?= htmlspecialchars($lastUpdated) ?>
        </div>
    <?php endif; ?>
</main>

<?php require_once '../../includes/footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
