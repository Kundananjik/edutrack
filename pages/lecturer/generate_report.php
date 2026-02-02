<?php
// Preload
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
require_once '../../includes/db.php';
require_login();
require_role(['lecturer']);

if (!isset($_GET['course_id']) || !is_numeric($_GET['course_id'])) {
    header('Location: attendance_reports.php');
    exit();
}

// Logged-in lecturer ID
$lecturer_id = $_SESSION['user_id'];

$course_id = intval($_GET['course_id']);
$month_filter = $_GET['month'] ?? '';

$course = null;
$students = [];
$sessions = [];
$attendance_data = [];
$error = '';

try {

    // Validate that the course belongs to the lecturer
    $stmt_verify = $pdo->prepare('
        SELECT c.name, c.course_code 
        FROM courses c
        JOIN lecturer_courses lc ON c.id = lc.course_id
        WHERE c.id = ? AND lc.lecturer_id = ?
    ');
    $stmt_verify->execute([$course_id, $lecturer_id]);
    $course = $stmt_verify->fetch(PDO::FETCH_ASSOC);

    if (!$course) {
        $error = 'You are not assigned to this course.';
    } else {

        // Get enrolled students
        $stmt_students = $pdo->prepare('
            SELECT u.id AS user_id, u.name, s.student_number
            FROM users u
            JOIN students s ON u.id = s.user_id
            JOIN enrollments e ON s.user_id = e.student_id
            WHERE e.course_id = ?
            ORDER BY u.name
        ');
        $stmt_students->execute([$course_id]);
        $students = $stmt_students->fetchAll(PDO::FETCH_ASSOC);

        // Get course sessions
        if ($month_filter) {
            $start_date = $month_filter . '-01';
            $end_date = date('Y-m-t', strtotime($start_date));

            $stmt_sessions = $pdo->prepare('
                SELECT id, created_at 
                FROM attendance_sessions 
                WHERE course_id = ? 
                AND created_at BETWEEN ? AND ? 
                ORDER BY created_at
            ');
            $stmt_sessions->execute([$course_id, $start_date, $end_date]);
        } else {
            $stmt_sessions = $pdo->prepare('
                SELECT id, created_at 
                FROM attendance_sessions 
                WHERE course_id = ?
                ORDER BY created_at
            ');
            $stmt_sessions->execute([$course_id]);
        }

        $sessions = $stmt_sessions->fetchAll(PDO::FETCH_ASSOC);

        // Attendance records
        if (!empty($sessions)) {
            $session_ids = array_column($sessions, 'id');
            $placeholders = implode(',', array_fill(0, count($session_ids), '?'));

            $stmt_records = $pdo->prepare("
                SELECT session_id, student_id 
                FROM attendance_records 
                WHERE session_id IN ($placeholders)
            ");
            $stmt_records->execute($session_ids);

            while ($record = $stmt_records->fetch(PDO::FETCH_ASSOC)) {
                $attendance_data[$record['student_id']][$record['session_id']] = 'Present';
            }
        }
    }

} catch (Exception $e) {
    $error = 'An error occurred while generating the report.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Attendance Report - Lecturer</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="stylesheet" href="css/dashboard.css">
</head>
<body>
<nav class="navbar navbar-expand-lg" style="background-color:#2fa360;">
    <div class="container">
        <a class="navbar-brand text-white" href="../../index.php">
            <img src="../../assets/logo.png" alt="EduTrack Logo" height="40">
        </a>
        <ul class="navbar-nav ms-auto">
            <li class="nav-item"><a class="nav-link text-white" href="../../index.php">Home</a></li>
            <li class="nav-item"><a class="nav-link text-white" href="../../logout.php">Logout</a></li>
        </ul>
    </div>
</nav>


<div class="container py-5">
    <a href="attendance_reports.php" class="btn btn-secondary back-link mb-3">
        <i class="fas fa-arrow-left"></i> Back to Reports
    </a>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>

    <?php elseif (!$course): ?>
        <div class="alert alert-info">Course not found.</div>

    <?php else: ?>

        <div class="mb-4">
            <h2 class="fw-bold">Attendance Report: <?= htmlspecialchars($course['name']) ?></h2>
            <p><strong>Course Code:</strong> <?= htmlspecialchars($course['course_code']) ?></p>
        </div>

        <!-- Month Filter -->
        <form method="GET" class="mb-3 no-print">
            <input type="hidden" name="course_id" value="<?= $course_id ?>">
            <label for="month">Select Month:</label>
            <input type="month" id="month" name="month" value="<?= htmlspecialchars($month_filter) ?>" class="form-control d-inline-block w-auto">
            <button type="submit" class="btn btn-primary">Filter</button>
        </form>

        <!-- Download / Print -->
        <div class="mb-3 no-print">
            <a href="download_report.php?course_id=<?= $course_id ?>&month=<?= urlencode($month_filter) ?>" class="btn btn-success">
                <i class="fas fa-download"></i> Download PDF
            </a>
        </div>

        <?php if (empty($sessions)): ?>
            <div class="alert alert-warning">No attendance sessions found for this course.</div>

        <?php elseif (empty($students)): ?>
            <div class="alert alert-warning">No students enrolled in this course.</div>

        <?php else: ?>

            <div class="table-responsive">
                <table class="table table-bordered table-striped align-middle text-center">
                    <thead class="table-dark">
                        <tr>
                            <th>Student Name</th>
                            <th>Student Number</th>
                            <?php foreach ($sessions as $session): ?>
                                <th>Session<br><?= (new DateTime($session['created_at']))->format('M d') ?></th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>

                    <tbody>
                        <?php foreach ($students as $student): ?>
                            <tr>
                                <td><?= htmlspecialchars($student['name']) ?></td>
                                <td><?= htmlspecialchars($student['student_number']) ?></td>
                                <?php foreach ($sessions as $session): ?>
                                    <td class="<?= isset($attendance_data[$student['user_id']][$session['id']]) ? 'present' : 'absent' ?>">
                                        <?= isset($attendance_data[$student['user_id']][$session['id']]) ? 'Present' : 'Absent' ?>
                                    </td>
                                <?php endforeach; ?>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>

                </table>
            </div>

        <?php endif; ?>

    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
<?php require_once '../../includes/footer.php'; ?>

</html>
