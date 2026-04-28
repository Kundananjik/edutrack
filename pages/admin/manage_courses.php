<?php
// ============================================
// ADMIN - MANAGE COURSES
// ============================================

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
require_once '../../includes/csrf.php';
require_once '../../includes/functions.php';

require_login();
require_role(['admin']);

try {
    $stmt = $pdo->query('SELECT id, name FROM programmes ORDER BY name ASC');
    $programmesForFilter = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log('Error fetching programmes: ' . $e->getMessage());
    $programmesForFilter = [];
}

$programmeFilter = (int) ($_GET['programme'] ?? 0);

try {
    $query = "
        SELECT
            c.id,
            c.name,
            c.course_code,
            c.status,
            p.name AS programme_name,
            GROUP_CONCAT(u.name ORDER BY u.name SEPARATOR ', ') AS lecturer_name
        FROM courses c
        INNER JOIN programmes p ON c.programme_id = p.id
        LEFT JOIN lecturer_courses lc ON c.id = lc.course_id
        LEFT JOIN users u ON lc.lecturer_id = u.id
    ";

    $params = [];
    if ($programmeFilter > 0) {
        $query .= ' WHERE c.programme_id = ?';
        $params[] = $programmeFilter;
    }

    $query .= ' GROUP BY c.id ORDER BY c.id DESC';

    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $courses = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log('Database error in manage_courses.php: ' . $e->getMessage());
    $courses = [];
    $_SESSION['error_message'] = 'Could not fetch course data. Please try again later.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/png" href="<?= asset_url('assets/favicon.png') ?>">
    <title>Manage Courses - EduTrack Admin</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="css/dashboard.css">
</head>
<body class="bg-light d-flex flex-column min-vh-100">

<?php require_once '../../includes/admin_navbar.php'; ?>

<main class="container py-5 flex-grow-1">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <h1 class="fw-bold mb-2">Manage Courses</h1>
            <p class="text-muted mb-0">Track course records, assignments, and publishing status.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="dashboard.php" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Back to Dashboard
            </a>
            <a href="add_course.php" class="btn btn-success">
                <i class="bi bi-plus-lg"></i> Add Course
            </a>
        </div>
    </div>

    <?php if (!empty($_SESSION['success_message'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($_SESSION['success_message']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['success_message']); ?>
    <?php endif; ?>

    <?php if (!empty($_SESSION['error_message'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($_SESSION['error_message']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['error_message']); ?>
    <?php endif; ?>

    <section class="card shadow-sm rounded-4">
        <div class="card-body">
            <form method="GET" class="row g-3 align-items-end mb-4">
                <div class="col-md-4">
                    <label for="programme" class="form-label">Filter by Programme</label>
                    <select name="programme" id="programme" class="form-select">
                        <option value="0">All Programmes</option>
                        <?php foreach ($programmesForFilter as $programme): ?>
                            <option value="<?= htmlspecialchars($programme['id']) ?>" <?= $programmeFilter === (int) $programme['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($programme['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-success">Apply Filter</button>
                </div>
                <?php if ($programmeFilter > 0): ?>
                    <div class="col-auto">
                        <a href="manage_courses.php" class="btn btn-outline-secondary">Reset</a>
                    </div>
                <?php endif; ?>
            </form>

            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle mb-0">
                    <thead class="table-success">
                        <tr>
                            <th scope="col">ID</th>
                            <th scope="col">Course Name</th>
                            <th scope="col">Code</th>
                            <th scope="col">Programme</th>
                            <th scope="col">Lecturer(s)</th>
                            <th scope="col">Status</th>
                            <th scope="col" class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($courses !== []): ?>
                            <?php foreach ($courses as $course): ?>
                                <tr>
                                    <td><?= htmlspecialchars($course['id']) ?></td>
                                    <td><?= htmlspecialchars($course['name']) ?></td>
                                    <td><?= htmlspecialchars($course['course_code']) ?></td>
                                    <td><?= htmlspecialchars($course['programme_name']) ?></td>
                                    <td><?= htmlspecialchars($course['lecturer_name'] ?? 'Not Assigned') ?></td>
                                    <td>
                                        <span class="badge bg-<?= $course['status'] === 'active' ? 'success' : ($course['status'] === 'inactive' ? 'warning text-dark' : 'secondary') ?>">
                                            <?= htmlspecialchars(ucfirst($course['status'])) ?>
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <a href="view_course.php?id=<?= urlencode((string) $course['id']) ?>" class="btn btn-sm btn-outline-success me-1" title="View Course">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="edit_course.php?id=<?= urlencode((string) $course['id']) ?>" class="btn btn-sm btn-outline-primary me-1" title="Edit Course">
                                            <i class="bi bi-pencil-square"></i>
                                        </a>
                                        <form action="delete_course.php" method="POST" class="d-inline delete-course-form">
                                            <input type="hidden" name="id" value="<?= htmlspecialchars($course['id']) ?>">
                                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(get_csrf_token()) ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete Course">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">No courses found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </section>
</main>

<?php require_once '../../includes/footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script <?= et_csp_attr('script') ?>>
document.querySelectorAll('.delete-course-form').forEach(form => {
    form.addEventListener('submit', function (e) {
        if (!confirm('Are you sure you want to delete this course?')) {
            e.preventDefault();
        }
    });
});
</script>
</body>
</html>
