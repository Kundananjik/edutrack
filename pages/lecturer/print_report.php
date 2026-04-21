<?php
require_once '../../includes/auth_check.php';
require_once '../../includes/db.php';
require_once '../../includes/lecturer_report_helper.php';
require_login();
require_role(['lecturer']); // CHANGED from admin → lecturer

if (!isset($_GET['course_id']) || !is_numeric($_GET['course_id'])) {
    header('Location: attendance_reports.php'); // lecturer reports page
    exit();
}

$course_id = intval($_GET['course_id']);
$month_filter = lecturer_normalize_report_month($_GET['month'] ?? '');
$month_filter = $month_filter !== '' ? $month_filter : lecturer_build_report_month($_GET['year'] ?? '', $_GET['month_number'] ?? '');
$search_filter = lecturer_normalize_report_search($_GET['search'] ?? '');
$attendance_filter = lecturer_normalize_report_attendance_filter($_GET['attendance_filter'] ?? 'all');
$available_report_months = [];

$course = null;
$students = [];
$sessions = [];
$attendance_data = [];
$student_stats = [];
$summary = [
    'student_count' => 0,
    'session_count' => 0,
    'attendance_marks' => 0,
    'attendance_rate' => 0,
];
$month_label = 'All Sessions';
$error = '';

try {
    $report = lecturer_fetch_attendance_report($pdo, (int) $_SESSION['user_id'], $course_id, $month_filter, $search_filter, $attendance_filter);
    $course = $report['course'];
    $students = $report['students'];
    $sessions = $report['sessions'];
    $attendance_data = $report['attendance_map'];
    $student_stats = $report['student_stats'];
    $summary = $report['summary'];
    $month_label = $report['month_label'];
    $available_report_months = lecturer_fetch_report_month_options($pdo, (int) $_SESSION['user_id'], $course_id);

    if ($course === null) {
        $error = 'Course not found or not assigned to you.';
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

    <link rel="icon" type="image/png" href="<?= asset_url('assets/favicon.png') ?>">
<title>Print Attendance Report - Lecturer</title>

<!-- Bootstrap -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

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
            <i class="bi bi-printer"></i> Print / Save PDF
        </button>
    </div>

    <!-- Month Filter -->
    <form method="GET" class="mb-3 no-print d-flex align-items-center gap-2">
        <input type="hidden" name="course_id" value="<?= $course_id ?>">
        <label for="month">Month:</label>
        <select id="month" name="month" class="form-select w-auto">
            <option value="">All Available Months</option>
            <?php foreach ($available_report_months as $month_option): ?>
                <option value="<?= htmlspecialchars($month_option['value']) ?>" <?= $month_filter === $month_option['value'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($month_option['label']) ?>
                </option>
            <?php endforeach; ?>
        </select>
        <input type="hidden" name="search" value="<?= htmlspecialchars($search_filter) ?>">
        <input type="hidden" name="attendance_filter" value="<?= htmlspecialchars($attendance_filter) ?>">
        <button type="submit" class="btn btn-secondary">Filter</button>
    </form>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>

    <?php elseif (!$course): ?>
        <div class="alert alert-info">Course not found or not assigned to you.</div>

    <?php else: ?>

        <p><strong>Course Code:</strong> <?= htmlspecialchars($course['course_code']) ?></p>
        <p><strong>Scope:</strong> <?= htmlspecialchars($month_label) ?></p>
        <p><strong>Student Search:</strong> <?= htmlspecialchars($search_filter !== '' ? $search_filter : 'None') ?></p>
        <p><strong>Attendance Filter:</strong> <?= htmlspecialchars(str_replace('_', ' ', $attendance_filter)) ?></p>
        <p><strong>Students:</strong> <?= (int) $summary['student_count'] ?> | <strong>Sessions:</strong> <?= (int) $summary['session_count'] ?> | <strong>Attendance Rate:</strong> <?= htmlspecialchars(number_format((float) $summary['attendance_rate'], 1)) ?>%</p>

        <?php if (empty($sessions)): ?>
            <div class="alert alert-warning">No attendance sessions for this month.</div>

        <?php elseif (empty($students)): ?>
            <div class="alert alert-warning">No students matched the active filters for this course.</div>

        <?php else: ?>

            <div class="table-responsive">
                <table class="table table-bordered table-striped align-middle text-center">
                    <thead class="table-dark">
                        <tr>
                            <th>Student Name</th>
                            <th>Student Number</th>
                            <th>Present</th>
                            <th>Absent</th>
                            <th>Rate</th>
                            <?php foreach ($sessions as $session): ?>
                                <th>
                                    Session<br>
                                    <?= (new DateTime($session['created_at']))->format('M d') ?>
                                </th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>

                    <tbody>
                        <?php foreach ($students as $student): ?>
                            <tr>
                                <td><?= htmlspecialchars($student['name']) ?></td>
                                <td><?= htmlspecialchars($student['student_number']) ?></td>
                                <td><?= (int) ($student_stats[$student['user_id']]['present_count'] ?? 0) ?></td>
                                <td><?= (int) ($student_stats[$student['user_id']]['absent_count'] ?? 0) ?></td>
                                <td><?= htmlspecialchars(number_format((float) ($student_stats[$student['user_id']]['attendance_rate'] ?? 0), 1)) ?>%</td>

                                <?php foreach ($sessions as $session): ?>
                                    <td class="<?= !empty($attendance_data[$student['user_id']][$session['id']]) ? 'present' : 'absent' ?>">
                                        <?= !empty($attendance_data[$student['user_id']][$session['id']]) ? 'Present' : 'Absent' ?>
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
</body>
</html>


