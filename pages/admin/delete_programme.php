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
// pages/admin/delete_programme.php

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
    $programmeId = intval($_POST['id'] ?? 0);
    $csrf_token = $_POST['csrf_token'] ?? '';

    // Validate CSRF
    if (!validate_csrf_token($csrf_token)) {
        $_SESSION['error_message'] = 'Invalid CSRF token. Please try again.';
        header('Location: manage_programmes.php');
        exit;
    }

    // Validate programme ID
    if ($programmeId <= 0) {
        $_SESSION['error_message'] = 'Invalid programme ID.';
        header('Location: manage_programmes.php');
        exit;
    }

    try {
        $pdo->beginTransaction();

        // Step 1: Get all course IDs under this programme
        $stmt = $pdo->prepare('SELECT id FROM courses WHERE programme_id = ?');
        $stmt->execute([$programmeId]);
        $courseIds = $stmt->fetchAll(PDO::FETCH_COLUMN);

        if (!empty($courseIds)) {
            // Step 2: Delete all enrollments linked to these courses
            $in = str_repeat('?,', count($courseIds) - 1) . '?';
            $stmt = $pdo->prepare("DELETE FROM enrollments WHERE course_id IN ($in)");
            $stmt->execute($courseIds);

            // Step 3: Delete lecturer_course assignments linked to these courses
            $stmt = $pdo->prepare("DELETE FROM lecturer_courses WHERE course_id IN ($in)");
            $stmt->execute($courseIds);

            // Step 4: Delete the courses themselves
            $stmt = $pdo->prepare("DELETE FROM courses WHERE id IN ($in)");
            $stmt->execute($courseIds);
        }

        // Step 5: Delete the programme itself
        $stmt = $pdo->prepare('DELETE FROM programmes WHERE id = ?');
        $stmt->execute([$programmeId]);

        $pdo->commit();

        $_SESSION['success_message'] = 'Programme deleted successfully.';
        header('Location: manage_programmes.php');
        exit;

    } catch (PDOException $e) {
        $pdo->rollBack();
        error_log("Error deleting programme ID $programmeId: " . $e->getMessage());
        $_SESSION['error_message'] = 'Failed to delete programme. Please try again later.';
        header('Location: manage_programmes.php');
        exit;
    }

} else {
    $_SESSION['error_message'] = 'Invalid request method.';
    header('Location: manage_programmes.php');
    exit;
}
