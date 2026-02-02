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
require_role(['lecturer']);

$lecturer_id = $_SESSION['user_id'];

// Fetch courses assigned to this lecturer
try {
    $stmt = $pdo->prepare('
        SELECT c.id, c.name AS course_name, c.course_code
        FROM courses c
        INNER JOIN lecturer_courses lc ON lc.course_id = c.id
        WHERE lc.lecturer_id = ?
        ORDER BY c.name ASC
    ');
    $stmt->execute([$lecturer_id]);
    $courses = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log('Error fetching lecturer courses: ' . $e->getMessage());
    $courses = [];
}

// Handle POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $message = trim($_POST['message'] ?? '');
    $course_id = $_POST['course_id'] ?? '';

    if (empty($title) || empty($message) || empty($course_id)) {
        $_SESSION['error_message'] = 'All fields are required.';
        header('Location: send_announcement.php');
        exit();
    }

    try {
        $stmt_verify = $pdo->prepare('SELECT COUNT(*) FROM lecturer_courses WHERE lecturer_id = ? AND course_id = ?');
        $stmt_verify->execute([$lecturer_id, $course_id]);

        if ($stmt_verify->fetchColumn() == 0) {
            $_SESSION['error_message'] = 'You are not authorized to send announcements for this course.';
            header('Location: send_announcement.php');
            exit();
        }

        $pdo->beginTransaction();

        $stmt_insert = $pdo->prepare("
            INSERT INTO announcements (title, message, audience, created_by)
            VALUES (?, ?, 'students', ?)
        ");
        $stmt_insert->execute([$title, $message, $lecturer_id]);
        $announcement_id = $pdo->lastInsertId();

        $stmt_students = $pdo->prepare("
            SELECT student_id FROM enrollments 
            WHERE course_id = ? AND enrollment_status = 'active'
        ");
        $stmt_students->execute([$course_id]);
        $students = $stmt_students->fetchAll(PDO::FETCH_COLUMN);

        if (!empty($students)) {
            $stmt_link = $pdo->prepare('
                INSERT INTO announcement_students (announcement_id, student_id) VALUES (?, ?)
            ');
            foreach ($students as $student_id) {
                $stmt_link->execute([$announcement_id, $student_id]);
            }
        }

        $pdo->commit();

        $_SESSION['success_message'] = 'Announcement sent successfully to students enrolled in ' . getCourseName($pdo, $course_id) . '!';
        header('Location: send_announcement.php');
        exit();
    } catch (PDOException $e) {
        $pdo->rollBack();
        error_log('Error sending announcement: ' . $e->getMessage());
        $_SESSION['error_message'] = 'Failed to send announcement. Please try again.';
        header('Location: send_announcement.php');
        exit();
    }
}

function getCourseName($pdo, $course_id)
{
    $stmt = $pdo->prepare('SELECT name FROM courses WHERE id = ?');
    $stmt->execute([$course_id]);
    return $stmt->fetchColumn() ?: 'Unknown Course';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Send Announcement - Lecturer | EduTrack</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<!-- Bootstrap CSS -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="stylesheet" href="css/dashboard.css">
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
<main class="container dashboard-container">
    <h1 class="mb-3">Send Announcement to Students</h1>
    <a href="dashboard.php" class="btn btn-secondary mb-4"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>

    <!-- Alerts -->
    <?php if (!empty($_SESSION['error_message'])): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($_SESSION['error_message']) ?></div>
        <?php unset($_SESSION['error_message']); ?>
    <?php endif; ?>
    <?php if (!empty($_SESSION['success_message'])): ?>
        <div class="alert alert-success"><?= htmlspecialchars($_SESSION['success_message']) ?></div>
        <?php unset($_SESSION['success_message']); ?>
    <?php endif; ?>

    <!-- Form -->
    <form action="send_announcement.php" method="POST" class="card p-4 shadow-sm">
        <div class="mb-3">
            <label for="course_id" class="form-label">Select Course:</label>
            <select id="course_id" name="course_id" class="form-select" required>
                <option value="">-- Select a Course --</option>
                <?php foreach ($courses as $course): ?>
                    <option value="<?= htmlspecialchars($course['id']) ?>">
                        <?= htmlspecialchars($course['course_name']) ?> (<?= htmlspecialchars($course['course_code']) ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="mb-3">
            <label for="title" class="form-label">Title:</label>
            <input type="text" id="title" name="title" class="form-control" required>
        </div>

        <div class="mb-3">
            <label for="message" class="form-label">Message:</label>
            <textarea id="message" name="message" rows="6" class="form-control" required></textarea>
        </div>

        <button type="submit" class="btn btn-primary"><i class="fas fa-paper-plane"></i> Send Announcement</button>
    </form>
</main>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<!-- Footer -->
<?php require_once '../../includes/footer.php'; ?>
</body>
</html>
