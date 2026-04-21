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

$filename = 'Student_Attendance_' . preg_replace('/[^A-Za-z0-9_-]/', '_', $student['student_number']) . '.csv';
header('Content-Type: text/csv; charset=UTF-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');

$output = fopen('php://output', 'w');
fputcsv($output, ['Student', $student['name']]);
fputcsv($output, ['Student Number', $student['student_number']]);
fputcsv($output, ['Programme', $student['programme_name']]);
fputcsv($output, ['Month Filter', $monthFilter !== '' ? date('F Y', strtotime($monthFilter . '-01')) : 'All Available Months']);
fputcsv($output, []);
fputcsv($output, ['Course', 'Course Code', 'Total Classes', 'Attended', 'Missed', 'Attendance Rate']);

foreach ($report['course_stats'] as $stats) {
    if ($courseFilter > 0 && (int) $stats['course']['id'] !== $courseFilter) {
        continue;
    }

    fputcsv($output, [
        $stats['course']['name'],
        $stats['course']['course_code'],
        $stats['total_classes'],
        $stats['attended_classes'],
        $stats['missed_classes'],
        $stats['attendance_rate'] . '%',
    ]);
}

fputcsv($output, []);
fputcsv($output, ['Attendance History']);
fputcsv($output, ['Date', 'Course', 'Course Code', 'Session Code', 'Status']);

foreach ($report['history'] as $item) {
    fputcsv($output, [
        date('Y-m-d H:i', strtotime($item['created_at'])),
        $item['course_name'],
        $item['course_code'],
        $item['session_code'] ?: 'N/A',
        $item['status'],
    ]);
}

fclose($output);
exit;
