<?php
// Preload (auto-locate includes/preload.php)
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

require_login();
require_role(['admin']);

try {
    $stmt = $pdo->prepare('
        SELECT a.title, a.message, a.audience, a.created_at, u.name AS sender
        FROM announcements a
        JOIN users u ON a.created_by = u.id
        ORDER BY a.created_at DESC
    ');
    $stmt->execute();
    $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log('Failed to fetch notifications: ' . $e->getMessage());
    $_SESSION['error_message'] = 'Failed to load notifications.';
    $notifications = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/png" href="<?= asset_url('assets/favicon.png') ?>">
    <title>Notifications - EduTrack Admin</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="css/dashboard.css">
</head>
<body class="bg-light d-flex flex-column min-vh-100">

<?php require_once '../../includes/admin_navbar.php'; ?>

<main class="container py-5 flex-grow-1">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <h1 class="fw-bold mb-2">Notifications</h1>
            <p class="text-muted mb-0">Recent announcement traffic visible to administrators.</p>
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

    <?php if ($notifications === []): ?>
        <div class="alert alert-info">No notifications available.</div>
    <?php else: ?>
        <div class="row g-4">
            <?php foreach ($notifications as $notification): ?>
                <div class="col-12">
                    <div class="card shadow-sm rounded-4 h-100">
                        <div class="card-body">
                            <h2 class="h5 card-title d-flex align-items-center gap-2">
                                <span><?= htmlspecialchars($notification['title']) ?></span>
                                <span class="badge bg-success"><?= htmlspecialchars(ucfirst($notification['audience'])) ?></span>
                            </h2>
                            <p class="card-text mb-3"><?= nl2br(htmlspecialchars($notification['message'])) ?></p>
                            <small class="text-muted">
                                Sent by <?= htmlspecialchars($notification['sender']) ?> on <?= htmlspecialchars(date('d-m-Y H:i', strtotime($notification['created_at']))) ?>
                            </small>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</main>

<?php require_once '../../includes/footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
