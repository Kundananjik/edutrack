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
require_once '../../includes/db.php';
require_once '../../includes/functions.php';
require_once '../../includes/csrf.php';

require_login();
require_role(['admin']);

$id = intval($_GET['id'] ?? 0);
if ($id <= 0) {
    redirect('manage_lecturers.php');
}

// Fetch lecturer data
$stmt = $pdo->prepare("SELECT id, name, email, phone, status FROM users WHERE id = ? AND role = 'lecturer'");
$stmt->execute([$id]);
$lecturer = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$lecturer) {
    $_SESSION['error_message'] = 'Lecturer not found.';
    redirect('manage_lecturers.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $_SESSION['error_message'] = 'Invalid CSRF token.';
    } else {
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $status = trim($_POST['status'] ?? 'active');
        $password = trim($_POST['password'] ?? '');

        if (empty($name) || empty($email)) {
            $_SESSION['error_message'] = 'Name and email are required.';
        } else {
            try {
                $updateQuery = 'UPDATE users SET name = ?, email = ?, phone = ?, status = ?';
                $params = [$name, $email, $phone, $status];
                if (!empty($password)) {
                    $updateQuery .= ', password = ?';
                    $params[] = password_hash($password, PASSWORD_DEFAULT);
                }
                $updateQuery .= ' WHERE id = ?';
                $params[] = $id;

                $stmt = $pdo->prepare($updateQuery);
                $stmt->execute($params);

                $_SESSION['success_message'] = 'Lecturer updated successfully!';
                redirect('manage_lecturers.php');
            } catch (PDOException $e) {
                error_log($e->getMessage());
                $_SESSION['error_message'] = 'Failed to update lecturer.';
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

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Custom dashboard styles (optional) -->
    <link rel="stylesheet" href="css/dashboard.css">
</head>
<body>
<div class="container py-4">
    <h1 class="mb-4">Edit Lecturer</h1>

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

    <form action="edit_lecturer.php?id=<?= $lecturer['id'] ?>" method="POST" class="needs-validation" novalidate>
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(get_csrf_token()) ?>">

        <div class="mb-3">
            <label for="name" class="form-label">Full Name</label>
            <input type="text" name="name" id="name" class="form-control" required
                   value="<?= htmlspecialchars($lecturer['name']) ?>">
        </div>

        <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input type="email" name="email" id="email" class="form-control" required
                   value="<?= htmlspecialchars($lecturer['email']) ?>">
        </div>

        <div class="mb-3">
            <label for="phone" class="form-label">Phone</label>
            <input type="text" name="phone" id="phone" class="form-control"
                   value="<?= htmlspecialchars($lecturer['phone']) ?>">
        </div>

        <div class="mb-3">
            <label for="password" class="form-label">Password <small class="text-muted">(Leave blank to keep current)</small></label>
            <input type="password" name="password" id="password" class="form-control">
        </div>

        <div class="mb-3">
            <label for="status" class="form-label">Status</label>
            <select name="status" id="status" class="form-select">
                <option value="active" <?= ($lecturer['status'] === 'active') ? 'selected' : '' ?>>Active</option>
                <option value="suspended" <?= ($lecturer['status'] === 'suspended') ? 'selected' : '' ?>>Suspended</option>
            </select>
        </div>

        <button type="submit" class="btn btn-success">
            <i class="fas fa-save"></i> Update Lecturer
        </button>
        <a href="manage_lecturers.php" class="btn btn-secondary ms-2">
            <i class="fas fa-arrow-left"></i> Back to Lecturers
        </a>
    </form>
</div>

<!-- Bootstrap 5 JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<?php require_once '../../includes/footer.php'; ?>
</body>
</html>
