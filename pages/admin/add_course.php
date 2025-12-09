<?php
// Preload (auto-locate includes/preload.php)
$__et=__DIR__;
for($__i=0;$__i<6;$__i++){
    $__p=$__et . '/includes/preload.php';
    if (file_exists($__p)) { require_once $__p; break; }
    $__et=dirname($__et);
}
unset($__et,$__i,$__p);
// pages/admin/add_course.php

require_once '../../includes/auth_check.php';
require_once '../../includes/config.php';
require_once '../../includes/db.php';
require_once '../../includes/csrf.php';
require_once '../../includes/functions.php';

require_login();
require_role(['admin']);

$lecturers = [];
$programmes = [];
$debug_messages = [];

// Fetch lecturers and programmes with debug info
try {
    // Fetch lecturers
    $stmt = $pdo->prepare("SELECT id, name FROM users WHERE role = 'lecturer' ORDER BY name");
    if ($stmt->execute()) {
        $lecturers = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if (empty($lecturers)) $debug_messages[] = "No lecturers found in the database.";
    } else {
        $debug_messages[] = "Failed to execute query for lecturers.";
    }

    // Fetch programmes
    $stmt = $pdo->prepare("SELECT id, name FROM programmes ORDER BY name");
    if ($stmt->execute()) {
        $programmes = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if (empty($programmes)) $debug_messages[] = "No programmes found in the database.";
    } else {
        $debug_messages[] = "Failed to execute query for programmes.";
    }

} catch (PDOException $e) {
    $debug_messages[] = "Database error: " . $e->getMessage();
    error_log("Database error fetching lecturers or programmes: " . $e->getMessage());
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
        $_SESSION['error_message'] = "Invalid CSRF token.";
        redirect("add_course.php");
    }

    $name         = trim($_POST['name'] ?? '');
    $course_code  = trim($_POST['course_code'] ?? '');
    $description  = trim($_POST['description'] ?? '');
    $credits      = intval($_POST['credits'] ?? 0);
    $status       = trim($_POST['status'] ?? 'active');
    $schedule     = trim($_POST['class_schedule'] ?? '');
    $programme_id = intval($_POST['programme_id'] ?? 0);
    $lecturer_id  = intval($_POST['lecturer_id'] ?? 0);

    if (empty($name) || empty($course_code) || $credits <= 0 || empty($status) || $programme_id <= 0 || $lecturer_id <= 0) {
        $_SESSION['error_message'] = "Please fill in all required fields.";
    } else {
        try {
            $stmt = $pdo->prepare("
                INSERT INTO courses (name, course_code, description, credits, status, class_schedule, programme_id)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$name, $course_code, $description, $credits, $status, $schedule, $programme_id]);

            $course_id = $pdo->lastInsertId();

            $stmt = $pdo->prepare("INSERT INTO lecturer_courses (lecturer_id, course_id) VALUES (?, ?)");
            $stmt->execute([$lecturer_id, $course_id]);

            $_SESSION['success_message'] = "Course added successfully!";
            redirect("manage_courses.php");

        } catch (PDOException $e) {
            if ($e->getCode() == '23000') {
                $_SESSION['error_message'] = "Error: A course with this code already exists.";
            } else {
                error_log("Database error in add_course.php: " . $e->getMessage());
                $_SESSION['error_message'] = "Failed to add course. Please try again.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Course - EduTrack</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Custom EduTrack CSS -->
    <link rel="stylesheet" href="css/dashboard.css">
</head>
<body>

<?php require_once '../../includes/admin_navbar.php'; ?>

<div class="page-wrapper container py-4">
    <div class="dashboard-container shadow-sm p-4 rounded bg-white">
        <h1 class="text-success mb-4"><i class="fas fa-book-open"></i> Add Course</h1>

        <!-- Alerts Section -->
        <div class="alerts mb-4">
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

            <?php if (!empty($debug_messages)): ?>
                <div class="alert alert-warning">
                    <strong>Debug info:</strong>
                    <ul class="mb-0">
                        <?php foreach ($debug_messages as $msg): ?>
                            <li><?= htmlspecialchars($msg) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
        </div>

        <!-- Add Course Form -->
        <form action="add_course.php" method="POST" class="user-form row g-3">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(get_csrf_token()) ?>">

            <div class="col-md-6">
                <label for="name" class="form-label">Course Name:</label>
                <input type="text" name="name" id="name" class="form-control" required
                       value="<?= htmlspecialchars($_POST['name'] ?? '') ?>">
            </div>

            <div class="col-md-6">
                <label for="course_code" class="form-label">Course Code:</label>
                <input type="text" name="course_code" id="course_code" class="form-control" required
                       value="<?= htmlspecialchars($_POST['course_code'] ?? '') ?>">
            </div>

            <div class="col-12">
                <label for="description" class="form-label">Description:</label>
                <textarea name="description" id="description" class="form-control"
                          rows="3"><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
            </div>

            <div class="col-md-4">
                <label for="credits" class="form-label">Credits:</label>
                <input type="number" name="credits" id="credits" min="1" class="form-control" required
                       value="<?= htmlspecialchars($_POST['credits'] ?? '') ?>">
            </div>

            <div class="col-md-4">
                <label for="status" class="form-label">Status:</label>
                <select name="status" id="status" class="form-select" required>
                    <option value="active" <?= (($_POST['status'] ?? 'active') === 'active') ? 'selected' : '' ?>>Active</option>
                    <option value="inactive" <?= (($_POST['status'] ?? '') === 'inactive') ? 'selected' : '' ?>>Inactive</option>
                    <option value="archived" <?= (($_POST['status'] ?? '') === 'archived') ? 'selected' : '' ?>>Archived</option>
                </select>
            </div>

            <div class="col-md-4">
                <label for="class_schedule" class="form-label">Class Schedule:</label>
                <input type="text" name="class_schedule" id="class_schedule" class="form-control"
                       value="<?= htmlspecialchars($_POST['class_schedule'] ?? '') ?>">
            </div>

            <div class="col-md-6">
                <label for="programme_id" class="form-label">Programme:</label>
                <select name="programme_id" id="programme_id" class="form-select" required>
                    <option value="">Select a Programme</option>
                    <?php foreach ($programmes as $programme): ?>
                        <option value="<?= htmlspecialchars($programme['id']) ?>"
                            <?= (($_POST['programme_id'] ?? 0) == $programme['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($programme['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-6">
                <label for="lecturer_id" class="form-label">Lecturer:</label>
                <select name="lecturer_id" id="lecturer_id" class="form-select" required>
                    <option value="">Select a Lecturer</option>
                    <?php foreach ($lecturers as $lecturer): ?>
                        <option value="<?= htmlspecialchars($lecturer['id']) ?>"
                            <?= (($_POST['lecturer_id'] ?? 0) == $lecturer['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($lecturer['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-12 text-end">
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-plus"></i> Add Course
                </button>
                <a href="manage_courses.php" class="btn btn-secondary ms-2">
                    <i class="fas fa-arrow-left"></i> Back to Courses
                </a>
            </div>
        </form>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>

<!-- Bootstrap JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
