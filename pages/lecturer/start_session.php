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
// pages/lecturer/start_session.php
require_once '../../includes/auth_check.php';
require_once '../../includes/config.php';
require_once '../../includes/db.php';
require_once '../../includes/functions.php';

require_login();
require_role(['lecturer']);

// --- Validate course ID ---
if (!isset($_POST['course_id']) || !is_numeric($_POST['course_id'])) {
    $_SESSION['error_message'] = 'Invalid course selection.';
    header('Location: my_courses.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$course_id = (int) $_POST['course_id'];

try {
    // --- Verify that lecturer is assigned to this course ---
    $stmt_verify = $pdo->prepare('
        SELECT COUNT(*) FROM lecturer_courses 
        WHERE lecturer_id = ? AND course_id = ?
    ');
    $stmt_verify->execute([$user_id, $course_id]);

    if ($stmt_verify->fetchColumn() == 0) {
        $_SESSION['error_message'] = 'You do not have permission to start a session for this course.';
        header('Location: my_courses.php');
        exit();
    }

    // --- Optional: End any previous active session for this course before creating a new one ---
    $pdo->prepare('
        UPDATE attendance_sessions 
        SET is_active = 0 
        WHERE course_id = ? AND lecturer_id = ? AND is_active = 1
    ')->execute([$course_id, $user_id]);

    // --- Generate unique session code ---
    $session_code = generateUniqueSessionCode($pdo);

    // --- Insert new active session ---
    $stmt_insert = $pdo->prepare('
        INSERT INTO attendance_sessions (course_id, lecturer_id, session_code, is_active)
        VALUES (?, ?, ?, 1)
    ');
    $stmt_insert->execute([$course_id, $user_id, $session_code]);

    // --- Prepare success message ---
    $_SESSION['success_message'] = sprintf(
        'Session started successfully for %s — Session Code: %s',
        htmlspecialchars(getCourseCode($pdo, $course_id)),
        htmlspecialchars($session_code)
    );

    header('Location: active_sessions.php');
    exit();

} catch (Exception $e) {
    error_log('Error in start_session.php: ' . $e->getMessage());
    $_SESSION['error_message'] = 'An error occurred while starting the session. Please try again.';
    header('Location: my_courses.php');
    exit();
}

// --- Helper Functions ---
function generateUniqueSessionCode($pdo)
{
    $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    do {
        $code = '';
        for ($i = 0; $i < 7; $i++) {
            $code .= $characters[random_int(0, strlen($characters) - 1)];
        }

        $stmt = $pdo->prepare('SELECT COUNT(*) FROM attendance_sessions WHERE session_code = ?');
        $stmt->execute([$code]);
    } while ($stmt->fetchColumn() > 0);

    return $code;
}

function getCourseCode($pdo, $courseId)
{
    $stmt = $pdo->prepare('SELECT course_code FROM courses WHERE id = ?');
    $stmt->execute([$courseId]);
    return $stmt->fetchColumn() ?: 'Unknown Course';
}
