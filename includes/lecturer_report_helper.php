<?php

function lecturer_normalize_report_month($month)
{
    $month = trim((string) $month);

    if ($month === '') {
        return '';
    }

    return preg_match('/^\d{4}-\d{2}$/', $month) ? $month : '';
}

function lecturer_build_report_month($year, $month)
{
    $year = (int) $year;
    $month = (int) $month;

    if ($year < 2000 || $year > 2100 || $month < 1 || $month > 12) {
        return '';
    }

    return sprintf('%04d-%02d', $year, $month);
}

function lecturer_normalize_report_search($search)
{
    return trim((string) $search);
}

function lecturer_normalize_report_attendance_filter($filter)
{
    $filter = trim((string) $filter);
    $allowed = ['all', 'at_risk', 'perfect', 'absent_only'];

    return in_array($filter, $allowed, true) ? $filter : 'all';
}

function lecturer_student_matches_filter(array $stats, $attendanceFilter)
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

function lecturer_fetch_dashboard_metrics(PDO $pdo, $lecturerId)
{
    $lecturerId = (int) $lecturerId;

    $metrics = [
        'courses' => 0,
        'students' => 0,
        'active_sessions' => 0,
        'attendance_today' => 0,
        'courses_with_sessions' => 0,
        'at_risk_students' => [],
        'at_risk_courses' => [],
        'active_session_courses' => [],
    ];

    $stmt = $pdo->prepare('SELECT COUNT(DISTINCT course_id) FROM lecturer_courses WHERE lecturer_id = ?');
    $stmt->execute([$lecturerId]);
    $metrics['courses'] = (int) $stmt->fetchColumn();

    $stmt = $pdo->prepare('
        SELECT COUNT(DISTINCT e.student_id)
        FROM enrollments e
        INNER JOIN lecturer_courses lc ON lc.course_id = e.course_id
        WHERE lc.lecturer_id = ?
    ');
    $stmt->execute([$lecturerId]);
    $metrics['students'] = (int) $stmt->fetchColumn();

    $stmt = $pdo->prepare('SELECT COUNT(*) FROM attendance_sessions WHERE lecturer_id = ? AND is_active = 1');
    $stmt->execute([$lecturerId]);
    $metrics['active_sessions'] = (int) $stmt->fetchColumn();

    $stmt = $pdo->prepare('
        SELECT COUNT(*)
        FROM attendance_records ar
        INNER JOIN attendance_sessions s ON s.id = ar.session_id
        WHERE s.lecturer_id = ?
          AND DATE(ar.signed_in_at) = CURDATE()
    ');
    $stmt->execute([$lecturerId]);
    $metrics['attendance_today'] = (int) $stmt->fetchColumn();

    $stmt = $pdo->prepare('
        SELECT COUNT(DISTINCT s.course_id)
        FROM attendance_sessions s
        WHERE s.lecturer_id = ?
    ');
    $stmt->execute([$lecturerId]);
    $metrics['courses_with_sessions'] = (int) $stmt->fetchColumn();

    $stmt = $pdo->prepare("
        SELECT
            u.id,
            u.name,
            s.student_number,
            ROUND((COUNT(ar.id) / NULLIF(COUNT(asess.id), 0)) * 100, 1) AS attendance_rate,
            COUNT(asess.id) AS total_sessions
        FROM students s
        INNER JOIN users u ON u.id = s.user_id
        INNER JOIN enrollments e ON e.student_id = s.user_id
        INNER JOIN lecturer_courses lc ON lc.course_id = e.course_id
        INNER JOIN attendance_sessions asess ON asess.course_id = e.course_id AND asess.lecturer_id = lc.lecturer_id
        LEFT JOIN attendance_records ar
            ON ar.session_id = asess.id
           AND ar.student_id = s.user_id
        WHERE lc.lecturer_id = ?
        GROUP BY u.id, u.name, s.student_number
        HAVING total_sessions > 0 AND attendance_rate < 75
        ORDER BY attendance_rate ASC, u.name ASC
        LIMIT 5
    ");
    $stmt->execute([$lecturerId]);
    $metrics['at_risk_students'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stmt = $pdo->prepare("
        SELECT
            c.id,
            c.name,
            c.course_code,
            ROUND(
                (COUNT(ar.id) / NULLIF(COUNT(DISTINCT e.student_id) * COUNT(DISTINCT asess.id), 0)) * 100,
                1
            ) AS attendance_rate,
            COUNT(DISTINCT e.student_id) AS student_count,
            COUNT(DISTINCT asess.id) AS session_count
        FROM courses c
        INNER JOIN lecturer_courses lc ON lc.course_id = c.id
        LEFT JOIN enrollments e ON e.course_id = c.id
        LEFT JOIN attendance_sessions asess ON asess.course_id = c.id AND asess.lecturer_id = lc.lecturer_id
        LEFT JOIN attendance_records ar ON ar.session_id = asess.id AND ar.student_id = e.student_id
        WHERE lc.lecturer_id = ?
        GROUP BY c.id, c.name, c.course_code
        HAVING session_count > 0 AND student_count > 0
        ORDER BY attendance_rate ASC, c.name ASC
        LIMIT 5
    ");
    $stmt->execute([$lecturerId]);
    $metrics['at_risk_courses'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stmt = $pdo->prepare("
        SELECT
            c.id,
            c.name,
            c.course_code,
            s.session_code,
            s.created_at
        FROM attendance_sessions s
        INNER JOIN courses c ON c.id = s.course_id
        WHERE s.lecturer_id = ?
          AND s.is_active = 1
        ORDER BY s.created_at DESC
        LIMIT 5
    ");
    $stmt->execute([$lecturerId]);
    $metrics['active_session_courses'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

    return $metrics;
}

function lecturer_fetch_attendance_report(PDO $pdo, $lecturerId, $courseId, $monthFilter = '', $search = '', $attendanceFilter = 'all')
{
    $lecturerId = (int) $lecturerId;
    $courseId = (int) $courseId;
    $monthFilter = lecturer_normalize_report_month($monthFilter);
    $search = lecturer_normalize_report_search($search);
    $attendanceFilter = lecturer_normalize_report_attendance_filter($attendanceFilter);

    $report = [
        'course' => null,
        'students' => [],
        'sessions' => [],
        'attendance_map' => [],
        'student_stats' => [],
        'session_trends' => [],
        'monthly_trends' => [],
        'insights' => [
            'best_session' => null,
            'worst_session' => null,
            'trend_direction' => 'steady',
        ],
        'summary' => [
            'student_count' => 0,
            'session_count' => 0,
            'attendance_marks' => 0,
            'attendance_rate' => 0,
        ],
        'month_filter' => $monthFilter,
        'search' => $search,
        'attendance_filter' => $attendanceFilter,
        'month_label' => $monthFilter !== '' ? date('F Y', strtotime($monthFilter . '-01')) : 'All Sessions',
    ];

    $stmtCourse = $pdo->prepare('
        SELECT c.id, c.name, c.course_code
        FROM courses c
        INNER JOIN lecturer_courses lc ON lc.course_id = c.id
        WHERE c.id = :course_id AND lc.lecturer_id = :lecturer_id
        LIMIT 1
    ');
    $stmtCourse->execute([
        'course_id' => $courseId,
        'lecturer_id' => $lecturerId,
    ]);
    $report['course'] = $stmtCourse->fetch(PDO::FETCH_ASSOC) ?: null;

    if ($report['course'] === null) {
        return $report;
    }

    $stmtStudents = $pdo->prepare('
        SELECT u.id AS user_id, u.name, s.student_number
        FROM enrollments e
        INNER JOIN students s ON s.user_id = e.student_id
        INNER JOIN users u ON u.id = s.user_id
        WHERE e.course_id = :course_id
        ORDER BY u.name ASC
    ');
    $stmtStudents->execute(['course_id' => $courseId]);
    $report['students'] = $stmtStudents->fetchAll(PDO::FETCH_ASSOC);

    if ($monthFilter !== '') {
        $startDate = $monthFilter . '-01 00:00:00';
        $endDate = date('Y-m-t 23:59:59', strtotime($startDate));

        $stmtSessions = $pdo->prepare('
            SELECT id, session_code, created_at
            FROM attendance_sessions
            WHERE course_id = :course_id
              AND created_at BETWEEN :start_date AND :end_date
            ORDER BY created_at ASC
        ');
        $stmtSessions->execute([
            'course_id' => $courseId,
            'start_date' => $startDate,
            'end_date' => $endDate,
        ]);
    } else {
        $stmtSessions = $pdo->prepare('
            SELECT id, session_code, created_at
            FROM attendance_sessions
            WHERE course_id = :course_id
            ORDER BY created_at ASC
        ');
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
            $studentId = (int) $record['student_id'];
            $sessionId = (int) $record['session_id'];
            $report['attendance_map'][$studentId][$sessionId] = true;
        }
    }

    $sessionCount = count($report['sessions']);
    $studentCount = count($report['students']);
    $attendanceMarks = 0;

    foreach ($report['students'] as $student) {
        $studentId = (int) $student['user_id'];
        $presentCount = 0;

        foreach ($report['sessions'] as $session) {
            $sessionId = (int) $session['id'];
            if (!empty($report['attendance_map'][$studentId][$sessionId])) {
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

    $overallStudents = count($report['students']);
    $bestSession = null;
    $worstSession = null;
    $monthlyBuckets = [];

    foreach ($report['sessions'] as $session) {
        $sessionId = (int) $session['id'];
        $presentCount = 0;

        foreach ($report['students'] as $student) {
            $studentId = (int) $student['user_id'];
            if (!empty($report['attendance_map'][$studentId][$sessionId])) {
                $presentCount++;
            }
        }

        $attendanceRate = $overallStudents > 0 ? round(($presentCount / $overallStudents) * 100, 1) : 0;
        $trendRow = [
            'session_id' => $sessionId,
            'session_code' => $session['session_code'] ?? '',
            'created_at' => $session['created_at'],
            'label' => date('M d, Y', strtotime($session['created_at'])),
            'present_count' => $presentCount,
            'absent_count' => max(0, $overallStudents - $presentCount),
            'attendance_rate' => $attendanceRate,
        ];
        $report['session_trends'][] = $trendRow;

        if ($bestSession === null || $attendanceRate > $bestSession['attendance_rate']) {
            $bestSession = $trendRow;
        }
        if ($worstSession === null || $attendanceRate < $worstSession['attendance_rate']) {
            $worstSession = $trendRow;
        }

        $monthKey = date('Y-m', strtotime($session['created_at']));
        if (!isset($monthlyBuckets[$monthKey])) {
            $monthlyBuckets[$monthKey] = [
                'label' => date('F Y', strtotime($session['created_at'])),
                'session_count' => 0,
                'present_total' => 0,
                'possible_total' => 0,
            ];
        }

        $monthlyBuckets[$monthKey]['session_count']++;
        $monthlyBuckets[$monthKey]['present_total'] += $presentCount;
        $monthlyBuckets[$monthKey]['possible_total'] += $overallStudents;
    }

    foreach ($monthlyBuckets as $bucket) {
        $report['monthly_trends'][] = [
            'label' => $bucket['label'],
            'session_count' => $bucket['session_count'],
            'attendance_rate' => $bucket['possible_total'] > 0 ? round(($bucket['present_total'] / $bucket['possible_total']) * 100, 1) : 0,
        ];
    }

    $report['insights']['best_session'] = $bestSession;
    $report['insights']['worst_session'] = $worstSession;

    if (count($report['session_trends']) >= 2) {
        $firstRate = (float) $report['session_trends'][0]['attendance_rate'];
        $lastRate = (float) $report['session_trends'][count($report['session_trends']) - 1]['attendance_rate'];
        if ($lastRate > $firstRate + 3) {
            $report['insights']['trend_direction'] = 'improving';
        } elseif ($lastRate < $firstRate - 3) {
            $report['insights']['trend_direction'] = 'declining';
        }
    }

    if ($search !== '' || $attendanceFilter !== 'all') {
        $filteredStudents = [];

        foreach ($report['students'] as $student) {
            $studentId = (int) $student['user_id'];
            $stats = $report['student_stats'][$studentId] ?? [
                'present_count' => 0,
                'absent_count' => 0,
                'attendance_rate' => 0,
            ];

            $matchesSearch = true;
            if ($search !== '') {
                $needle = mb_strtolower($search);
                $haystack = mb_strtolower($student['name'] . ' ' . $student['student_number']);
                $matchesSearch = mb_strpos($haystack, $needle) !== false;
            }

            if ($matchesSearch && lecturer_student_matches_filter($stats, $attendanceFilter)) {
                $filteredStudents[] = $student;
            }
        }

        $report['students'] = $filteredStudents;
    }

    $possibleMarks = $studentCount * $sessionCount;
    $filteredStudentCount = count($report['students']);
    $filteredAttendanceMarks = 0;

    foreach ($report['students'] as $student) {
        $studentId = (int) $student['user_id'];
        $filteredAttendanceMarks += (int) ($report['student_stats'][$studentId]['present_count'] ?? 0);
    }

    $filteredPossibleMarks = $filteredStudentCount * $sessionCount;
    $report['summary'] = [
        'student_count' => $filteredStudentCount,
        'session_count' => $sessionCount,
        'attendance_marks' => $filteredAttendanceMarks,
        'attendance_rate' => $filteredPossibleMarks > 0 ? round(($filteredAttendanceMarks / $filteredPossibleMarks) * 100, 1) : 0,
        'matched_student_count' => $filteredStudentCount,
        'overall_student_count' => $studentCount,
        'overall_attendance_marks' => $attendanceMarks,
        'overall_attendance_rate' => $possibleMarks > 0 ? round(($attendanceMarks / $possibleMarks) * 100, 1) : 0,
    ];

    return $report;
}

function lecturer_fetch_report_month_options(PDO $pdo, $lecturerId, $courseId)
{
    $lecturerId = (int) $lecturerId;
    $courseId = (int) $courseId;

    $stmt = $pdo->prepare('
        SELECT DISTINCT DATE_FORMAT(s.created_at, "%Y-%m") AS month_value
        FROM attendance_sessions s
        INNER JOIN lecturer_courses lc ON lc.course_id = s.course_id
        WHERE s.course_id = :course_id
          AND lc.lecturer_id = :lecturer_id
        ORDER BY month_value DESC
    ');
    $stmt->execute([
        'course_id' => $courseId,
        'lecturer_id' => $lecturerId,
    ]);

    $options = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $monthValue = lecturer_normalize_report_month($row['month_value'] ?? '');
        if ($monthValue === '') {
            continue;
        }

        $options[] = [
            'value' => $monthValue,
            'year' => (int) substr($monthValue, 0, 4),
            'month_number' => (int) substr($monthValue, 5, 2),
            'label' => date('F Y', strtotime($monthValue . '-01')),
        ];
    }

    return $options;
}
