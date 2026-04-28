<?php
// ============================================
// ADMIN - ENROLLMENT MANAGEMENT
// ============================================

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
require_once '../../includes/csrf.php';
require_once '../../includes/functions.php';

require_login();
require_role(['admin']);

$csrfToken = get_csrf_token();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
        $_SESSION['error_message'] = 'Invalid CSRF token.';
        redirect('enrollment.php');
    }

    $action = $_POST['action'] ?? '';

    try {
        if ($action === 'add') {
            $studentId = (int) ($_POST['student_id'] ?? 0);
            $programmeId = (int) ($_POST['programme_id'] ?? 0);

            if ($studentId <= 0 || $programmeId <= 0) {
                $_SESSION['error_message'] = 'Please select both student and programme.';
            } else {
                $stmt = $pdo->prepare('SELECT user_id FROM students WHERE user_id = ?');
                $stmt->execute([$studentId]);
                if ($stmt->rowCount() === 0) {
                    $_SESSION['error_message'] = 'Selected student is not registered.';
                } else {
                    $stmt = $pdo->prepare('SELECT id FROM courses WHERE programme_id = ?');
                    $stmt->execute([$programmeId]);
                    $courses = $stmt->fetchAll(PDO::FETCH_COLUMN);

                    if ($courses === []) {
                        $_SESSION['error_message'] = 'No courses found in the selected programme.';
                    } else {
                        $added = 0;
                        foreach ($courses as $courseId) {
                            $stmtCheck = $pdo->prepare('SELECT COUNT(*) FROM enrollments WHERE student_id = ? AND course_id = ?');
                            $stmtCheck->execute([$studentId, $courseId]);
                            if ((int) $stmtCheck->fetchColumn() === 0) {
                                $stmtInsert = $pdo->prepare("INSERT INTO enrollments (student_id, course_id, enrollment_status) VALUES (?, ?, 'active')");
                                $stmtInsert->execute([$studentId, $courseId]);
                                $added++;
                            }
                        }
                        $_SESSION['success_message'] = $added . ' enrollment(s) added successfully!';
                    }
                }
            }
        } elseif ($action === 'update') {
            $studentId = (int) ($_POST['student_id'] ?? 0);
            $newProgrammeId = (int) ($_POST['new_programme_id'] ?? 0);

            if ($studentId <= 0 || $newProgrammeId <= 0) {
                $_SESSION['error_message'] = 'Student and new programme are required.';
            } else {
                $stmt = $pdo->prepare('SELECT id FROM courses WHERE programme_id = ?');
                $stmt->execute([$newProgrammeId]);
                $newCourses = $stmt->fetchAll(PDO::FETCH_COLUMN);

                if ($newCourses === []) {
                    $_SESSION['error_message'] = 'No courses found in the selected programme.';
                } else {
                    $stmt = $pdo->prepare('DELETE FROM enrollments WHERE student_id = ?');
                    $stmt->execute([$studentId]);

                    foreach ($newCourses as $courseId) {
                        $stmtInsert = $pdo->prepare("INSERT INTO enrollments (student_id, course_id, enrollment_status) VALUES (?, ?, 'active')");
                        $stmtInsert->execute([$studentId, $courseId]);
                    }

                    $_SESSION['success_message'] = 'Student successfully moved to the new programme!';
                }
            }
        } elseif ($action === 'delete_programme_enrollment') {
            $studentId = (int) ($_POST['student_id'] ?? 0);
            $programmeId = (int) ($_POST['programme_id'] ?? 0);

            if ($studentId <= 0 || $programmeId <= 0) {
                $_SESSION['error_message'] = 'Invalid student or programme ID.';
            } else {
                $stmt = $pdo->prepare('
                    DELETE e FROM enrollments e
                    JOIN courses c ON c.id = e.course_id
                    WHERE e.student_id = ? AND c.programme_id = ?
                ');
                $stmt->execute([$studentId, $programmeId]);
                $_SESSION['success_message'] = 'All enrollments in this programme deleted successfully!';
            }
        } else {
            $_SESSION['error_message'] = 'Invalid action.';
        }
    } catch (PDOException $e) {
        error_log('Database error in enrollment.php: ' . $e->getMessage());
        $_SESSION['error_message'] = 'An error occurred. Please try again.';
    }

    redirect('enrollment.php');
}

