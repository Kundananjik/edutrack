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
require_once '../../includes/lecturer_report_helper.php';
require_login();
require_role(['lecturer']);

if (!isset($_GET['course_id']) || !is_numeric($_GET['course_id'])) {
    header('Location: attendance_reports.php');
    exit();
}

// Logged-in lecturer ID
$lecturer_id = (int) $_SESSION['user_id'];
$course_id = (int) $_GET['course_id'];
$month_filter = lecturer_normalize_report_month($_GET['month'] ?? '');
$month_filter = $month_filter !== '' ? $month_filter : lecturer_build_report_month($_GET['year'] ?? '', $_GET['month_number'] ?? '');
$search_filter = lecturer_normalize_report_search($_GET['search'] ?? '');
$attendance_filter = lecturer_normalize_report_attendance_filter($_GET['attendance_filter'] ?? 'all');
$selected_month_number = $month_filter !== '' ? (int) substr($month_filter, 5, 2) : 0;
$selected_year = $month_filter !== '' ? (int) substr($month_filter, 0, 4) : (int) date('Y');
$available_report_months = [];

$course = null;
$students = [];
$sessions = [];
$attendance_data = [];
$student_stats = [];
$session_trends = [];
$monthly_trends = [];
$insights = [
    'best_session' => null,
    'worst_session' => null,
    'trend_direction' => 'steady',
];
$summary = [
    'student_count' => 0,
    'session_count' => 0,
    'attendance_marks' => 0,
    'attendance_rate' => 0,
];
$month_label = 'All Sessions';
$error = '';

