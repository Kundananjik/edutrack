<?php
// ============================================
// ADMIN - MANAGE STUDENTS
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
require_once '../../includes/functions.php';

require_login();
require_role(['admin']);

// Fetch students with programme info
try {
    $stmt = $pdo->prepare("
        SELECT s.user_id, s.student_number, s.programme_id, u.name, u.email, u.status, p.name AS programme_name
        FROM students s
        JOIN users u ON s.user_id = u.id
        JOIN programmes p ON s.programme_id = p.id
        ORDER BY u.name
    ");
    $stmt->execute();
    $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Database error fetching students: " . $e->getMessage());
    $_SESSION['error_message'] = "Failed to load students.";
    $students = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Students - EduTrack Admin</title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="css/dashboard.css">
</head>
<body class="bg-light d-flex flex-column min-vh-100">

<?php require_once '../../includes/admin_navbar.php'; ?>

<main class="container py-5 flex-grow-1">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="fw-bold">Manage Students</h1>
        <div>
            <a href="dashboard.php" class="btn btn-secondary me-2">
                <i class="fas fa-arrow-left"></i> Dashboard
            </a>
            <a href="add_student.php" class="btn btn-success">
                <i class="fas fa-plus"></i> Add Student
            </a>
        </div>
    </div>

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

    <div class="card shadow-sm rounded-4 p-3">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th scope="col">Name</th>
                        <th scope="col">Student Number</th>
                        <th scope="col">Email</th>
                        <th scope="col">Programme</th>
                        <th scope="col">Status</th>
                        <th scope="col" class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($students): ?>
                        <?php foreach ($students as $student): ?>
                            <tr>
                                <td><?= htmlspecialchars($student['name']) ?></td>
                                <td><?= htmlspecialchars($student['student_number']) ?></td>
                                <td><?= htmlspecialchars($student['email']) ?></td>
                                <td><?= htmlspecialchars($student['programme_name']) ?></td>
                                <td>
                                    <span class="badge <?= $student['status'] === 'active' ? 'bg-success' : 'bg-secondary' ?>">
                                        <?= htmlspecialchars(ucfirst($student['status'])) ?>
                                    </span>
                                </td>
                                <td class="text-center">
                                    <a href="edit_student.php?id=<?= urlencode($student['user_id']) ?>" class="btn btn-sm btn-primary me-1" title="Edit Student">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="delete_student.php" method="POST" class="d-inline delete-form">
                                        <input type="hidden" name="id" value="<?= htmlspecialchars($student['user_id']) ?>">
                                        <button type="submit" class="btn btn-sm btn-danger" title="Delete Student">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center text-muted">No students found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<!-- Footer -->
<?php
$footer = __DIR__ . '/../../includes/footer.php';
if (file_exists($footer)) {
    require_once $footer;
} else {
    echo '<footer class="mt-auto py-4 bg-white border-top">
            <div class="container text-center text-muted">
                <p class="mb-0">&copy; ' . date('Y') . ' EduTrack. All rights reserved.</p>
            </div>
          </footer>';
}
?>

<!-- Bootstrap 5 JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script <?= et_csp_attr('script') ?>>
document.querySelectorAll('.delete-form').forEach(form => {
    form.addEventListener('submit', function(e) {
        if (!confirm('Are you sure you want to delete this student?')) {
            e.preventDefault();
        }
    });
});
</script>

</body>
</html>