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

require_once '../../includes/auth_check.php';
require_once '../../includes/config.php';
require_once '../../includes/db.php';
require_once '../../includes/admin_report_helper.php';

require_login();
require_role(['admin']);

$programmeFilter = (int) ($_GET['programme_id'] ?? 0);
$lecturerFilter = (int) ($_GET['lecturer_id'] ?? 0);
$riskFilter = admin_normalize_attendance_filter($_GET['risk'] ?? 'all');
$courses = [];
$filterOptions = [
    'programmes' => [],
    'lecturers' => [],
];
$error = null;

try {
    $filterOptions = admin_fetch_report_filter_options($pdo);
    $courses = admin_fetch_report_catalog($pdo, $programmeFilter, $lecturerFilter, $riskFilter);
} catch (Exception $e) {
    error_log('Database error in admin/attendance_reports.php: ' . $e->getMessage());
    $error = 'An error occurred while fetching courses. Please try again later.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">

    <link rel="icon" type="image/png" href="<?= asset_url('assets/favicon.png') ?>">
<title>Attendance Reports - Admin</title>

<!-- Bootstrap CSS -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

<!-- Font Awesome -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

<!-- Custom EduTrack CSS -->
<link rel="stylesheet" href="css/dashboard.css">

</head>
<body class="bg-light">

<?php require_once '../../includes/admin_navbar.php'; ?>

<div class="container py-5 dashboard-container">

    <!-- Back to Dashboard Button -->
    <div class="mb-4">
        <a href="dashboard.php" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Back to Dashboard
        </a>
    </div>

    <h1 class="mb-4">Attendance Reports</h1>

    <form method="GET" class="row g-3 align-items-end mb-4">
        <div class="col-md-4">
            <label for="programme_id" class="form-label">Programme</label>
            <select id="programme_id" name="programme_id" class="form-select">
                <option value="0">All Programmes</option>
                <?php foreach ($filterOptions['programmes'] as $programme): ?>
                    <option value="<?= (int) $programme['id'] ?>" <?= $programmeFilter === (int) $programme['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($programme['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-4">
            <label for="lecturer_id" class="form-label">Lecturer</label>
            <select id="lecturer_id" name="lecturer_id" class="form-select">
                <option value="0">All Lecturers</option>
                <?php foreach ($filterOptions['lecturers'] as $lecturer): ?>
                    <option value="<?= (int) $lecturer['id'] ?>" <?= $lecturerFilter === (int) $lecturer['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($lecturer['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-4">
            <label for="risk" class="form-label">Risk Filter</label>
            <select id="risk" name="risk" class="form-select">
                <option value="all" <?= $riskFilter === 'all' ? 'selected' : '' ?>>All Courses</option>
                <option value="at_risk" <?= $riskFilter === 'at_risk' ? 'selected' : '' ?>>Attendance Under 75%</option>
                <option value="perfect" <?= $riskFilter === 'perfect' ? 'selected' : '' ?>>Perfect 100%</option>
                <option value="absent_only" <?= $riskFilter === 'absent_only' ? 'selected' : '' ?>>Below Perfect</option>
            </select>
        </div>
        <div class="col-12 d-flex gap-2">
            <button type="submit" class="btn btn-success">Apply Filters</button>
            <a href="attendance_reports.php" class="btn btn-outline-secondary">Reset</a>
        </div>
    </form>

    <!-- Error or Info Messages -->
    <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php elseif (empty($courses)): ?>
        <div class="alert alert-info">No courses are currently available to generate reports for.</div>
    <?php else: ?>
        <p>Select a course below to open the full admin attendance report:</p>
        <div class="row row-cols-1 row-cols-md-2 row-cols-xl-3 g-4">
            <?php foreach ($courses as $course): ?>
                <div class="col">
                    <a href="generate_report.php?course_id=<?= $course['id'] ?>" class="card h-100 text-decoration-none text-dark shadow-sm">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start gap-3 mb-3">
                                <div>
                                    <p class="small text-uppercase fw-semibold mb-2"><?= htmlspecialchars($course['course_code']) ?></p>
                                    <h5 class="card-title mb-1"><?= htmlspecialchars($course['name']) ?></h5>
                                    <p class="text-muted small mb-0"><?= htmlspecialchars($course['programme_name']) ?></p>
                                </div>
                                <i class="bi bi-bar-chart-line fs-3 text-success"></i>
                            </div>
                            <p class="small mb-2"><strong>Lecturer(s):</strong> <?= htmlspecialchars($course['lecturer_names'] ?: 'Not Assigned') ?></p>
                            <p class="small mb-1">Students: <?= (int) $course['student_count'] ?></p>
                            <p class="small mb-1">Sessions: <?= (int) $course['session_count'] ?></p>
                            <p class="small mb-0">Attendance Rate: <?= htmlspecialchars(number_format((float) ($course['attendance_rate'] ?? 0), 1)) ?>%</p>
                        </div>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<?php require_once '../../includes/footer.php'; ?>
</body>
</html>


