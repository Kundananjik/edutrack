<?php
// Preload (auto-locate includes/preload.php)
$__et=__DIR__;
for($__i=0;$__i<6;$__i++){
    $__p=$__et . '/includes/preload.php';
    if (file_exists($__p)) { require_once $__p; break; }
    $__et=dirname($__et);
}
unset($__et,$__i,$__p);
// pages/student/view_announcements.php

require_once '../../includes/auth_check.php';
require_once '../../includes/config.php';
require_once '../../includes/db.php';

require_login();
require_role(['student']);

$student_id = $_SESSION['user_id'];

try {
    $stmt = $pdo->prepare("
        SELECT a.id, a.title, a.message, a.created_at,
               u.name AS sender_name, u.role AS sender_role
        FROM announcements a
        INNER JOIN announcement_students ast ON ast.announcement_id = a.id
        INNER JOIN users u ON a.created_by = u.id
        WHERE ast.student_id = ?
        ORDER BY a.created_at DESC
    ");
    $stmt->execute([$student_id]);
    $announcements = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log('Error fetching announcements: ' . $e->getMessage());
    $announcements = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>View Announcements - EduTrack</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- EduTrack Custom CSS -->
    <link rel="stylesheet" href="css/student.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light mb-4">
        <div class="container">
            <a class="navbar-brand" href="../../index.php">
                <img src="../../assets/logo.png" alt="EduTrack Logo" height="40">
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="dashboard.php">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../../logout.php">Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <main class="container">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h1><i class="fas fa-bullhorn"></i> Announcements</h1>
            <a href="dashboard.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
        </div>
        <p class="text-muted mb-4">
            Stay updated with the latest announcements from your lecturers.
        </p>

        <?php if (empty($announcements)): ?>
            <div class="alert alert-info">No announcements available at the moment.</div>
        <?php else: ?>
            <div class="row g-3">
                <?php foreach ($announcements as $a): ?>
                    <div class="col-md-6">
                        <div class="card shadow-sm h-100 announcement-card">
                            <div class="card-body">
                                <h5 class="card-title"><?= htmlspecialchars($a['title']); ?></h5>
                                <p class="card-text"><?= nl2br(htmlspecialchars($a['message'])); ?></p>
                            </div>
                            <div class="card-footer d-flex justify-content-between small">
                                <span><i class="fas fa-user"></i> <?= htmlspecialchars(ucfirst($a['sender_role'])) ?>: <?= htmlspecialchars($a['sender_name']); ?></span>
                                <span><i class="fas fa-clock"></i> <?= htmlspecialchars(date('F j, Y, g:i a', strtotime($a['created_at']))); ?></span>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </main>

    <?php require_once '../../includes/footer.php'; ?>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
