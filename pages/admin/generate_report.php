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
require_once '../../includes/admin_report_helper.php';
require_login();
require_role(['admin']);

if (!isset($_GET['course_id']) || !is_numeric($_GET['course_id'])) {
    header('Location: attendance_reports.php');
    exit();
}

$course_id = intval($_GET['course_id']);
$month_filter = admin_normalize_report_month($_GET['month'] ?? '');
$search_filter = admin_normalize_report_search($_GET['search'] ?? '');
$attendance_filter = admin_normalize_attendance_filter($_GET['attendance_filter'] ?? 'all');

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
    'matched_student_count' => 0,
    'overall_student_count' => 0,
];
$month_options = [];
$error = '';

try {
    $report = admin_fetch_attendance_report($pdo, $course_id, $month_filter, $search_filter, $attendance_filter);
    $course = $report['course'];
    $students = $report['students'];
    $sessions = $report['sessions'];
    $attendance_data = $report['attendance_map'];
    $student_stats = $report['student_stats'];
    $summary = $report['summary'];
    $month_options = admin_fetch_report_month_options($pdo, $course_id);

    if ($course === null) {
        $error = 'Course not found.';
    }

} catch (Exception $e) {
    $error = 'An error occurred while generating the report.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">

    <link rel="icon" type="image/png" href="<?= asset_url('assets/favicon.png') ?>">
<title>Attendance Report - Admin</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
<link rel="stylesheet" href="css/dashboard.css">
</head>
<body class="bg-light">

<?php require_once '../../includes/admin_navbar.php'; ?>

<div class="container py-5">
    <a href="attendance_reports.php" class="btn btn-secondary back-link mb-3">
        <i class="bi bi-arrow-left"></i> Back to Reports
    </a>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php elseif (!$course): ?>
        <div class="alert alert-info">Course not found.</div>
    <?php else: ?>
        <div class="mb-4">
            <h2 class="fw-bold">Attendance Report: <?= htmlspecialchars($course['name']) ?></h2>
            <p><strong>Course Code:</strong> <?= htmlspecialchars($course['course_code']) ?></p>
            <p><strong>Programme:</strong> <?= htmlspecialchars($course['programme_name'] ?? 'N/A') ?></p>
            <p><strong>Lecturer(s):</strong> <?= htmlspecialchars($course['lecturer_names'] ?? 'Not Assigned') ?></p>
        </div>

        <form method="GET" class="mb-3 no-print row g-3 align-items-end">
            <input type="hidden" name="course_id" value="<?= $course_id ?>">
            <div class="col-md-4">
                <label for="month" class="form-label">Month</label>
                <select id="month" name="month" class="form-select">
                    <option value="">All Available Months</option>
                    <?php foreach ($month_options as $month_option): ?>
                        <option value="<?= htmlspecialchars($month_option['value']) ?>" <?= $month_filter === $month_option['value'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($month_option['label']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4">
                <label for="search" class="form-label">Student Search</label>
                <input id="search" name="search" class="form-control" value="<?= htmlspecialchars($search_filter) ?>" placeholder="Name or student number">
            </div>
            <div class="col-md-4">
                <label for="attendance_filter" class="form-label">Attendance Filter</label>
                <select id="attendance_filter" name="attendance_filter" class="form-select">
                    <option value="all" <?= $attendance_filter === 'all' ? 'selected' : '' ?>>All Students</option>
                    <option value="at_risk" <?= $attendance_filter === 'at_risk' ? 'selected' : '' ?>>At Risk Under 75%</option>
                    <option value="perfect" <?= $attendance_filter === 'perfect' ? 'selected' : '' ?>>Perfect 100%</option>
                    <option value="absent_only" <?= $attendance_filter === 'absent_only' ? 'selected' : '' ?>>With Absences</option>
                </select>
            </div>
            <div class="col-12 d-flex gap-2">
                <button type="submit" class="btn btn-primary">Apply Filter</button>
                <a href="generate_report.php?course_id=<?= $course_id ?>" class="btn btn-outline-secondary">Reset</a>
            </div>
        </form>

        <div class="mb-3 no-print">
            <a href="download_report.php?course_id=<?= $course_id ?>&month=<?= urlencode($month_filter) ?>&search=<?= urlencode($search_filter) ?>&attendance_filter=<?= urlencode($attendance_filter) ?>" class="btn btn-success">
                <i class="bi bi-download"></i> Download PDF
            </a>
            <a href="download_report.php?course_id=<?= $course_id ?>&month=<?= urlencode($month_filter) ?>&search=<?= urlencode($search_filter) ?>&attendance_filter=<?= urlencode($attendance_filter) ?>&format=csv" class="btn btn-outline-success">
                <i class="bi bi-filetype-csv"></i> Export CSV
            </a>
            <a href="print_report.php?course_id=<?= $course_id ?>&month=<?= urlencode($month_filter) ?>&search=<?= urlencode($search_filter) ?>&attendance_filter=<?= urlencode($attendance_filter) ?>" class="btn btn-outline-secondary" target="_blank" rel="noopener noreferrer">
                <i class="bi bi-printer"></i> Print View
            </a>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-md-3"><div class="card"><div class="card-body"><strong>Students</strong><br><?= (int) $summary['student_count'] ?></div></div></div>
            <div class="col-md-3"><div class="card"><div class="card-body"><strong>Sessions</strong><br><?= (int) $summary['session_count'] ?></div></div></div>
            <div class="col-md-3"><div class="card"><div class="card-body"><strong>Attendance Marks</strong><br><?= (int) $summary['attendance_marks'] ?></div></div></div>
            <div class="col-md-3"><div class="card"><div class="card-body"><strong>Attendance Rate</strong><br><?= htmlspecialchars(number_format((float) $summary['attendance_rate'], 1)) ?>%</div></div></div>
        </div>

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
                                <th>Session<br><?= (new DateTime($session['created_at']))->format('M d') ?></th>
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

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>


