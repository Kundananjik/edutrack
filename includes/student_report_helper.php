<?php

function student_normalize_report_month($month)
{
    $month = trim((string) $month);

    if ($month === '') {
        return '';
    }

    return preg_match('/^\d{4}-\d{2}$/', $month) ? $month : '';
}

function student_fetch_report_month_options(PDO $pdo, $studentId)
{
    $stmt = $pdo->prepare('
        SELECT DISTINCT DATE_FORMAT(s.created_at, "%Y-%m") AS month_value
        FROM attendance_sessions s
        INNER JOIN enrollments e ON e.course_id = s.course_id
        WHERE e.student_id = :student_id
        ORDER BY month_value DESC
    ');
    $stmt->execute(['student_id' => (int) $studentId]);

    $months = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $value = student_normalize_report_month($row['month_value'] ?? '');
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

function student_fetch_report_data(PDO $pdo, $studentId, $monthFilter = '', $courseFilter = 0)
{
    $studentId = (int) $studentId;
    $monthFilter = student_normalize_report_month($monthFilter);
    $courseFilter = (int) $courseFilter;

    $data = [
        'student' => null,
        'courses' => [],
        'course_stats' => [],
        'history' => [],
        'monthly_trends' => [],
        'insights' => [
            'top_risk_course' => null,
            'best_course' => null,
            'last_attended' => null,
        ],
        'summary' => [
            'course_count' => 0,
            'classes_attended' => 0,
            'classes_missed' => 0,
            'attendance_rate' => 0,
        ],
        'filters' => [
            'month' => $monthFilter,
            'course_id' => $courseFilter,
        ],
    ];

    $stmtStudent = $pdo->prepare('
        SELECT u.name, u.email, s.student_number, p.name AS programme_name
        FROM users u
        INNER JOIN students s ON s.user_id = u.id
        INNER JOIN programmes p ON p.id = s.programme_id
        WHERE u.id = :student_id
        LIMIT 1
    ');
    $stmtStudent->execute(['student_id' => $studentId]);
    $data['student'] = $stmtStudent->fetch(PDO::FETCH_ASSOC) ?: null;

    $stmtCourses = $pdo->prepare('
        SELECT c.id, c.name, c.course_code, c.class_schedule
        FROM courses c
        INNER JOIN enrollments e ON e.course_id = c.id
        WHERE e.student_id = :student_id
        ORDER BY c.name ASC
    ');
    $stmtCourses->execute(['student_id' => $studentId]);
    $data['courses'] = $stmtCourses->fetchAll(PDO::FETCH_ASSOC);

    $courseLookup = [];
    foreach ($data['courses'] as $course) {
        $courseId = (int) $course['id'];
        $courseLookup[$courseId] = $course;
        $data['course_stats'][$courseId] = [
            'course' => $course,
            'total_classes' => 0,
            'attended_classes' => 0,
            'missed_classes' => 0,
            'attendance_rate' => 0,
            'risk_level' => 'good',
        ];
    }

    $params = ['student_id' => $studentId];
    $where = ['e.student_id = :student_id'];

    if ($monthFilter !== '') {
        $params['start_date'] = $monthFilter . '-01 00:00:00';
        $params['end_date'] = date('Y-m-t 23:59:59', strtotime($params['start_date']));
        $where[] = 's.created_at BETWEEN :start_date AND :end_date';
    }

    if ($courseFilter > 0) {
        $params['course_id'] = $courseFilter;
        $where[] = 'c.id = :course_id';
    }

    $sql = '
        SELECT
            c.id AS course_id,
            c.name AS course_name,
            c.course_code,
            s.id AS session_id,
            s.session_code,
            s.created_at,
            ar.signed_in_at
        FROM attendance_sessions s
        INNER JOIN courses c ON c.id = s.course_id
        INNER JOIN enrollments e ON e.course_id = c.id
        LEFT JOIN attendance_records ar
            ON ar.session_id = s.id
           AND ar.student_id = e.student_id
        WHERE ' . implode(' AND ', $where) . '
        ORDER BY s.created_at DESC
    ';

    $stmtHistory = $pdo->prepare($sql);
    $stmtHistory->execute($params);

    $monthlyBuckets = [];
    while ($row = $stmtHistory->fetch(PDO::FETCH_ASSOC)) {
        $courseId = (int) $row['course_id'];
        if (!isset($data['course_stats'][$courseId])) {
            continue;
        }

        $present = !empty($row['signed_in_at']);
        $historyRow = [
            'course_id' => $courseId,
            'course_name' => $row['course_name'],
            'course_code' => $row['course_code'],
            'session_code' => $row['session_code'],
            'created_at' => $row['created_at'],
            'signed_in_at' => $row['signed_in_at'],
            'status' => $present ? 'Present' : 'Absent',
        ];
        $data['history'][] = $historyRow;

        $data['course_stats'][$courseId]['total_classes']++;
        if ($present) {
            $data['course_stats'][$courseId]['attended_classes']++;
            if ($data['insights']['last_attended'] === null) {
                $data['insights']['last_attended'] = $historyRow;
            }
        }

        $monthKey = date('Y-m', strtotime($row['created_at']));
        if (!isset($monthlyBuckets[$monthKey])) {
            $monthlyBuckets[$monthKey] = [
                'label' => date('F Y', strtotime($row['created_at'])),
                'total' => 0,
                'attended' => 0,
            ];
        }
        $monthlyBuckets[$monthKey]['total']++;
        if ($present) {
            $monthlyBuckets[$monthKey]['attended']++;
        }
    }

    foreach ($data['course_stats'] as $courseId => &$stats) {
        $stats['missed_classes'] = max(0, $stats['total_classes'] - $stats['attended_classes']);
        $stats['attendance_rate'] = $stats['total_classes'] > 0
            ? round(($stats['attended_classes'] / $stats['total_classes']) * 100, 1)
            : 0;
        if ($stats['attendance_rate'] < 50) {
            $stats['risk_level'] = 'high';
        } elseif ($stats['attendance_rate'] < 75) {
            $stats['risk_level'] = 'warning';
        }
    }
    unset($stats);

    foreach ($monthlyBuckets as $bucket) {
        $data['monthly_trends'][] = [
            'label' => $bucket['label'],
            'attendance_rate' => $bucket['total'] > 0 ? round(($bucket['attended'] / $bucket['total']) * 100, 1) : 0,
            'attended' => $bucket['attended'],
            'total' => $bucket['total'],
        ];
    }

    foreach ($data['course_stats'] as $stats) {
        if ($data['insights']['top_risk_course'] === null) {
            $data['insights']['top_risk_course'] = $stats;
        } elseif ((float) $stats['attendance_rate'] < (float) $data['insights']['top_risk_course']['attendance_rate']) {
            $data['insights']['top_risk_course'] = $stats;
        }

        if ($data['insights']['best_course'] === null) {
            $data['insights']['best_course'] = $stats;
        } elseif ((float) $stats['attendance_rate'] > (float) $data['insights']['best_course']['attendance_rate']) {
            $data['insights']['best_course'] = $stats;
        }
    }

    $visibleCourseStats = $data['course_stats'];
    if ($courseFilter > 0) {
        $visibleCourseStats = isset($data['course_stats'][$courseFilter]) ? [$courseFilter => $data['course_stats'][$courseFilter]] : [];
    }

    $totalClasses = 0;
    $attendedClasses = 0;
    foreach ($visibleCourseStats as $stats) {
        $totalClasses += (int) $stats['total_classes'];
        $attendedClasses += (int) $stats['attended_classes'];
    }

    $data['summary'] = [
        'course_count' => count($visibleCourseStats),
        'classes_attended' => $attendedClasses,
        'classes_missed' => max(0, $totalClasses - $attendedClasses),
        'attendance_rate' => $totalClasses > 0 ? round(($attendedClasses / $totalClasses) * 100, 1) : 0,
    ];

    return $data;
}
