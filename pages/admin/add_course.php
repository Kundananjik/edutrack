<?php
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

$lecturers = [];
$programmes = [];
$debugMessages = [];

try {
    $stmt = $pdo->prepare("SELECT id, name FROM users WHERE role = 'lecturer' ORDER BY name");
    if ($stmt->execute()) {
        $lecturers = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if ($lecturers === []) {
            $debugMessages[] = 'No lecturers found in the database.';
        }
    } else {
        $debugMessages[] = 'Failed to execute query for lecturers.';
    }

    $stmt = $pdo->prepare('SELECT id, name FROM programmes ORDER BY name');
    if ($stmt->execute()) {
        $programmes = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if ($programmes === []) {
            $debugMessages[] = 'No programmes found in the database.';
        }
    } else {
        $debugMessages[] = 'Failed to execute query for programmes.';
    }
} catch (PDOException $e) {
    $debugMessages[] = 'Database error: ' . $e->getMessage();
    error_log('Database error fetching lecturers or programmes: ' . $e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
        $_SESSION['error_message'] = 'Invalid CSRF token.';
        redirect('add_course.php');
    }

    $name = trim($_POST['name'] ?? '');
    $courseCode = trim($_POST['course_code'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $credits = (int) ($_POST['credits'] ?? 0);
    $status = trim($_POST['status'] ?? 'active');
    $schedule = trim($_POST['class_schedule'] ?? '');
    $programmeId = (int) ($_POST['programme_id'] ?? 0);
    $lecturerId = (int) ($_POST['lecturer_id'] ?? 0);

    if ($name === '' || $courseCode === '' || $credits <= 0 || $status === '' || $programmeId <= 0 || $lecturerId <= 0) {
        $_SESSION['error_message'] = 'Please fill in all required fields.';
    } else {
        try {
            $stmt = $pdo->prepare('
                INSERT INTO courses (name, course_code, description, credits, status, class_schedule, programme_id)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ');
            $stmt->execute([$name, $courseCode, $description, $credits, $status, $schedule, $programmeId]);

            $courseId = $pdo->lastInsertId();

            $stmt = $pdo->prepare('INSERT INTO lecturer_courses (lecturer_id, course_id) VALUES (?, ?)');
            $stmt->execute([$lecturerId, $courseId]);

            $_SESSION['success_message'] = 'Course added successfully!';
            redirect('manage_courses.php');
        } catch (PDOException $e) {
            if ($e->getCode() === '23000') {
                $_SESSION['error_message'] = 'A course with this code already exists.';
            } else {
                error_log('Database error in add_course.php: ' . $e->getMessage());
                $_SESSION['error_message'] = 'Failed to add course. Please try again.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Course - EduTrack Admin</title>
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
            <h1 class="fw-bold mb-2">Add Course</h1>
            <p class="text-muted mb-0">Create a course and assign a lecturer and programme immediately.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="dashboard.php" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Back to Dashboard
            </a>
            <a href="manage_courses.php" class="btn btn-outline-success">
                <i class="bi bi-journal-bookmark"></i> Manage Courses
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

    <?php if ($debugMessages !== []): ?>
        <div class="alert alert-warning">
            <strong>Setup notes:</strong>
            <ul class="mb-0">
                <?php foreach ($debugMessages as $message): ?>
                    <li><?= htmlspecialchars($message) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <section class="card shadow-sm rounded-4">
        <div class="card-body">
            <form action="add_course.php" method="POST" class="row g-3">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(get_csrf_token()) ?>">

                <div class="col-md-6">
                    <label for="name" class="form-label">Course Name</label>
                    <input type="text" name="name" id="name" class="form-control" required value="<?= htmlspecialchars($_POST['name'] ?? '') ?>">
                </div>

                <div class="col-md-6">
                    <label for="course_code" class="form-label">Course Code</label>
                    <input type="text" name="course_code" id="course_code" class="form-control" required value="<?= htmlspecialchars($_POST['course_code'] ?? '') ?>">
                </div>

                <div class="col-12">
                    <label for="description" class="form-label">Description</label>
                    <textarea name="description" id="description" class="form-control" rows="3"><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
                </div>

                <div class="col-md-4">
                    <label for="credits" class="form-label">Credits</label>
                    <input type="number" name="credits" id="credits" min="1" class="form-control" required value="<?= htmlspecialchars($_POST['credits'] ?? '') ?>">
                </div>

                <div class="col-md-4">
                    <label for="status" class="form-label">Status</label>
                    <select name="status" id="status" class="form-select" required>
                        <option value="active" <?= (($_POST['status'] ?? 'active') === 'active') ? 'selected' : '' ?>>Active</option>
                        <option value="inactive" <?= (($_POST['status'] ?? '') === 'inactive') ? 'selected' : '' ?>>Inactive</option>
                        <option value="archived" <?= (($_POST['status'] ?? '') === 'archived') ? 'selected' : '' ?>>Archived</option>
                    </select>
                </div>

                <div class="col-md-4">
                    <label for="class_schedule" class="form-label">Class Schedule</label>
                    <input type="text" name="class_schedule" id="class_schedule" class="form-control" value="<?= htmlspecialchars($_POST['class_schedule'] ?? '') ?>">
                </div>

                <div class="col-md-6">
                    <label for="programme_id" class="form-label">Programme</label>
                    <select name="programme_id" id="programme_id" class="form-select" required>
                        <option value="">Select a Programme</option>
                        <?php foreach ($programmes as $programme): ?>
                            <option value="<?= (int) $programme['id'] ?>" <?= (($_POST['programme_id'] ?? 0) == $programme['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($programme['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-6">
                    <label for="lecturer_id" class="form-label">Lecturer</label>
                    <select name="lecturer_id" id="lecturer_id" class="form-select" required>
                        <option value="">Select a Lecturer</option>
                        <?php foreach ($lecturers as $lecturer): ?>
                            <option value="<?= (int) $lecturer['id'] ?>" <?= (($_POST['lecturer_id'] ?? 0) == $lecturer['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($lecturer['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-12">
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-plus-lg"></i> Add Course
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
