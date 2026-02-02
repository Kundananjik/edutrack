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
// pages/lecturer/my_courses.php
require_once '../../includes/auth_check.php';
require_once '../../includes/config.php';
require_once '../../includes/db.php';
require_once '../../includes/functions.php';

require_login();
require_role(['lecturer']);

$user_id = $_SESSION['user_id'];
$courses = [];
$warnings = [];
$error = '';
$active_sessions_by_course = [];

try {
    // Fetch all active sessions for the lecturer
    $stmt_active_sessions = $pdo->prepare('SELECT course_id FROM attendance_sessions WHERE lecturer_id = ? AND is_active = 1');
    $stmt_active_sessions->execute([$user_id]);
    $active_course_ids = $stmt_active_sessions->fetchAll(PDO::FETCH_COLUMN);
    $active_sessions_by_course = array_flip($active_course_ids); // quick lookup

    // Fetch all course IDs assigned to the lecturer
    $stmt = $pdo->prepare('SELECT course_id FROM lecturer_courses WHERE lecturer_id = ?');
    $stmt->execute([$user_id]);
    $course_links = $stmt->fetchAll(PDO::FETCH_COLUMN);

    if (!empty($course_links)) {
        $placeholders = implode(',', array_fill(0, count($course_links), '?'));
        $stmt_courses = $pdo->prepare("
            SELECT id, name, course_code, description, class_schedule
            FROM courses 
            WHERE id IN ($placeholders)
            ORDER BY name ASC
        ");
        $stmt_courses->execute($course_links);
        $courses = $stmt_courses->fetchAll(PDO::FETCH_ASSOC);

        $fetched_ids = array_column($courses, 'id');
        foreach ($course_links as $cid) {
            if (!in_array($cid, $fetched_ids)) {
                $warnings[] = "Warning: Course with ID $cid is linked to you but missing from the courses table.";
            }
        }
    }
} catch (Exception $e) {
    error_log('Database error in my_courses.php: ' . $e->getMessage());
    $error = 'An error occurred while fetching your courses. Please try again later.';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>My Courses</title>
<!-- Bootstrap CSS -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="stylesheet" href="css/dashboard.css">
</head>
<body>

<!-- NAVBAR -->
<nav class="navbar navbar-expand-lg navbar-dark bg-success mb-4">
    <div class="container">
        <a class="navbar-brand" href="../../index.php">
            <img src="../../assets/logo.png" alt="EduTrack Logo" height="30">
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item"><a class="nav-link" href="dashboard.php">Dashboard</a></li>
                <li class="nav-item"><a class="nav-link" href="../../logout.php">Logout</a></li>
            </ul>
        </div>
    </div>
</nav>

<div class="container dashboard-container">
    <a href="dashboard.php" class="btn btn-secondary mb-3"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>
    <h1 class="mb-4">My Courses</h1>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <?php if (!empty($warnings)): ?>
        <?php foreach ($warnings as $w): ?>
            <div class="alert alert-warning"><?= htmlspecialchars($w); ?></div>
        <?php endforeach; ?>
    <?php endif; ?>

    <?php if (empty($courses)): ?>
        <div class="alert alert-info">You are not currently assigned to any courses.</div>
    <?php else: ?>
        <div class="accordion" id="coursesAccordion">
            <?php foreach ($courses as $index => $course):
                $has_active_session = isset($active_sessions_by_course[$course['id']]);
                ?>
                <div class="accordion-item">
                    <h2 class="accordion-header" id="heading<?= $index; ?>">
                        <button class="accordion-button collapsed d-flex justify-content-between align-items-center" type="button" data-bs-toggle="collapse" data-bs-target="#collapse<?= $index; ?>" aria-expanded="false" aria-controls="collapse<?= $index; ?>">
                            <span><?= htmlspecialchars($course['name']); ?> (<?= htmlspecialchars($course['course_code']); ?>)</span>
                            <?php if ($has_active_session): ?>
                                <span class="badge bg-warning text-dark">Active Session</span>
                            <?php endif; ?>
                        </button>
                    </h2>
                    <div id="collapse<?= $index; ?>" class="accordion-collapse collapse" aria-labelledby="heading<?= $index; ?>" data-bs-parent="#coursesAccordion">
                        <div class="accordion-body">
                            <p><strong>Description:</strong> <?= htmlspecialchars($course['description']); ?></p>
                            <p><strong>Class Schedule:</strong> <?= htmlspecialchars($course['class_schedule']); ?></p>
                            <div class="d-flex">
                                <a href="view_course.php?id=<?= $course['id']; ?>" class="btn btn-primary me-2">
                                    <i class="fas fa-eye"></i> View Course
                                </a>
                                <form action="start_session.php" method="post" class="d-inline">
                                    <input type="hidden" name="course_id" value="<?= htmlspecialchars($course['id']); ?>">
                                    <button type="submit" class="btn btn-success" <?= $has_active_session ? 'disabled' : '' ?>>
                                        <i class="fas fa-play-circle"></i> Start Session
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
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
