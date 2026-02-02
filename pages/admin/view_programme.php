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
// pages/admin/view_programme.php

require_once '../../includes/auth_check.php';
require_once '../../includes/config.php';
require_once '../../includes/db.php';
require_once '../../includes/functions.php';

require_login();
require_role(['admin']);

// Get the programme ID from the URL
$id = intval($_GET['id'] ?? 0);

if ($id <= 0) {
    $_SESSION['error_message'] = 'Invalid programme ID.';
    redirect('manage_programmes.php');
}

try {
    // Fetch the programme details
    $stmt = $pdo->prepare('SELECT * FROM programmes WHERE id = ?');
    $stmt->execute([$id]);
    $programme = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$programme) {
        $_SESSION['error_message'] = 'Programme not found.';
        redirect('manage_programmes.php');
    }

    // Fetch the courses associated with this programme
    $stmt = $pdo->prepare('SELECT id, name, course_code FROM courses WHERE programme_id = ? ORDER BY course_code');
    $stmt->execute([$id]);
    $courses = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    error_log('Database error in view_programme.php: ' . $e->getMessage());
    $_SESSION['error_message'] = 'Could not retrieve programme data. Please try again later.';
    redirect('manage_programmes.php');
}
?>

<!DOCTYPE html>
<html lang="en">

</head>
<body>
<?php require_once '../../includes/admin_navbar.php'; ?>

<div class="container my-4">
    <link rel="stylesheet" href="css/dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

<div class="page-wrapper">
    <div class="dashboard-container">
        <h1>View Programme: <?= htmlspecialchars($programme['name']) ?></h1>

        <div class="alerts">
            <?php if (!empty($_SESSION['success_message'])): ?>
                <div class="alert alert-success"><?= htmlspecialchars($_SESSION['success_message']) ?></div>
                <?php unset($_SESSION['success_message']); ?>
            <?php endif; ?>
        </div>

        <div class="details-card">
            <div class="detail-row">
                <span class="detail-label">Programme ID:</span>
                <span class="detail-value"><?= htmlspecialchars($programme['id']) ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Programme Code:</span>
                <span class="detail-value"><?= htmlspecialchars($programme['code']) ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Department:</span>
                <span class="detail-value"><?= htmlspecialchars($programme['department']) ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Duration:</span>
                <span class="detail-value"><?= htmlspecialchars($programme['duration']) ?> Years</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Created At:</span>
                <span class="detail-value"><?= htmlspecialchars($programme['created_at']) ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Last Updated:</span>
                <span class="detail-value"><?= htmlspecialchars($programme['updated_at']) ?></span>
            </div>
        </div>

        <div class="content-section">
            <h2>Associated Courses</h2>
            <?php if (empty($courses)): ?>
                <p>No courses are currently associated with this programme.</p>
            <?php else: ?>
                <ul class="course-list">
                    <?php foreach ($courses as $course): ?>
                        <li>
                            <span><?= htmlspecialchars($course['name']) ?> (<?= htmlspecialchars($course['course_code']) ?>)</span>
                            <div class="course-actions">
                                <a href="view_course.php?id=<?= urlencode($course['id']) ?>" class="btn btn-sm btn-info">
                                    <i class="fas fa-eye"></i> View
                                </a>
                                <a href="edit_course.php?id=<?= urlencode($course['id']) ?>" class="btn btn-sm btn-edit">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>

        <div class="actions" style="margin-top: 20px;">
            <a href="edit_programme.php?id=<?= urlencode($programme['id']) ?>" class="btn btn-primary">
                <i class="fas fa-edit"></i> Edit Programme
            </a>
            <a href="manage_programmes.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to List
            </a>
        </div>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>

</body>
</html>
<?php
require_once '../../includes/auth_check.php';
require_once '../../includes/config.php';
require_once '../../includes/db.php';
require_once '../../includes/functions.php';

require_login();
require_role(['admin']);

$id = intval($_GET['id'] ?? 0);
if ($id <= 0) {
    $_SESSION['error_message'] = 'Invalid programme ID.';
    redirect('manage_programmes.php');
}

try {
    $stmt = $pdo->prepare('SELECT * FROM programmes WHERE id = ?');
    $stmt->execute([$id]);
    $programme = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$programme) {
        $_SESSION['error_message'] = 'Programme not found.';
        redirect('manage_programmes.php');
    }

    $stmt = $pdo->prepare('SELECT id, name, course_code FROM courses WHERE programme_id = ? ORDER BY course_code');
    $stmt->execute([$id]);
    $courses = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    error_log('Database error in view_programme.php: ' . $e->getMessage());
    $_SESSION['error_message'] = 'Could not retrieve programme data. Please try again later.';
    redirect('manage_programmes.php');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>View Programme - EduTrack</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<!-- Bootstrap CSS -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<!-- Font Awesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<!-- Custom CSS -->
<link rel="stylesheet" href="css/dashboard.css">

<style>
html, body { height: 100%; margin: 0; display: flex; flex-direction: column; }
main { flex: 1 0 auto; }
.course-actions a { margin-left: 5px; }
</style>
</head>
<body>

<main class="container my-5">
    <h1 class="mb-4">View Programme: <?= htmlspecialchars($programme['name']) ?></h1>

    <?php if (!empty($_SESSION['success_message'])): ?>
        <div class="alert alert-success"><?= htmlspecialchars($_SESSION['success_message']) ?></div>
        <?php unset($_SESSION['success_message']); ?>
    <?php endif; ?>

    <div class="card mb-4 shadow-sm">
        <div class="card-body">
            <h5 class="card-title">Programme Details</h5>
            <ul class="list-group list-group-flush">
                <li class="list-group-item"><strong>ID:</strong> <?= htmlspecialchars($programme['id']) ?></li>
                <li class="list-group-item"><strong>Code:</strong> <?= htmlspecialchars($programme['code']) ?></li>
                <li class="list-group-item"><strong>Department:</strong> <?= htmlspecialchars($programme['department']) ?></li>
                <li class="list-group-item"><strong>Duration:</strong> <?= htmlspecialchars($programme['duration']) ?> Years</li>
                <li class="list-group-item"><strong>Created At:</strong> <?= htmlspecialchars($programme['created_at']) ?></li>
                <li class="list-group-item"><strong>Last Updated:</strong> <?= htmlspecialchars($programme['updated_at']) ?></li>
            </ul>
        </div>
    </div>

    <div class="card mb-4 shadow-sm">
        <div class="card-body">
            <h5 class="card-title">Associated Courses</h5>
            <?php if (empty($courses)): ?>
                <p>No courses are currently associated with this programme.</p>
            <?php else: ?>
                <ul class="list-group">
                    <?php foreach ($courses as $course): ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <?= htmlspecialchars($course['name']) ?> (<?= htmlspecialchars($course['course_code']) ?>)
                            <div class="course-actions">
                                <a href="view_course.php?id=<?= urlencode($course['id']) ?>" class="btn btn-sm btn-info"><i class="fas fa-eye"></i> View</a>
                                <a href="edit_course.php?id=<?= urlencode($course['id']) ?>" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i> Edit</a>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
    </div>

    <div class="mb-5">
        <a href="edit_programme.php?id=<?= urlencode($programme['id']) ?>" class="btn btn-primary me-2"><i class="fas fa-edit"></i> Edit Programme</a>
        <a href="manage_programmes.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Back to List</a>
    </div>
</main>

<?php require_once '../../includes/footer.php'; ?>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
