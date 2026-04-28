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

$studentId = (int) ($_GET['id'] ?? 0);
if ($studentId <= 0) {
    $_SESSION['error_message'] = 'Invalid student ID.';
    redirect('manage_students.php');
}

try {
    $stmt = $pdo->prepare("
        SELECT
            u.id,
            u.name,
            u.email,
            u.status,
            u.created_at,
            s.student_number,
            p.name AS programme_name,
            p.code AS programme_code
        FROM users u
        INNER JOIN students s ON s.user_id = u.id
        LEFT JOIN programmes p ON p.id = s.programme_id
        WHERE u.id = :id
        LIMIT 1
    ");
    $stmt->execute(['id' => $studentId]);
    $student = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$student) {
        $_SESSION['error_message'] = 'Student not found.';
        redirect('manage_students.php');
    }

    $stmt = $pdo->prepare("
        SELECT c.id, c.course_code, c.name, p.name AS programme_name
        FROM enrollments e
        INNER JOIN courses c ON c.id = e.course_id
        LEFT JOIN programmes p ON p.id = c.programme_id
        WHERE e.student_id = :student_id
        ORDER BY c.name ASC
    ");
    $stmt->execute(['student_id' => $studentId]);
    $courses = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stmt = $pdo->prepare("
        SELECT
            COUNT(ar.id) AS attendance_marks,
            COUNT(DISTINCT ar.session_id) AS attended_sessions
        FROM attendance_records ar
        WHERE ar.student_id = :student_id
    ");
    $stmt->execute(['student_id' => $studentId]);
    $attendanceStats = $stmt->fetch(PDO::FETCH_ASSOC) ?: ['attendance_marks' => 0, 'attended_sessions' => 0];
} catch (PDOException $e) {
    error_log('Database error in view_student.php: ' . $e->getMessage());
    $_SESSION['error_message'] = 'Could not retrieve student details. Please try again later.';
    redirect('manage_students.php');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">

    <link rel="icon" type="image/png" href="<?= asset_url('assets/favicon.png') ?>">
    <title>View Student - EduTrack Admin</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="css/dashboard.css">
</head>
<body class="bg-light d-flex flex-column min-vh-100">

<?php require_once '../../includes/admin_navbar.php'; ?>

<main class="container py-5 flex-grow-1">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <h1 class="fw-bold mb-2"><?= htmlspecialchars($student['name']) ?></h1>
            <p class="text-muted mb-0">Student profile, programme, and enrolled courses.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="edit_student.php?id=<?= urlencode((string) $student['id']) ?>" class="btn btn-success">
                <i class="bi bi-pencil-square"></i> Edit Student
            </a>
            <a href="manage_students.php" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Back to Students
            </a>
        </div>
    </div>

    <section class="mb-5">
        <div class="row g-4">
            <div class="col-md-6 col-xl-3">
                <div class="dashboard-card text-center d-block">
                    <i class="bi bi-person-vcard fs-2 mb-2"></i>
                    <h3>Student Number</h3>
                    <p class="fs-6 mb-0"><?= htmlspecialchars($student['student_number']) ?></p>
                </div>
            </div>
            <div class="col-md-6 col-xl-3">
                <div class="dashboard-card text-center d-block">
                    <i class="bi bi-mortarboard fs-2 mb-2"></i>
                    <h3>Programme</h3>
                    <p class="fs-6 mb-0"><?= htmlspecialchars($student['programme_code'] ?: $student['programme_name'] ?: 'Not assigned') ?></p>
                </div>
            </div>
            <div class="col-md-6 col-xl-3">
                <div class="dashboard-card text-center d-block">
                    <i class="bi bi-envelope fs-2 mb-2"></i>
                    <h3>Email</h3>
                    <p class="fs-6 mb-0"><?= htmlspecialchars($student['email']) ?></p>
                </div>
            </div>
            <div class="col-md-6 col-xl-3">
                <div class="dashboard-card text-center d-block">
                    <i class="bi bi-check2-square fs-2 mb-2"></i>
                    <h3>Attendance Marks</h3>
                    <p><?= (int) ($attendanceStats['attendance_marks'] ?? 0) ?></p>
                </div>
            </div>
        </div>
    </section>

    <section class="card shadow-sm rounded-4 mb-4">
        <div class="card-body">
            <h2 class="h4 mb-3">Student Details</h2>
            <div class="row g-3">
                <div class="col-md-6">
                    <strong>Status:</strong> <?= htmlspecialchars(ucfirst($student['status'])) ?>
                </div>
                <div class="col-md-6">
                    <strong>Joined:</strong> <?= htmlspecialchars(date('M j, Y', strtotime($student['created_at']))) ?>
                </div>
                <div class="col-md-12">
                    <strong>Programme Name:</strong> <?= htmlspecialchars($student['programme_name'] ?: 'Not assigned') ?>
                </div>
            </div>
        </div>
    </section>

    <section class="card shadow-sm rounded-4">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h2 class="h4 mb-0">Enrolled Courses</h2>
                <small class="text-muted">Attended sessions: <?= (int) ($attendanceStats['attended_sessions'] ?? 0) ?></small>
            </div>

            <?php if ($courses === []): ?>
                <p class="text-muted mb-0">This student has no course enrollments yet.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-striped table-hover align-middle mb-0">
                        <thead class="table-success">
                            <tr>
                                <th scope="col">Course Code</th>
                                <th scope="col">Course</th>
                                <th scope="col">Programme</th>
                                <th scope="col" class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($courses as $course): ?>
                                <tr>
                                    <td><?= htmlspecialchars($course['course_code']) ?></td>
                                    <td><?= htmlspecialchars($course['name']) ?></td>
                                    <td><?= htmlspecialchars($course['programme_name'] ?: 'Unassigned') ?></td>
                                    <td class="text-center">
                                        <a href="view_course.php?id=<?= urlencode((string) $course['id']) ?>" class="btn btn-sm btn-outline-success">
                                            <i class="bi bi-eye"></i> View Course
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
