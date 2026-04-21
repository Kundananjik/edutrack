<?php
// Preload (auto-locate includes/preload.php)
$__et = __DIR__;
for ($__i = 0;$__i < 6;$__i++) {
    $__p = $__et . '/includes/preload.php';
    if (file_exists($__p)) {
        require_once $__p;
        break;
    }
    $__et = dirname($__et);
}
unset($__et,$__i,$__p);

require_once '../../includes/auth_check.php';
require_once '../../includes/config.php';
require_once '../../includes/db.php';
require_once '../../includes/csrf.php';
require_once '../../includes/student_report_helper.php';

require_login();
require_role(['student']);

$user_id = (int) ($_SESSION['user_id'] ?? 0);
if ($user_id <= 0) {
    header('Location: ../../auth/login.php');
    exit;
}

$month_filter = student_normalize_report_month($_GET['month'] ?? '');
$course_filter = (int) ($_GET['course_id'] ?? 0);

try {
    $report = student_fetch_report_data($pdo, $user_id, $month_filter, $course_filter);
    $student = $report['student'];
    $courses = $report['courses'];
    $course_stats = $report['course_stats'];
    $history = $report['history'];
    $monthly_trends = $report['monthly_trends'];
    $insights = $report['insights'];
    $summary = $report['summary'];
    $available_months = student_fetch_report_month_options($pdo, $user_id);

    if (!$student) {
        throw new RuntimeException('Student information not found.');
    }
} catch (Throwable $e) {
    error_log('Student dashboard error: ' . $e->getMessage());
    $student = null;
    $courses = [];
    $course_stats = [];
    $history = [];
    $monthly_trends = [];
    $insights = [
        'top_risk_course' => null,
        'best_course' => null,
        'last_attended' => null,
    ];
    $summary = [
        'course_count' => 0,
        'classes_attended' => 0,
        'classes_missed' => 0,
        'attendance_rate' => 0,
    ];
    $available_months = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="icon" type="image/png" href="<?= asset_url('assets/favicon.png') ?>">
    <meta name="csrf-token" content="<?= htmlspecialchars(get_csrf_token()) ?>">
    <title>Student Dashboard - EduTrack</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="css/student.css">
    <script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
</head>
<body>

<?php require_once '../../includes/student_navbar.php'; ?>

<div class="container my-5 student-dashboard">
    <h1 class="mb-4">Welcome, <?= htmlspecialchars($student['name'] ?? 'Student'); ?>!</h1>

    <section class="student-hero mb-4">
        <div>
            <p class="student-overline mb-2">Attendance Overview</p>
            <h2 class="mb-2">Your attendance at a glance</h2>
            <p class="mb-0">Track your classes, check risk areas, filter by month, and export your personal attendance report.</p>
        </div>
        <div class="student-hero-chip">
            <span><?= htmlspecialchars(number_format((float) $summary['attendance_rate'], 1)) ?>%</span>
            <small>Overall attendance</small>
        </div>
    </section>

    <?php if (!empty($insights['top_risk_course']) && (float) $insights['top_risk_course']['attendance_rate'] < 75): ?>
        <section class="student-alert-banner mb-4">
            <div>
                <p class="student-overline mb-2">Needs Attention</p>
                <h3 class="mb-1"><?= htmlspecialchars($insights['top_risk_course']['course']['name']) ?> is your lowest attendance course</h3>
                <p class="mb-0">Current attendance is <?= htmlspecialchars(number_format((float) $insights['top_risk_course']['attendance_rate'], 1)) ?>%. Prioritize upcoming sessions for this course.</p>
            </div>
            <span class="student-risk-pill <?= $insights['top_risk_course']['risk_level'] === 'high' ? 'risk-high' : 'risk-warning' ?>">
                <?= htmlspecialchars(number_format((float) $insights['top_risk_course']['attendance_rate'], 1)) ?>%
            </span>
        </section>
    <?php endif; ?>

    <section class="student-summary-grid mb-5">
        <article class="student-summary-card">
            <span>Courses In Scope</span>
            <strong><?= (int) $summary['course_count'] ?></strong>
        </article>
        <article class="student-summary-card">
            <span>Classes Attended</span>
            <strong><?= (int) $summary['classes_attended'] ?></strong>
        </article>
        <article class="student-summary-card">
            <span>Classes Missed</span>
            <strong><?= (int) $summary['classes_missed'] ?></strong>
        </article>
        <article class="student-summary-card">
            <span>Attendance Rate</span>
            <strong><?= htmlspecialchars(number_format((float) $summary['attendance_rate'], 1)) ?>%</strong>
        </article>
    </section>

    <section class="student-insight-grid mb-5">
        <article class="student-insight-card">
            <span>Most At-Risk Course</span>
            <strong><?= htmlspecialchars($insights['top_risk_course']['course']['course_code'] ?? 'N/A') ?></strong>
            <small>
                <?= !empty($insights['top_risk_course']) ? htmlspecialchars($insights['top_risk_course']['course']['name']) . ' • ' . htmlspecialchars(number_format((float) $insights['top_risk_course']['attendance_rate'], 1)) . '%' : 'No attendance data yet' ?>
            </small>
        </article>
        <article class="student-insight-card">
            <span>Best Course</span>
            <strong><?= htmlspecialchars($insights['best_course']['course']['course_code'] ?? 'N/A') ?></strong>
            <small>
                <?= !empty($insights['best_course']) ? htmlspecialchars($insights['best_course']['course']['name']) . ' • ' . htmlspecialchars(number_format((float) $insights['best_course']['attendance_rate'], 1)) . '%' : 'No attendance data yet' ?>
            </small>
        </article>
        <article class="student-insight-card">
            <span>Last Attended Class</span>
            <strong><?= htmlspecialchars($insights['last_attended']['course_code'] ?? 'N/A') ?></strong>
            <small>
                <?= !empty($insights['last_attended']) ? htmlspecialchars(date('M d, Y H:i', strtotime($insights['last_attended']['created_at']))) : 'No successful sign-ins yet' ?>
            </small>
        </article>
    </section>

    <section class="student-filter-bar mb-5">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-4">
                <label for="month" class="form-label">Month</label>
                <select id="month" name="month" class="form-select">
                    <option value="">All Available Months</option>
                    <?php foreach ($available_months as $month): ?>
                        <option value="<?= htmlspecialchars($month['value']) ?>" <?= $month_filter === $month['value'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($month['label']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4">
                <label for="course_id" class="form-label">Course</label>
                <select id="course_id" name="course_id" class="form-select">
                    <option value="0">All Courses</option>
                    <?php foreach ($courses as $course): ?>
                        <option value="<?= (int) $course['id'] ?>" <?= $course_filter === (int) $course['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($course['name'] . ' (' . $course['course_code'] . ')') ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4 d-flex gap-2">
                <button type="submit" class="btn btn-success">Apply Filter</button>
                <a href="dashboard.php" class="btn btn-outline-secondary">Reset</a>
            </div>
        </form>
        <div class="student-export-actions mt-3">
            <a href="download_report.php?month=<?= urlencode($month_filter) ?>&course_id=<?= $course_filter ?>" class="btn btn-outline-success">
                <i class="bi bi-filetype-csv me-2"></i>Export CSV
            </a>
            <a href="print_report.php?month=<?= urlencode($month_filter) ?>&course_id=<?= $course_filter ?>" target="_blank" rel="noopener noreferrer" class="btn btn-outline-secondary">
                <i class="bi bi-printer me-2"></i>Print View
            </a>
        </div>
    </section>

    <section id="take-attendance" class="mb-5">
        <div class="student-qr-panel">
            <div class="student-qr-copy">
                <p class="student-overline mb-2">QR Attendance</p>
                <h2>Scan to mark attendance</h2>
                <p>Use the live scanner below with the lecturer’s QR code. Attendance is now QR-only, so wait for the session QR to appear before scanning.</p>
                <ul class="student-qr-steps">
                    <li>Open the scanner and allow camera access.</li>
                    <li>Point your camera at the lecturer’s QR code.</li>
                    <li>Wait for the success message before leaving the page.</li>
                </ul>
            </div>
            <div class="student-qr-scanner">
                <div id="qr-reader" class="mb-3"></div>
                <div id="qr-result" class="alert d-none mb-0" role="status" aria-live="polite"></div>
            </div>
        </div>
    </section>

    <section id="summary" class="mb-5">
        <h2>Course Attendance Summary</h2>
        <?php if (empty($course_stats)): ?>
            <div class="student-empty-state">
                <h3>No course attendance yet</h3>
                <p>No attendance records matched your current filters. Try resetting the filters or wait until your lecturer starts sessions for your courses.</p>
            </div>
        <?php else: ?>
        <div class="student-course-grid">
            <?php foreach ($course_stats as $stats): ?>
                <?php
                if ($course_filter > 0 && (int) $stats['course']['id'] !== $course_filter) {
                    continue;
                }
                $riskClass = $stats['risk_level'] === 'high' ? 'risk-high' : ($stats['risk_level'] === 'warning' ? 'risk-warning' : 'risk-good');
                ?>
                <article class="card student-course-card <?= $riskClass ?>">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start gap-3">
                            <div>
                                <p class="student-overline mb-2"><?= htmlspecialchars($stats['course']['course_code']) ?></p>
                                <h5 class="card-title mb-1"><?= htmlspecialchars($stats['course']['name']) ?></h5>
                            </div>
                            <span class="student-risk-pill <?= $riskClass ?>">
                                <?= $stats['risk_level'] === 'high' ? 'High Risk' : ($stats['risk_level'] === 'warning' ? 'Watchlist' : 'On Track') ?>
                            </span>
                        </div>
                        <p class="mt-3 mb-2">Attended <?= (int) $stats['attended_classes'] ?> of <?= (int) $stats['total_classes'] ?> classes.</p>
                        <div class="progress mb-2">
                            <div class="progress-bar" role="progressbar" style="width: <?= max(0, min(100, (float) $stats['attendance_rate'])) ?>%;" aria-valuenow="<?= (float) $stats['attendance_rate'] ?>" aria-valuemin="0" aria-valuemax="100">
                                <?= htmlspecialchars(number_format((float) $stats['attendance_rate'], 1)) ?>%
                            </div>
                        </div>
                        <div class="student-course-metrics">
                            <span>Present: <?= (int) $stats['attended_classes'] ?></span>
                            <span>Missed: <?= (int) $stats['missed_classes'] ?></span>
                        </div>
                        <?php if ($stats['attendance_rate'] < 75 && $stats['total_classes'] > 0): ?>
                            <div class="alert alert-warning mt-3 mb-0">
                                Your attendance in this course is below 75%. Prioritize upcoming classes.
                            </div>
                        <?php endif; ?>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </section>

    <section class="student-trend-section mb-5">
        <div class="student-section-head">
            <div>
                <p class="student-overline mb-2">Personal Trends</p>
                <h2 class="mb-1">Attendance patterns over time</h2>
                <p class="mb-0 text-muted">Use these quick visuals to spot whether your attendance is improving month by month.</p>
            </div>
        </div>
        <?php if (empty($monthly_trends)): ?>
            <div class="student-empty-state">
                <h3>No monthly trend data yet</h3>
                <p>Once your attendance sessions are recorded, your monthly trend will appear here.</p>
            </div>
        <?php else: ?>
            <div class="student-trend-bars">
                <?php foreach ($monthly_trends as $trend): ?>
                    <div class="student-trend-row">
                        <div class="student-trend-label"><?= htmlspecialchars($trend['label']) ?></div>
                        <div class="student-trend-track">
                            <div class="student-trend-fill" style="width: <?= max(0, min(100, (float) $trend['attendance_rate'])) ?>%"></div>
                        </div>
                        <div class="student-trend-value"><?= htmlspecialchars(number_format((float) $trend['attendance_rate'], 1)) ?>%</div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>

    <section id="my-attendance" class="mb-5">
        <h2>Attendance History</h2>
        <?php if (empty($history)): ?>
            <div class="student-empty-state">
                <h3>No attendance history found</h3>
                <p>Your current month or course filter returned no rows. Reset the filters to view your full attendance history.</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead class="table-success">
                        <tr>
                            <th>Date</th>
                            <th>Course</th>
                            <th>Session Code</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($history as $item): ?>
                            <tr>
                                <td><?= htmlspecialchars(date('Y-m-d H:i', strtotime($item['created_at']))); ?></td>
                                <td><?= htmlspecialchars($item['course_name'] . ' (' . $item['course_code'] . ')'); ?></td>
                                <td><?= htmlspecialchars($item['session_code'] ?: 'N/A'); ?></td>
                                <td class="<?= $item['status'] === 'Present' ? 'text-success' : 'text-danger'; ?>">
                                    <?= htmlspecialchars($item['status']); ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </section>

    <section class="mb-5">
        <h2>Announcements</h2>
        <a href="view_announcements.php" class="btn btn-success btn-lg w-100">
            <i class="bi bi-megaphone me-2"></i> View Announcements
        </a>
    </section>

    <section id="profile" class="mb-5">
        <h2>Profile</h2>
        <table class="table table-bordered">
            <tr><th>Name</th><td><?= htmlspecialchars($student['name']); ?></td></tr>
            <tr><th>Student Number</th><td><?= htmlspecialchars($student['student_number']); ?></td></tr>
            <tr><th>Programme</th><td><?= htmlspecialchars($student['programme_name']); ?></td></tr>
            <tr><th>Email</th><td><?= htmlspecialchars($student['email']); ?></td></tr>
        </table>
        <a href="../account/change_password.php" class="btn btn-outline-success">
            <i class="bi bi-shield-lock me-2"></i>Change Password
        </a>
    </section>
</div>

<script <?= et_csp_attr('script') ?>>
let scanInProgress = false;
let attendanceCaptured = false;

function showQrFeedback(message, type = 'info') {
    const el = document.getElementById('qr-result');
    const bsType = type === 'success' ? 'success' : (type === 'danger' ? 'danger' : 'info');
    el.className = `alert alert-${bsType}`;
    el.textContent = message;
}

function onScanSuccess(decodedText) {
    if (scanInProgress || attendanceCaptured) {
        return;
    }
    scanInProgress = true;
    showQrFeedback('Processing scan...', 'info');

    const token = document.querySelector('meta[name="csrf-token"]').content;
    const formData = new FormData();
    formData.append('session_code', decodedText);
    formData.append('qr', 1);
    formData.append('csrf_token', token);

    fetch('../../controllers/student/mark_attendance.php', {
        method: 'POST',
        body: formData
    }).then(res => res.text())
      .then(data => {
          const message = (data || '').trim();
          if (/successfully recorded/i.test(message)) {
              attendanceCaptured = true;
              showQrFeedback('QR scanned successfully. Attendance has been taken.', 'success');
              scanner.clear().catch(() => {});
          } else {
              showQrFeedback(message || 'Unable to record attendance.', 'danger');
          }
      })
      .catch(err => showQrFeedback('Error: ' + err, 'danger'))
      .finally(() => {
          if (!attendanceCaptured) {
              scanInProgress = false;
          }
      });
}

const scanner = new Html5QrcodeScanner("qr-reader", { fps: 10, qrbox: 250 });
scanner.render(onScanSuccess);
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<?php require_once '../../includes/footer.php'; ?>
</body>
</html>
