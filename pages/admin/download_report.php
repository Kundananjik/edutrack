<?php
require_once '../../includes/auth_check.php';
require_once '../../includes/db.php';
require_login();
require_role(['admin']);

// Include Dompdf
require_once '../../vendor/autoload.php';
use Dompdf\Dompdf;

if (!isset($_GET['course_id']) || !is_numeric($_GET['course_id'])) {
    header("Location: attendance_reports.php");
    exit();
}

$course_id = intval($_GET['course_id']);
$month_filter = $_GET['month'] ?? '';

$course = null;
$students = [];
$sessions = [];
$attendance_data = [];

try {
    // Get course
    $stmt_course = $pdo->prepare("SELECT name, course_code FROM courses WHERE id = ?");
    $stmt_course->execute([$course_id]);
    $course = $stmt_course->fetch(PDO::FETCH_ASSOC);
    if (!$course) die("Course not found.");

    // Get students
    $stmt_students = $pdo->prepare("
        SELECT u.id AS user_id, u.name, s.student_number
        FROM users u
        JOIN students s ON u.id = s.user_id
        JOIN enrollments e ON s.user_id = e.student_id
        WHERE e.course_id = ?
        ORDER BY u.name
    ");
    $stmt_students->execute([$course_id]);
    $students = $stmt_students->fetchAll(PDO::FETCH_ASSOC);

    // Get sessions filtered by month
    if ($month_filter) {
        $start_date = $month_filter . '-01';
        $end_date = date('Y-m-t', strtotime($start_date));

        $stmt_sessions = $pdo->prepare("
            SELECT id, created_at 
            FROM attendance_sessions 
            WHERE course_id = ? 
            AND created_at BETWEEN ? AND ? 
            ORDER BY created_at ASC
        ");
        $stmt_sessions->execute([$course_id, $start_date, $end_date]);
    } else {
        $stmt_sessions = $pdo->prepare("SELECT id, created_at FROM attendance_sessions WHERE course_id = ? ORDER BY created_at ASC");
        $stmt_sessions->execute([$course_id]);
    }
    $sessions = $stmt_sessions->fetchAll(PDO::FETCH_ASSOC);

    // Attendance records
    if (!empty($sessions)) {
        $session_ids = array_column($sessions, 'id');
        $placeholders = implode(',', array_fill(0, count($session_ids), '?'));
        $stmt_records = $pdo->prepare("SELECT session_id, student_id FROM attendance_records WHERE session_id IN ($placeholders)");
        $stmt_records->execute($session_ids);

        while ($record = $stmt_records->fetch(PDO::FETCH_ASSOC)) {
            $attendance_data[$record['student_id']][$record['session_id']] = 'Present';
        }
    }

} catch (Exception $e) {
    die("Error generating report.");
}

// Build HTML for PDF
$html = '<h2 style="text-align:center;">Attendance Report</h2>';
$html .= '<p><strong>Course:</strong> ' . htmlspecialchars($course['name']) . '<br>';
$html .= '<strong>Course Code:</strong> ' . htmlspecialchars($course['course_code']) . '</p>';

$html .= '<table border="1" cellpadding="5" cellspacing="0" style="width:100%; border-collapse: collapse; text-align:center;">';
$html .= '<thead><tr><th>Student Name</th><th>Student Number</th>';
foreach ($sessions as $session) {
    $html .= '<th>' . (new DateTime($session['created_at']))->format('M d') . '</th>';
}
$html .= '</tr></thead><tbody>';

foreach ($students as $student) {
    $html .= '<tr>';
    $html .= '<td>' . htmlspecialchars($student['name']) . '</td>';
    $html .= '<td>' . htmlspecialchars($student['student_number']) . '</td>';
    foreach ($sessions as $session) {
        $html .= '<td>' . (isset($attendance_data[$student['user_id']][$session['id']]) ? 'Present' : 'Absent') . '</td>';
    }
    $html .= '</tr>';
}

$html .= '</tbody></table>';

// Generate PDF
$dompdf = new Dompdf();
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'landscape');
$dompdf->render();

$filename = 'Attendance_Report_' . $course['course_code'] . '_' . ($month_filter ?: 'All') . '.pdf';
$dompdf->stream($filename, ["Attachment" => true]);
exit;
