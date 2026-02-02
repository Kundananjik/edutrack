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

// Ensure only admins access
require_login();
require_role(['admin']);

/**
 * Get a count of rows from a table
 */
function getCount($pdo, $table, $column = null, $value = null)
{
    $sql = "SELECT COUNT(*) FROM {$table}";
    $params = [];
    if ($column && $value) {
        $sql .= " WHERE {$column} = ?";
        $params[] = $value;
    }
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return (int)$stmt->fetchColumn();
    } catch (PDOException $e) {
        error_log('Database error in getCount: ' . $e->getMessage());
        return 0;
    }
}

// Fetch counts
$students    = getCount($pdo, 'users', 'role', 'student');
$lecturers   = getCount($pdo, 'users', 'role', 'lecturer');
$programmes  = getCount($pdo, 'programmes');
$courses     = getCount($pdo, 'courses');
$enrollments = getCount($pdo, 'enrollments');

// Admin name
$adminName = htmlspecialchars($_SESSION['name'] ?? 'Admin');

// Timestamp
$lastUpdated = date('F j, Y, g:i a');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EduTrack - Admin Dashboard</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Custom EduTrack CSS -->
    <link rel="stylesheet" href="css/dashboard.css">
</head>
<body>

<?php require_once '../../includes/admin_navbar.php'; ?>

<main class="dashboard-container container mt-4">
    <h1 class="text-success mb-4">Welcome, <?= $adminName ?>!</h1>

    <!-- User Management -->
    <section class="mb-5">
        <h2 class="mb-3">User Management</h2>
        <div class="row g-4">
            <div class="col-md-3 col-sm-6">
                <a href="add_student.php" class="dashboard-card text-center d-block">
                    <i class="fas fa-user-plus fa-2x mb-2"></i>
                    <h3>Add Student</h3>
                </a>
            </div>
            <div class="col-md-3 col-sm-6">
                <a href="manage_students.php" class="dashboard-card text-center d-block">
                    <i class="fas fa-user-graduate fa-2x mb-2"></i>
                    <h3>Manage Students</h3>
                    <p><?= $students ?></p>
                </a>
            </div>
            <div class="col-md-3 col-sm-6">
                <a href="add_lecturer.php" class="dashboard-card text-center d-block">
                    <i class="fas fa-user-plus fa-2x mb-2"></i>
                    <h3>Add Lecturer</h3>
                </a>
            </div>
            <div class="col-md-3 col-sm-6">
                <a href="manage_lecturers.php" class="dashboard-card text-center d-block">
                    <i class="fas fa-chalkboard-teacher fa-2x mb-2"></i>
                    <h3>Manage Lecturers</h3>
                    <p><?= $lecturers ?></p>
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
                    <i class="fas fa-book fa-2x mb-2"></i>
                    <h3>Programmes</h3>
                    <p><?= $programmes ?></p>
                </a>
            </div>
            <div class="col-md-3 col-sm-6">
                <a href="manage_courses.php" class="dashboard-card text-center d-block">
                    <i class="fas fa-book-open fa-2x mb-2"></i>
                    <h3>Courses</h3>
                    <p><?= $courses ?></p>
                </a>
            </div>
            <div class="col-md-3 col-sm-6">
                <a href="enrollment.php" class="dashboard-card text-center d-block">
                    <i class="fas fa-user-plus fa-2x mb-2"></i>
                    <h3>Enrollments</h3>
                    <p><?= $enrollments ?></p>
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
                    <i class="fas fa-chart-line fa-2x mb-2"></i>
                    <h3>Attendance Reports</h3>
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
                    <i class="fas fa-bullhorn fa-2x mb-2"></i>
                    <h3>Send Announcement</h3>
                </a>
            </div>
            <div class="col-md-3 col-sm-6">
                <a href="view_announcements.php" class="dashboard-card text-center d-block">
                    <i class="fas fa-eye fa-2x mb-2"></i>
                    <h3>View Announcements</h3>
                </a>
            </div>
            <div class="col-md-3 col-sm-6">
                <a href="view_messages.php" class="dashboard-card text-center d-block">
                    <i class="fas fa-envelope fa-2x mb-2"></i>
                    <h3>Messages</h3>
                </a>
            </div>
            <div class="col-md-3 col-sm-6">
                <a href="notifications.php" class="dashboard-card text-center d-block">
                    <i class="fas fa-bell fa-2x mb-2"></i>
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
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
