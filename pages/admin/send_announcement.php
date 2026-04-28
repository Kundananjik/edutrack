<?php
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
require_once '../../includes/config.php';
require_once '../../includes/db.php';
require_once '../../includes/functions.php';
require_once '../../includes/csrf.php';

require_login();
require_role(['admin']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
        $_SESSION['error_message'] = 'Invalid CSRF token.';
        redirect('send_announcement.php');
    }

    $title = trim($_POST['title'] ?? '');
    $message = trim($_POST['message'] ?? '');
    $audience = $_POST['audience'] ?? 'all';
    $adminId = $_SESSION['user_id'];

    if ($title === '' || $message === '') {
        $_SESSION['error_message'] = 'Title and message cannot be empty.';
        redirect('send_announcement.php');
    }

    try {
        $stmt = $pdo->prepare('INSERT INTO announcements (title, message, audience, created_by) VALUES (?, ?, ?, ?)');
        $stmt->execute([$title, $message, $audience, $adminId]);
        $_SESSION['success_message'] = 'Announcement sent successfully!';
        redirect('send_announcement.php');
    } catch (PDOException $e) {
        error_log('Announcement insert error: ' . $e->getMessage());
        $_SESSION['error_message'] = 'Failed to send announcement.';
        redirect('send_announcement.php');
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Send Announcement - EduTrack Admin</title>
    <link rel="icon" type="image/png" href="<?= asset_url('assets/favicon.png') ?>">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="css/dashboard.css">
</head>
<body class="bg-light d-flex flex-column min-vh-100">

<?php require_once '../../includes/admin_navbar.php'; ?>

<main class="container py-5 flex-grow-1">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <h1 class="fw-bold mb-2"><i class="bi bi-megaphone me-2"></i>Send Announcement</h1>
            <p class="text-muted mb-0">Publish an announcement to students, lecturers, or the whole platform.</p>
        </div>
        <a href="dashboard.php" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Back to Dashboard
        </a>
    </div>

    <?php if (!empty($_SESSION['error_message'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($_SESSION['error_message']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['error_message']); ?>
    <?php endif; ?>

    <?php if (!empty($_SESSION['success_message'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($_SESSION['success_message']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['success_message']); ?>
    <?php endif; ?>

    <section class="card shadow-sm rounded-4">
        <div class="card-body">
            <form action="send_announcement.php" method="POST" class="row g-3">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(get_csrf_token()) ?>">

                <div class="col-12">
                    <label for="title" class="form-label">Title</label>
                    <input type="text" id="title" name="title" class="form-control" required value="<?= htmlspecialchars($_POST['title'] ?? '') ?>">
                </div>

                <div class="col-12">
                    <label for="message" class="form-label">Message</label>
                    <textarea id="message" name="message" rows="6" class="form-control" required><?= htmlspecialchars($_POST['message'] ?? '') ?></textarea>
                </div>

                <div class="col-md-4">
                    <label for="audience" class="form-label">Audience</label>
                    <select id="audience" name="audience" class="form-select">
                        <option value="students" <?= (($_POST['audience'] ?? 'all') === 'students') ? 'selected' : '' ?>>Students</option>
                        <option value="lecturers" <?= (($_POST['audience'] ?? '') === 'lecturers') ? 'selected' : '' ?>>Lecturers</option>
                        <option value="all" <?= (($_POST['audience'] ?? 'all') === 'all') ? 'selected' : '' ?>>All</option>
                    </select>
                </div>

                <div class="col-12">
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-send me-1"></i> Send Announcement
                    </button>
                </div>
            </form>
        </div>
    </section>
</main>

<?php require_once '../../includes/footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
