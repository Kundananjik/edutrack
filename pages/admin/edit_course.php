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
require_once '../../includes/csrf.php';
require_once '../../includes/functions.php';

require_login();
require_role(['admin']);

if (!isset($_GET['id']) || !filter_var($_GET['id'], FILTER_VALIDATE_INT)) {
    $_SESSION['error_message'] = 'Invalid course ID.';
    redirect('manage_courses.php');
}

$courseId = (int) $_GET['id'];
$course = null;

try {
    $stmt = $pdo->prepare('
        SELECT c.*, lc.lecturer_id
        FROM courses c
        LEFT JOIN lecturer_courses lc ON c.id = lc.course_id
        WHERE c.id = ?
    ');
    $stmt->execute([$courseId]);
    $course = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$course) {
        $_SESSION['error_message'] = 'Course not found.';
        redirect('manage_courses.php');
    }

    $assignedLecturerId = $course['lecturer_id'];

    $stmt = $pdo->prepare("SELECT id, name FROM users WHERE role = 'lecturer' ORDER BY name");
    $stmt->execute();
    $lecturers = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stmt = $pdo->prepare('SELECT id, name FROM programmes ORDER BY name');
    $stmt->execute();
    $programmes = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log('DB error edit_course.php: ' . $e->getMessage());
    $_SESSION['error_message'] = 'Could not load course data.';
    redirect('manage_courses.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
        $_SESSION['error_message'] = 'Invalid CSRF token.';
    } else {
        $name = trim($_POST['name'] ?? '');
        $courseCode = trim($_POST['course_code'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $credits = (int) ($_POST['credits'] ?? 0);
        $status = trim($_POST['status'] ?? 'active');
        $schedule = trim($_POST['class_schedule'] ?? '');
        $programmeId = (int) ($_POST['programme_id'] ?? 0);
        $newLecturerId = (int) ($_POST['lecturer_id'] ?? 0);

        if ($name === '' || $courseCode === '' || $credits <= 0 || $status === '' || $programmeId <= 0 || $newLecturerId <= 0) {
            $_SESSION['error_message'] = 'Please fill all required fields.';
        } else {
            try {
                $pdo->beginTransaction();

                $stmt = $pdo->prepare('
                    UPDATE courses
                    SET name = ?, course_code = ?, description = ?, credits = ?, status = ?, class_schedule = ?, programme_id = ?, updated_at = CURRENT_TIMESTAMP()
                    WHERE id = ?
                ');
                $stmt->execute([$name, $courseCode, $description, $credits, $status, $schedule, $programmeId, $courseId]);

                if ((int) $assignedLecturerId !== $newLecturerId) {
                    $stmt = $pdo->prepare('DELETE FROM lecturer_courses WHERE course_id = ?');
                    $stmt->execute([$courseId]);

                    $stmt = $pdo->prepare('INSERT INTO lecturer_courses (lecturer_id, course_id) VALUES (?, ?)');
                    $stmt->execute([$newLecturerId, $courseId]);
                }

                $pdo->commit();
                $_SESSION['success_message'] = 'Course updated successfully!';
                redirect('manage_courses.php');
            } catch (PDOException $e) {
                $pdo->rollBack();
                if ($e->getCode() === '23000') {
                    $_SESSION['error_message'] = 'Course code already exists.';
                } else {
                    error_log('DB error edit_course.php: ' . $e->getMessage());
                    $_SESSION['error_message'] = 'Failed to update course.';
                }
            }
        }
    }
}

$currentStatus = $_POST['status'] ?? $course['status'];
$currentProgrammeId = $_POST['programme_id'] ?? $course['programme_id'];
$currentLecturerSelection = $_POST['lecturer_id'] ?? $assignedLecturerId;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="<?= asset_url('assets/favicon.png') ?>">
    <title>Edit Course - EduTrack Admin</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="css/dashboard.css">
</head>
<body class="bg-light d-flex flex-column min-vh-100">

<?php require_once '../../includes/admin_navbar.php'; ?>

<main class="container py-5 flex-grow-1">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <h1 class="fw-bold mb-2">Edit Course</h1>
            <p class="text-muted mb-0">Update the course profile, assignment, and delivery settings.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="view_course.php?id=<?= urlencode((string) $courseId) ?>" class="btn btn-outline-success">
                <i class="bi bi-eye"></i> View Course
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

    <?php if (!empty($_SESSION['error_message'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($_SESSION['error_message']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['error_message']); ?>
    <?php endif; ?>

    <section class="card shadow-sm rounded-4">
        <div class="card-body">
            <form action="edit_course.php?id=<?= urlencode((string) $courseId) ?>" method="POST" class="row g-3">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(get_csrf_token()) ?>">

                <div class="col-md-6">
                    <label for="name" class="form-label">Course Name</label>
                    <input type="text" name="name" id="name" class="form-control" required value="<?= htmlspecialchars($_POST['name'] ?? $course['name']) ?>">
                </div>

                <div class="col-md-6">
                    <label for="course_code" class="form-label">Course Code</label>
                    <input type="text" name="course_code" id="course_code" class="form-control" required value="<?= htmlspecialchars($_POST['course_code'] ?? $course['course_code']) ?>">
                </div>

                <div class="col-12">
                    <label for="description" class="form-label">Description</label>
                    <textarea name="description" id="description" class="form-control" rows="3"><?= htmlspecialchars($_POST['description'] ?? $course['description']) ?></textarea>
                </div>

                <div class="col-md-4">
                    <label for="credits" class="form-label">Credits</label>
                    <input type="number" name="credits" id="credits" class="form-control" min="1" required value="<?= htmlspecialchars($_POST['credits'] ?? $course['credits']) ?>">
                </div>

                <div class="col-md-4">
                    <label for="status" class="form-label">Status</label>
                    <select name="status" id="status" class="form-select" required>
                        <option value="active" <?= $currentStatus === 'active' ? 'selected' : '' ?>>Active</option>
                        <option value="inactive" <?= $currentStatus === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                        <option value="archived" <?= $currentStatus === 'archived' ? 'selected' : '' ?>>Archived</option>
                    </select>
                </div>

                <div class="col-md-4">
                    <label for="class_schedule" class="form-label">Class Schedule</label>
                    <input type="text" name="class_schedule" id="class_schedule" class="form-control" value="<?= htmlspecialchars($_POST['class_schedule'] ?? $course['class_schedule']) ?>">
                </div>

                <div class="col-md-6">
                    <label for="programme_id" class="form-label">Programme</label>
                    <select name="programme_id" id="programme_id" class="form-select" required>
                        <option value="">Select a Programme</option>
                        <?php foreach ($programmes as $programme): ?>
                            <option value="<?= (int) $programme['id'] ?>" <?= (int) $currentProgrammeId === (int) $programme['id'] ? 'selected' : '' ?>>
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
                            <option value="<?= (int) $lecturer['id'] ?>" <?= (int) $currentLecturerSelection === (int) $lecturer['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($lecturer['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-12">
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-floppy"></i> Save Changes
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
