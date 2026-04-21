<?php

require_once '../../includes/auth_check.php';
require_once '../../includes/db.php';
require_once '../../includes/lecturer_report_helper.php';
require_login();
require_role(['lecturer']);

require_once '../../vendor/autoload.php';
use Dompdf\Dompdf;

if (!isset($_GET['course_id']) || !is_numeric($_GET['course_id'])) {
    header('Location: attendance_reports.php');
    exit();
}

$course_id = intval($_GET['course_id']);
$lecturer_id = (int) $_SESSION['user_id'];
$month_filter = lecturer_normalize_report_month($_GET['month'] ?? '');
$search_filter = lecturer_normalize_report_search($_GET['search'] ?? '');
$attendance_filter = lecturer_normalize_report_attendance_filter($_GET['attendance_filter'] ?? 'all');
$format = strtolower(trim((string) ($_GET['format'] ?? 'pdf')));

$course = null;
$students = [];
$sessions = [];
$attendance_data = [];
$student_stats = [];
$summary = [];

try {
    $report = lecturer_fetch_attendance_report($pdo, $lecturer_id, $course_id, $month_filter, $search_filter, $attendance_filter);
    $course = $report['course'];
    $students = $report['students'];
    $sessions = $report['sessions'];
    $attendance_data = $report['attendance_map'];
    $student_stats = $report['student_stats'];
    $summary = $report['summary'];
} catch (Exception $e) {
    error_log('Error exporting lecturer report: ' . $e->getMessage());
    die('Error generating report.');
}

if ($course === null) {
    die('Access denied. You are not assigned to this course.');
}

if ($format === 'csv') {
    $filename = 'Attendance_Report_' . $course['course_code'] . '_' . ($month_filter ?: 'All') . '.csv';
    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');

    $output = fopen('php://output', 'w');
    fputcsv($output, ['Course', $course['name']]);
    fputcsv($output, ['Course Code', $course['course_code']]);
    fputcsv($output, ['Month Scope', $report['month_label']]);
    fputcsv($output, ['Student Search', $search_filter !== '' ? $search_filter : 'None']);
    fputcsv($output, ['Attendance Filter', str_replace('_', ' ', $attendance_filter)]);
    fputcsv($output, []);

    $header = ['Student Name', 'Student Number', 'Present', 'Absent', 'Attendance Rate'];
    foreach ($sessions as $session) {
        $header[] = (new DateTime($session['created_at']))->format('M d Y');
    }
    fputcsv($output, $header);

    foreach ($students as $student) {
        $studentId = (int) $student['user_id'];
        $row = [
            $student['name'],
            $student['student_number'],
            $student_stats[$studentId]['present_count'] ?? 0,
            $student_stats[$studentId]['absent_count'] ?? 0,
            ($student_stats[$studentId]['attendance_rate'] ?? 0) . '%',
        ];

        foreach ($sessions as $session) {
            $row[] = !empty($attendance_data[$studentId][$session['id']]) ? 'Present' : 'Absent';
        }

        fputcsv($output, $row);
    }

    fclose($output);
    exit;
}

$html = '<h2 style="text-align:center;">Attendance Report</h2>';
$html .= '<p><strong>Course:</strong> ' . htmlspecialchars($course['name']) . '<br>';
$html .= '<strong>Course Code:</strong> ' . htmlspecialchars($course['course_code']) . '<br>';
$html .= '<strong>Scope:</strong> ' . htmlspecialchars($report['month_label']) . '<br>';
$html .= '<strong>Student Search:</strong> ' . htmlspecialchars($search_filter !== '' ? $search_filter : 'None') . '<br>';
$html .= '<strong>Attendance Filter:</strong> ' . htmlspecialchars(str_replace('_', ' ', $attendance_filter)) . '<br>';
$html .= '<strong>Students:</strong> ' . (int) $summary['student_count'] . ' | ';
$html .= '<strong>Sessions:</strong> ' . (int) $summary['session_count'] . ' | ';
$html .= '<strong>Attendance Rate:</strong> ' . number_format((float) $summary['attendance_rate'], 1) . '%</p>';

$html .= '<table border="1" cellpadding="5" cellspacing="0" style="width:100%; border-collapse: collapse; text-align:center;">';
$html .= '<thead><tr><th>Student Name</th><th>Student Number</th><th>Present</th><th>Absent</th><th>Rate</th>';

foreach ($sessions as $session) {
    $html .= '<th>' . (new DateTime($session['created_at']))->format('M d') . '</th>';
}

$html .= '</tr></thead><tbody>';

foreach ($students as $student) {
    $studentId = (int) $student['user_id'];
    $html .= '<tr>';
    $html .= '<td>' . htmlspecialchars($student['name']) . '</td>';
    $html .= '<td>' . htmlspecialchars($student['student_number']) . '</td>';
    $html .= '<td>' . (int) ($student_stats[$studentId]['present_count'] ?? 0) . '</td>';
    $html .= '<td>' . (int) ($student_stats[$studentId]['absent_count'] ?? 0) . '</td>';
    $html .= '<td>' . number_format((float) ($student_stats[$studentId]['attendance_rate'] ?? 0), 1) . '%</td>';

    foreach ($sessions as $session) {
        $html .= '<td>' . (!empty($attendance_data[$studentId][$session['id']]) ? 'Present' : 'Absent') . '</td>';
    }

    $html .= '</tr>';
}

$html .= '</tbody></table>';

// PDF Generate
$dompdf = new Dompdf();
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'landscape');
$dompdf->render();

$filename = 'Attendance_Report_' . $course['course_code'] . '_' . ($month_filter ?: 'All') . '.pdf';

$dompdf->stream($filename, ['Attachment' => true]);
exit;
