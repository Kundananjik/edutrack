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
require_once '../../includes/csrf.php';
require_once '../../includes/functions.php';

require_login();
require_role(['admin']);

// Fetch programmes for dropdown
try {
    $stmt = $pdo->prepare('SELECT id, name FROM programmes ORDER BY name');
    $stmt->execute();
    $programmes = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log('Database error fetching programmes: ' . $e->getMessage());
    $_SESSION['error_message'] = 'Could not load programmes for the form.';
    $programmes = [];
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
        $_SESSION['error_message'] = 'Invalid CSRF token.';
    } else {
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = trim($_POST['password'] ?? '');
        $student_number = trim($_POST['student_number'] ?? '');
        $programme_id = intval($_POST['programme_id'] ?? 0);
        $status = trim($_POST['status'] ?? 'active');

        if (empty($name) || empty($email) || empty($password) || empty($student_number) || $programme_id <= 0) {
            $_SESSION['error_message'] = 'All fields are required.';
        } else {
            try {
                $stmt = $pdo->prepare('SELECT COUNT(*) FROM users WHERE email = ?');
                $stmt->execute([$email]);
                if ($stmt->fetchColumn() > 0) {
                    $_SESSION['error_message'] = 'Email already exists.';
                } else {
                    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                    $pdo->beginTransaction();
                    $stmt = $pdo->prepare("INSERT INTO users (name,email,role,status,password) VALUES (?, ?, 'student', ?, ?)");
                    $stmt->execute([$name, $email, $status, $hashedPassword]);
                    $user_id = $pdo->lastInsertId();
                    $stmt = $pdo->prepare('INSERT INTO students (user_id, student_number, programme_id) VALUES (?, ?, ?)');
                    $stmt->execute([$user_id, $student_number, $programme_id]);
                    $pdo->commit();
                    $_SESSION['success_message'] = 'Student added successfully!';
                    redirect('manage_students.php');
                }
            } catch (PDOException $e) {
                $pdo->rollBack();
                error_log('Database error adding student: ' . $e->getMessage());
                $_SESSION['error_message'] = 'Failed to add student.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Student - EduTrack</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <!-- Bootstrap CSS -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

        <!-- Font Awesome -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

        <!-- Custom EduTrack CSS -->
        <link rel="stylesheet" href="css/dashboard.css">
</head>
<body class="bg-light">
    <?php require_once '../../includes/admin_navbar.php'; ?>

<div class="container py-5">
    <div class="card shadow-sm rounded-4">
        <div class="card-body">
            <div class="d-flex justify-content-between mb-3">
                <a href="dashboard.php" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Dashboard
                </a>
            </div>

            <h2 class="mb-4">Add Student</h2>

            <!-- Alerts -->
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

            <form action="add_student.php" method="POST" class="row g-3">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(get_csrf_token()) ?>">

                <div class="col-md-6">
                    <label for="name" class="form-label">Full Name</label>
                    <input type="text" name="name" id="name" class="form-control" required
                           value="<?= htmlspecialchars($_POST['name'] ?? '') ?>">
                </div>

                <div class="col-md-6">
                    <label for="student_number" class="form-label">Student Number</label>
                    <input type="text" name="student_number" id="student_number" class="form-control" required
                           value="<?= htmlspecialchars($_POST['student_number'] ?? '') ?>">
                </div>

                <div class="col-md-6">
                    <label for="programme_id" class="form-label">Programme</label>
                    <select name="programme_id" id="programme_id" class="form-select" required>
                        <option value="">Select a Programme</option>
                        <?php foreach ($programmes as $programme): ?>
                            <option value="<?= $programme['id'] ?>" <?= (($_POST['programme_id'] ?? 0) == $programme['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($programme['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-6">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" name="email" id="email" class="form-control" required
                           value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                </div>

                <div class="col-md-6">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" name="password" id="password" class="form-control" required>
                </div>

                <div class="col-md-6">
                    <label for="status" class="form-label">Status</label>
                    <select name="status" id="status" class="form-select">
                        <option value="active" <?= (($_POST['status'] ?? 'active') === 'active') ? 'selected' : '' ?>>Active</option>
                        <option value="suspended" <?= (($_POST['status'] ?? '') === 'suspended') ? 'selected' : '' ?>>Suspended</option>
                    </select>
                </div>

                <div class="col-12 mt-3">
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-plus"></i> Add Student
                    </button>
                    <a href="manage_students.php" class="btn btn-secondary ms-2">
                        <i class="fas fa-arrow-left"></i> Back to Students
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<?php require_once '../../includes/footer.php'; ?>
</body>
</html>
