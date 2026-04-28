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

$lecturerId = (int) ($_GET['id'] ?? 0);
if ($lecturerId <= 0) {
    $_SESSION['error_message'] = 'Invalid lecturer ID.';
    redirect('manage_lecturers.php');
}

try {
    $stmt = $pdo->prepare("
        SELECT id, name, email, phone, status, created_at
        FROM users
        WHERE id = :id AND role = 'lecturer'
        LIMIT 1
    ");
    $stmt->execute(['id' => $lecturerId]);
    $lecturer = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$lecturer) {
        $_SESSION['error_message'] = 'Lecturer not found.';
        redirect('manage_lecturers.php');
    }

    $stmt = $pdo->prepare("
        SELECT c.id, c.course_code, c.name, p.name AS programme_name
        FROM lecturer_courses lc
        INNER JOIN courses c ON c.id = lc.course_id
        LEFT JOIN programmes p ON p.id = c.programme_id
        WHERE lc.lecturer_id = :lecturer_id
        ORDER BY c.name ASC
    ");
    $stmt->execute(['lecturer_id' => $lecturerId]);
    $courses = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stmt = $pdo->prepare("
        SELECT COUNT(DISTINCT lc.course_id)
        FROM lecturer_courses lc
        WHERE lc.lecturer_id = :lecturer_id
    ");
    $stmt->execute(['lecturer_id' => $lecturerId]);
    $courseCount = (int) $stmt->fetchColumn();
} catch (PDOException $e) {
    error_log('Database error in view_lecturer.php: ' . $e->getMessage());
    $_SESSION['error_message'] = 'Could not retrieve lecturer details. Please try again later.';
    redirect('manage_lecturers.php');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">

    <link rel="icon" type="image/png" href="<?= asset_url('assets/favicon.png') ?>">
    <title>View Lecturer - EduTrack Admin</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="css/dashboard.css">
</head>
<body class="bg-light d-flex flex-column min-vh-100">

<?php require_once '../../includes/admin_navbar.php'; ?>

<main class="container py-5 flex-grow-1">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <h1 class="fw-bold mb-2"><?= htmlspecialchars($lecturer['name']) ?></h1>
            <p class="text-muted mb-0">Lecturer profile and assigned courses.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="edit_lecturer.php?id=<?= urlencode((string) $lecturer['id']) ?>" class="btn btn-success">
                <i class="bi bi-pencil-square"></i> Edit Lecturer
            </a>
            <a href="manage_lecturers.php" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Back to Lecturers
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

    <?php if (!empty($_SESSION['error_message'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($_SESSION['error_message']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['error_message']); ?>
    <?php endif; ?>

    <section class="mb-5">
        <div class="row g-4">
            <div class="col-md-6 col-xl-3">
                <div class="dashboard-card text-center d-block">
                    <i class="bi bi-envelope fs-2 mb-2"></i>
                    <h3>Email</h3>
                    <p class="fs-6 mb-0"><?= htmlspecialchars($lecturer['email']) ?></p>
                </div>
            </div>
            <div class="col-md-6 col-xl-3">
                <div class="dashboard-card text-center d-block">
                    <i class="bi bi-telephone fs-2 mb-2"></i>
                    <h3>Phone</h3>
                    <p class="fs-6 mb-0"><?= htmlspecialchars($lecturer['phone'] ?: 'Not provided') ?></p>
                </div>
            </div>
            <div class="col-md-6 col-xl-3">
                <div class="dashboard-card text-center d-block">
                    <i class="bi bi-person-badge fs-2 mb-2"></i>
                    <h3>Status</h3>
                    <p class="fs-6 mb-0"><?= htmlspecialchars(ucfirst($lecturer['status'])) ?></p>
                </div>
            </div>
            <div class="col-md-6 col-xl-3">
                <div class="dashboard-card text-center d-block">
                    <i class="bi bi-journal-bookmark fs-2 mb-2"></i>
                    <h3>Assigned Courses</h3>
                    <p><?= $courseCount ?></p>
                </div>
            </div>
        </div>
    </section>

    <section class="card shadow-sm rounded-4">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h2 class="h4 mb-0">Assigned Courses</h2>
                <small class="text-muted">Added <?= htmlspecialchars(date('M j, Y', strtotime($lecturer['created_at']))) ?></small>
            </div>

            <?php if ($courses === []): ?>
                <p class="text-muted mb-0">This lecturer has no assigned courses yet.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-striped table-hover align-middle mb-0">
                        <thead class="table-success">
                            <tr>
                                <th scope="col">Course Code</th>
                                <th scope="col">Course</th>
                                <th scope="col">Programme</th>
                                <th scope="col" class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($courses as $course): ?>
                                <tr>
                                    <td><?= htmlspecialchars($course['course_code']) ?></td>
                                    <td><?= htmlspecialchars($course['name']) ?></td>
                                    <td><?= htmlspecialchars($course['programme_name'] ?: 'Unassigned') ?></td>
                                    <td class="text-center">
                                        <a href="view_course.php?id=<?= urlencode((string) $course['id']) ?>" class="btn btn-sm btn-outline-success">
                                            <i class="bi bi-eye"></i> View Course
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
