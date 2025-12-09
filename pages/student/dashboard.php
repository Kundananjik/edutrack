<?php
// Preload (auto-locate includes/preload.php)
$__et=__DIR__;
for($__i=0;$__i<6;$__i++){
    $__p=$__et . '/includes/preload.php';
    if (file_exists($__p)) { require_once $__p; break; }
    $__et=dirname($__et);
}
unset($__et,$__i,$__p);
// pages/student/dashboard.php

require_once '../../includes/auth_check.php';
require_once '../../includes/config.php';
require_once '../../includes/db.php';
require_once '../../includes/csrf.php';
require_login();
require_role(['student']);

$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
    header('Location: ../../auth/login.php');
    exit;
}

try {
    // Fetch student info (PDO)
    $stmt = $pdo->prepare('
        SELECT u.name, u.email, s.student_number, p.name AS programme_name
        FROM users u
        JOIN students s ON u.id = s.user_id
        JOIN programmes p ON s.programme_id = p.id
        WHERE u.id = :uid
    ');
    $stmt->execute([':uid' => $user_id]);
    $student = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$student) {
        throw new RuntimeException('Student information not found.');
    }

    // Fetch courses
    $stmt = $pdo->prepare('
        SELECT c.id, c.name, c.course_code, c.class_schedule
        FROM courses c
        JOIN enrollments e ON c.id = e.course_id
        WHERE e.student_id = :uid
    ');
    $stmt->execute([':uid' => $user_id]);
    $courses = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch attendance per course
    $course_attendance = [];
    $stmtAttendance = $pdo->prepare('
        SELECT s.created_at, ar.signed_in_at
        FROM attendance_sessions s
        LEFT JOIN attendance_records ar
          ON ar.session_id = s.id AND ar.student_id = :uid
        WHERE s.course_id = :cid
        ORDER BY s.created_at ASC
    ');
    foreach ($courses as $course) {
        $stmtAttendance->execute([':uid' => $user_id, ':cid' => $course['id']]);
        $course_attendance[$course['id']] = $stmtAttendance->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (PDOException $e) {
    error_log('Student dashboard DB error: ' . $e->getMessage());
    $student = null;
    $courses = [];
    $course_attendance = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="<?= htmlspecialchars(get_csrf_token()) ?>">
    <title>Student Dashboard - EduTrack</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="css/student.css">
    <script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-success">
    <div class="container">
        <a class="navbar-brand" href="../../index.php">
            <img src="../../assets/logo.png" alt="EduTrack Logo" height="40">
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item"><a class="nav-link" href="../../index.php">Home</a></li>
                <li class="nav-item"><a class="nav-link" href="#take-attendance">Take Attendance</a></li>
                <li class="nav-item"><a class="nav-link" href="#my-attendance">My Attendance</a></li>
                <li class="nav-item"><a class="nav-link" href="#profile">Profile</a></li>
                <li class="nav-item"><a class="nav-link" href="#summary">Summary</a></li>
                <li class="nav-item"><a class="nav-link" href="../../logout.php">Logout</a></li>
            </ul>
        </div>
    </div>
</nav>

<div class="container my-5">
    <h1 class="mb-4">Welcome, <?= htmlspecialchars($student['name']); ?>!</h1>

    <!-- Take Attendance -->
    <section id="take-attendance" class="mb-5">
        <h2>Take Attendance</h2>
        <p>Scan the QR code provided by your lecturer to mark your attendance.</p>
        <div id="qr-reader" class="mb-3"></div>
        <p id="qr-result"></p>

        <h5>Or Sign-in Manually</h5>
        <?php if (!empty($_SESSION['sign_in_message'])): ?>
            <div class="alert <?= strpos($_SESSION['sign_in_message'], 'Error') === 0 ? 'alert-danger' : 'alert-success'; ?>">
                <?= htmlspecialchars($_SESSION['sign_in_message']); ?>
            </div>
            <?php unset($_SESSION['sign_in_message']); ?>
        <?php endif; ?>

        <form action="../../controllers/student/mark_attendance.php" method="POST" class="row g-3">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(get_csrf_token()); ?>">

            <div class="col-md-6">
                <label for="course_id" class="form-label">Select Course</label>
                <select name="course_id" id="course_id" class="form-select" required>
                    <option value="">-- Choose a course --</option>
                    <?php foreach ($courses as $course): ?>
                        <option value="<?= $course['id']; ?>"><?= htmlspecialchars($course['name'] . " (" . $course['course_code'] . ")"); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-6">
                <label for="session_code" class="form-label">Session Code</label>
                <input type="text" name="session_code" id="session_code" class="form-control" placeholder="Enter session code" required>
            </div>
            <div class="col-12">
                <button type="submit" class="btn btn-success">Sign-in to Class</button>
            </div>
        </form>
    </section>

    <!-- My Attendance -->
    <section id="my-attendance" class="mb-5">
        <h2>My Attendance</h2>
        <label for="attendance-course-select" class="form-label">Select Course</label>
        <select id="attendance-course-select" class="form-select mb-3">
            <option value="">-- Choose a course --</option>
            <?php foreach ($courses as $course): ?>
                <option value="<?= $course['id']; ?>"><?= htmlspecialchars($course['name'] . " (" . $course['course_code'] . ")"); ?></option>
            <?php endforeach; ?>
        </select>

        <p id="select-course-msg" class="text-muted">Select a course to view attendance.</p>

        <?php foreach ($courses as $course): ?>
            <div id="attendance-table-<?= $course['id']; ?>" class="table-responsive" style="display:none;">
                <h4><?= htmlspecialchars($course['name']); ?> (<?= htmlspecialchars($course['course_code']); ?>)</h4>
                <table class="table table-bordered table-striped">
                    <thead class="table-success">
                        <tr>
                            <th>Date</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($course_attendance[$course['id']])): ?>
                            <?php foreach ($course_attendance[$course['id']] as $att): ?>
                                <tr>
                                    <td><?= htmlspecialchars(date('Y-m-d H:i', strtotime($att['created_at']))); ?></td>
                                    <td class="<?= $att['signed_in_at'] ? 'text-success' : 'text-danger'; ?>">
                                        <?= $att['signed_in_at'] ? 'Present' : 'Absent'; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="2">No attendance records.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        <?php endforeach; ?>
    </section>

    <!-- Announcements -->
    <section class="mb-5">
        <h2>Announcements</h2>
        <a href="view_announcements.php" class="btn btn-success btn-lg w-100">
            <i class="fas fa-bullhorn me-2"></i> View Announcements
        </a>
    </section>

    <!-- Profile -->
    <section id="profile" class="mb-5">
        <h2>Profile</h2>
        <table class="table table-bordered">
            <tr><th>Name</th><td><?= htmlspecialchars($student['name']); ?></td></tr>
            <tr><th>Student Number</th><td><?= htmlspecialchars($student['student_number']); ?></td></tr>
            <tr><th>Programme</th><td><?= htmlspecialchars($student['programme_name']); ?></td></tr>
            <tr><th>Email</th><td><?= htmlspecialchars($student['email']); ?></td></tr>
        </table>
    </section>

    <!-- Attendance Summary -->
    <section id="summary" class="mb-5">
        <h2>Attendance Summary</h2>
        <?php foreach ($courses as $course):
            $stmt = $pdo->prepare('SELECT COUNT(*) FROM attendance_sessions WHERE course_id = :cid');
            $stmt->execute([':cid' => $course['id']]);
            $total = (int)$stmt->fetchColumn();

            $stmt = $pdo->prepare('SELECT COUNT(*) FROM attendance_records WHERE student_id=:sid AND session_id IN (SELECT id FROM attendance_sessions WHERE course_id=:cid)');
            $stmt->execute([':sid' => $user_id, ':cid' => $course['id']]);
            $attended = (int)$stmt->fetchColumn();

            $percentage = ($total > 0) ? round(($attended / $total) * 100) : 0;
        ?>
            <div class="card mb-3">
                <div class="card-body">
                    <h5 class="card-title"><?= htmlspecialchars($course['name'] . " (" . $course['course_code'] . ")"); ?></h5>
                    <p class="card-text">Attended <?= $attended ?> of <?= $total ?> classes (<?= $percentage ?>%)</p>
                    <div class="progress">
                        <div class="progress-bar" role="progressbar" style="width: <?= $percentage; ?>%;" aria-valuenow="<?= $percentage; ?>" aria-valuemin="0" aria-valuemax="100"><?= $percentage; ?>%</div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </section>
</div>

<!-- JS -->
<script <?= et_csp_attr('script') ?>>
function onScanSuccess(decodedText) {
    const token = document.querySelector('meta[name="csrf-token"]').content;
    const formData = new FormData();
    formData.append('session_code', decodedText);
    formData.append('qr', 1);
    formData.append('csrf_token', token);

    fetch('../../controllers/student/mark_attendance.php', {
        method: 'POST',
        body: formData
    }).then(res => res.text())
      .then(data => document.getElementById('qr-result').innerText = data)
      .catch(err => document.getElementById('qr-result').innerText = "Error: " + err);
}

const scanner = new Html5QrcodeScanner("qr-reader", { fps: 10, qrbox: 250 });
scanner.render(onScanSuccess);

// My Attendance JS
const courseSelect = document.getElementById('attendance-course-select');
const courseTables = document.querySelectorAll('[id^=attendance-table-]');
const msg = document.getElementById('select-course-msg');

// Hide all tables initially
courseTables.forEach(table => table.style.display = 'none');

courseSelect.addEventListener('change', function() {
    const selectedId = this.value;
    courseTables.forEach(table => table.style.display = 'none');
    if (selectedId) {
        const tableToShow = document.getElementById('attendance-table-' + selectedId);
        if (tableToShow) tableToShow.style.display = 'block';
        msg.style.display = 'none';
    } else {
        msg.style.display = 'block';
    }
});
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<?php
require_once '../../includes/footer.php';
?>
</body>
</html>
