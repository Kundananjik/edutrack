<?php

/**
 * Controller: controllers/student/mark_attendance.php
 * Purpose: Receives POST from student dashboard to mark attendance (QR/manual).
 * Security: Requires logged-in student, enforces CSRF on POST.
 */
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
// controllers/student/mark_attendance.php

require_once '../../includes/auth_check.php';
require_once '../../includes/config.php';
require_once '../../includes/db.php';
require_once '../../includes/csrf.php';

require_login();
require_role(['student']);

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
    http_response_code(400);
    echo 'Invalid request';
    exit;
}

$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
    header('Location: ../../auth/login.php');
    exit;
}

// Get POST values
$session_code = trim($_POST['session_code'] ?? '');
$course_id = $_POST['course_id'] ?? null;
$qr = isset($_POST['qr']) ? true : false;

try {
    if ($qr && $session_code) {
        // QR scan: validate session code
        $stmt = $pdo->prepare('
            SELECT id, course_id
            FROM attendance_sessions 
            WHERE session_code = ? AND is_active = 1
        ');
        $stmt->execute([$session_code]);
        $session = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$session) {
            throw new Exception('Invalid or inactive session.');
        }

        $course_id = $session['course_id'];
        $session_id = $session['id'];

    } elseif ($course_id && $session_code) {
        // Manual sign-in: validate session code for selected course
        $stmt = $pdo->prepare('
            SELECT id 
            FROM attendance_sessions 
            WHERE course_id = ? AND session_code = ? AND is_active = 1
            LIMIT 1
        ');
        $stmt->execute([$course_id, $session_code]);
        $session = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$session) {
            throw new Exception('Invalid session code or session is not active for this course.');
        }

        $session_id = $session['id'];

    } else {
        throw new Exception('Please select a course and enter a valid session code.');
    }

    // Check if student already signed in
    $stmt = $pdo->prepare('
        SELECT COUNT(*) 
        FROM attendance_records 
        WHERE student_id = ? AND session_id = ?
    ');
    $stmt->execute([$user_id, $session_id]);
    if ($stmt->fetchColumn() > 0) {
        throw new Exception('You have already marked attendance for this session.');
    }

    // Insert attendance record
    $stmt = $pdo->prepare('
        INSERT INTO attendance_records (student_id, session_id, signed_in_at)
        VALUES (?, ?, NOW())
    ');
    $stmt->execute([$user_id, $session_id]);

    if ($qr) {
        echo 'Attendance successfully recorded!';
        exit;
    } else {
        $_SESSION['sign_in_message'] = 'Attendance recorded successfully!';
        header('Location: ../../pages/student/dashboard.php');
        exit;
    }

} catch (Exception $e) {
    if ($qr) {
        echo $e->getMessage();
        exit;
    } else {
        $_SESSION['sign_in_message'] = 'Error: ' . $e->getMessage();
        header('Location: ../../pages/student/dashboard.php');
        exit;
    }
}