try {
    $report = lecturer_fetch_attendance_report($pdo, $lecturer_id, $course_id, $month_filter, $search_filter, $attendance_filter);
    $course = $report['course'];
    $students = $report['students'];
    $sessions = $report['sessions'];
    $attendance_data = $report['attendance_map'];
    $student_stats = $report['student_stats'];
    $session_trends = $report['session_trends'];
    $monthly_trends = $report['monthly_trends'];
    $insights = $report['insights'];
    $summary = $report['summary'];
    $month_label = $report['month_label'];
    $available_report_months = lecturer_fetch_report_month_options($pdo, $lecturer_id, $course_id);

    if ($course === null) {
        $error = 'You are not assigned to this course.';
    }
} catch (Exception $e) {
    error_log('Error generating lecturer report: ' . $e->getMessage());
    $error = 'An error occurred while generating the report.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">

    <link rel="icon" type="image/png" href="<?= asset_url('assets/favicon.png') ?>">
<title>Attendance Report - Lecturer</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
<link rel="stylesheet" href="css/dashboard.css">
</head>
<body>
<?php require_once '../../includes/lecturer_navbar.php'; ?>


<div class="container py-5 dashboard-container report-page">
    <a href="attendance_reports.php" class="btn btn-secondary back-link mb-3">
        <i class="bi bi-arrow-left"></i> Back to Reports
    </a>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>

    <?php elseif (!$course): ?>
        <div class="alert alert-info">Course not found.</div>

    <?php else: ?>

        <div class="report-header mb-4">
            <div>
                <p class="report-overline mb-2">Attendance Workspace</p>
                <h2 class="fw-bold mb-2"><?= htmlspecialchars($course['name']) ?></h2>
                <p class="mb-1"><strong>Course Code:</strong> <?= htmlspecialchars($course['course_code']) ?></p>
                <p class="text-muted mb-0"><strong>Scope:</strong> <?= htmlspecialchars($month_label) ?></p>
            </div>
            <div class="report-header-actions no-print">
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
        </div>

        <form method="GET" class="report-filter-bar mb-4 no-print">
            <input type="hidden" name="course_id" value="<?= $course_id ?>">
            <div>
                <label for="month" class="form-label">Month</label>
                <select id="month" name="month" class="form-select">
                    <option value="">All Available Months</option>
                    <?php foreach ($available_report_months as $month_option): ?>
                        <option value="<?= htmlspecialchars($month_option['value']) ?>" <?= $month_filter === $month_option['value'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($month_option['label']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label for="search" class="form-label">Student Search</label>
                <input type="text" id="search" name="search" value="<?= htmlspecialchars($search_filter) ?>" class="form-control" placeholder="Name or student number">
            </div>
            <div>
                <label for="attendance_filter" class="form-label">Attendance Filter</label>
                <select id="attendance_filter" name="attendance_filter" class="form-select">
                    <option value="all" <?= $attendance_filter === 'all' ? 'selected' : '' ?>>All Students</option>
                    <option value="at_risk" <?= $attendance_filter === 'at_risk' ? 'selected' : '' ?>>At Risk Under 75%</option>
                    <option value="perfect" <?= $attendance_filter === 'perfect' ? 'selected' : '' ?>>Perfect 100%</option>
                    <option value="absent_only" <?= $attendance_filter === 'absent_only' ? 'selected' : '' ?>>With Absences</option>
                </select>
            </div>
            <div class="report-filter-actions">
                <button type="submit" class="btn btn-primary">Apply Filter</button>
                <a href="generate_report.php?course_id=<?= $course_id ?>" class="btn btn-outline-secondary">Reset</a>
            </div>
        </form>

        <?php if ($search_filter !== '' || $attendance_filter !== 'all'): ?>
            <div class="alert alert-info">
                Showing <?= (int) $summary['matched_student_count'] ?> of <?= (int) $summary['overall_student_count'] ?> students
                <?php if ($search_filter !== ''): ?>
                    matching "<strong><?= htmlspecialchars($search_filter) ?></strong>"
                <?php endif; ?>
                <?php if ($attendance_filter !== 'all'): ?>
                    with filter <strong><?= htmlspecialchars(str_replace('_', ' ', $attendance_filter)) ?></strong>
                <?php endif; ?>.
            </div>
        <?php endif; ?>

        <div class="report-summary-grid mb-4">
            <article class="report-summary-card">
                <span>Total Students</span>
                <strong><?= (int) $summary['student_count'] ?></strong>
            </article>
            <article class="report-summary-card">
                <span>Total Sessions</span>
                <strong><?= (int) $summary['session_count'] ?></strong>
            </article>
            <article class="report-summary-card">
                <span>Attendance Marks</span>
                <strong><?= (int) $summary['attendance_marks'] ?></strong>
            </article>
            <article class="report-summary-card">
                <span>Attendance Rate</span>
                <strong><?= htmlspecialchars(number_format((float) $summary['attendance_rate'], 1)) ?>%</strong>
            </article>
        </div>

        <?php if (!empty($session_trends)): ?>
            <section class="report-trends mb-4">
                <div class="report-trends-header mb-3">
                    <div>
                        <p class="report-overline mb-2">Per-Course Trends</p>
                        <h3 class="mb-1">Attendance movement over time</h3>
                        <p class="text-muted mb-0">Track turnout per session and watch the course trend across available months.</p>
                    </div>
                    <div class="trend-badge trend-<?= htmlspecialchars($insights['trend_direction']) ?>">
                        <?= htmlspecialchars(ucfirst($insights['trend_direction'])) ?> Trend
                    </div>
                </div>

                <div class="report-trend-insights mb-4">
                    <article class="trend-insight-card">
                        <span>Best Session</span>
                        <strong><?= htmlspecialchars($insights['best_session']['label'] ?? 'N/A') ?></strong>
                        <small><?= isset($insights['best_session']['attendance_rate']) ? htmlspecialchars(number_format((float) $insights['best_session']['attendance_rate'], 1)) . '% attendance' : 'No data' ?></small>
                    </article>
                    <article class="trend-insight-card">
                        <span>Lowest Session</span>
                        <strong><?= htmlspecialchars($insights['worst_session']['label'] ?? 'N/A') ?></strong>
                        <small><?= isset($insights['worst_session']['attendance_rate']) ? htmlspecialchars(number_format((float) $insights['worst_session']['attendance_rate'], 1)) . '% attendance' : 'No data' ?></small>
                    </article>
                    <article class="trend-insight-card">
                        <span>Monthly Buckets</span>
                        <strong><?= count($monthly_trends) ?></strong>
                        <small>Months with recorded attendance sessions</small>
                    </article>
                </div>

                <div class="report-chart-grid">
                    <article class="report-chart-card">
                        <h4>Session Attendance Rate</h4>
                        <div class="trend-bars">
                            <?php foreach ($session_trends as $trend): ?>
                                <div class="trend-bar-item">
                                    <div class="trend-bar-label"><?= htmlspecialchars(date('M d', strtotime($trend['created_at']))) ?></div>
                                    <div class="trend-bar-track">
                                        <div class="trend-bar-fill" style="width: <?= max(0, min(100, (float) $trend['attendance_rate'])) ?>%"></div>
                                    </div>
                                    <div class="trend-bar-value"><?= htmlspecialchars(number_format((float) $trend['attendance_rate'], 1)) ?>%</div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </article>

                    <article class="report-chart-card">
                        <h4>Monthly Trend</h4>
                        <?php if (!empty($monthly_trends)): ?>
                            <div class="trend-bars compact">
                                <?php foreach ($monthly_trends as $trend): ?>
                                    <div class="trend-bar-item">
                                        <div class="trend-bar-label"><?= htmlspecialchars($trend['label']) ?></div>
                                        <div class="trend-bar-track">
                                            <div class="trend-bar-fill alt" style="width: <?= max(0, min(100, (float) $trend['attendance_rate'])) ?>%"></div>
                                        </div>
                                        <div class="trend-bar-value"><?= htmlspecialchars(number_format((float) $trend['attendance_rate'], 1)) ?>%</div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <p class="text-muted mb-0">No monthly trend data available.</p>
                        <?php endif; ?>
                    </article>
                </div>
            </section>
        <?php endif; ?>

        <?php if (empty($sessions)): ?>
            <div class="alert alert-warning">No attendance sessions found for this course.</div>

        <?php elseif (empty($students)): ?>
            <div class="alert alert-warning">No students matched the active filters for this course.</div>

        <?php else: ?>

            <div class="table-responsive">
                <table class="table table-bordered table-striped align-middle text-center report-table">
                    <thead class="table-dark">
                        <tr>
                            <th>Student Name</th>
                            <th>Student Number</th>
                            <th>Present</th>
                            <th>Absent</th>
                            <th>Rate</th>
                            <?php foreach ($sessions as $session): ?>
                                <th>
                                    Session<br><?= (new DateTime($session['created_at']))->format('M d') ?>
                                    <?php if (!empty($session['session_code'])): ?>
                                        <small class="d-block text-light-emphasis"><?= htmlspecialchars($session['session_code']) ?></small>
                                    <?php endif; ?>
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

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
<?php require_once '../../includes/footer.php'; ?>

</html>


