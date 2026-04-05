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
require_once '../../includes/functions.php';

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
$device_hash = et_device_fingerprint_hash();
$client_ip = et_client_ip();
$user_agent = substr((string)($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 255);

try {
    if ($qr && $session_code) {
        // QR scan: validate short-lived signed token (rotates every 20s)
        if (!preg_match('/^ETQR1\.(\d+)\.(\d+)\.([a-f0-9]{20})$/i', $session_code, $parts)) {
            throw new Exception('Invalid or expired QR token. Please re-scan.');
        }

        $token_session_id = (int)$parts[1];
        $stmt = $pdo->prepare('
            SELECT id, course_id, session_code
            FROM attendance_sessions 
            WHERE id = ? AND is_active = 1
            LIMIT 1
        ');
        $stmt->execute([$token_session_id]);
        $session = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$session || !et_validate_session_qr_token($session_code, (int)$session['id'], (string)$session['session_code'])) {
            throw new Exception('Invalid or expired QR token. Please re-scan.');
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

    // Optional strict device binding:
    // when enabled, the account can only mark attendance from the same previously used device fingerprint.
    $strict_device_binding = et_env_bool('ATTENDANCE_DEVICE_STRICT', false);
    $has_device_hash_col = et_db_column_exists($pdo, 'attendance_records', 'device_hash');
    $has_ip_col = et_db_column_exists($pdo, 'attendance_records', 'ip_address');
    $has_ua_col = et_db_column_exists($pdo, 'attendance_records', 'user_agent');

    if ($strict_device_binding) {
        if (!$has_device_hash_col) {
            throw new Exception('Device binding is enabled but database migration is missing. Contact admin.');
        }

        $stmt = $pdo->prepare('
            SELECT device_hash
            FROM attendance_records
            WHERE student_id = ?
              AND device_hash IS NOT NULL
              AND device_hash <> ""
            ORDER BY signed_in_at DESC
            LIMIT 1
        ');
        $stmt->execute([$user_id]);
        $known_device_hash = (string)$stmt->fetchColumn();

        if ($known_device_hash !== '' && !hash_equals($known_device_hash, $device_hash)) {
            throw new Exception('Device verification failed. Please use your registered device for attendance.');
        }
    }

    // Insert attendance record
    $columns = ['student_id', 'session_id', 'signed_in_at'];
    $valuesSql = ['?', '?', 'NOW()'];
    $params = [$user_id, $session_id];

    if ($has_device_hash_col) {
        $columns[] = 'device_hash';
        $valuesSql[] = '?';
        $params[] = $device_hash;
    }
    if ($has_ua_col) {
        $columns[] = 'user_agent';
        $valuesSql[] = '?';
        $params[] = $user_agent;
    }
    if ($has_ip_col) {
        $columns[] = 'ip_address';
        $valuesSql[] = '?';
        $params[] = $client_ip;
    }

    $sql = 'INSERT INTO attendance_records (' . implode(', ', $columns) . ') VALUES (' . implode(', ', $valuesSql) . ')';
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

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
