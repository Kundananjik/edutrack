<?php
require_once '../../includes/auth_check.php';
require_once '../../includes/db.php';
require_once '../../includes/student_report_helper.php';

require_login();
require_role(['student']);

$studentId = (int) ($_SESSION['user_id'] ?? 0);
$monthFilter = student_normalize_report_month($_GET['month'] ?? '');
$courseFilter = (int) ($_GET['course_id'] ?? 0);

$report = student_fetch_report_data($pdo, $studentId, $monthFilter, $courseFilter);
$student = $report['student'];

if (!$student) {
    die('Unable to generate report.');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Student Attendance Report</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { font-size: 12px; background: #fff; }
        table th, table td { vertical-align: middle !important; }
        @media print { .no-print { display: none !important; } }
    </style>
</head>
<body class="p-4">
<div class="container">
    <div class="d-flex justify-content-between align-items-start mb-4 no-print">
        <div>
            <h2>Student Attendance Report</h2>
            <p class="mb-1"><strong>Name:</strong> <?= htmlspecialchars($student['name']) ?></p>
            <p class="mb-1"><strong>Student Number:</strong> <?= htmlspecialchars($student['student_number']) ?></p>
            <p class="mb-0"><strong>Month Filter:</strong> <?= htmlspecialchars($monthFilter !== '' ? date('F Y', strtotime($monthFilter . '-01')) : 'All Available Months') ?></p>
        </div>
        <button onclick="window.print()" class="btn btn-success">Print / Save PDF</button>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-3"><div class="border rounded p-3"><strong>Courses</strong><br><?= (int) $report['summary']['course_count'] ?></div></div>
        <div class="col-md-3"><div class="border rounded p-3"><strong>Attended</strong><br><?= (int) $report['summary']['classes_attended'] ?></div></div>
        <div class="col-md-3"><div class="border rounded p-3"><strong>Missed</strong><br><?= (int) $report['summary']['classes_missed'] ?></div></div>
        <div class="col-md-3"><div class="border rounded p-3"><strong>Attendance Rate</strong><br><?= htmlspecialchars(number_format((float) $report['summary']['attendance_rate'], 1)) ?>%</div></div>
    </div>

    <h3>Course Summary</h3>
    <table class="table table-bordered table-striped mb-4">
        <thead>
            <tr>
                <th>Course</th>
                <th>Code</th>
                <th>Total Classes</th>
                <th>Attended</th>
                <th>Missed</th>
                <th>Rate</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($report['course_stats'] as $stats): ?>
                <?php if ($courseFilter > 0 && (int) $stats['course']['id'] !== $courseFilter) { continue; } ?>
                <tr>
                    <td><?= htmlspecialchars($stats['course']['name']) ?></td>
                    <td><?= htmlspecialchars($stats['course']['course_code']) ?></td>
                    <td><?= (int) $stats['total_classes'] ?></td>
                    <td><?= (int) $stats['attended_classes'] ?></td>
                    <td><?= (int) $stats['missed_classes'] ?></td>
                    <td><?= htmlspecialchars(number_format((float) $stats['attendance_rate'], 1)) ?>%</td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <h3>Attendance History</h3>
    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>Date</th>
                <th>Course</th>
                <th>Session Code</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($report['history'] as $item): ?>
                <tr>
                    <td><?= htmlspecialchars(date('Y-m-d H:i', strtotime($item['created_at']))) ?></td>
                    <td><?= htmlspecialchars($item['course_name'] . ' (' . $item['course_code'] . ')') ?></td>
                    <td><?= htmlspecialchars($item['session_code'] ?: 'N/A') ?></td>
                    <td><?= htmlspecialchars($item['status']) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
</body>
</html>
