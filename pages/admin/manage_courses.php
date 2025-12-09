<?php
// pages/admin/manage_courses.php

// Auto-load preload.php (searches up to 6 directories)
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

// Fetch programmes for dropdown filter
try {
    $stmt = $pdo->query("SELECT id, name FROM programmes ORDER BY name ASC");
    $programmesForFilter = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Error fetching programmes: " . $e->getMessage());
    $programmesForFilter = [];
}

// Handle filtering
$programmeFilter = intval($_GET['programme'] ?? 0);

try {
    $query = "
        SELECT 
            c.id, 
            c.name, 
            c.course_code, 
            c.status,
            p.name AS programme_name,
            GROUP_CONCAT(u.name ORDER BY u.name SEPARATOR ', ') AS lecturer_name
        FROM 
            courses c
        INNER JOIN 
            programmes p ON c.programme_id = p.id
        LEFT JOIN 
            lecturer_courses lc ON c.id = lc.course_id
        LEFT JOIN 
            users u ON lc.lecturer_id = u.id
    ";

    $params = [];
    if ($programmeFilter > 0) {
        $query .= " WHERE c.programme_id = ?";
        $params[] = $programmeFilter;
    }

    $query .= " GROUP BY c.id ORDER BY c.id DESC";

    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $courses = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    error_log("Database error in manage_courses.php: " . $e->getMessage());
    $courses = [];
    $_SESSION['error_message'] = "Could not fetch course data. Please try again later.";
}
?>

<!DOCTYPE html>
<html lang="en">

</head>
<body>
<?php require_once '../../includes/admin_navbar.php'; ?>

<main class="container py-4">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Custom Admin CSS (EduTrack Branding) -->
    <link rel="stylesheet" href="css/dashboard.css">
</head>
<body>

<div class="container-fluid py-4">
    <div class="container bg-white rounded shadow-sm p-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="text-success fw-bold">Manage Courses</h1>
            <a href="add_course.php" class="btn btn-success">
                <i class="fas fa-plus"></i> Add Course
            </a>
        </div>

        <div class="d-flex justify-content-between mb-3">
            <a href="dashboard.php" class="btn btn-outline-secondary"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>
        </div>

        <!-- Filter Form -->
        <form method="GET" class="row g-3 align-items-center mb-4">
            <div class="col-auto">
                <label for="programme" class="col-form-label fw-semibold">Filter by Programme:</label>
            </div>
            <div class="col-auto">
                <select name="programme" id="programme" class="form-select" onchange="this.form.submit()">
                    <option value="0">All Programmes</option>
                    <?php foreach ($programmesForFilter as $programme): ?>
                        <option 
                            value="<?= htmlspecialchars($programme['id']) ?>" 
                            <?= $programmeFilter == $programme['id'] ? 'selected' : '' ?>
                        >
                            <?= htmlspecialchars($programme['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php if ($programmeFilter > 0): ?>
                <div class="col-auto">
                    <a href="manage_courses.php" class="btn btn-outline-secondary">Reset</a>
                </div>
            <?php endif; ?>
        </form>

        <!-- Alerts -->
        <?php if (!empty($_SESSION['success_message'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($_SESSION['success_message']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['success_message']); ?>
        <?php endif; ?>

        <?php if (!empty($_SESSION['error_message'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($_SESSION['error_message']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['error_message']); ?>
        <?php endif; ?>

        <!-- Courses Table -->
        <div class="table-responsive">
            <table class="table table-hover align-middle">
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
                    <?php if (!empty($courses)): ?>
                        <?php foreach ($courses as $course): ?>
                            <tr>
                                <td><?= htmlspecialchars($course['id']) ?></td>
                                <td><?= htmlspecialchars($course['name']) ?></td>
                                <td><?= htmlspecialchars($course['course_code']) ?></td>
                                <td><?= htmlspecialchars($course['programme_name']) ?></td>
                                <td><?= htmlspecialchars($course['lecturer_name'] ?? 'Not Assigned') ?></td>
                                <td>
                                    <span class="badge bg-<?= $course['status'] === 'active' ? 'success' : ($course['status'] === 'inactive' ? 'warning' : 'secondary') ?>">
                                        <?= htmlspecialchars(ucfirst($course['status'])) ?>
                                    </span>
                                </td>
                                <td class="text-center">
                                    <a href="view_course.php?id=<?= urlencode($course['id']) ?>" class="btn btn-sm btn-outline-success me-1">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="edit_course.php?id=<?= urlencode($course['id']) ?>" class="btn btn-sm btn-outline-primary me-1">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="delete_course.php" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this course?');">
                                        <input type="hidden" name="id" value="<?= htmlspecialchars($course['id']) ?>">
                                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(get_csrf_token()) ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="text-center text-muted">No courses found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<?php require_once '../../includes/footer.php'; ?>
</body>
</html>
