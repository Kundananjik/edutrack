<?php
// Preload (auto-locate includes/preload.php)
$__et=__DIR__;
for($__i=0;$__i<6;$__i++){
    $__p=$__et . '/includes/preload.php';
    if (file_exists($__p)) { require_once $__p; break; }
    $__et=dirname($__et);
}
unset($__et,$__i,$__p);

require_once '../../includes/auth_check.php';
require_once '../../includes/config.php';
require_once '../../includes/db.php';

require_login();
require_role(['lecturer']);

// Fetch the user ID from the session
$user_id = $_SESSION['user_id'];
$courses = [];
$error = null;

try {
    // Fetch all courses assigned to this lecturer
    $stmt = $pdo->prepare("
        SELECT c.id, c.name
        FROM courses c
        JOIN lecturer_courses lc ON c.id = lc.course_id
        WHERE lc.lecturer_id = ?
        ORDER BY c.name
    ");
    $stmt->execute([$user_id]);
    $courses = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    error_log("Database error in lecturer/attendance_reports.php: " . $e->getMessage());
    $error = "An error occurred while fetching your courses. Please try again later.";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Attendance Reports - Lecturer</title>

<!-- Bootstrap CSS -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<!-- Font Awesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<!-- Custom EduTrack CSS -->
<link rel="stylesheet" href="css/dashboard.css">
</head>
<body>
<nav class="navbar navbar-expand-lg" style="background-color:#2fa360;">
    <div class="container">
        <a class="navbar-brand text-white" href="../../index.php">
            <img src="../../assets/logo.png" alt="EduTrack Logo" height="40">
        </a>
        <ul class="navbar-nav ms-auto">
            <li class="nav-item"><a class="nav-link text-white" href="../../index.php">Home</a></li>
            <li class="nav-item"><a class="nav-link text-white" href="../../logout.php">Logout</a></li>
        </ul>
    </div>
</nav>

<div class="container py-5 dashboard-container">

    <!-- Back Button -->
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
        <div class="alert alert-info">You have no courses available to generate reports for.</div>

    <?php else: ?>
        <p>Select a course below to view or generate an attendance report:</p>

        <div class="row row-cols-1 row-cols-md-3 g-4">
            <?php foreach ($courses as $course): ?>
                <div class="col">
                    <a href="generate_report.php?course_id=<?= $course['id'] ?>"
                       class="card h-100 text-decoration-none text-dark shadow-sm">

                        <div class="card-body text-center">
                            <i class="fas fa-file-alt fa-3x mb-3 text-success"></i>
                            <h5 class="card-title">
                                <?= htmlspecialchars($course['name']) ?>
                            </h5>
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
