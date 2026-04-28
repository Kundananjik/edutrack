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
require_once '../../includes/csrf.php';

require_login();
require_role(['admin']);

$id = (int) ($_GET['id'] ?? 0);
if ($id <= 0) {
    redirect('manage_students.php');
}

$stmt = $pdo->prepare('
    SELECT u.id, u.name, u.email, u.status, s.student_number, s.programme_id
    FROM users u
    JOIN students s ON u.id = s.user_id
    WHERE u.id = ?
');
$stmt->execute([$id]);
$student = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$student) {
    $_SESSION['error_message'] = 'Student not found.';
    redirect('manage_students.php');
}

$programmes = $pdo->query('SELECT id, name FROM programmes ORDER BY name')->fetchAll(PDO::FETCH_ASSOC);

$name = $_POST['name'] ?? $student['name'];
$studentNumber = $_POST['student_number'] ?? $student['student_number'];
$email = $_POST['email'] ?? $student['email'];
$programmeId = $_POST['programme_id'] ?? $student['programme_id'];
$status = $_POST['status'] ?? $student['status'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $_SESSION['error_message'] = 'Invalid CSRF token.';
    } else {
        $password = trim($_POST['password'] ?? '');

        if ($name === '' || $email === '' || $studentNumber === '' || (int) $programmeId <= 0) {
            $_SESSION['error_message'] = 'All fields are required.';
        } else {
            try {
                $pdo->beginTransaction();

                $updateUserQuery = 'UPDATE users SET name = ?, email = ?, status = ?';
                $params = [$name, $email, $status];
                if ($password !== '') {
                    $updateUserQuery .= ', password = ?';
                    $params[] = password_hash($password, PASSWORD_DEFAULT);
                }
                $updateUserQuery .= ' WHERE id = ?';
                $params[] = $id;

                $stmt = $pdo->prepare($updateUserQuery);
                $stmt->execute($params);

                $stmt = $pdo->prepare('UPDATE students SET student_number = ?, programme_id = ? WHERE user_id = ?');
                $stmt->execute([$studentNumber, (int) $programmeId, $id]);

                $pdo->commit();
                $_SESSION['success_message'] = 'Student updated successfully!';
                redirect('manage_students.php');
            } catch (PDOException $e) {
                $pdo->rollBack();
                error_log($e->getMessage());
                $_SESSION['error_message'] = 'Failed to update student.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/png" href="<?= asset_url('assets/favicon.png') ?>">
    <title>Edit Student - EduTrack Admin</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="css/dashboard.css">
</head>
<body class="bg-light d-flex flex-column min-vh-100">

<?php require_once '../../includes/admin_navbar.php'; ?>

<main class="container py-5 flex-grow-1">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <h1 class="fw-bold mb-2">Edit Student</h1>
            <p class="text-muted mb-0">Update student identity, programme, status, and optional password.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="view_student.php?id=<?= urlencode((string) $student['id']) ?>" class="btn btn-outline-success">
                <i class="bi bi-eye"></i> View Student
            </a>
            <a href="manage_students.php" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Back to Students
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
            <form method="POST" class="row g-3">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(get_csrf_token()) ?>">

                <div class="col-md-6">
                    <label for="name" class="form-label">Full Name</label>
                    <input type="text" name="name" id="name" class="form-control" required value="<?= htmlspecialchars($name) ?>">
                </div>

                <div class="col-md-6">
                    <label for="student_number" class="form-label">Student Number</label>
                    <input type="text" name="student_number" id="student_number" class="form-control" required value="<?= htmlspecialchars($studentNumber) ?>">
                </div>

                <div class="col-md-6">
                    <label for="programme_id" class="form-label">Programme</label>
                    <select name="programme_id" id="programme_id" class="form-select" required>
                        <option value="">Select a Programme</option>
                        <?php foreach ($programmes as $programme): ?>
                            <option value="<?= htmlspecialchars($programme['id']) ?>" <?= (int) $programmeId === (int) $programme['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($programme['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-6">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" name="email" id="email" class="form-control" required value="<?= htmlspecialchars($email) ?>">
                </div>

                <div class="col-md-6">
                    <label for="status" class="form-label">Status</label>
                    <select name="status" id="status" class="form-select">
                        <option value="active" <?= $status === 'active' ? 'selected' : '' ?>>Active</option>
                        <option value="suspended" <?= $status === 'suspended' ? 'selected' : '' ?>>Suspended</option>
                    </select>
                </div>

                <div class="col-md-6">
                    <label for="password" class="form-label">Password <small class="text-muted">(Leave blank to keep current)</small></label>
                    <input type="password" name="password" id="password" class="form-control">
                </div>

                <div class="col-12">
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-floppy"></i> Update Student
                    </button>
                </div>
            </form>
        </div>
    </section>
</main>

<?php require_once '../../includes/footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
