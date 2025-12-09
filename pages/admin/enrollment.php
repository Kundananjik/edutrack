<?php
// Preload (auto-locate includes/preload.php)
$__et = __DIR__;
for ($__i = 0; $__i < 6; $__i++) {
    $__p = $__et . '/includes/preload.php';
    if (file_exists($__p)) { require_once $__p; break; }
    $__et = dirname($__et);
}
unset($__et, $__i, $__p);

// pages/admin/enrollment.php
require_once '../../includes/auth_check.php';
require_once '../../includes/config.php';
require_once '../../includes/db.php';
require_once '../../includes/csrf.php';
require_once '../../includes/functions.php';

require_login();
require_role(['admin']);

// Generate CSRF token
$csrf_token = get_csrf_token();

// Handle POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
        $_SESSION['error_message'] = "Invalid CSRF token.";
        redirect("enrollment.php");
    }

    $action = $_POST['action'] ?? '';

    try {
        if ($action === 'add') {
            $student_id   = intval($_POST['student_id'] ?? 0);
            $programme_id = intval($_POST['programme_id'] ?? 0);

            if ($student_id <= 0 || $programme_id <= 0) {
                $_SESSION['error_message'] = "Please select both student and programme.";
            } else {
                $stmt = $pdo->prepare("SELECT user_id FROM students WHERE user_id = ?");
                $stmt->execute([$student_id]);
                if ($stmt->rowCount() === 0) {
                    $_SESSION['error_message'] = "Selected student is not registered.";
                } else {
                    $stmt = $pdo->prepare("SELECT id FROM courses WHERE programme_id = ?");
                    $stmt->execute([$programme_id]);
                    $courses = $stmt->fetchAll(PDO::FETCH_COLUMN);

                    if (empty($courses)) {
                        $_SESSION['error_message'] = "No courses found in the selected programme.";
                    } else {
                        $added = 0;
                        foreach ($courses as $course_id) {
                            $stmtCheck = $pdo->prepare("SELECT COUNT(*) FROM enrollments WHERE student_id = ? AND course_id = ?");
                            $stmtCheck->execute([$student_id, $course_id]);
                            if ($stmtCheck->fetchColumn() == 0) {
                                $stmtInsert = $pdo->prepare("INSERT INTO enrollments (student_id, course_id, enrollment_status) VALUES (?, ?, 'active')");
                                $stmtInsert->execute([$student_id, $course_id]);
                                $added++;
                            }
                        }
                        $_SESSION['success_message'] = "$added enrollment(s) added successfully!";
                    }
                }
            }

        } elseif ($action === 'update') {
            $student_id       = intval($_POST['student_id'] ?? 0);
            $new_programme_id = intval($_POST['new_programme_id'] ?? 0);

            if ($student_id <= 0 || $new_programme_id <= 0) {
                $_SESSION['error_message'] = "Student and new programme are required.";
            } else {
                $stmt = $pdo->prepare("SELECT id FROM courses WHERE programme_id = ?");
                $stmt->execute([$new_programme_id]);
                $new_courses = $stmt->fetchAll(PDO::FETCH_COLUMN);

                if (empty($new_courses)) {
                    $_SESSION['error_message'] = "No courses found in the selected programme.";
                } else {
                    $stmt = $pdo->prepare("DELETE FROM enrollments WHERE student_id = ?");
                    $stmt->execute([$student_id]);

                    foreach ($new_courses as $course_id) {
                        $stmtInsert = $pdo->prepare("INSERT INTO enrollments (student_id, course_id, enrollment_status) VALUES (?, ?, 'active')");
                        $stmtInsert->execute([$student_id, $course_id]);
                    }

                    $_SESSION['success_message'] = "Student successfully moved to the new programme!";
                }
            }

        } elseif ($action === 'delete_programme_enrollment') {
            $student_id   = intval($_POST['student_id'] ?? 0);
            $programme_id = intval($_POST['programme_id'] ?? 0);

            if ($student_id <= 0 || $programme_id <= 0) {
                $_SESSION['error_message'] = "Invalid student or programme ID.";
            } else {
                $stmt = $pdo->prepare("
                    DELETE e FROM enrollments e
                    JOIN courses c ON c.id = e.course_id
                    WHERE e.student_id = ? AND c.programme_id = ?
                ");
                $stmt->execute([$student_id, $programme_id]);
                $_SESSION['success_message'] = "All enrollments in this programme deleted successfully!";
            }
        } else {
            $_SESSION['error_message'] = "Invalid action.";
        }

    } catch (PDOException $e) {
        error_log("Database error in enrollment.php: " . $e->getMessage());
        $_SESSION['error_message'] = "An error occurred. Please try again.";
    }

    redirect("enrollment.php");
}

