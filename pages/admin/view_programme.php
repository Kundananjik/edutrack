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

$id = (int) ($_GET['id'] ?? 0);
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
    <link rel="icon" type="image/png" href="<?= asset_url('assets/favicon.png') ?>">
    <title>View Programme - EduTrack Admin</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="css/dashboard.css">
</head>
<body class="bg-light d-flex flex-column min-vh-100">

<?php require_once '../../includes/admin_navbar.php'; ?>

<main class="container py-5 flex-grow-1">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <h1 class="fw-bold mb-2"><?= htmlspecialchars($programme['name']) ?></h1>
            <p class="text-muted mb-0">Programme details and linked courses.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="edit_programme.php?id=<?= urlencode((string) $programme['id']) ?>" class="btn btn-success">
                <i class="bi bi-pencil-square"></i> Edit Programme
            </a>
            <a href="manage_programmes.php" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Back to Programmes
            </a>
        </div>
    </div>

    <?php if (!empty($_SESSION['success_message'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($_SESSION['success_message']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['success_message']); ?>
    <?php endif; ?>

    <section class="card shadow-sm rounded-4 mb-4">
        <div class="card-body">
            <h2 class="h4 mb-3">Programme Details</h2>
            <div class="row g-3">
                <div class="col-md-6"><strong>ID:</strong> <?= htmlspecialchars($programme['id']) ?></div>
                <div class="col-md-6"><strong>Code:</strong> <?= htmlspecialchars($programme['code']) ?></div>
                <div class="col-md-6"><strong>Department:</strong> <?= htmlspecialchars($programme['department']) ?></div>
                <div class="col-md-6"><strong>Duration:</strong> <?= htmlspecialchars($programme['duration']) ?> Years</div>
                <div class="col-md-6"><strong>Created At:</strong> <?= htmlspecialchars($programme['created_at']) ?></div>
                <div class="col-md-6"><strong>Last Updated:</strong> <?= htmlspecialchars($programme['updated_at']) ?></div>
            </div>
        </div>
    </section>

    <section class="card shadow-sm rounded-4">
        <div class="card-body">
            <h2 class="h4 mb-3">Associated Courses</h2>
            <?php if ($courses === []): ?>
                <p class="text-muted mb-0">No courses are currently associated with this programme.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-striped table-hover align-middle mb-0">
                        <thead class="table-success">
                            <tr>
                                <th scope="col">Course Code</th>
                                <th scope="col">Course</th>
                                <th scope="col" class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($courses as $course): ?>
                                <tr>
                                    <td><?= htmlspecialchars($course['course_code']) ?></td>
                                    <td><?= htmlspecialchars($course['name']) ?></td>
                                    <td class="text-center">
                                        <a href="view_course.php?id=<?= urlencode((string) $course['id']) ?>" class="btn btn-sm btn-outline-success me-2">
                                            <i class="bi bi-eye"></i> View
                                        </a>
                                        <a href="edit_course.php?id=<?= urlencode((string) $course['id']) ?>" class="btn btn-sm btn-outline-secondary">
                                            <i class="bi bi-pencil-square"></i> Edit
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </section>
</main>

<?php require_once '../../includes/footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
