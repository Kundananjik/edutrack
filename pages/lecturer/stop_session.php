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
require_login();
require_role(['lecturer']);

header('Content-Type: application/json');

if (!isset($_POST['session_id']) || !is_numeric($_POST['session_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid session ID']);
    exit;
}

$session_id = (int)$_POST['session_id'];
$user_id = $_SESSION['user_id'];

try {
    // Only allow lecturer to stop their own sessions
    $stmt = $pdo->prepare('
        UPDATE attendance_sessions 
        SET is_active = 0 
        WHERE id = ? AND lecturer_id = ? AND is_active = 1
    ');
    $stmt->execute([$session_id, $user_id]);

    if ($stmt->rowCount() > 0) {
        echo json_encode(['status' => 'success', 'message' => 'Session stopped successfully']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Session not found or already stopped']);
    }
} catch (Exception $e) {
    error_log('Error stopping session: ' . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'Database error occurred']);
}
