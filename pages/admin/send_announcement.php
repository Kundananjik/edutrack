<?php
// Preload (auto-locate includes/preload.php)
$__et=__DIR__;
for($__i=0;$__i<6;$__i++){
    $__p=$__et . '/includes/preload.php';
    if (file_exists($__p)) { require_once $__p; break; }
    $__et=dirname($__et);
}
unset($__et,$__i,$__p);
require_once '../../includes/auth_check.php';
require_once '../../includes/config.php';
require_once '../../includes/db.php';
require_once '../../includes/functions.php';

require_login();
require_role(['admin']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $message = trim($_POST['message'] ?? '');
    $audience = $_POST['audience'] ?? 'all';
    $admin_id = $_SESSION['user_id'];

    if (empty($title) || empty($message)) {
        $_SESSION['error_message'] = "Title and message cannot be empty.";
        header("Location: send_announcement.php");
        exit();
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO announcements (title, message, audience, created_by) VALUES (?, ?, ?, ?)");
        $stmt->execute([$title, $message, $audience, $admin_id]);
        $_SESSION['success_message'] = "Announcement sent successfully!";
        header("Location: send_announcement.php");
        exit();
    } catch (PDOException $e) {
        error_log("Announcement insert error: " . $e->getMessage());
        $_SESSION['error_message'] = "Failed to send announcement.";
        header("Location: send_announcement.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Send Announcement - EduTrack</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<!-- Bootstrap CSS -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

<!-- Font Awesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<!-- Custom CSS -->
<link rel="stylesheet" href="css/dashboard.css">
</head>
<body>

<?php require_once '../../includes/admin_navbar.php'; ?>

<div class="container mb-5">
    <h1 class="mb-4 text-success"><i class="fas fa-bullhorn me-2"></i>Send Announcement</h1>
    <div class="d-flex justify-content-between mb-3">
        <a href="dashboard.php" class="btn btn-outline-secondary"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>
    </div>

    <!-- Alerts -->
    <?php if (!empty($_SESSION['error_message'])): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($_SESSION['error_message']) ?></div>
        <?php unset($_SESSION['error_message']); ?>
    <?php endif; ?>
    <?php if (!empty($_SESSION['success_message'])): ?>
        <div class="alert alert-success"><?= htmlspecialchars($_SESSION['success_message']) ?></div>
        <?php unset($_SESSION['success_message']); ?>
    <?php endif; ?>

    <form action="send_announcement.php" method="POST" class="card p-4 shadow-sm bg-white">
        <div class="mb-3">
            <label for="title" class="form-label">Title:</label>
            <input type="text" id="title" name="title" class="form-control" required>
        </div>

        <div class="mb-3">
            <label for="message" class="form-label">Message:</label>
            <textarea id="message" name="message" rows="6" class="form-control" required></textarea>
        </div>

        <div class="mb-3">
            <label for="audience" class="form-label">Audience:</label>
            <select id="audience" name="audience" class="form-select">
                <option value="students">Students</option>
                <option value="lecturers">Lecturers</option>
                <option value="all" selected>All</option>
            </select>
        </div>

        <button type="submit" class="btn btn-success"><i class="fas fa-paper-plane me-1"></i> Send Announcement</button>
    </form>
</div>

<?php require_once '../../includes/footer.php'; ?>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
