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
require_once '../../includes/csrf.php';
require_once '../../includes/functions.php';

require_login();
require_role(['admin']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
        $_SESSION['error_message'] = 'Invalid CSRF token.';
        redirect('add_programme.php');
    }

    $name = trim($_POST['name'] ?? '');
    $code = trim($_POST['code'] ?? '');
    $department = trim($_POST['department'] ?? '');
    $duration = trim($_POST['duration'] ?? '');

    if ($name === '' || $code === '' || $department === '' || $duration === '') {
        $_SESSION['error_message'] = 'Please fill in all required fields.';
    } elseif (!is_numeric($duration) || (float) $duration <= 0) {
        $_SESSION['error_message'] = 'Duration must be a positive number.';
    } else {
        try {
            $stmt = $pdo->prepare('
                INSERT INTO programmes (name, code, department, duration)
                VALUES (:name, :code, :department, :duration)
            ');
            $stmt->execute([
                ':name' => $name,
                ':code' => $code,
                ':department' => $department,
                ':duration' => $duration,
            ]);

            $_SESSION['success_message'] = 'Programme added successfully!';
            redirect('manage_programmes.php');
        } catch (PDOException $e) {
            if ($e->getCode() === '23000') {
                $_SESSION['error_message'] = 'A programme with this code already exists. Please use a unique code.';
            } else {
                error_log('Database error in add_programme.php: ' . $e->getMessage());
                $_SESSION['error_message'] = 'Failed to add programme. Please try again.';
            }
        }
    }
}

$formValues = $_POST ?? [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/png" href="<?= asset_url('assets/favicon.png') ?>">
    <title>Add Programme - EduTrack Admin</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="css/dashboard.css">
</head>
<body class="bg-light d-flex flex-column min-vh-100">

<?php require_once '../../includes/admin_navbar.php'; ?>

<main class="container py-5 flex-grow-1">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <h1 class="fw-bold mb-2">Add Programme</h1>
            <p class="text-muted mb-0">Create a new academic programme and make it available for course assignment.</p>
        </div>
        <a href="manage_programmes.php" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Back to Programmes
        </a>
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
            <form action="add_programme.php" method="POST" class="row g-3">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(get_csrf_token()) ?>">

                <div class="col-md-6">
                    <label for="name" class="form-label">Programme Name</label>
                    <input type="text" class="form-control" id="name" name="name" required value="<?= htmlspecialchars($formValues['name'] ?? '') ?>">
                </div>

                <div class="col-md-6">
                    <label for="code" class="form-label">Programme Code</label>
                    <input type="text" class="form-control" id="code" name="code" required value="<?= htmlspecialchars($formValues['code'] ?? '') ?>">
                </div>

                <div class="col-md-6">
                    <label for="department" class="form-label">Department</label>
                    <input type="text" class="form-control" id="department" name="department" required value="<?= htmlspecialchars($formValues['department'] ?? '') ?>">
                </div>

                <div class="col-md-6">
                    <label for="duration" class="form-label">Duration (Years)</label>
                    <input type="number" class="form-control" id="duration" name="duration" min="1" required value="<?= htmlspecialchars($formValues['duration'] ?? '') ?>">
                </div>

                <div class="col-12">
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-plus-lg"></i> Add Programme
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