// Fetch data for display
try {
    $stmt = $pdo->prepare("
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
    ");
    $stmt->execute();
    $enrollments = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stmt = $pdo->prepare("SELECT COUNT(DISTINCT student_id) AS total_students_enrolled FROM enrollments");
    $stmt->execute();
    $total_students_enrolled = $stmt->fetchColumn();

    $stmt = $pdo->prepare("SELECT u.id, u.name FROM users u JOIN students s ON s.user_id = u.id ORDER BY u.name");
    $stmt->execute();
    $students = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stmt = $pdo->prepare("SELECT id, name FROM programmes ORDER BY name");
    $stmt->execute();
    $programmes = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    error_log("Database error fetching data for enrollment.php: " . $e->getMessage());
    $_SESSION['error_message'] = "Could not load data. Please try again.";
    redirect("dashboard.php");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Enrollment Management - Admin</title>

<!-- Bootstrap CSS -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<!-- Font Awesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<!-- Custom CSS -->
<link rel="stylesheet" href="css/dashboard.css">
<link rel="stylesheet" href="css/enrollments.css">
</head>
<body class="bg-light">

<?php require_once '../../includes/admin_navbar.php'; ?>

<main class="container mt-4">
    <div class="dashboard-container bg-white p-4 rounded shadow-sm">

        <div class="d-flex justify-content-between mb-3">
            <a href="dashboard.php" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
        </div>

        <h1 class="mb-4 text-success fw-bold">Enrollment Management</h1>

        <!-- Total Students -->
        <div class="alert alert-info text-center fw-bold">
            Total Students Enrolled: <?= htmlspecialchars($total_students_enrolled) ?>
        </div>

        <!-- Alerts -->
        <div class="alerts mb-4">
            <?php if (!empty($_SESSION['success_message'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?= htmlspecialchars($_SESSION['success_message']) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php unset($_SESSION['success_message']); ?>
            <?php endif; ?>

            <?php if (!empty($_SESSION['error_message'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?= htmlspecialchars($_SESSION['error_message']) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php unset($_SESSION['error_message']); ?>
            <?php endif; ?>
        </div>

        <!-- Add Enrollment Form -->
        <div class="card border-success mb-5">
            <div class="card-header bg-success text-white fw-bold">
                <i class="fas fa-plus-circle"></i> Add New Enrollment
            </div>
            <div class="card-body">
                <form action="enrollment.php" method="POST" class="row g-3">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                    <input type="hidden" name="action" value="add">

                    <div class="col-md-6">
                        <label for="student_id" class="form-label">Student:</label>
                        <select name="student_id" id="student_id" class="form-select" required>
                            <option value="">-- Select Student --</option>
                            <?php foreach ($students as $student): ?>
                                <option value="<?= htmlspecialchars($student['id']) ?>"><?= htmlspecialchars($student['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label for="programme_id" class="form-label">Programme:</label>
                        <select name="programme_id" id="programme_id" class="form-select" required>
                            <option value="">-- Select Programme --</option>
                            <?php foreach ($programmes as $programme): ?>
                                <option value="<?= htmlspecialchars($programme['id']) ?>"><?= htmlspecialchars($programme['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-12 text-end">
                        <button type="submit" class="btn btn-success rounded-pill px-4">
                            <i class="fas fa-plus-circle"></i> Enroll Student
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Enrollment Table -->
        <div class="enrollment-list">
            <h2 class="fw-bold text-success mb-3">All Students Enrolled</h2>
            <?php if (empty($enrollments)): ?>
                <p class="text-muted">No students enrolled yet.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover align-middle text-center">
                        <thead class="table-success">
                            <tr>
                                <th>Student</th>
                                <th>Programme</th>
                                <th>Total Courses</th>
                                <th>Change Programme</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($enrollments as $enrollment): ?>
                            <tr>
                                <td><?= htmlspecialchars($enrollment['student_name']) ?></td>
                                <td><?= htmlspecialchars($enrollment['programme_name']) ?></td>
                                <td class="text-center"><?= htmlspecialchars($enrollment['total_courses']) ?></td>
                                <td>
                                    <form action="enrollment.php" method="POST" class="d-flex gap-2 justify-content-center align-items-center">
                                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                                        <input type="hidden" name="action" value="update">
                                        <input type="hidden" name="student_id" value="<?= htmlspecialchars($enrollment['student_id']) ?>">

                                        <select name="new_programme_id" class="form-select form-select-sm" style="width: 200px;">
                                            <?php foreach ($programmes as $programme): ?>
                                                <option value="<?= htmlspecialchars($programme['id']) ?>" <?= ($enrollment['programme_id'] == $programme['id']) ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($programme['name']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>

                                        <button type="submit" class="btn btn-outline-success btn-sm" title="Change Programme">
                                            <i class="fas fa-exchange-alt"></i>
                                        </button>
                                    </form>
                                </td>
                                <td class="text-center">
                                    <form action="enrollment.php" method="POST" onsubmit="return confirm('Are you sure you want to delete all enrollments in this programme?');">
                                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                                        <input type="hidden" name="action" value="delete_programme_enrollment">
                                        <input type="hidden" name="student_id" value="<?= htmlspecialchars($enrollment['student_id']) ?>">
                                        <input type="hidden" name="programme_id" value="<?= htmlspecialchars($enrollment['programme_id']) ?>">
                                        <button type="submit" class="btn btn-outline-danger btn-sm" title="Delete Enrollment">
                                            <i class="fas fa-trash-alt"></i>
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
    </div>
</main>

<?php require_once '../../includes/footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
