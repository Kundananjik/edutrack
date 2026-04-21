<?php

function admin_normalize_report_month($month)
{
    $month = trim((string) $month);

    if ($month === '') {
        return '';
    }

    return preg_match('/^\d{4}-\d{2}$/', $month) ? $month : '';
}

function admin_normalize_report_search($search)
{
    return trim((string) $search);
}

function admin_normalize_attendance_filter($filter)
{
    $filter = trim((string) $filter);
    $allowed = ['all', 'at_risk', 'perfect', 'absent_only'];

    return in_array($filter, $allowed, true) ? $filter : 'all';
}

function admin_student_matches_filter(array $stats, $attendanceFilter)
{
    switch ($attendanceFilter) {
        case 'at_risk':
            return (float) ($stats['attendance_rate'] ?? 0) < 75;
        case 'perfect':
            return (float) ($stats['attendance_rate'] ?? 0) >= 100;
        case 'absent_only':
            return (int) ($stats['absent_count'] ?? 0) > 0;
        case 'all':
        default:
            return true;
    }
}

function admin_fetch_dashboard_metrics(PDO $pdo)
{
    $metrics = [
        'students' => 0,
        'lecturers' => 0,
        'programmes' => 0,
        'courses' => 0,
        'enrollments' => 0,
        'active_sessions' => 0,
        'attendance_today' => 0,
        'at_risk_students' => [],
        'at_risk_courses' => [],
        'courses_without_lecturer' => [],
    ];

    $metrics['students'] = (int) $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'student'")->fetchColumn();
    $metrics['lecturers'] = (int) $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'lecturer'")->fetchColumn();
    $metrics['programmes'] = (int) $pdo->query('SELECT COUNT(*) FROM programmes')->fetchColumn();
    $metrics['courses'] = (int) $pdo->query('SELECT COUNT(*) FROM courses')->fetchColumn();
    $metrics['enrollments'] = (int) $pdo->query('SELECT COUNT(*) FROM enrollments')->fetchColumn();
    $metrics['active_sessions'] = (int) $pdo->query('SELECT COUNT(*) FROM attendance_sessions WHERE is_active = 1')->fetchColumn();
    $metrics['attendance_today'] = (int) $pdo->query('SELECT COUNT(*) FROM attendance_records WHERE DATE(signed_in_at) = CURDATE()')->fetchColumn();

    $metrics['at_risk_students'] = $pdo->query("
        SELECT
            u.id,
            u.name,
            s.student_number,
            ROUND(
                (COUNT(ar.id) / NULLIF(COUNT(asess.id), 0)) * 100,
                1
            ) AS attendance_rate,
            COUNT(asess.id) AS total_sessions
        FROM students s
        INNER JOIN users u ON u.id = s.user_id
        INNER JOIN enrollments e ON e.student_id = s.user_id
        INNER JOIN attendance_sessions asess ON asess.course_id = e.course_id
        LEFT JOIN attendance_records ar
            ON ar.session_id = asess.id
           AND ar.student_id = s.user_id
        GROUP BY u.id, u.name, s.student_number
        HAVING total_sessions > 0 AND attendance_rate < 75
        ORDER BY attendance_rate ASC, u.name ASC
        LIMIT 5
    ")->fetchAll(PDO::FETCH_ASSOC);

    $metrics['at_risk_courses'] = $pdo->query("
        SELECT
            c.id,
            c.name,
            c.course_code,
            p.name AS programme_name,
            ROUND(
                (COUNT(ar.id) / NULLIF(COUNT(DISTINCT e.student_id) * COUNT(DISTINCT asess.id), 0)) * 100,
                1
            ) AS attendance_rate,
            COUNT(DISTINCT e.student_id) AS student_count,
            COUNT(DISTINCT asess.id) AS session_count
        FROM courses c
        INNER JOIN programmes p ON p.id = c.programme_id
        LEFT JOIN enrollments e ON e.course_id = c.id
        LEFT JOIN attendance_sessions asess ON asess.course_id = c.id
        LEFT JOIN attendance_records ar ON ar.session_id = asess.id AND ar.student_id = e.student_id
        GROUP BY c.id, c.name, c.course_code, p.name
        HAVING session_count > 0 AND student_count > 0
        ORDER BY attendance_rate ASC, c.name ASC
        LIMIT 5
    ")->fetchAll(PDO::FETCH_ASSOC);

    $metrics['courses_without_lecturer'] = $pdo->query("
        SELECT c.id, c.name, c.course_code
        FROM courses c
        LEFT JOIN lecturer_courses lc ON lc.course_id = c.id
        WHERE lc.lecturer_id IS NULL
        ORDER BY c.name ASC
        LIMIT 5
    ")->fetchAll(PDO::FETCH_ASSOC);

    return $metrics;
}

function admin_fetch_report_catalog(PDO $pdo, $programmeId = 0, $lecturerId = 0, $riskFilter = 'all')
{
    $programmeId = (int) $programmeId;
    $lecturerId = (int) $lecturerId;
    $riskFilter = admin_normalize_attendance_filter($riskFilter);

    $sql = "
        SELECT
            c.id,
            c.name,
            c.course_code,
            p.name AS programme_name,
            GROUP_CONCAT(DISTINCT u.name ORDER BY u.name SEPARATOR ', ') AS lecturer_names,
            COUNT(DISTINCT e.student_id) AS student_count,
            COUNT(DISTINCT s.id) AS session_count,
            ROUND(
                (COUNT(ar.id) / NULLIF(COUNT(DISTINCT e.student_id) * COUNT(DISTINCT s.id), 0)) * 100,
                1
            ) AS attendance_rate
        FROM courses c
        INNER JOIN programmes p ON p.id = c.programme_id
        LEFT JOIN lecturer_courses lc ON lc.course_id = c.id
        LEFT JOIN users u ON u.id = lc.lecturer_id
        LEFT JOIN enrollments e ON e.course_id = c.id
        LEFT JOIN attendance_sessions s ON s.course_id = c.id
        LEFT JOIN attendance_records ar ON ar.session_id = s.id AND ar.student_id = e.student_id
    ";

    $conditions = [];
    $params = [];
    if ($programmeId > 0) {
        $conditions[] = 'c.programme_id = :programme_id';
        $params['programme_id'] = $programmeId;
    }
    if ($lecturerId > 0) {
        $conditions[] = 'lc.lecturer_id = :lecturer_id';
        $params['lecturer_id'] = $lecturerId;
    }

    if ($conditions !== []) {
        $sql .= ' WHERE ' . implode(' AND ', $conditions);
    }

    $sql .= ' GROUP BY c.id, c.name, c.course_code, p.name';

    if ($riskFilter === 'at_risk') {
        $sql .= ' HAVING attendance_rate < 75';
    } elseif ($riskFilter === 'perfect') {
        $sql .= ' HAVING attendance_rate >= 100';
    } elseif ($riskFilter === 'absent_only') {
        $sql .= ' HAVING attendance_rate < 100';
    }

    $sql .= ' ORDER BY attendance_rate ASC, c.name ASC';

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function admin_fetch_attendance_report(PDO $pdo, $courseId, $monthFilter = '', $search = '', $attendanceFilter = 'all')
{
    $courseId = (int) $courseId;
    $monthFilter = admin_normalize_report_month($monthFilter);
    $search = admin_normalize_report_search($search);
    $attendanceFilter = admin_normalize_attendance_filter($attendanceFilter);

    $report = [
        'course' => null,
        'students' => [],
        'sessions' => [],
        'attendance_map' => [],
        'student_stats' => [],
        'summary' => [
            'student_count' => 0,
            'session_count' => 0,
            'attendance_marks' => 0,
            'attendance_rate' => 0,
            'matched_student_count' => 0,
            'overall_student_count' => 0,
        ],
        'month_filter' => $monthFilter,
        'search' => $search,
        'attendance_filter' => $attendanceFilter,
        'month_label' => $monthFilter !== '' ? date('F Y', strtotime($monthFilter . '-01')) : 'All Sessions',
    ];

    $stmtCourse = $pdo->prepare("
        SELECT c.id, c.name, c.course_code, p.name AS programme_name,
               GROUP_CONCAT(DISTINCT u.name ORDER BY u.name SEPARATOR ', ') AS lecturer_names
        FROM courses c
        INNER JOIN programmes p ON p.id = c.programme_id
        LEFT JOIN lecturer_courses lc ON lc.course_id = c.id
        LEFT JOIN users u ON u.id = lc.lecturer_id
        WHERE c.id = :course_id
        GROUP BY c.id, c.name, c.course_code, p.name
        LIMIT 1
    ");
    $stmtCourse->execute(['course_id' => $courseId]);
    $report['course'] = $stmtCourse->fetch(PDO::FETCH_ASSOC) ?: null;

    if ($report['course'] === null) {
        return $report;
    }

    $stmtStudents = $pdo->prepare("
        SELECT u.id AS user_id, u.name, s.student_number
        FROM enrollments e
        INNER JOIN students s ON s.user_id = e.student_id
        INNER JOIN users u ON u.id = s.user_id
        WHERE e.course_id = :course_id
        ORDER BY u.name ASC
    ");
    $stmtStudents->execute(['course_id' => $courseId]);
    $report['students'] = $stmtStudents->fetchAll(PDO::FETCH_ASSOC);

    if ($monthFilter !== '') {
        $startDate = $monthFilter . '-01 00:00:00';
        $endDate = date('Y-m-t 23:59:59', strtotime($startDate));
        $stmtSessions = $pdo->prepare("
            SELECT id, session_code, created_at
            FROM attendance_sessions
            WHERE course_id = :course_id
              AND created_at BETWEEN :start_date AND :end_date
            ORDER BY created_at ASC
        ");
        $stmtSessions->execute([
            'course_id' => $courseId,
            'start_date' => $startDate,
            'end_date' => $endDate,
        ]);
    } else {
        $stmtSessions = $pdo->prepare("
            SELECT id, session_code, created_at
            FROM attendance_sessions
            WHERE course_id = :course_id
            ORDER BY created_at ASC
        ");
        $stmtSessions->execute(['course_id' => $courseId]);
    }

    $report['sessions'] = $stmtSessions->fetchAll(PDO::FETCH_ASSOC);
    $sessionIds = array_column($report['sessions'], 'id');
    if ($sessionIds !== []) {
        $placeholders = implode(',', array_fill(0, count($sessionIds), '?'));
        $stmtRecords = $pdo->prepare("
            SELECT session_id, student_id
            FROM attendance_records
            WHERE session_id IN ($placeholders)
        ");
        $stmtRecords->execute($sessionIds);
        while ($record = $stmtRecords->fetch(PDO::FETCH_ASSOC)) {
            $report['attendance_map'][(int) $record['student_id']][(int) $record['session_id']] = true;
        }
    }

    $sessionCount = count($report['sessions']);
    $overallStudentCount = count($report['students']);
    $attendanceMarks = 0;

    foreach ($report['students'] as $student) {
        $studentId = (int) $student['user_id'];
        $presentCount = 0;
        foreach ($report['sessions'] as $session) {
            if (!empty($report['attendance_map'][$studentId][(int) $session['id']])) {
                $presentCount++;
            }
        }
        $absentCount = max(0, $sessionCount - $presentCount);
        $attendanceMarks += $presentCount;
        $report['student_stats'][$studentId] = [
            'present_count' => $presentCount,
            'absent_count' => $absentCount,
            'attendance_rate' => $sessionCount > 0 ? round(($presentCount / $sessionCount) * 100, 1) : 0,
        ];
    }

    if ($search !== '' || $attendanceFilter !== 'all') {
        $filteredStudents = [];
        foreach ($report['students'] as $student) {
            $studentId = (int) $student['user_id'];
            $stats = $report['student_stats'][$studentId];
            $matchesSearch = true;
            if ($search !== '') {
                $needle = mb_strtolower($search);
                $haystack = mb_strtolower($student['name'] . ' ' . $student['student_number']);
                $matchesSearch = mb_strpos($haystack, $needle) !== false;
            }
            if ($matchesSearch && admin_student_matches_filter($stats, $attendanceFilter)) {
                $filteredStudents[] = $student;
            }
        }
        $report['students'] = $filteredStudents;
    }

    $filteredStudentCount = count($report['students']);
    $filteredAttendanceMarks = 0;
    foreach ($report['students'] as $student) {
        $filteredAttendanceMarks += (int) $report['student_stats'][(int) $student['user_id']]['present_count'];
    }
    $possibleMarks = $filteredStudentCount * $sessionCount;
    $overallPossibleMarks = $overallStudentCount * $sessionCount;

    $report['summary'] = [
        'student_count' => $filteredStudentCount,
        'session_count' => $sessionCount,
        'attendance_marks' => $filteredAttendanceMarks,
        'attendance_rate' => $possibleMarks > 0 ? round(($filteredAttendanceMarks / $possibleMarks) * 100, 1) : 0,
        'matched_student_count' => $filteredStudentCount,
        'overall_student_count' => $overallStudentCount,
        'overall_attendance_rate' => $overallPossibleMarks > 0 ? round(($attendanceMarks / $overallPossibleMarks) * 100, 1) : 0,
    ];

    return $report;
}

function admin_fetch_report_filter_options(PDO $pdo)
{
    return [
        'programmes' => $pdo->query('SELECT id, name FROM programmes ORDER BY name ASC')->fetchAll(PDO::FETCH_ASSOC),
        'lecturers' => $pdo->query("SELECT id, name FROM users WHERE role = 'lecturer' ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC),
    ];
}

function admin_fetch_report_month_options(PDO $pdo, $courseId)
{
    $stmt = $pdo->prepare("
        SELECT DISTINCT DATE_FORMAT(created_at, '%Y-%m') AS month_value
        FROM attendance_sessions
        WHERE course_id = :course_id
        ORDER BY month_value DESC
    ");
    $stmt->execute(['course_id' => (int) $courseId]);

    $months = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $value = admin_normalize_report_month($row['month_value'] ?? '');
        if ($value === '') {
            continue;
        }
        $months[] = [
            'value' => $value,
            'label' => date('F Y', strtotime($value . '-01')),
        ];
    }
    return $months;
}

function admin_fetch_audit_snapshot(PDO $pdo)
{
    $audit = [
        'active_sessions' => [],
        'sessions_without_attendance' => [],
        'courses_without_lecturer' => [],
        'inactive_students_with_enrollments' => [],
    ];

    $audit['active_sessions'] = $pdo->query("
        SELECT s.id, s.session_code, s.created_at, c.name AS course_name, u.name AS lecturer_name
        FROM attendance_sessions s
        INNER JOIN courses c ON c.id = s.course_id
        INNER JOIN users u ON u.id = s.lecturer_id
        WHERE s.is_active = 1
        ORDER BY s.created_at DESC
        LIMIT 10
    ")->fetchAll(PDO::FETCH_ASSOC);

    $audit['sessions_without_attendance'] = $pdo->query("
        SELECT s.id, s.session_code, s.created_at, c.name AS course_name
        FROM attendance_sessions s
        INNER JOIN courses c ON c.id = s.course_id
        LEFT JOIN attendance_records ar ON ar.session_id = s.id
        GROUP BY s.id, s.session_code, s.created_at, c.name
        HAVING COUNT(ar.id) = 0
        ORDER BY s.created_at DESC
        LIMIT 10
    ")->fetchAll(PDO::FETCH_ASSOC);

    $audit['courses_without_lecturer'] = $pdo->query("
        SELECT c.id, c.name, c.course_code, p.name AS programme_name
        FROM courses c
        INNER JOIN programmes p ON p.id = c.programme_id
        LEFT JOIN lecturer_courses lc ON lc.course_id = c.id
        WHERE lc.lecturer_id IS NULL
        ORDER BY c.name ASC
        LIMIT 10
    ")->fetchAll(PDO::FETCH_ASSOC);

    $audit['inactive_students_with_enrollments'] = $pdo->query("
        SELECT u.id, u.name, s.student_number, COUNT(e.id) AS enrollment_count
        FROM users u
        INNER JOIN students s ON s.user_id = u.id
        INNER JOIN enrollments e ON e.student_id = u.id
        WHERE u.status <> 'active'
        GROUP BY u.id, u.name, s.student_number
        ORDER BY u.name ASC
        LIMIT 10
    ")->fetchAll(PDO::FETCH_ASSOC);

    return $audit;
}
