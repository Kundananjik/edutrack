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
// pages/admin/delete_course.php

require_once '../../includes/auth_check.php';
require_once '../../includes/config.php';
require_once '../../includes/db.php';
require_once '../../includes/csrf.php';
require_once '../../includes/functions.php';

require_login();
require_role(['admin']);

if (!function_exists('validate_csrf_token')) {
    /**
     * Fallback CSRF validation: compares provided token with token stored in session.
     * This will only be used if includes/csrf.php does not define validate_csrf_token().
     */
    function validate_csrf_token($token)
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (empty($token) || empty($_SESSION['csrf_token'])) {
            return false;
        }
        return hash_equals($_SESSION['csrf_token'], $token);
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $courseId = intval($_POST['id'] ?? 0);
    $csrf_token = $_POST['csrf_token'] ?? '';

    if (!validate_csrf_token($csrf_token)) {
        $_SESSION['error_message'] = 'Invalid CSRF token. Please try again.';
        header('Location: manage_courses.php');
        exit;
    }

    if ($courseId <= 0) {
        $_SESSION['error_message'] = 'Invalid course ID.';
        header('Location: manage_courses.php');
        exit;
    }

    try {
        // Start a transaction for safety
        $pdo->beginTransaction();

        // 1. Remove related records from lecturer_courses (if any)
        $stmt = $pdo->prepare('DELETE FROM lecturer_courses WHERE course_id = ?');
        $stmt->execute([$courseId]);

        // 2. Remove related records from enrollments (if applicable)
        $stmt = $pdo->prepare('DELETE FROM enrollments WHERE course_id = ?');
        $stmt->execute([$courseId]);

        // 3. Delete the course itself
        $stmt = $pdo->prepare('DELETE FROM courses WHERE id = ?');
        $stmt->execute([$courseId]);

        $pdo->commit();

        $_SESSION['success_message'] = 'Course deleted successfully.';
        header('Location: manage_courses.php');
        exit;

    } catch (PDOException $e) {
        $pdo->rollBack();
        error_log("Error deleting course ID $courseId: " . $e->getMessage());
        $_SESSION['error_message'] = 'Failed to delete course. Please try again later.';
        header('Location: manage_courses.php');
        exit;
    }

} else {
    // Invalid request method
    $_SESSION['error_message'] = 'Invalid request.';
    header('Location: manage_courses.php');
    exit;
}
