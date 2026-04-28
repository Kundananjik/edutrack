<?php
// ============================================
// ADMIN - MANAGE PROGRAMMES
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
    $stmt = $pdo->query('SELECT DISTINCT department FROM programmes ORDER BY department');
    $departments = $stmt->fetchAll(PDO::FETCH_COLUMN);
} catch (PDOException $e) {
    error_log('Database error fetching departments: ' . $e->getMessage());
    $departments = [];
}

$departmentFilter = trim($_GET['department'] ?? '');

try {
    $query = 'SELECT * FROM programmes';
    $params = [];

    if ($departmentFilter !== '') {
        $query .= ' WHERE department = ?';
        $params[] = $departmentFilter;
    }

    $query .= ' ORDER BY id DESC';

    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $programmes = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log('Database error in manage_programmes.php: ' . $e->getMessage());
    $_SESSION['error_message'] = 'Could not fetch programme data. Please try again later.';
    $programmes = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/png" href="<?= asset_url('assets/favicon.png') ?>">
    <title>Manage Programmes - EduTrack Admin</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="css/dashboard.css">
</head>
<body class="bg-light d-flex flex-column min-vh-100">

<?php require_once '../../includes/admin_navbar.php'; ?>

<main class="container py-5 flex-grow-1">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <h1 class="fw-bold mb-2">Manage Programmes</h1>
            <p class="text-muted mb-0">Review, filter, and maintain academic programme records.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="dashboard.php" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Back to Dashboard
            </a>
            <a href="add_programme.php" class="btn btn-success">
                <i class="bi bi-plus-lg"></i> Add Programme
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

    <section class="card shadow-sm rounded-4">
        <div class="card-body">
            <form method="GET" class="row g-3 align-items-end mb-4">
                <div class="col-md-4">
                    <label for="department" class="form-label">Filter by Department</label>
                    <select name="department" id="department" class="form-select">
                        <option value="">All Departments</option>
                        <?php foreach ($departments as $department): ?>
                            <option value="<?= htmlspecialchars($department) ?>" <?= $departmentFilter === $department ? 'selected' : '' ?>>
                                <?= htmlspecialchars($department) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-success">Apply Filter</button>
                </div>
                <?php if ($departmentFilter !== ''): ?>
                    <div class="col-auto">
                        <a href="manage_programmes.php" class="btn btn-outline-secondary">Reset</a>
                    </div>
                <?php endif; ?>
            </form>

            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle mb-0">
                    <thead class="table-success">
                        <tr>
                            <th scope="col">ID</th>
                            <th scope="col">Name</th>
                            <th scope="col">Code</th>
                            <th scope="col">Department</th>
                            <th scope="col">Duration</th>
                            <th scope="col" class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($programmes !== []): ?>
                            <?php foreach ($programmes as $programme): ?>
                                <tr>
                                    <td><?= htmlspecialchars($programme['id']) ?></td>
                                    <td><?= htmlspecialchars($programme['name']) ?></td>
                                    <td><?= htmlspecialchars($programme['code']) ?></td>
                                    <td><?= htmlspecialchars($programme['department']) ?></td>
                                    <td><?= htmlspecialchars($programme['duration']) ?> Years</td>
                                    <td class="text-center">
                                        <a href="view_programme.php?id=<?= urlencode((string) $programme['id']) ?>" class="btn btn-sm btn-outline-success me-1" title="View Programme">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="edit_programme.php?id=<?= urlencode((string) $programme['id']) ?>" class="btn btn-sm btn-outline-primary me-1" title="Edit Programme">
                                            <i class="bi bi-pencil-square"></i>
                                        </a>
                                        <form action="delete_programme.php" method="POST" class="d-inline delete-programme-form">
                                            <input type="hidden" name="id" value="<?= htmlspecialchars($programme['id']) ?>">
                                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(get_csrf_token()) ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete Programme">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">No programmes found.</td>
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
document.querySelectorAll('.delete-programme-form').forEach(form => {
    form.addEventListener('submit', function (e) {
        if (!confirm('Are you sure you want to delete this programme?')) {
            e.preventDefault();
        }
    });
});
</script>
</body>
</html>
