<?php
// Preload (auto-locate includes/preload.php)
$__et=__DIR__;
for($__i=0;$__i<6;$__i++){
    $__p=$__et . '/includes/preload.php';
    if (file_exists($__p)) { require_once $__p; break; }
    $__et=dirname($__et);
}
unset($__et,$__i,$__p);
require_once '../../includes/auth_check.php';
require_once '../../includes/config.php';
require_once '../../includes/db.php';
require_once '../../includes/functions.php';

require_login();
require_role(['lecturer']);

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: my_courses.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$course_id = $_GET['id'];
$course = null;
$students = [];
$sessions = [];
$attendance_data = [];
$error = '';

try {
    // Verify lecturer assignment
    $stmt_verify = $pdo->prepare("SELECT COUNT(*) FROM lecturer_courses WHERE lecturer_id = ? AND course_id = ?");
    $stmt_verify->execute([$user_id, $course_id]);
    if ($stmt_verify->fetchColumn() === 0) {
        $error = "You do not have permission to view this course.";
    } else {
        // Fetch course details
        $stmt_course = $pdo->prepare("SELECT name, course_code FROM courses WHERE id = ?");
        $stmt_course->execute([$course_id]);
        $course = $stmt_course->fetch(PDO::FETCH_ASSOC);

        // Fetch enrolled students
        $stmt_students = $pdo->prepare("
            SELECT u.id AS user_id, u.name, s.student_number, u.email
            FROM users u
            JOIN students s ON u.id = s.user_id
            JOIN enrollments e ON s.user_id = e.student_id
            WHERE e.course_id = ?
            ORDER BY u.name
        ");
        $stmt_students->execute([$course_id]);
        $students = $stmt_students->fetchAll(PDO::FETCH_ASSOC);

        // Fetch all attendance sessions
        $stmt_sessions = $pdo->prepare("SELECT id, created_at FROM attendance_sessions WHERE course_id = ? ORDER BY created_at ASC");
        $stmt_sessions->execute([$course_id]);
        $sessions = $stmt_sessions->fetchAll(PDO::FETCH_ASSOC);

        // Fetch all attendance records
        if (!empty($sessions)) {
            $session_ids = array_column($sessions, 'id');
            $placeholders = implode(',', array_fill(0, count($session_ids), '?'));
            $stmt_records = $pdo->prepare("SELECT session_id, student_id FROM attendance_records WHERE session_id IN ($placeholders)");
            $stmt_records->execute($session_ids);

            while ($record = $stmt_records->fetch(PDO::FETCH_ASSOC)) {
                $attendance_data[$record['student_id']][$record['session_id']] = 'present';
            }
        }
    }
} catch (Exception $e) {
    error_log("Database error in view_course.php: " . $e->getMessage());
    $error = "An error occurred while fetching course data. Please try again later.";
}

// CSV Download
if (isset($_GET['download']) && $_GET['download'] === 'csv' && $course && !empty($students)) {
    $filename = "students_" . strtolower(str_replace(' ', '_', $course['course_code'])) . ".csv";
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    $output = fopen('php://output', 'w');

    // Header: include attendance sessions
    $header = ['Student Name', 'Student Number', 'Email'];
    foreach ($sessions as $sess) {
        $header[] = 'Session ' . (new DateTime($sess['created_at']))->format('M d');
    }
    fputcsv($output, $header);

    foreach ($students as $student) {
        $row = [$student['name'], $student['student_number'], $student['email']];
        foreach ($sessions as $sess) {
            $row[] = isset($attendance_data[$student['user_id']][$sess['id']]) ? 'Present' : 'Absent';
        }
        fputcsv($output, $row);
    }

    fclose($output);
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Course & Attendance</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/dashboard.css">
    <link rel="stylesheet" href="css/view_course.css">
</head>
<body>
<nav class="navbar">
    <div class="container">
        <div class="logo">
            <a href="../../index.php"><img src="../../assets/logo.png" alt="EduTrack Logo"></a>
        </div>
        <ul class="nav-links">
            <li><a href="../../index.php">Home</a></li>
            <li><a href="dashboard.php">Dashboard</a></li>
            <li><a href="../../logout.php">Logout</a></li>
        </ul>
    </div>
</nav>

<div class="dashboard-container">
    <a href="my_courses.php" class="back-link"><i class="fas fa-arrow-left"></i> Back to My Courses</a>

    <?php if ($error): ?>
        <p class="alert alert-danger"><?= htmlspecialchars($error); ?></p>
    <?php elseif (!$course): ?>
        <p class="alert alert-info">Course not found.</p>
    <?php else: ?>
        <div class="details-card">
            <h1><?= htmlspecialchars($course['name']); ?></h1>
            <p><strong>Course Code:</strong> <?= htmlspecialchars($course['course_code']); ?></p>
        </div>

        <div class="content-section">
            <div class="section-header">
                <h2>Enrolled Students & Attendance</h2>
                <?php if (!empty($students)): ?>
                    <a href="?id=<?= $course_id; ?>&download=csv" class="download-btn">
                        <i class="fas fa-download"></i> Download CSV
                    </a>
                <?php endif; ?>
            </div>

            <?php if (empty($students)): ?>
                <p class="alert alert-info">No students enrolled.</p>
            <?php else: ?>
                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Student Name</th>
                                <th>Student Number</th>
                                <th>Email</th>
                                <?php foreach ($sessions as $sess): ?>
                                    <th><?= (new DateTime($sess['created_at']))->format('M d'); ?></th>
                                <?php endforeach; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($students as $student): ?>
                                <tr>
                                    <td><?= htmlspecialchars($student['name']); ?></td>
                                    <td><?= htmlspecialchars($student['student_number']); ?></td>
                                    <td><?= htmlspecialchars($student['email']); ?></td>
                                    <?php foreach ($sessions as $sess): ?>
                                        <td class="<?= isset($attendance_data[$student['user_id']][$sess['id']]) ? 'present' : 'absent'; ?>">
                                            <?= isset($attendance_data[$student['user_id']][$sess['id']]) ? 'Present' : 'Absent'; ?>
                                        </td>
                                    <?php endforeach; ?>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<?php require_once '../../includes/footer.php'; ?>
</body>
</html>