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
require_once '../../includes/functions.php';

require_login();

// Determine user role and ID
$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['role'] ?? 'admin';

// Fetch announcements relevant to the user
try {
    if ($user_role === 'student') {
        $stmt = $pdo->prepare("
            SELECT a.title, a.message, a.audience, a.created_at, u.name AS sender
            FROM announcements a
            JOIN users u ON a.created_by = u.id
            WHERE a.audience IN ('students','all')
            ORDER BY a.created_at DESC
        ");
        $stmt->execute();
    } elseif ($user_role === 'lecturer') {
        $stmt = $pdo->prepare("
            SELECT a.title, a.message, a.audience, a.created_at, u.name AS sender
            FROM announcements a
            JOIN users u ON a.created_by = u.id
            WHERE a.audience IN ('lecturers','all')
            ORDER BY a.created_at DESC
        ");
        $stmt->execute();
    } else { // admin sees all
        $stmt = $pdo->prepare('
            SELECT a.title, a.message, a.audience, a.created_at, u.name AS sender
            FROM announcements a
            JOIN users u ON a.created_by = u.id
            ORDER BY a.created_at DESC
        ');
        $stmt->execute();
    }
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
<title>Notifications - EduTrack</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<!-- Bootstrap CSS -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

<!-- Font Awesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<!-- Custom CSS -->
<link rel="stylesheet" href="css/dashboard.css">
<style>
    .notification-card {
        margin-bottom: 15px;
    }
    .badge {
        font-size: 0.8rem;
    }
</style>
</head>
<body>

<?php require_once '../../includes/admin_navbar.php'; ?>

<main class="container my-5">
    <h1 class="mb-4">Notifications</h1>

    <?php if (!empty($_SESSION['error_message'])): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($_SESSION['error_message']) ?></div>
        <?php unset($_SESSION['error_message']); ?>
    <?php endif; ?>

    <?php if (empty($notifications)): ?>
        <div class="alert alert-info">No notifications available.</div>
    <?php else: ?>
        <div class="row">
            <?php foreach ($notifications as $n): ?>
                <div class="col-12">
                    <div class="card notification-card shadow-sm">
                        <div class="card-body">
                            <h5 class="card-title">
                                <?= htmlspecialchars($n['title']) ?> 
                                <span class="badge bg-success"><?= ucfirst($n['audience']) ?></span>
                            </h5>
                            <p class="card-text"><?= htmlspecialchars($n['message']) ?></p>
                            <small class="text-muted">Sent by <?= htmlspecialchars($n['sender']) ?> on <?= date('d-m-Y H:i', strtotime($n['created_at'])) ?></small>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <div class="mt-4">
        <div class="d-flex justify-content-between mb-3">
            <a href="dashboard.php" class="btn btn-outline-secondary"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>
        </div>
    </div>
</main>

<?php require_once '../../includes/footer.php'; ?>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
