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
require_once '../../includes/auth_check.php';
require_once '../../includes/config.php';
require_once '../../includes/db.php';
require_once '../../includes/csrf.php';
require_once '../../includes/functions.php';

require_login();
require_role(['admin']);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error_message'] = 'Invalid request method.';
    redirect('manage_lecturers.php');
}

$id = intval($_POST['id'] ?? 0);
$csrf_token = $_POST['csrf_token'] ?? '';

if (!validate_csrf_token($csrf_token)) {
    $_SESSION['error_message'] = 'Invalid CSRF token.';
    redirect('manage_lecturers.php');
}

if ($id <= 0) {
    $_SESSION['error_message'] = 'Invalid lecturer ID.';
    redirect('manage_lecturers.php');
}

try {
    // Optional: check if lecturer exists
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ? AND role = 'lecturer'");
    $stmt->execute([$id]);
    $lecturer = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$lecturer) {
        $_SESSION['error_message'] = 'Lecturer not found.';
        redirect('manage_lecturers.php');
    }

    // Delete lecturer
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ? AND role = 'lecturer'");
    $stmt->execute([$id]);

    $_SESSION['success_message'] = 'Lecturer deleted successfully.';

} catch (PDOException $e) {
    error_log("Error deleting lecturer ID $id: " . $e->getMessage());
    $_SESSION['error_message'] = 'Failed to delete lecturer. Check logs.';
}

redirect('manage_lecturers.php');
