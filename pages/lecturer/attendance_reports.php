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
require_once '../../includes/lecturer_report_helper.php';

require_login();
require_role(['lecturer']);

// Fetch the user ID from the session
$user_id = $_SESSION['user_id'];
$courses = [];
$error = null;

try {
    $stmt = $pdo->prepare('
        SELECT
            c.id,
            c.name,
            c.course_code,
            COUNT(DISTINCT e.student_id) AS student_count,
            COUNT(DISTINCT s.id) AS session_count
        FROM courses c
        JOIN lecturer_courses lc ON c.id = lc.course_id
        LEFT JOIN enrollments e ON e.course_id = c.id
        LEFT JOIN attendance_sessions s ON s.course_id = c.id
        WHERE lc.lecturer_id = ?
        GROUP BY c.id, c.name, c.course_code
        ORDER BY c.name
    ');
    $stmt->execute([$user_id]);
    $courses = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    error_log('Database error in lecturer/attendance_reports.php: ' . $e->getMessage());
    $error = 'An error occurred while fetching your courses. Please try again later.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">

    <link rel="icon" type="image/png" href="<?= asset_url('assets/favicon.png') ?>">
<title>Attendance Reports - Lecturer</title>

<!-- Bootstrap CSS -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

<!-- Font Awesome -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

<!-- Custom EduTrack CSS -->
<link rel="stylesheet" href="css/dashboard.css">
</head>
<body>
<?php require_once '../../includes/lecturer_navbar.php'; ?>

<div class="container py-5 dashboard-container report-hub">

    <!-- Back Button -->
    <div class="mb-4">
        <a href="dashboard.php" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Back to Dashboard
        </a>
    </div>

    <h1 class="mb-4">Attendance Reports</h1>

    <!-- Error or Info Messages -->
    <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>

    <?php elseif (empty($courses)): ?>
        <div class="alert alert-info">You have no courses available to generate reports for.</div>

    <?php else: ?>
        <div class="report-hero mb-4">
            <div>
                <p class="text-uppercase small fw-semibold mb-2">Lecturer Reports</p>
                <h2 class="mb-2">Attendance reporting hub</h2>
                <p class="mb-0">Open a course report to review session-by-session attendance, inspect percentages, or export the report as PDF, CSV, or print-ready output.</p>
            </div>
            <div class="report-hero-chip">
                <span><?= count($courses) ?></span>
                <small>Courses ready</small>
            </div>
        </div>

        <div class="row row-cols-1 row-cols-md-2 row-cols-xl-3 g-4">
            <?php foreach ($courses as $course): ?>
                <div class="col">
                    <a href="generate_report.php?course_id=<?= $course['id'] ?>"
                       class="card h-100 text-decoration-none text-dark shadow-sm report-course-card">

                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start gap-3 mb-3">
                                <div>
                                    <p class="report-course-code mb-2"><?= htmlspecialchars($course['course_code']) ?></p>
                                    <h5 class="card-title mb-1"><?= htmlspecialchars($course['name']) ?></h5>
                                </div>
                                <i class="bi bi-bar-chart-line-fill fs-3 text-success"></i>
                            </div>

                            <div class="report-course-metrics">
                                <div>
                                    <strong><?= (int) $course['student_count'] ?></strong>
                                    <span>Students</span>
                                </div>
                                <div>
                                    <strong><?= (int) $course['session_count'] ?></strong>
                                    <span>Sessions</span>
                                </div>
                            </div>

                            <p class="card-text mt-3 mb-0">Open the report workspace for summaries, filters, and exports.</p>
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


