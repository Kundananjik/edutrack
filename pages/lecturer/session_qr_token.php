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

require_once '../../includes/auth_check.php';
require_once '../../includes/config.php';
require_once '../../includes/db.php';
require_once '../../includes/functions.php';

require_login();
require_role(['lecturer']);

header('Content-Type: application/json; charset=UTF-8');

$lecturerId = (int)($_SESSION['user_id'] ?? 0);
$sessionId = isset($_GET['session_id']) ? (int)$_GET['session_id'] : 0;

if ($lecturerId <= 0 || $sessionId <= 0) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Invalid request.']);
    exit;
}

try {
    $stmt = $pdo->prepare('
        SELECT id, session_code
        FROM attendance_sessions
        WHERE id = ? AND lecturer_id = ? AND is_active = 1
        LIMIT 1
    ');
    $stmt->execute([$sessionId, $lecturerId]);
    $session = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$session) {
        http_response_code(404);
        echo json_encode(['status' => 'error', 'message' => 'Session not found or inactive.']);
        exit;
    }

    $token = et_generate_session_qr_token((int)$session['id'], (string)$session['session_code']);
    echo json_encode(['status' => 'ok', 'token' => $token]);
} catch (Throwable $e) {
    error_log('session_qr_token.php error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Failed to generate token.']);
}
