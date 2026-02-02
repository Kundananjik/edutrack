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
// pages/lecturer/active_sessions.php
require_once '../../includes/auth_check.php';
require_once '../../includes/config.php';
require_once '../../includes/db.php';
require_once '../../includes/functions.php';

require_login();
require_role(['lecturer']);

$user_id = $_SESSION['user_id'];
$sessions = [];
$notification = $_SESSION['session_started'] ?? null;
unset($_SESSION['session_started']);

try {
    // Auto-remove expired sessions (older than 4 hours)
    $stmtCleanup = $pdo->prepare('
        UPDATE attendance_sessions
        SET is_active = 0
        WHERE is_active = 1 AND created_at < NOW() - INTERVAL 4 HOUR
    ');
    $stmtCleanup->execute();

    // Fetch active sessions
    $stmt = $pdo->prepare('
        SELECT asess.id, c.course_code, c.name AS course_name, asess.session_code, asess.created_at
        FROM attendance_sessions asess
        JOIN courses c ON asess.course_id = c.id
        WHERE asess.lecturer_id = ? AND asess.is_active = 1
        ORDER BY asess.created_at DESC
    ');
    $stmt->execute([$user_id]);
    $sessions = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    error_log('Database error in active_sessions.php: ' . $e->getMessage());
    $error = 'An error occurred while fetching active sessions. Please try again later.';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Active Sessions - EduTrack</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="stylesheet" href="css/dashboard.css">
<link rel="stylesheet" href="css/active_sessions.css">
<style>
/* EduTrack Theme Overrides */
.navbar { background-color: #2fa360; }
.nav-link, .navbar-brand { color: #fff !important; }
.nav-link:hover { text-decoration: underline; }

.btn-primary, .btn-success { background-color: #2fa360; border-color: #2fa360; }
.btn-primary:hover, .btn-success:hover { background-color: #27a04c; border-color: #27a04c; }

.table thead { background-color: #e6f4ea; }
.table-hover tbody tr:hover { background-color: #f1fdf4; }

.qr-code { margin: auto; border: 2px solid #2fa360; padding: 5px; border-radius: 8px; }

.card { border-radius: 12px; }
</style>
</head>
<body>

<nav class="navbar navbar-expand-lg mb-4">
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
        <li class="nav-item"><a class="nav-link" href="dashboard.php">Dashboard</a></li>
        <li class="nav-item"><a class="nav-link" href="../../logout.php">Logout</a></li>
      </ul>
    </div>
  </div>
</nav>

<div class="container dashboard-container">

    <a href="dashboard.php" class="btn btn-secondary mb-3"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>

    <h1 class="mb-4" style="color:#2fa360;">Active Attendance Sessions</h1>

    <?php if (!empty($notification)): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle"></i> <?= htmlspecialchars($notification) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (!empty($error ?? '')): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($error) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (empty($sessions)): ?>
        <div class="alert alert-info">There are no active attendance sessions at this time.</div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle text-center">
                <thead>
                    <tr>
                        <th>Course Code</th>
                        <th>Course Name</th>
                        <th>Session Code</th>
                        <th>QR Code</th>
                        <th>Created At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($sessions as $session): ?>
                    <tr id="session-<?= $session['id']; ?>">
                        <td><?= htmlspecialchars($session['course_code']); ?></td>
                        <td><?= htmlspecialchars($session['course_name']); ?></td>
                        <td><strong><?= htmlspecialchars($session['session_code']); ?></strong></td>
                        <td><div class="qr-code" data-code="<?= htmlspecialchars($session['session_code']); ?>"></div></td>
                        <td><?= (new DateTime($session['created_at']))->format('F j, Y, g:i a'); ?></td>
                        <td>
                            <button class="btn btn-danger btn-sm delete" data-id="<?= $session['id']; ?>" title="Stop Session">
                                <i class="fas fa-stop-circle"></i> Stop
                            </button>
                            <a href="view_session.php?id=<?= $session['id']; ?>" class="btn btn-success btn-sm" title="View Attendance">
                                <i class="fas fa-eye"></i> View
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php require_once '../../includes/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>
<script <?= et_csp_attr('script') ?>>
document.addEventListener('DOMContentLoaded', () => {
    // Generate QR codes
    document.querySelectorAll('.qr-code').forEach(el => {
        const code = el.getAttribute('data-code');
        if (code) new QRCode(el, {
            text: code,
            width: 150,
            height: 140,
            colorDark: "#000000",
            colorLight: "#fff",
            correctLevel: QRCode.CorrectLevel.H
        });
    });

    // Stop session via AJAX
    document.querySelectorAll('.delete').forEach(btn => {
        btn.addEventListener('click', () => {
            const sessionId = btn.dataset.id;
            if (!confirm('Are you sure you want to stop this session?')) return;

            fetch('stop_session.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: `session_id=${sessionId}`
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    const row = document.getElementById(`session-${sessionId}`);
                    if (row) row.remove();
                } else {
                    alert(data.message);
                }
            })
            .catch(err => {
                console.error(err);
                alert('An error occurred while stopping the session.');
            });
        });
    });
});
</script>
</body>
</html>
