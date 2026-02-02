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

$id = intval($_GET['id'] ?? 0);

// Fetch programme details
$programme = null;
if ($id > 0) {
    try {
        $stmt = $pdo->prepare('SELECT * FROM programmes WHERE id = ?');
        $stmt->execute([$id]);
        $programme = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$programme) {
            $_SESSION['error_message'] = 'Programme not found.';
            redirect('manage_programmes.php');
        }

    } catch (PDOException $e) {
        error_log('Database error: ' . $e->getMessage());
        $_SESSION['error_message'] = 'Could not retrieve programme data.';
        redirect('manage_programmes.php');
    }
} else {
    $_SESSION['error_message'] = 'Invalid programme ID.';
    redirect('manage_programmes.php');
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
        $_SESSION['error_message'] = 'Invalid CSRF token.';
    } else {
        $id         = intval($_POST['id'] ?? 0);
        $name       = trim($_POST['name'] ?? '');
        $code       = trim($_POST['code'] ?? '');
        $department = trim($_POST['department'] ?? '');
        $duration   = intval($_POST['duration'] ?? 0);

        $isValid = true;
        if ($id <= 0 || empty($name) || empty($code) || empty($department) || $duration <= 0) {
            $_SESSION['error_message'] = 'All fields are required and duration must be positive.';
            $isValid = false;
        }

        if ($isValid) {
            try {
                $stmt = $pdo->prepare('
                    UPDATE programmes
                    SET name = ?, code = ?, department = ?, duration = ?, updated_at = NOW()
                    WHERE id = ?
                ');
                $stmt->execute([$name, $code, $department, $duration, $id]);

                $_SESSION['success_message'] = ($stmt->rowCount() > 0)
                    ? 'Programme updated successfully!'
                    : 'No changes made.';

                redirect('manage_programmes.php');
            } catch (PDOException $e) {
                if ($e->getCode() == '23000') {
                    $_SESSION['error_message'] = 'A programme with this code already exists.';
                } else {
                    error_log('Database error: ' . $e->getMessage());
                    $_SESSION['error_message'] = 'Failed to update programme.';
                }
            }
        }
    }
}

// Preserve form data
$formData = $_POST ?? $programme;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Programme - EduTrack</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/dashboard.css">

</head>
<body>
<?php require_once '../../includes/admin_navbar.php'; ?>

<div class="container py-4">
    <h1 class="mb-4">Edit Programme: <?= htmlspecialchars($programme['name'] ?? '') ?></h1>

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

    <form action="edit_programme.php" method="POST" class="needs-validation" novalidate>
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(get_csrf_token()) ?>">
        <input type="hidden" name="id" value="<?= htmlspecialchars($id) ?>">

        <div class="mb-3">
            <label for="name" class="form-label">Programme Name</label>
            <input type="text" class="form-control" id="name" name="name" required value="<?= htmlspecialchars($formData['name'] ?? '') ?>">
        </div>

        <div class="mb-3">
            <label for="code" class="form-label">Programme Code</label>
            <input type="text" class="form-control" id="code" name="code" required value="<?= htmlspecialchars($formData['code'] ?? '') ?>">
        </div>

        <div class="mb-3">
            <label for="department" class="form-label">Department</label>
            <input type="text" class="form-control" id="department" name="department" required value="<?= htmlspecialchars($formData['department'] ?? '') ?>">
        </div>

        <div class="mb-3">
            <label for="duration" class="form-label">Duration (Years)</label>
            <input type="number" class="form-control" id="duration" name="duration" min="1" required value="<?= htmlspecialchars($formData['duration'] ?? '') ?>">
        </div>

        <button type="submit" class="btn btn-success">
            <i class="fas fa-save"></i> Save Changes
        </button>
        <a href="manage_programmes.php" class="btn btn-secondary ms-2">
            <i class="fas fa-arrow-left"></i> Back to List
        </a>
    </form>
</div>

<!-- Bootstrap 5 JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<?php require_once '../../includes/footer.php'; ?>

</body>
</html>
