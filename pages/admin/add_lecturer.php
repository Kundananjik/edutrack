<?php
// pages/admin/add_lecturer.php
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

require_once '../../includes/config.php';
require_once '../../includes/db.php';
require_once '../../includes/auth_check.php';
require_once '../../includes/csrf.php';
require_once '../../includes/functions.php';

require_login();
require_role(['admin']);

try {
    $stmt = $pdo->prepare('SELECT id, name, course_code FROM courses ORDER BY name');
    $stmt->execute();
    $courses = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log('Error fetching courses: ' . $e->getMessage());
    $courses = [];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
        $_SESSION['error_message'] = 'Invalid CSRF token.';
    } else {
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = trim($_POST['password'] ?? '');
        $status = trim($_POST['status'] ?? 'active');
        $assignedCourses = $_POST['courses'] ?? [];

        if ($name === '' || $email === '' || $password === '') {
            $_SESSION['error_message'] = 'All fields are required.';
        } else {
            try {
                $stmt = $pdo->prepare('SELECT COUNT(*) FROM users WHERE email = ?');
                $stmt->execute([$email]);
                if ((int) $stmt->fetchColumn() > 0) {
                    $_SESSION['error_message'] = 'Email already exists.';
                } else {
                    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                    $pdo->beginTransaction();

                    $stmt = $pdo->prepare("INSERT INTO users (name, email, role, status, password) VALUES (?, ?, 'lecturer', ?, ?)");
                    $stmt->execute([$name, $email, $status, $hashedPassword]);
                    $lecturerId = $pdo->lastInsertId();

                    if ($assignedCourses !== []) {
                        $stmt = $pdo->prepare('INSERT INTO lecturer_courses (lecturer_id, course_id) VALUES (?, ?)');
                        foreach ($assignedCourses as $courseId) {
                            $stmt->execute([$lecturerId, (int) $courseId]);
                        }
                    }

                    $pdo->commit();
                    $_SESSION['success_message'] = 'Lecturer added successfully!';
                    redirect('manage_lecturers.php');
                }
            } catch (PDOException $e) {
                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }
                error_log('Database error adding lecturer: ' . $e->getMessage());
                $_SESSION['error_message'] = 'Failed to add lecturer.';
            }
        }
    }
}

$formValues = $_SERVER['REQUEST_METHOD'] === 'POST' ? $_POST : [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Lecturer - EduTrack Admin</title>
    <link rel="icon" type="image/png" href="<?= asset_url('assets/favicon.png') ?>">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="css/dashboard.css">
</head>
<body class="bg-light d-flex flex-column min-vh-100">

<?php require_once '../../includes/admin_navbar.php'; ?>

<main class="container py-5 flex-grow-1">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <h1 class="fw-bold mb-2">Add Lecturer</h1>
            <p class="text-muted mb-0">Create a lecturer account and optionally assign course ownership immediately.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="dashboard.php" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Back to Dashboard
            </a>
            <a href="manage_lecturers.php" class="btn btn-outline-success">
                <i class="bi bi-people"></i> Manage Lecturers
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

    <?php if (!empty($_SESSION['error_message'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($_SESSION['error_message']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['error_message']); ?>
    <?php endif; ?>

    <section class="card shadow-sm rounded-4">
        <div class="card-body">
            <form action="add_lecturer.php" method="POST" autocomplete="off" class="row g-3">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(get_csrf_token()) ?>">

                <div class="col-md-6">
                    <label for="name" class="form-label">Full Name</label>
                    <input type="text" name="name" id="name" class="form-control" value="<?= htmlspecialchars($formValues['name'] ?? '') ?>" required>
                </div>

                <div class="col-md-6">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" name="email" id="email" class="form-control" value="<?= htmlspecialchars($formValues['email'] ?? '') ?>" required>
                </div>

                <div class="col-md-6">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" name="password" id="password" class="form-control" required>
                </div>

                <div class="col-md-6">
                    <label for="status" class="form-label">Status</label>
                    <select name="status" id="status" class="form-select">
                        <option value="active" <?= (($formValues['status'] ?? 'active') === 'active') ? 'selected' : '' ?>>Active</option>
                        <option value="suspended" <?= (($formValues['status'] ?? '') === 'suspended') ? 'selected' : '' ?>>Suspended</option>
                    </select>
                </div>

                <?php if ($courses !== []): ?>
                    <div class="col-12">
                        <label for="courses" class="form-label">Assign Courses</label>
                        <select name="courses[]" id="courses" multiple class="form-select">
                            <?php foreach ($courses as $course): ?>
                                <option value="<?= (int) $course['id'] ?>" <?= in_array($course['id'], $formValues['courses'] ?? [], true) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($course['name'] . ' (' . $course['course_code'] . ')') ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="form-text">Hold Ctrl or Cmd to select multiple courses.</div>
                    </div>
                <?php endif; ?>

                <div class="col-12">
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-plus-lg"></i> Add Lecturer
                    </button>
                </div>
            </form>
        </div>
    </section>
</main>

<?php require_once '../../includes/footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
