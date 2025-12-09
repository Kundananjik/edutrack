<?php
// Preload (auto-locate includes/preload.php)
$__et=__DIR__;
for($__i=0;$__i<6;$__i++){
    $__p=$__et . '/includes/preload.php';
    if (file_exists($__p)) { require_once $__p; break; }
    $__et=dirname($__et);
}
unset($__et,$__i,$__p);

// pages/admin/manage_lecturers.php
require_once '../../includes/auth_check.php';
require_once '../../includes/config.php';
require_once '../../includes/db.php';
require_once '../../includes/functions.php';
require_once '../../includes/csrf.php';

require_login();
require_role(['admin']);

// Generate CSRF token if not exists
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Fetch lecturers
try {
    $stmt = $pdo->prepare("
        SELECT u.id, u.name, u.email, u.status, u.phone
        FROM users u
        WHERE u.role = 'lecturer'
        ORDER BY u.name
    ");
    $stmt->execute();
    $lecturers = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Database error fetching lecturers: " . $e->getMessage());
    $_SESSION['error_message'] = "Failed to load lecturers.";
    $lecturers = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Lecturers</title>

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Custom EduTrack CSS -->
    <link rel="stylesheet" href="css/dashboard.css">
</head>
<body class="bg-light">

    <?php require_once '../../includes/admin_navbar.php'; ?>

    <div class="container py-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="fw-bold">Manage Lecturers</h1>
            <a href="add_lecturer.php" class="btn btn-success rounded-3">
                <i class="fas fa-plus"></i> Add Lecturer
            </a>
        </div>

        <!-- Alert Messages -->
        <?php if (!empty($_SESSION['success_message'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($_SESSION['success_message']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['success_message']); ?>
        <?php endif; ?>

        <?php if (!empty($_SESSION['error_message'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($_SESSION['error_message']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['error_message']); ?>
        <?php endif; ?>

        <div class="card shadow-sm rounded-4">
            <div class="card-body">
                <div class="d-flex justify-content-between mb-3">
                    <a href="dashboard.php" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Dashboard
                    </a>
                </div>

                <div class="table-responsive">
                    <table class="table table-striped table-hover align-middle">
                        <thead class="table-success">
                            <tr>
                                <th scope="col">Name</th>
                                <th scope="col">Email</th>
                                <th scope="col">Phone</th>
                                <th scope="col">Status</th>
                                <th scope="col" class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if ($lecturers): ?>
                            <?php foreach ($lecturers as $lecturer): ?>
                                <tr>
                                    <td><?= htmlspecialchars($lecturer['name']) ?></td>
                                    <td><?= htmlspecialchars($lecturer['email']) ?></td>
                                    <td><?= htmlspecialchars($lecturer['phone'] ?: '—') ?></td>
                                    <td>
                                        <span class="badge <?= $lecturer['status'] === 'active' ? 'bg-success' : 'bg-secondary' ?>">
                                            <?= htmlspecialchars(ucfirst($lecturer['status'])) ?>
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group" role="group">
                                            <a href="view_lecturer.php?id=<?= $lecturer['id'] ?>" class="btn btn-sm btn-outline-info" title="View Lecturer">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="edit_lecturer.php?id=<?= $lecturer['id'] ?>" class="btn btn-sm btn-outline-secondary" title="Edit Lecturer">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form action="delete_lecturer.php" method="POST" class="d-inline">
                                                <input type="hidden" name="id" value="<?= htmlspecialchars($lecturer['id']) ?>">
                                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete Lecturer"
                                                        onclick="return confirm('Are you sure you want to delete this lecturer?');">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="5" class="text-center text-muted py-4">No lecturers found.</td></tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <?php require_once '../../includes/footer.php'; ?>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
