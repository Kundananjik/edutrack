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
// pages/lecturer/view_announcements.php

require_once '../../includes/auth_check.php';
require_once '../../includes/config.php';
require_once '../../includes/db.php';

require_login();
require_role(['lecturer']);

$lecturer_id = $_SESSION['user_id'];

try {
    // Fetch announcements sent by this lecturer or to all lecturers
    $stmt = $pdo->prepare("
        SELECT a.id, a.title, a.message, a.created_at,
               u.name AS sender_name, u.role AS sender_role
        FROM announcements a
        JOIN users u ON a.created_by = u.id
        LEFT JOIN announcement_students ast ON a.id = ast.announcement_id
        LEFT JOIN enrollments e ON ast.student_id = e.student_id
        WHERE a.created_by = :lecturer_id
           OR a.audience = 'lecturers'
        GROUP BY a.id
        ORDER BY a.created_at DESC
    ");
    $stmt->execute(['lecturer_id' => $lecturer_id]);
    $announcements = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    error_log('Failed to fetch announcements: ' . $e->getMessage());
    $_SESSION['error_message'] = 'Unable to load announcements.';
    $announcements = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>View Announcements - EduTrack</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

<!-- NAVBAR -->
<nav class="navbar navbar-expand-lg navbar-dark bg-success mb-4">
    <div class="container">
        <a class="navbar-brand" href="../../index.php">
            <img src="../../assets/logo.png" alt="EduTrack Logo" height="30">
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item"><a class="nav-link" href="dashboard.php">Dashboard</a></li>
                <li class="nav-item"><a class="nav-link" href="../../logout.php">Logout</a></li>
            </ul>
        </div>
    </div>
</nav>

<!-- MAIN CONTENT -->
<div class="container">
    <h1 class="mb-4"><i class="fas fa-bullhorn"></i> Announcements</h1>

    <!-- ALERTS -->
    <?php if (!empty($_SESSION['error_message'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($_SESSION['error_message']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['error_message']); ?>
    <?php endif; ?>

    <?php if (empty($announcements)): ?>
        <div class="alert alert-info">No announcements available at the moment.</div>
    <?php else: ?>
        <div class="row row-cols-1 row-cols-md-2 g-4">
            <?php foreach ($announcements as $a): ?>
                <div class="col">
                    <div class="card h-100 shadow-sm">
                        <div class="card-body">
                            <h5 class="card-title"><?= htmlspecialchars($a['title']); ?></h5>
                            <h6 class="card-subtitle mb-2 text-muted">
                                <?= htmlspecialchars(date('F j, Y, g:i a', strtotime($a['created_at']))); ?>
                            </h6>
                            <p class="card-text"><?= nl2br(htmlspecialchars($a['message'])); ?></p>
                        </div>
                        <div class="card-footer text-muted small">
                            Posted by <?= htmlspecialchars(ucfirst($a['sender_role'])) ?>: <?= htmlspecialchars($a['sender_name']); ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <a href="dashboard.php" class="btn btn-secondary mt-4">
        <i class="fas fa-arrow-left"></i> Back to Dashboard
    </a>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
