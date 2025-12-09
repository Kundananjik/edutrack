<?php
// Preload (auto-locate includes/preload.php)
$__et=__DIR__;
for($__i=0;$__i<6;$__i++){
    $__p=$__et . '/includes/preload.php';
    if (file_exists($__p)) { require_once $__p; break; }
    $__et=dirname($__et);
}
unset($__et,$__i,$__p);
require_once '../../includes/auth_check.php';
require_once '../../includes/db.php';
require_once '../../includes/functions.php';
require_once '../../includes/csrf.php';

require_login();
require_role(['admin']);

$id = intval($_GET['id'] ?? 0);
if ($id <= 0) {
    redirect('manage_students.php');
}

// Fetch student data
$stmt = $pdo->prepare("
    SELECT u.id, u.name, u.email, u.status, s.student_number, s.programme_id
    FROM users u
    JOIN students s ON u.id = s.user_id
    WHERE u.id = ?
");
$stmt->execute([$id]);
$student = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$student) {
    $_SESSION['error_message'] = "Student not found.";
    redirect('manage_students.php');
}

// Fetch programmes
$programmes = $pdo->query("SELECT id, name FROM programmes ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);

// Initialize form variables (preserve submitted values)
$name = $_POST['name'] ?? $student['name'];
$student_number = $_POST['student_number'] ?? $student['student_number'];
$email = $_POST['email'] ?? $student['email'];
$programme_id = $_POST['programme_id'] ?? $student['programme_id'];
$status = $_POST['status'] ?? $student['status'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $_SESSION['error_message'] = "Invalid CSRF token.";
    } else {
        $password = trim($_POST['password'] ?? '');

        if (empty($name) || empty($email) || empty($student_number) || $programme_id <= 0) {
            $_SESSION['error_message'] = "All fields are required.";
        } else {
            try {
                $pdo->beginTransaction();

                $updateUserQuery = "UPDATE users SET name = ?, email = ?, status = ?";
                $params = [$name, $email, $status];
                if (!empty($password)) {
                    $updateUserQuery .= ", password = ?";
                    $params[] = password_hash($password, PASSWORD_DEFAULT);
                }
                $updateUserQuery .= " WHERE id = ?";
                $params[] = $id;

                $stmt = $pdo->prepare($updateUserQuery);
                $stmt->execute($params);

                $stmt = $pdo->prepare("UPDATE students SET student_number = ?, programme_id = ? WHERE user_id = ?");
                $stmt->execute([$student_number, $programme_id, $id]);

                $pdo->commit();
                $_SESSION['success_message'] = "Student updated successfully!";
                redirect('manage_students.php');
            } catch (PDOException $e) {
                $pdo->rollBack();
                error_log($e->getMessage());
                $_SESSION['error_message'] = "Failed to update student.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

</head>
<body>
<?php require_once '../../includes/admin_navbar.php'; ?>

<div class="container py-4">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="css/dashboard.css">
</head>
<body class="bg-light">

<div class="container my-5">
    <a href="manage_students.php" class="btn btn-outline-secondary mb-3">
        <i class="fas fa-arrow-left"></i> Back to Students
    </a>

    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">
            <h3 class="mb-0"><i class="fas fa-user-edit"></i> Edit Student</h3>
        </div>
        <div class="card-body">
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

            <!-- Form -->
            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(get_csrf_token()) ?>">

                <div class="mb-3">
                    <label for="name" class="form-label">Full Name</label>
                    <input type="text" name="name" id="name" class="form-control" required value="<?= htmlspecialchars($name) ?>">
                </div>

                <div class="mb-3">
                    <label for="student_number" class="form-label">Student Number</label>
                    <input type="text" name="student_number" id="student_number" class="form-control" required value="<?= htmlspecialchars($student_number) ?>">
                </div>

                <div class="mb-3">
                    <label for="programme_id" class="form-label">Programme</label>
                    <select name="programme_id" id="programme_id" class="form-select" required>
                        <option value="">Select a Programme</option>
                        <?php foreach ($programmes as $programme): ?>
                            <option value="<?= htmlspecialchars($programme['id']) ?>" <?= ($programme_id == $programme['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($programme['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" name="email" id="email" class="form-control" required value="<?= htmlspecialchars($email) ?>">
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label">Password <small class="text-muted">(Leave blank to keep current)</small></label>
                    <input type="password" name="password" id="password" class="form-control">
                </div>

                <div class="mb-3">
                    <label for="status" class="form-label">Status</label>
                    <select name="status" id="status" class="form-select">
                        <option value="active" <?= ($status === 'active') ? 'selected' : '' ?>>Active</option>
                        <option value="suspended" <?= ($status === 'suspended') ? 'selected' : '' ?>>Suspended</option>
                    </select>
                </div>

                <button type="submit" class="btn btn-success">
                    <i class="fas fa-save"></i> Update Student
                </button>
            </form>
        </div>
    </div>
</div>

<!-- Bootstrap JS Bundle (with Popper) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<?php require_once '../../includes/footer.php'; ?>

</body>
</html>
