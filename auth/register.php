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
// auth/register.php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/session.php';
require_once '../includes/functions.php';
require_once '../includes/csrf.php';

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
        $errors[] = 'Invalid CSRF token.';
    }
    $name = cleanInput($_POST['name'] ?? '');
    $student_number = cleanInput($_POST['student_number'] ?? '');
    $email = cleanInput($_POST['email'] ?? '');
    $programme_id = cleanInput($_POST['programme'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Validation
    if (empty($name) || empty($student_number) || empty($email) || empty($programme_id) || empty($password)) {
        $errors[] = 'All fields are required.';
    }

    if ($password !== $confirm_password) {
        $errors[] = 'Passwords do not match.';
    }

    if (strlen($password) < 8) {
        $errors[] = 'Password must be at least 8 characters long.';
    } elseif (!preg_match('/[A-Za-z]/', $password) || !preg_match('/\d/', $password)) {
        $errors[] = 'Password must include at least one letter and one number.';
    }

    // Check duplicate email
    $stmt = $pdo->prepare('SELECT id FROM users WHERE email = :em');
    $stmt->execute(['em' => $email]);
    if ($stmt->rowCount() > 0) {
        $errors[] = 'Email already registered.';
    }

    // Check duplicate student_number
    $stmt = $pdo->prepare('SELECT user_id FROM students WHERE student_number = :sn');
    $stmt->execute(['sn' => $student_number]);
    if ($stmt->rowCount() > 0) {
        $errors[] = 'Student Number already registered.';
    }

    if (empty($errors)) {
        try {
            $pdo->beginTransaction();

            // Insert into users table
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("
                INSERT INTO users (name, email, password, role, status, created_at)
                VALUES (:name, :email, :password, 'student', 'active', CURRENT_TIMESTAMP)
            ");
            $stmt->execute([
                'name' => $name,
                'email' => $email,
                'password' => $hashed
            ]);

            $user_id = $pdo->lastInsertId();

            // Insert into students table
            $stmt = $pdo->prepare('
                INSERT INTO students (user_id, student_number, programme_id)
                VALUES (:user_id, :student_number, :programme_id)
            ');
            $stmt->execute([
                'user_id' => $user_id,
                'student_number' => $student_number,
                'programme_id' => $programme_id
            ]);

            $pdo->commit();
            $success = "Registration successful! You can now log in.";
        } catch (PDOException $e) {
            $pdo->rollBack();
            error_log('Registration error: ' . $e->getMessage());
            $errors[] = 'Database error: Could not register.';
        }
    }
}

// Fetch programmes for dropdown
$programmes = [];
try {
    $stmt = $pdo->query('SELECT id, name FROM programmes ORDER BY name ASC');
    $programmes = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log('Failed to fetch programmes: ' . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Registration</title>
    <link rel="icon" type="image/png" href="<?= asset_url('assets/favicon.png') ?>">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <link rel="stylesheet" href="<?= asset_url('assets/css/style.css') ?>">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

</head>
<body class="auth-page">
<nav class="navbar navbar-light landing-navbar">
    <div class="container landing-nav">
        <!-- Brand (Centered) -->
        <a class="navbar-brand mx-auto" href="../index.php">
            <img src="<?= asset_url('assets/logo.png') ?>" alt="EduTrack Logo" height="40" width="auto">
        </a>

        <!-- Desktop Navigation -->
        <div class="d-none d-lg-flex ms-auto landing-nav-actions">
            <ul class="navbar-nav d-flex flex-row gap-2 align-items-center">
                <li class="nav-item">
                    <a class="btn btn-success ms-2" href="login.php">Login</a>
                </li>
            </ul>
        </div>
        <div class="d-lg-none ms-auto landing-nav-actions">
            <a href="login.php" class="btn btn-success w-100">Login</a>
        </div>
    </div>
</nav>

<main class="auth-wrapper">
    <div class="container auth-layout">
        <section class="auth-aside">
            <p class="eyebrow dark">Create Account</p>
            <h1>Join EduTrack in minutes.</h1>
            <p>Register to access real-time attendance insights, personalized dashboards, and secure records.</p>
            <ul>
                <li>Use your student number and institutional email.</li>
                <li>Choose your programme to get the right dashboard.</li>
                <li>Secure credentials with strong password rules.</li>
            </ul>
        </section>
        <section class="auth-card">
            <h2>Student Registration</h2>
            <p class="auth-subtitle">Create your account to get started.</p>

            <?php if (!empty($errors)): ?>
                <div class="error">
                    <ul><?php foreach ($errors as $e) {
                        echo "<li>$e</li>";
                    } ?></ul>
                </div>
            <?php elseif ($success): ?>
                <div class="success">
                    <?php echo htmlspecialchars($success); ?>
                    <a href="login.php">Login here</a>.
                </div>
            <?php endif; ?>

            <form class="auth-form" method="POST" action="" autocomplete="off">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(get_csrf_token()) ?>">
                <label for="name">Full Name</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-person" aria-hidden="true"></i></span>
                    <input type="text" name="name" id="name" required>
                </div>

                <label for="student_number">Student Number</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-hash" aria-hidden="true"></i></span>
                    <input type="text" name="student_number" id="student_number" required>
                </div>

                <label for="email">Email</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-envelope" aria-hidden="true"></i></span>
                    <input type="email" name="email" id="email" required>
                </div>

                <label for="programme">Programme</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-mortarboard" aria-hidden="true"></i></span>
                    <select name="programme" id="programme" required>
                        <option value="">-- Select Programme --</option>
                        <?php foreach ($programmes as $p): ?>
                            <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <label for="password">Password</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-lock" aria-hidden="true"></i></span>
                    <input type="password" name="password" id="password" required>
                </div>

                <label for="confirm_password">Confirm Password</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-shield-lock" aria-hidden="true"></i></span>
                    <input type="password" name="confirm_password" id="confirm_password" required>
                </div>

                <button type="submit" class="btn btn-success">Create Account</button>
            </form>

            <div class="auth-links">
                <a href="login.php">Already registered? Login here</a>
            </div>
        </section>
    </div>
</main>

<?php require_once '../includes/footer.php'; ?>
</body>
</html>
