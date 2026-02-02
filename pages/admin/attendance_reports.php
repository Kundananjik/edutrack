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

require_login();
require_role(['admin']);

$courses = [];
$error = null;

try {
    // Fetch all courses
    $stmt = $pdo->query('SELECT id, name FROM courses ORDER BY name');
    $courses = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    error_log('Database error in admin/attendance_reports.php: ' . $e->getMessage());
    $error = 'An error occurred while fetching courses. Please try again later.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Attendance Reports - Admin</title>

<!-- Bootstrap CSS -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<!-- Font Awesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<!-- Custom EduTrack CSS -->
<link rel="stylesheet" href="css/dashboard.css">

</head>
<body class="bg-light">

<?php require_once '../../includes/admin_navbar.php'; ?>

<div class="container py-5 dashboard-container">

    <!-- Back to Dashboard Button -->
    <div class="mb-4">
        <a href="dashboard.php" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left"></i> Back to Dashboard
        </a>
    </div>

    <h1 class="mb-4">Attendance Reports</h1>

    <!-- Error or Info Messages -->
    <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php elseif (empty($courses)): ?>
        <div class="alert alert-info">No courses are currently available to generate reports for.</div>
    <?php else: ?>
        <p>Select a course below to view or generate an attendance report:</p>
        <div class="row row-cols-1 row-cols-md-3 g-4">
            <?php foreach ($courses as $course): ?>
                <div class="col">
                    <a href="generate_report.php?course_id=<?= $course['id'] ?>" class="card h-100 text-decoration-none text-dark shadow-sm">
                        <div class="card-body text-center">
                            <i class="fas fa-file-alt fa-3x mb-3 text-success"></i>
                            <h5 class="card-title"><?= htmlspecialchars($course['name']) ?></h5>
                            <p class="card-text">View / Download Report</p>
                        </div>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<?php require_once '../../includes/footer.php'; ?>
</body>
</html>
