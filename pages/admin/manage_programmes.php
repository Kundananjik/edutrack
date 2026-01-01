<?php
// ============================================
// ADMIN - MANAGE PROGRAMMES
// ============================================

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
require_once '../../includes/csrf.php';
require_once '../../includes/functions.php';

require_login();
require_role(['admin']);

// Fetch distinct departments for filter
try {
    $stmt = $pdo->query("SELECT DISTINCT department FROM programmes ORDER BY department");
    $departments = $stmt->fetchAll(PDO::FETCH_COLUMN);
} catch (PDOException $e) {
    error_log("Database error fetching departments: " . $e->getMessage());
    $departments = [];
}

// Handle filtering
$departmentFilter = trim($_GET['department'] ?? '');

try {
    $query = "SELECT * FROM programmes";
    $params = [];

    if (!empty($departmentFilter)) {
        $query .= " WHERE department = ?";
        $params[] = $departmentFilter;
    }

    $query .= " ORDER BY id DESC";

    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $programmes = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Database error in manage_programmes.php: " . $e->getMessage());
    $_SESSION['error_message'] = "Could not fetch programme data. Please try again later.";
    $programmes = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Programmes - EduTrack Admin</title>
    
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="css/dashboard.css">
</head>
<body class="bg-light">

<?php require_once '../../includes/admin_navbar.php'; ?>

<main class="container py-5">
    <div class="card shadow-sm rounded-4 p-4">
        <h2 class="mb-4">Manage Programmes</h2>

        <div class="d-flex justify-content-between mb-3">
            <a href="dashboard.php" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
            <!-- Add Programme -->
            <a href="add_programme.php" class="btn btn-success">
                <i class="fas fa-plus"></i> Add Programme
            </a>
        </div>

        <!-- Filter Form -->
        <form method="GET" class="row g-3 mb-3">
            <div class="col-md-4">
                <label for="department" class="form-label">Filter by Department:</label>
                <select name="department" id="department" class="form-select" onchange="this.form.submit()">
                    <option value="">All Departments</option>
                    <?php foreach ($departments as $dept): ?>
                        <option value="<?= htmlspecialchars($dept) ?>" <?= $departmentFilter === $dept ? 'selected' : '' ?>>
                            <?= htmlspecialchars($dept) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </form>

        <!-- Alerts -->
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

        <!-- Programmes Table -->
        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th scope="col">ID</th>
                        <th scope="col">Name</th>
                        <th scope="col">Code</th>
                        <th scope="col">Department</th>
                        <th scope="col">Duration (Years)</th>
                        <th scope="col" class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($programmes)): ?>
                        <?php foreach ($programmes as $programme): ?>
                            <tr>
                                <td><?= htmlspecialchars($programme['id']) ?></td>
                                <td><?= htmlspecialchars($programme['name']) ?></td>
                                <td><?= htmlspecialchars($programme['code']) ?></td>
                                <td><?= htmlspecialchars($programme['department']) ?></td>
                                <td><?= htmlspecialchars($programme['duration']) ?></td>
                                <td class="text-center">
                                    <a href="view_programme.php?id=<?= urlencode($programme['id']) ?>" class="btn btn-info btn-sm me-1" title="View">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="edit_programme.php?id=<?= urlencode($programme['id']) ?>" class="btn btn-secondary btn-sm me-1" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="delete_programme.php" method="POST" class="d-inline delete-programme-form">
                                        <input type="hidden" name="id" value="<?= htmlspecialchars($programme['id']) ?>">
                                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(get_csrf_token()) ?>">
                                        <button type="submit" class="btn btn-danger btn-sm" title="Delete">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center text-muted">No programmes found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<?php require_once '../../includes/footer.php'; ?>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script <?= et_csp_attr('script') ?>>
document.querySelectorAll('.delete-programme-form').forEach(form => {
    form.addEventListener('submit', function(e) {
        if (!confirm('Are you sure you want to delete this programme?')) {
            e.preventDefault();
        }
    });
});
</script>

</body>
</html>