<?php
// Preload (auto-locate includes/preload.php)
$__et=__DIR__;
for($__i=0;$__i<6;$__i++){
    $__p=$__et . '/includes/preload.php';
    if (file_exists($__p)) { require_once $__p; break; }
    $__et=dirname($__et);
}
unset($__et,$__i,$__p);
// pages/admin/edit_course.php

require_once '../../includes/auth_check.php';
require_once '../../includes/config.php';
require_once '../../includes/db.php';
require_once '../../includes/csrf.php';
require_once '../../includes/functions.php';

require_login();
require_role(['admin']);

// Validate course ID
if (!isset($_GET['id']) || !filter_var($_GET['id'], FILTER_VALIDATE_INT)) {
    $_SESSION['error_message'] = "Invalid course ID.";
    redirect("manage_courses.php");
}

$courseId = intval($_GET['id']);
$course = null;

// Fetch course, lecturers, programmes
try {
    $stmt = $pdo->prepare("
        SELECT c.*, lc.lecturer_id 
        FROM courses c 
        LEFT JOIN lecturer_courses lc ON c.id = lc.course_id 
        WHERE c.id = ?
    ");
    $stmt->execute([$courseId]);
    $course = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$course) {
        $_SESSION['error_message'] = "Course not found.";
        redirect("manage_courses.php");
    }

    $currentLecturerId = $course['lecturer_id'];

    // All lecturers
    $stmt = $pdo->prepare("SELECT id, name FROM users WHERE role = 'lecturer' ORDER BY name");
    $stmt->execute();
    $lecturers = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // All programmes
    $stmt = $pdo->prepare("SELECT id, name FROM programmes ORDER BY name");
    $stmt->execute();
    $programmes = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    error_log("DB error edit_course.php: " . $e->getMessage());
    $_SESSION['error_message'] = "Could not load course data.";
    redirect("manage_courses.php");
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
        $_SESSION['error_message'] = "Invalid CSRF token.";
    } else {
        $name = trim($_POST['name'] ?? '');
        $course_code = trim($_POST['course_code'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $credits = intval($_POST['credits'] ?? 0);
        $status = trim($_POST['status'] ?? 'active');
        $schedule = trim($_POST['class_schedule'] ?? '');
        $programme_id = intval($_POST['programme_id'] ?? 0);
        $newLecturerId = intval($_POST['lecturer_id'] ?? 0);

        if (!$name || !$course_code || $credits <= 0 || !$status || $programme_id <= 0 || $newLecturerId <= 0) {
            $_SESSION['error_message'] = "Please fill all required fields.";
        } else {
            try {
                $pdo->beginTransaction();

                $stmt = $pdo->prepare("
                    UPDATE courses
                    SET name = ?, course_code = ?, description = ?, credits = ?, status = ?, class_schedule = ?, programme_id = ?, updated_at = CURRENT_TIMESTAMP()
                    WHERE id = ?
                ");
                $stmt->execute([$name, $course_code, $description, $credits, $status, $schedule, $programme_id, $courseId]);

                if ($currentLecturerId != $newLecturerId) {
                    $stmt = $pdo->prepare("DELETE FROM lecturer_courses WHERE course_id = ?");
                    $stmt->execute([$courseId]);

                    $stmt = $pdo->prepare("INSERT INTO lecturer_courses (lecturer_id, course_id) VALUES (?, ?)");
                    $stmt->execute([$newLecturerId, $courseId]);
                }

                $pdo->commit();
                $_SESSION['success_message'] = "Course updated successfully!";
                redirect("manage_courses.php");

            } catch (PDOException $e) {
                $pdo->rollBack();
                if ($e->getCode() === '23000') {
                    $_SESSION['error_message'] = "Course code already exists.";
                } else {
                    error_log("DB error edit_course.php: " . $e->getMessage());
                    $_SESSION['error_message'] = "Failed to update course.";
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
</head>
<body>
<?php require_once '../../includes/admin_navbar.php'; ?>

<div class="container mt-4">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
<div class="container py-4">

    <h1 class="mb-4">Edit Course: <?= htmlspecialchars($course['name']) ?></h1>

    <!-- Alerts -->
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

    <form action="edit_course.php?id=<?= $courseId ?>" method="POST" class="needs-validation" novalidate>
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(get_csrf_token()) ?>">

        <div class="mb-3">
            <label for="name" class="form-label">Course Name</label>
            <input type="text" name="name" id="name" class="form-control" required
                   value="<?= htmlspecialchars($_POST['name'] ?? $course['name']) ?>">
        </div>

        <div class="mb-3">
            <label for="course_code" class="form-label">Course Code</label>
            <input type="text" name="course_code" id="course_code" class="form-control" required
                   value="<?= htmlspecialchars($_POST['course_code'] ?? $course['course_code']) ?>">
        </div>

        <div class="mb-3">
            <label for="description" class="form-label">Description</label>
            <textarea name="description" id="description" class="form-control"><?= htmlspecialchars($_POST['description'] ?? $course['description']) ?></textarea>
        </div>

        <div class="mb-3">
            <label for="credits" class="form-label">Credits</label>
            <input type="number" name="credits" id="credits" class="form-control" min="1" required
                   value="<?= htmlspecialchars($_POST['credits'] ?? $course['credits']) ?>">
        </div>

        <div class="mb-3">
            <label for="status" class="form-label">Status</label>
            <?php $currentStatus = $_POST['status'] ?? $course['status']; ?>
            <select name="status" id="status" class="form-select" required>
                <option value="active" <?= ($currentStatus === 'active') ? 'selected' : '' ?>>Active</option>
                <option value="inactive" <?= ($currentStatus === 'inactive') ? 'selected' : '' ?>>Inactive</option>
                <option value="archived" <?= ($currentStatus === 'archived') ? 'selected' : '' ?>>Archived</option>
            </select>
        </div>

        <div class="mb-3">
            <label for="class_schedule" class="form-label">Class Schedule</label>
            <input type="text" name="class_schedule" id="class_schedule" class="form-control"
                   value="<?= htmlspecialchars($_POST['class_schedule'] ?? $course['class_schedule']) ?>">
        </div>

        <div class="mb-3">
            <label for="programme_id" class="form-label">Programme</label>
            <select name="programme_id" id="programme_id" class="form-select" required>
                <option value="">Select a Programme</option>
                <?php $currentProgrammeId = $_POST['programme_id'] ?? $course['programme_id']; ?>
                <?php foreach ($programmes as $programme): ?>
                    <option value="<?= $programme['id'] ?>" <?= ($currentProgrammeId == $programme['id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($programme['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="mb-3">
            <label for="lecturer_id" class="form-label">Lecturer</label>
            <select name="lecturer_id" id="lecturer_id" class="form-select" required>
                <option value="">Select a Lecturer</option>
                <?php $currentLecturerId = $_POST['lecturer_id'] ?? $currentLecturerId; ?>
                <?php foreach ($lecturers as $lecturer): ?>
                    <option value="<?= $lecturer['id'] ?>" <?= ($currentLecturerId == $lecturer['id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($lecturer['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <button type="submit" class="btn btn-success">
            <i class="fas fa-save"></i> Save Changes
        </button>
        <a href="manage_courses.php" class="btn btn-secondary ms-2"><i class="fas fa-arrow-left"></i> Back to Courses</a>
    </form>
</div>

<!-- Bootstrap 5 JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<?php require_once '../../includes/footer.php'; ?>
</body>
</html>