try {
    $stmt = $pdo->prepare('
        SELECT
            u.id AS student_id,
            u.name AS student_name,
            p.id AS programme_id,
            p.name AS programme_name,
            COUNT(DISTINCT c.id) AS total_courses,
            MAX(e.enrollment_status) AS enrollment_status
        FROM enrollments e
        JOIN users u ON u.id = e.student_id
        JOIN courses c ON c.id = e.course_id
        JOIN programmes p ON p.id = c.programme_id
        GROUP BY u.id, p.id
        ORDER BY u.name, p.name
    ');
    $stmt->execute();
    $enrollments = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stmt = $pdo->prepare('SELECT COUNT(DISTINCT student_id) AS total_students_enrolled FROM enrollments');
    $stmt->execute();
    $totalStudentsEnrolled = $stmt->fetchColumn();

    $stmt = $pdo->prepare('SELECT u.id, u.name FROM users u JOIN students s ON s.user_id = u.id ORDER BY u.name');
    $stmt->execute();
    $students = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stmt = $pdo->prepare('SELECT id, name FROM programmes ORDER BY name');
    $stmt->execute();
    $programmes = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log('Database error fetching data for enrollment.php: ' . $e->getMessage());
    $_SESSION['error_message'] = 'Could not load data. Please try again.';
    redirect('dashboard.php');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Enrollment Management - EduTrack Admin</title>
    <link rel="icon" type="image/png" href="<?= asset_url('assets/favicon.png') ?>">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="css/dashboard.css">
    <link rel="stylesheet" href="css/enrollments.css">
</head>
<body class="bg-light d-flex flex-column min-vh-100">

<?php require_once '../../includes/admin_navbar.php'; ?>

<main class="container py-5 flex-grow-1">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <h1 class="fw-bold mb-2">Enrollment Management</h1>
            <p class="text-muted mb-0">Assign students to programme course sets and maintain enrollment coverage.</p>
        </div>
        <a href="dashboard.php" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Back to Dashboard
        </a>
    </div>

    <div class="alert alert-info text-center fw-bold">
        Total Students Enrolled: <?= htmlspecialchars((string) $totalStudentsEnrolled) ?>
    </div>

    <?php if (!empty($_SESSION['success_message'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($_SESSION['success_message']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['success_message']); ?>
    <?php endif; ?>

    <?php if (!empty($_SESSION['error_message'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($_SESSION['error_message']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['error_message']); ?>
    <?php endif; ?>

    <section class="card shadow-sm rounded-4 mb-4">
        <div class="card-header bg-success text-white fw-bold">
            <i class="bi bi-plus-circle"></i> Add New Enrollment
        </div>
        <div class="card-body">
            <form action="enrollment.php" method="POST" class="row g-3">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                <input type="hidden" name="action" value="add">

                <div class="col-md-6">
                    <label for="student_id" class="form-label">Student</label>
                    <select name="student_id" id="student_id" class="form-select" required>
                        <option value="">Select Student</option>
                        <?php foreach ($students as $student): ?>
                            <option value="<?= htmlspecialchars($student['id']) ?>"><?= htmlspecialchars($student['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-6">
                    <label for="programme_id" class="form-label">Programme</label>
                    <select name="programme_id" id="programme_id" class="form-select" required>
                        <option value="">Select Programme</option>
                        <?php foreach ($programmes as $programme): ?>
                            <option value="<?= htmlspecialchars($programme['id']) ?>"><?= htmlspecialchars($programme['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-12">
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-plus-circle"></i> Enroll Student
                    </button>
                </div>
            </form>
        </div>
    </section>

    <section class="card shadow-sm rounded-4">
        <div class="card-body">
            <h2 class="h4 fw-bold mb-3">All Students Enrolled</h2>
            <?php if ($enrollments === []): ?>
                <p class="text-muted mb-0">No students enrolled yet.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-striped table-hover align-middle text-center mb-0">
                        <thead class="table-success">
                            <tr>
                                <th scope="col">Student</th>
                                <th scope="col">Programme</th>
                                <th scope="col">Total Courses</th>
                                <th scope="col">Change Programme</th>
                                <th scope="col">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($enrollments as $enrollment): ?>
                                <tr>
                                    <td><?= htmlspecialchars($enrollment['student_name']) ?></td>
                                    <td><?= htmlspecialchars($enrollment['programme_name']) ?></td>
                                    <td><?= htmlspecialchars($enrollment['total_courses']) ?></td>
                                    <td>
                                        <form action="enrollment.php" method="POST" class="d-flex gap-2 justify-content-center align-items-center">
                                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                                            <input type="hidden" name="action" value="update">
                                            <input type="hidden" name="student_id" value="<?= htmlspecialchars($enrollment['student_id']) ?>">

                                            <select name="new_programme_id" class="form-select form-select-sm" style="width: 200px;" aria-label="Select new programme">
                                                <?php foreach ($programmes as $programme): ?>
                                                    <option value="<?= htmlspecialchars($programme['id']) ?>" <?= ($enrollment['programme_id'] == $programme['id']) ? 'selected' : '' ?>>
                                                        <?= htmlspecialchars($programme['name']) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>

                                            <button type="submit" class="btn btn-outline-success btn-sm" title="Change Programme" aria-label="Change programme">
                                                <i class="bi bi-arrow-left-right"></i>
                                            </button>
                                        </form>
                                    </td>
                                    <td>
                                        <form action="enrollment.php" method="POST" class="delete-enrollment-form">
                                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                                            <input type="hidden" name="action" value="delete_programme_enrollment">
                                            <input type="hidden" name="student_id" value="<?= htmlspecialchars($enrollment['student_id']) ?>">
                                            <input type="hidden" name="programme_id" value="<?= htmlspecialchars($enrollment['programme_id']) ?>">
                                            <button type="submit" class="btn btn-outline-danger btn-sm" title="Delete Enrollment" aria-label="Delete enrollment">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
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
<script <?= et_csp_attr('script') ?>>
document.querySelectorAll('.delete-enrollment-form').forEach(form => {
    form.addEventListener('submit', function (e) {
        if (!confirm('Are you sure you want to delete all enrollments in this programme?')) {
            e.preventDefault();
        }
    });
});
</script>
</body>
</html>
