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
require_role(['admin']);

// Delete announcement if requested
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    try {
        $stmt = $pdo->prepare('DELETE FROM announcements WHERE id = ?');
        $stmt->execute([$delete_id]);
        $_SESSION['success_message'] = 'Announcement deleted successfully.';
        header('Location: view_announcements.php');
        exit();
    } catch (PDOException $e) {
        error_log('Failed to delete announcement: ' . $e->getMessage());
        $_SESSION['error_message'] = 'Failed to delete announcement.';
        header('Location: view_announcements.php');
        exit();
    }
}

// Fetch announcements
try {
    $stmt = $pdo->query('
        SELECT a.id, a.title, a.message, a.audience, a.created_at, u.name AS sender
        FROM announcements a
        JOIN users u ON a.created_by = u.id
        ORDER BY a.created_at DESC
    ');
    $announcements = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log('Failed to fetch announcements: ' . $e->getMessage());
    $_SESSION['error_message'] = 'Failed to load announcements.';
    $announcements = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>View Announcements - EduTrack</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="css/dashboard.css">
</head>
<body class="d-flex flex-column min-vh-100">

<?php require_once '../../includes/admin_navbar.php'; ?>

<main class="container my-5 flex-grow-1">
    <h1 class="mb-4">All Announcements</h1>

    <?php if (!empty($_SESSION['error_message'])): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($_SESSION['error_message']) ?></div>
        <?php unset($_SESSION['error_message']); ?>
    <?php endif; ?>
    <?php if (!empty($_SESSION['success_message'])): ?>
        <div class="alert alert-success"><?= htmlspecialchars($_SESSION['success_message']) ?></div>
        <?php unset($_SESSION['success_message']); ?>
    <?php endif; ?>

    <?php if (empty($announcements)): ?>
        <p>No announcements have been sent yet.</p>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-striped table-hover align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>Title</th>
                        <th>Message</th>
                        <th>Audience</th>
                        <th>Sent By</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($announcements as $a): ?>
                        <tr>
                            <td><?= htmlspecialchars($a['title']) ?></td>
                            <td><?= htmlspecialchars($a['message']) ?></td>
                            <td><?= ucfirst($a['audience']) ?></td>
                            <td><?= htmlspecialchars($a['sender']) ?></td>
                            <td><?= date('d-m-Y H:i', strtotime($a['created_at'])) ?></td>
                            <td>
                                <a href="view_announcements.php?delete_id=<?= $a['id'] ?>" 
                                   class="btn btn-sm btn-danger delete-announcement-link">
                                   <i class="fas fa-trash"></i> Delete
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>

    <div class="mt-4">
        <a href="send_announcement.php" class="btn btn-success me-2"><i class="fas fa-plus"></i> Send New Announcement</a>
        <div class="d-flex justify-content-between mb-3">
            <a href="dashboard.php" class="btn btn-outline-secondary"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>
        </div>
    </div>
</main>

<?php require_once '../../includes/footer.php'; ?>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script <?= et_csp_attr('script') ?>>
document.querySelectorAll('.delete-announcement-link').forEach(link => {
    link.addEventListener('click', function(e) {
        if (!confirm('Are you sure you want to delete this announcement?')) {
            e.preventDefault();
        }
    });
});
</script>
</body>
</html>
