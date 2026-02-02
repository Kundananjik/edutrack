<?php
require_once '../../includes/auth_check.php';
require_once '../../includes/db.php';
require_login();
require_role(['admin']);

if (!isset($_GET['course_id']) || !is_numeric($_GET['course_id'])) {
    header('Location: attendance_reports.php');
    exit();
}

$course_id = intval($_GET['course_id']);
$month_filter = $_GET['month'] ?? '';

$course = null;
$students = [];
$sessions = [];
$attendance_data = [];
$error = '';

try {
    // Fetch course details
    $stmt_course = $pdo->prepare('SELECT name, course_code FROM courses WHERE id = ?');
    $stmt_course->execute([$course_id]);
    $course = $stmt_course->fetch(PDO::FETCH_ASSOC);

    if (!$course) {
        $error = 'Course not found.';
    } else {
        // Fetch enrolled students
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

        // Fetch attendance sessions with optional month filter
        if ($month_filter) {
            $start_date = $month_filter . '-01';
            $end_date = date('Y-m-t', strtotime($start_date));

            $stmt_sessions = $pdo->prepare('
                SELECT id, created_at 
                FROM attendance_sessions 
                WHERE course_id = ? 
                AND created_at BETWEEN ? AND ? 
                ORDER BY created_at ASC
            ');
            $stmt_sessions->execute([$course_id, $start_date, $end_date]);
        } else {
            $stmt_sessions = $pdo->prepare('SELECT id, created_at FROM attendance_sessions WHERE course_id = ? ORDER BY created_at ASC');
            $stmt_sessions->execute([$course_id]);
        }
        $sessions = $stmt_sessions->fetchAll(PDO::FETCH_ASSOC);

        // Fetch attendance records
        if (!empty($sessions)) {
            $session_ids = array_column($sessions, 'id');
            $placeholders = implode(',', array_fill(0, count($session_ids), '?'));
            $stmt_records = $pdo->prepare("SELECT session_id, student_id FROM attendance_records WHERE session_id IN ($placeholders)");
            $stmt_records->execute($session_ids);

            while ($record = $stmt_records->fetch(PDO::FETCH_ASSOC)) {
                $attendance_data[$record['student_id']][$record['session_id']] = 'Present';
            }
        }
    }
} catch (Exception $e) {
    error_log('Error generating print report: ' . $e->getMessage());
    $error = 'An error occurred while generating the report. Please try again later.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Print Attendance Report</title>

<!-- Bootstrap -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
body { font-size: 12px; }
table th, table td { vertical-align: middle !important; text-align: center; }
.present { color: green; font-weight: bold; }
.absent { color: red; font-weight: bold; }
@media print {
    .no-print { display: none !important; }
}
</style>
</head>
<body class="bg-white p-4">

<div class="container">
    <div class="d-flex justify-content-between mb-3 no-print">
        <h3>Attendance Report: <?= htmlspecialchars($course['name'] ?? '') ?></h3>
        <button onclick="window.print()" class="btn btn-primary">
            <i class="fas fa-print"></i> Print / Save PDF
        </button>
    </div>

    <!-- Month Filter -->
    <form method="GET" class="mb-3 no-print d-flex align-items-center gap-2">
        <input type="hidden" name="course_id" value="<?= $course_id ?>">
        <label for="month">Select Month:</label>
        <input type="month" id="month" name="month" value="<?= htmlspecialchars($month_filter) ?>" class="form-control w-auto">
        <button type="submit" class="btn btn-secondary">Filter</button>
    </form>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php elseif (!$course): ?>
        <div class="alert alert-info">Course not found.</div>
    <?php else: ?>
        <p><strong>Course Code:</strong> <?= htmlspecialchars($course['course_code']) ?></p>

        <?php if (empty($sessions)): ?>
            <div class="alert alert-warning">No attendance sessions for this month.</div>
        <?php elseif (empty($students)): ?>
            <div class="alert alert-warning">No students are currently enrolled in this course.</div>
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

<!-- Font Awesome -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>
</body>
</html>
