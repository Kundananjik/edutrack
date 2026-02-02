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
// pages/admin/add_lecturer.php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '../../includes/config.php';
require_once '../../includes/db.php';
require_once '../../includes/auth_check.php';
require_once '../../includes/csrf.php';
require_once '../../includes/functions.php';

require_login();
require_role(['admin']);

// Fetch courses for lecturer assignment
try {
    $stmt = $pdo->prepare('SELECT id, name, course_code FROM courses ORDER BY name');
    $stmt->execute();
    $courses = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log('Error fetching courses: ' . $e->getMessage());
    $courses = [];
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
        $_SESSION['error_message'] = 'Invalid CSRF token.';
    } else {
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = trim($_POST['password'] ?? '');
        $status = trim($_POST['status'] ?? 'active');
        $assigned_courses = $_POST['courses'] ?? [];

        if (empty($name) || empty($email) || empty($password)) {
            $_SESSION['error_message'] = 'All fields are required.';
        } else {
            try {
                $stmt = $pdo->prepare('SELECT COUNT(*) FROM users WHERE email = ?');
                $stmt->execute([$email]);
                if ($stmt->fetchColumn() > 0) {
                    $_SESSION['error_message'] = 'Email already exists.';
                } else {
                    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                    $pdo->beginTransaction();
                    $stmt = $pdo->prepare("INSERT INTO users (name, email, role, status, password) VALUES (?, ?, 'lecturer', ?, ?)");
                    $stmt->execute([$name, $email, $status, $hashedPassword]);
                    $lecturer_id = $pdo->lastInsertId();
                    if (!empty($assigned_courses)) {
                        $stmt = $pdo->prepare('INSERT INTO lecturer_courses (lecturer_id, course_id) VALUES (?, ?)');
                        foreach ($assigned_courses as $course_id) {
                            $stmt->execute([$lecturer_id, intval($course_id)]);
                        }
                    }
                    $pdo->commit();
                    $_SESSION['success_message'] = 'Lecturer added successfully!';
                    header('Location: manage_lecturers.php');
                    exit();
                }
            } catch (PDOException $e) {
                $pdo->rollBack();
                error_log('Database error adding lecturer: ' . $e->getMessage());
                $_SESSION['error_message'] = 'Failed to add lecturer.';
            }
        }
    }
}

$formValues = ($_SERVER['REQUEST_METHOD'] === 'POST') ? $_POST : [];
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Add Lecturer - EduTrack</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Custom EduTrack Styles -->
    <link rel="stylesheet" href="/edutrack/pages/admin/css/dashboard.css">
</head>

<body class="bg-light">
    <?php require_once '../../includes/admin_navbar.php'; ?>

    <div class="dashboard-container container py-5">

        <!-- Back to Dashboard Button on Top -->
        <div class="mb-4">
            <a href="dashboard.php" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
        </div>

        <h1 class="text-center mb-4">Add Lecturer</h1>

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

        <div class="card shadow-sm p-4 rounded-4">
            <form action="" method="POST" autocomplete="off">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(get_csrf_token()) ?>">

                <div class="mb-3">
                    <label for="name" class="form-label fw-semibold">Full Name</label>
                    <input type="text" name="name" id="name" class="form-control rounded-3"
                        value="<?= htmlspecialchars($formValues['name'] ?? '') ?>" required>
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label fw-semibold">Email</label>
                    <input type="email" name="email" id="email" class="form-control rounded-3"
                        value="<?= htmlspecialchars($formValues['email'] ?? '') ?>" required>
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label fw-semibold">Password</label>
                    <input type="password" name="password" id="password" class="form-control rounded-3" required>
                </div>

                <div class="mb-3">
                    <label for="status" class="form-label fw-semibold">Status</label>
                    <select name="status" id="status" class="form-select rounded-3">
                        <option value="active" <?= (($formValues['status'] ?? 'active') === 'active') ? 'selected' : '' ?>>Active</option>
                        <option value="suspended" <?= (($formValues['status'] ?? '') === 'suspended') ? 'selected' : '' ?>>Suspended</option>
                    </select>
                </div>

                <?php if (!empty($courses)): ?>
                    <div class="mb-3">
                        <label for="courses" class="form-label fw-semibold">Assign Courses</label>
                        <select name="courses[]" id="courses" multiple class="form-select rounded-3">
                            <?php foreach ($courses as $course): ?>
                                <option value="<?= $course['id'] ?>" <?= in_array($course['id'], $formValues['courses'] ?? []) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($course['name'] . ' (' . $course['course_code'] . ')') ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="form-text">Hold Ctrl (Cmd on Mac) to select multiple courses</div>
                    </div>
                <?php endif; ?>

                <div class="mt-4 text-end">
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-plus"></i> Add Lecturer
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Custom EduTrack CSS -->
    <link rel="stylesheet" href="css/dashboard.css">