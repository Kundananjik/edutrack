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

require_login('../../auth/login.php');
require_role(['student', 'lecturer'], '../../includes/unauthorized.php');

$userId = (int) ($_SESSION['user_id'] ?? 0);
$role = $_SESSION['role'] ?? '';
$error = '';
$success = '';

if (!in_array($role, ['student', 'lecturer'], true)) {
    header('Location: ../../auth/login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
        $error = 'Invalid CSRF token.';
    } else {
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        if ($currentPassword === '' || $newPassword === '' || $confirmPassword === '') {
            $error = 'All password fields are required.';
        } elseif ($newPassword !== $confirmPassword) {
            $error = 'New password and confirmation do not match.';
        } elseif (strlen($newPassword) < 8) {
            $error = 'New password must be at least 8 characters.';
        } elseif (!preg_match('/[A-Za-z]/', $newPassword) || !preg_match('/\d/', $newPassword)) {
            $error = 'New password must include at least one letter and one number.';
        } elseif ($currentPassword === $newPassword) {
            $error = 'New password must be different from the current password.';
        } else {
            try {
                $stmt = $pdo->prepare('SELECT password FROM users WHERE id = :id AND role = :role LIMIT 1');
                $stmt->execute([
                    'id' => $userId,
                    'role' => $role,
                ]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);

                if (!$user || !password_verify($currentPassword, $user['password'])) {
                    $error = 'Current password is incorrect.';
                } else {
                    $update = $pdo->prepare('UPDATE users SET password = :password WHERE id = :id');
                    $update->execute([
                        'password' => password_hash($newPassword, PASSWORD_DEFAULT),
                        'id' => $userId,
                    ]);

                    session_regenerate_id(true);
                    $success = 'Password updated successfully.';
                }
            } catch (PDOException $e) {
                error_log('Change password error: ' . $e->getMessage());
                $error = 'Unable to update password right now. Please try again later.';
            }
        }
    }
}

$pageTitle = $role === 'lecturer' ? 'Lecturer Password' : 'Student Password';
$dashboardHref = $role === 'lecturer' ? '../lecturer/dashboard.php' : '../student/dashboard.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?> - EduTrack</title>
    <link rel="icon" type="image/png" href="<?= asset_url('assets/favicon.png') ?>">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <?php if ($role === 'lecturer'): ?>
        <link rel="stylesheet" href="../lecturer/css/dashboard.css">
    <?php else: ?>
        <link rel="stylesheet" href="../student/css/student.css">
    <?php endif; ?>
</head>
<body>
<?php
if ($role === 'lecturer') {
    $lecturerHomeHref = '../../index.php';
    $lecturerDashboardHref = '../lecturer/dashboard.php';
    $lecturerCoursesHref = '../lecturer/my_courses.php';
    $lecturerSessionsHref = '../lecturer/active_sessions.php';
    $lecturerAnnouncementsHref = '../lecturer/view_announcements.php';
    $lecturerChangePasswordHref = 'change_password.php';
    $lecturerLogoutHref = '../../logout.php';
    require_once '../../includes/lecturer_navbar.php';
} else {
    $studentHomeHref = '../../index.php';
    $studentDashboardHref = '../student/dashboard.php';
    $studentAnnouncementsHref = '../student/view_announcements.php';
    $studentChangePasswordHref = 'change_password.php';
    $studentLogoutHref = '../../logout.php';
    require_once '../../includes/student_navbar.php';
}
?>

<main class="container my-5">
    <div class="row justify-content-center">
        <div class="col-lg-7">
            <div class="card shadow-sm border-0">
                <div class="card-body p-4 p-md-5">
                    <p class="text-success text-uppercase small fw-semibold mb-2"><?= htmlspecialchars($role) ?> account</p>
                    <h1 class="h3 mb-3">Change Password</h1>
                    <p class="text-muted mb-4">Use your current password to set a new one for this account.</p>

                    <?php if ($error !== ''): ?>
                        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                    <?php endif; ?>

                    <?php if ($success !== ''): ?>
                        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
                    <?php endif; ?>

                    <form method="POST" action="">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(get_csrf_token()) ?>">

                        <div class="mb-3">
                            <label for="current_password" class="form-label">Current Password</label>
                            <input type="password" class="form-control" id="current_password" name="current_password" required>
                        </div>

                        <div class="mb-3">
                            <label for="new_password" class="form-label">New Password</label>
                            <input type="password" class="form-control" id="new_password" name="new_password" required>
                            <div class="form-text">Use at least 8 characters with at least one letter and one number.</div>
                        </div>

                        <div class="mb-4">
                            <label for="confirm_password" class="form-label">Confirm New Password</label>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                        </div>

                        <div class="d-flex flex-column flex-sm-row gap-2">
                            <button type="submit" class="btn btn-success">
                                <i class="bi bi-shield-lock me-2"></i>Update Password
                            </button>
                            <a href="<?= htmlspecialchars($dashboardHref) ?>" class="btn btn-outline-secondary">Back to Dashboard</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</main>

<?php require_once '../../includes/footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
