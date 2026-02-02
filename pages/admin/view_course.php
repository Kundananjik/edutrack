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
// pages/admin/view_course.php

require_once '../../includes/auth_check.php';
require_once '../../includes/config.php';
require_once '../../includes/db.php';
require_once '../../includes/functions.php';

require_login();
require_role(['admin']);

// Get the course ID from the URL
$course_id = intval($_GET['id'] ?? 0);
if ($course_id <= 0) {
    $_SESSION['error_message'] = 'Invalid course ID.';
    redirect('manage_courses.php');
}

try {
    // Fetch course details
    $stmt = $pdo->prepare('
        SELECT c.*, p.name AS programme_name, p.code AS programme_code
        FROM courses c
        LEFT JOIN programmes p ON c.programme_id = p.id
        WHERE c.id = ?
    ');
    $stmt->execute([$course_id]);
    $course = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$course) {
        $_SESSION['error_message'] = 'Course not found.';
        redirect('manage_courses.php');
    }

    // Fetch lecturers assigned to this course
    $stmt = $pdo->prepare('
        SELECT u.id, u.name
        FROM lecturer_courses lc
        JOIN users u ON lc.lecturer_id = u.id
        WHERE lc.course_id = ?
    ');
    $stmt->execute([$course_id]);
    $lecturers = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch students enrolled in this course
    $stmt = $pdo->prepare('
        SELECT u.id, u.name, s.student_number
        FROM enrollments e
        JOIN students s ON e.student_id = s.user_id
        JOIN users u ON s.user_id = u.id
        WHERE e.course_id = ?
        ORDER BY u.name
    ');
    $stmt->execute([$course_id]);
    $students = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    error_log('Database error in view_course.php: ' . $e->getMessage());
    $_SESSION['error_message'] = 'Could not retrieve course data. Please try again later.';
    redirect('manage_courses.php');
}
?>
<!DOCTYPE html>
<html lang="en">

</head>
<body>
<?php require_once '../../includes/admin_navbar.php'; ?>

<main class="container py-4">

<!-- Bootstrap CSS -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<!-- Font Awesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<!-- Custom CSS -->
<link rel="stylesheet" href="css/dashboard.css">

</head>
<body>

<main class="container my-5">
    <h1 class="mb-4">View Course: <?= htmlspecialchars($course['name']) ?></h1>

    <?php if (!empty($_SESSION['success_message'])): ?>
        <div class="alert alert-success"><?= htmlspecialchars($_SESSION['success_message']) ?></div>
        <?php unset($_SESSION['success_message']); ?>
    <?php endif; ?>

    <div class="card mb-4 shadow-sm">
        <div class="card-body">
            <h5 class="card-title">Course Details</h5>
            <ul class="list-group list-group-flush">
                <li class="list-group-item"><strong>Course ID:</strong> <?= htmlspecialchars($course['id']) ?></li>
                <li class="list-group-item"><strong>Course Code:</strong> <?= htmlspecialchars($course['course_code']) ?></li>
                <li class="list-group-item"><strong>Programme:</strong> <?= htmlspecialchars($course['programme_name']) ?> (<?= htmlspecialchars($course['programme_code']) ?>)</li>
                <li class="list-group-item"><strong>Lecturer(s):</strong> 
                    <?php
                    if (!empty($lecturers)) {
                        echo implode(', ', array_map(fn ($l) => htmlspecialchars($l['name']), $lecturers));
                    } else {
                        echo 'Not assigned';
                    }
?>
                </li>
                <li class="list-group-item"><strong>Created At:</strong> <?= htmlspecialchars($course['created_at']) ?></li>
                <li class="list-group-item"><strong>Last Updated:</strong> <?= htmlspecialchars($course['updated_at']) ?></li>
            </ul>
        </div>
    </div>

    <div class="card mb-4 shadow-sm">
        <div class="card-body">
            <h5 class="card-title">Enrolled Students</h5>
            <?php if (empty($students)): ?>
                <p>No students are enrolled in this course yet.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-striped table-hover align-middle">
                        <thead class="table-dark">
                            <tr>
                                <th>Student Number</th>
                                <th>Name</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($students as $s): ?>
                                <tr>
                                    <td><?= htmlspecialchars($s['student_number']) ?></td>
                                    <td><?= htmlspecialchars($s['name']) ?></td>
                                    <td>
                                        <a href="view_student.php?id=<?= $s['id'] ?>" class="btn btn-sm btn-info">
                                            <i class="fas fa-eye"></i> View
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="mb-5">
        <a href="edit_course.php?id=<?= urlencode($course['id']) ?>" class="btn btn-primary me-2">
            <i class="fas fa-edit"></i> Edit Course
        </a>
        <a href="manage_courses.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to List
        </a>
    </div>
</main>

<?php require_once '../../includes/footer.php'; ?>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
