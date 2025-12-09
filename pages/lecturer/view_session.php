<?php
// Preload (auto-locate includes/preload.php)
$__et=__DIR__;
for($__i=0;$__i<6;$__i++){
    $__p=$__et . '/includes/preload.php';
    if (file_exists($__p)) { require_once $__p; break; }
    $__et=dirname($__et);
}
unset($__et,$__i,$__p);
// pages/lecturer/view_session.php
require_once '../../includes/auth_check.php';
require_once '../../includes/config.php';
require_once '../../includes/db.php';
require_once '../../includes/functions.php';

require_login();
require_role(['lecturer']);

// Check if a session ID was provided via GET
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['error_message'] = "Invalid session ID provided.";
    header("Location: active_sessions.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$session_id = $_GET['id'];
$sessionDetails = null;
$attendanceRecords = [];
$error = '';

try {
    // Step 1: Fetch session details and verify ownership
    $stmt_session = $pdo->prepare("
        SELECT 
            asess.id, 
            asess.session_code, 
            c.course_code, 
            c.name AS course_name
        FROM attendance_sessions asess
        JOIN courses c ON asess.course_id = c.id
        WHERE asess.id = ? AND asess.lecturer_id = ?
    ");
    $stmt_session->execute([$session_id, $user_id]);
    $sessionDetails = $stmt_session->fetch(PDO::FETCH_ASSOC);

    // If the session doesn't exist or doesn't belong to this lecturer, show an error
    if (!$sessionDetails) {
        $_SESSION['error_message'] = "Session not found or you do not have permission to view it.";
        header("Location: active_sessions.php");
        exit();
    }

    // Step 2: Fetch all attendance records for this session
    // NOTE: This assumes an 'attendance_records' table exists.
    // We join with the users and students tables to get the student's name and number.
    $stmt_records = $pdo->prepare("
        SELECT 
            u.name AS student_name,
            s.student_number,
            ar.signed_in_at
        FROM attendance_records ar
        JOIN users u ON ar.student_id = u.id
        LEFT JOIN students s ON s.user_id = u.id
        WHERE ar.session_id = ?
        ORDER BY ar.signed_in_at ASC
    ");
    $stmt_records->execute([$session_id]);
    $attendanceRecords = $stmt_records->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    error_log("Database error in view_session.php: " . $e->getMessage());
    $error = "An error occurred while fetching attendance records. Please try again later.";
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Attendance - <?= htmlspecialchars($sessionDetails['course_code']) ?></title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/dashboard.css">
</head>

<body>
<nav class="navbar">
    <div class="container">
        <div class="logo">
            <a href="../../index.php">
                <img src="../../assets/logo.png" alt="EduTrack Logo">
            </a>
        </div>
        <ul class="nav-links">
            <li><a href="../../index.php">Home</a></li>
            <li><a href="dashboard.php">Dashboard</a></li>
            <li><a href="../../logout.php">Logout</a></li>
        </ul>
    </div>
</nav>

<div class="dashboard-container">
    <a href="active_sessions.php" class="back-link"><i class="fas fa-arrow-left"></i> Back to Active Sessions</a>
    <h1>Attendance Records</h1>
    
    <div class="details-card">
        <div class="detail-row">
            <span class="detail-label">Course:</span>
            <span class="detail-value"><?= htmlspecialchars($sessionDetails['course_code']) ?> - <?= htmlspecialchars($sessionDetails['course_name']) ?></span>
        </div>
        <div class="detail-row">
            <span class="detail-label">Session Code:</span>
            <span class="detail-value"><?= htmlspecialchars($sessionDetails['session_code']) ?></span>
        </div>
    </div>
    
    <?php if ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php elseif (empty($attendanceRecords)): ?>
        <p class="info-message">No students have signed in for this session yet.</p>
    <?php else: ?>
        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Student Name</th>
                        <th>Student Number</th>
                        <th>Sign-in Time</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($attendanceRecords as $record): ?>
                        <tr>
                            <td><?= htmlspecialchars($record['student_name']) ?></td>
                            <td><?= htmlspecialchars($record['student_number']) ?></td>
                            <td><?= (new DateTime($record['signed_in_at']))->format('F j, Y, g:i a') ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<?php require_once '../../includes/footer.php'; ?>
</body>
</html>