<?php
// Preload (auto-locate includes/preload.php)
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
require_once '../../includes/admin_report_helper.php';

require_login();
require_role(['admin']);

$audit = [
    'active_sessions' => [],
    'sessions_without_attendance' => [],
    'courses_without_lecturer' => [],
    'inactive_students_with_enrollments' => [],
];
$error = null;

try {
    $audit = admin_fetch_audit_snapshot($pdo);
} catch (Exception $e) {
    error_log('Database error in admin/audit_overview.php: ' . $e->getMessage());
    $error = 'An error occurred while loading the audit overview. Please try again later.';
}

$summaryCards = [
    [
        'label' => 'Active Sessions',
        'count' => count($audit['active_sessions']),
        'icon' => 'bi-broadcast-pin',
        'href' => 'dashboard.php',
    ],
    [
        'label' => 'Empty Sessions',
        'count' => count($audit['sessions_without_attendance']),
        'icon' => 'bi-calendar-x',
        'href' => 'attendance_reports.php',
    ],
    [
        'label' => 'Courses Without Lecturer',
        'count' => count($audit['courses_without_lecturer']),
        'icon' => 'bi-person-x',
        'href' => 'manage_courses.php',
    ],
    [
        'label' => 'Inactive Enrolled Students',
        'count' => count($audit['inactive_students_with_enrollments']),
        'icon' => 'bi-exclamation-diamond',
        'href' => 'manage_students.php',
    ],
];

$lastUpdated = date('F j, Y, g:i a');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">

    <link rel="icon" type="image/png" href="<?= asset_url('assets/favicon.png') ?>">
<title>Audit Overview - Admin</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
<link rel="stylesheet" href="css/dashboard.css">
</head>
<body class="bg-light">

<?php require_once '../../includes/admin_navbar.php'; ?>

<main class="container py-5 dashboard-container">
    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
        <div>
            <h1 class="mb-2">Audit Overview</h1>
            <p class="text-muted mb-0">Review admin-side operational gaps that need follow-up.</p>
        </div>
        <a href="dashboard.php" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Back to Dashboard
        </a>
    </div>

    <?php if ($error !== null): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php else: ?>
        <section class="mb-5">
            <div class="row g-4">
                <?php foreach ($summaryCards as $card): ?>
                    <div class="col-md-6 col-xl-3">
                        <a href="<?= htmlspecialchars($card['href']) ?>" class="dashboard-card text-center d-block">
                            <i class="bi <?= htmlspecialchars($card['icon']) ?> fs-2 mb-2"></i>
                            <h3><?= htmlspecialchars($card['label']) ?></h3>
                            <p><?= (int) $card['count'] ?></p>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>

        <section class="mb-5">
            <div class="card">
                <div class="card-header">
                    <h2 class="h5 mb-0">Active Attendance Sessions</h2>
                </div>
                <div class="card-body">
                    <?php if ($audit['active_sessions'] === []): ?>
                        <p class="text-muted mb-0">No active attendance sessions right now.</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>Session Code</th>
                                        <th>Course</th>
                                        <th>Lecturer</th>
                                        <th>Created</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($audit['active_sessions'] as $session): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($session['session_code']) ?></td>
                                            <td><?= htmlspecialchars($session['course_name']) ?></td>
                                            <td><?= htmlspecialchars($session['lecturer_name']) ?></td>
                                            <td><?= htmlspecialchars(date('M j, Y g:i a', strtotime($session['created_at']))) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </section>

        <section class="mb-5">
            <div class="row g-4">
                <div class="col-xl-6">
                    <div class="card h-100">
                        <div class="card-header">
                            <h2 class="h5 mb-0">Sessions Without Attendance Records</h2>
                        </div>
                        <div class="card-body">
                            <?php if ($audit['sessions_without_attendance'] === []): ?>
                                <p class="text-muted mb-0">Every recorded session has at least one attendance mark.</p>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table align-middle mb-0">
                                        <thead>
                                            <tr>
                                                <th>Session Code</th>
                                                <th>Course</th>
                                                <th>Created</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($audit['sessions_without_attendance'] as $session): ?>
                                                <tr>
                                                    <td><?= htmlspecialchars($session['session_code']) ?></td>
                                                    <td><?= htmlspecialchars($session['course_name']) ?></td>
                                                    <td><?= htmlspecialchars(date('M j, Y g:i a', strtotime($session['created_at']))) ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <div class="col-xl-6">
                    <div class="card h-100">
                        <div class="card-header">
                            <h2 class="h5 mb-0">Courses Without Lecturer Assignment</h2>
                        </div>
                        <div class="card-body">
                            <?php if ($audit['courses_without_lecturer'] === []): ?>
                                <p class="text-muted mb-0">All listed courses have lecturer assignments.</p>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table align-middle mb-0">
                                        <thead>
                                            <tr>
                                                <th>Course</th>
                                                <th>Code</th>
                                                <th>Programme</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($audit['courses_without_lecturer'] as $course): ?>
                                                <tr>
                                                    <td><?= htmlspecialchars($course['name']) ?></td>
                                                    <td><?= htmlspecialchars($course['course_code']) ?></td>
                                                    <td><?= htmlspecialchars($course['programme_name']) ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="mb-4">
            <div class="card">
                <div class="card-header">
                    <h2 class="h5 mb-0">Inactive Students Still Holding Enrollments</h2>
                </div>
                <div class="card-body">
                    <?php if ($audit['inactive_students_with_enrollments'] === []): ?>
                        <p class="text-muted mb-0">No inactive students are currently attached to enrollments.</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>Student</th>
                                        <th>Student Number</th>
                                        <th>Enrollment Count</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($audit['inactive_students_with_enrollments'] as $student): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($student['name']) ?></td>
                                            <td><?= htmlspecialchars($student['student_number']) ?></td>
                                            <td><?= (int) $student['enrollment_count'] ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </section>
    <?php endif; ?>

    <div class="last-updated text-muted mt-4">
        Last updated: <?= htmlspecialchars($lastUpdated) ?>
    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<?php require_once '../../includes/footer.php'; ?>
</body>
</html>
