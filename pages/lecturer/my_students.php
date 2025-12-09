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

$user_id = $_SESSION['user_id'];
$courses = [];

try {
    // Fetch courses taught by the lecturer
    $stmt = $pdo->prepare("SELECT c.id, c.name, c.course_code
                           FROM courses c
                           JOIN lecturer_courses lc ON c.id = lc.course_id
                           WHERE lc.lecturer_id = ?
                           ORDER BY c.name");
    $stmt->execute([$user_id]);
    $courses_list = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // For each course, fetch enrolled students
    foreach ($courses_list as $course) {
        $stmt = $pdo->prepare("SELECT u.name, s.student_number, u.email, p.name AS programme
                               FROM enrollments e
                               JOIN students s ON e.student_id = s.user_id
                               JOIN users u ON s.user_id = u.id
                               JOIN programmes p ON s.programme_id = p.id
                               WHERE e.course_id = ?
                               ORDER BY u.name");
        $stmt->execute([$course['id']]);
        $students = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $courses[$course['id']] = [
            'info' => $course,
            'students' => $students
        ];
    }

} catch (Exception $e) {
    error_log("Database error in my_students.php: " . $e->getMessage());
    $error = "An error occurred while fetching your students. Please try again later.";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>My Students</title>
<!-- Bootstrap CSS -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="stylesheet" href="css/dashboard.css">
</head>
<body>

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
    <h1 class="mb-4">My Students</h1>

    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php elseif (empty($courses)): ?>
        <div class="alert alert-info">You are not assigned to any courses yet.</div>
    <?php else: ?>
        <div class="accordion" id="coursesAccordion">
            <?php foreach ($courses as $course_id => $course): ?>
                <div class="accordion-item">
                    <h2 class="accordion-header" id="heading-<?= $course_id ?>">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-<?= $course_id ?>" aria-expanded="false" aria-controls="collapse-<?= $course_id ?>">
                            <?= htmlspecialchars($course['info']['name'] . " (" . $course['info']['course_code'] . ")"); ?>
                        </button>
                    </h2>
                    <div id="collapse-<?= $course_id ?>" class="accordion-collapse collapse" aria-labelledby="heading-<?= $course_id ?>" data-bs-parent="#coursesAccordion">
                        <div class="accordion-body">
                            <?php if (empty($course['students'])): ?>
                                <div class="alert alert-warning">No students are currently enrolled in this course.</div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-bordered table-striped align-middle">
                                        <thead class="table-success">
                                            <tr>
                                                <th>Student Name</th>
                                                <th>Student Number</th>
                                                <th>Email</th>
                                                <th>Programme</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($course['students'] as $student): ?>
                                                <tr>
                                                    <td><?= htmlspecialchars($student['name']); ?></td>
                                                    <td><?= htmlspecialchars($student['student_number']); ?></td>
                                                    <td><?= htmlspecialchars($student['email']); ?></td>
                                                    <td><?= htmlspecialchars($student['programme']); ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<?php require_once '../../includes/footer.php'; ?>
</body>
</html>
