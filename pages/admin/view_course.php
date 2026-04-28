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

require_login();
require_role(['admin']);

$courseId = (int) ($_GET['id'] ?? 0);
if ($courseId <= 0) {
    $_SESSION['error_message'] = 'Invalid course ID.';
    redirect('manage_courses.php');
}

try {
    $stmt = $pdo->prepare('
        SELECT c.*, p.name AS programme_name, p.code AS programme_code
        FROM courses c
        LEFT JOIN programmes p ON c.programme_id = p.id
        WHERE c.id = ?
    ');
    $stmt->execute([$courseId]);
    $course = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$course) {
        $_SESSION['error_message'] = 'Course not found.';
        redirect('manage_courses.php');
    }

    $stmt = $pdo->prepare('
        SELECT u.id, u.name
        FROM lecturer_courses lc
        JOIN users u ON lc.lecturer_id = u.id
        WHERE lc.course_id = ?
        ORDER BY u.name
    ');
    $stmt->execute([$courseId]);
    $lecturers = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stmt = $pdo->prepare('
        SELECT u.id, u.name, s.student_number
        FROM enrollments e
        JOIN students s ON e.student_id = s.user_id
        JOIN users u ON s.user_id = u.id
        WHERE e.course_id = ?
        ORDER BY u.name
    ');
    $stmt->execute([$courseId]);
    $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log('Database error in view_course.php: ' . $e->getMessage());
    $_SESSION['error_message'] = 'Could not retrieve course data. Please try again later.';
    redirect('manage_courses.php');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/png" href="<?= asset_url('assets/favicon.png') ?>">
    <title>View Course - EduTrack Admin</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="css/dashboard.css">
</head>
<body class="bg-light d-flex flex-column min-vh-100">

<?php require_once '../../includes/admin_navbar.php'; ?>

<main class="container py-5 flex-grow-1">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <h1 class="fw-bold mb-2"><?= htmlspecialchars($course['name']) ?></h1>
            <p class="text-muted mb-0">Course details, lecturer assignments, and enrolled students.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="edit_course.php?id=<?= urlencode((string) $course['id']) ?>" class="btn btn-success">
                <i class="bi bi-pencil-square"></i> Edit Course
            </a>
            <a href="manage_courses.php" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Back to Courses
            </a>
        </div>
    </div>

    <?php if (!empty($_SESSION['success_message'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($_SESSION['success_message']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['success_message']); ?>
    <?php endif; ?>

    <section class="card shadow-sm rounded-4 mb-4">
        <div class="card-body">
            <h2 class="h4 mb-3">Course Details</h2>
            <div class="row g-3">
                <div class="col-md-6"><strong>Course ID:</strong> <?= htmlspecialchars($course['id']) ?></div>
                <div class="col-md-6"><strong>Course Code:</strong> <?= htmlspecialchars($course['course_code']) ?></div>
                <div class="col-md-6"><strong>Programme:</strong> <?= htmlspecialchars($course['programme_name'] ?: 'Not assigned') ?></div>
                <div class="col-md-6"><strong>Programme Code:</strong> <?= htmlspecialchars($course['programme_code'] ?: 'N/A') ?></div>
                <div class="col-md-12">
                    <strong>Lecturer(s):</strong>
                    <?= $lecturers === [] ? 'Not assigned' : htmlspecialchars(implode(', ', array_column($lecturers, 'name'))) ?>
                </div>
                <div class="col-md-6"><strong>Created At:</strong> <?= htmlspecialchars($course['created_at']) ?></div>
                <div class="col-md-6"><strong>Last Updated:</strong> <?= htmlspecialchars($course['updated_at']) ?></div>
            </div>
        </div>
    </section>

    <section class="card shadow-sm rounded-4">
        <div class="card-body">
            <h2 class="h4 mb-3">Enrolled Students</h2>
            <?php if ($students === []): ?>
                <p class="text-muted mb-0">No students are enrolled in this course yet.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-striped table-hover align-middle mb-0">
                        <thead class="table-success">
                            <tr>
                                <th scope="col">Student Number</th>
                                <th scope="col">Name</th>
                                <th scope="col" class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($students as $student): ?>
                                <tr>
                                    <td><?= htmlspecialchars($student['student_number']) ?></td>
                                    <td><?= htmlspecialchars($student['name']) ?></td>
                                    <td class="text-center">
                                        <a href="view_student.php?id=<?= urlencode((string) $student['id']) ?>" class="btn btn-sm btn-outline-success">
                                            <i class="bi bi-eye"></i> View Student
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </section>
</main>

<?php require_once '../../includes/footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
