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
// auth/login.php

require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/session.php';
require_once '../includes/functions.php';
require_once '../includes/csrf.php';

// Redirect if already logged in
if (isLoggedIn()) {
    switch ($_SESSION['role']) {
        case 'admin':
            redirect('../pages/admin/dashboard.php');
            break;
        case 'lecturer':
            redirect('../pages/lecturer/dashboard.php');
            break;
        case 'student':
            redirect('../pages/student/dashboard.php');
            break;
        default:
            redirect('../index.php');
    }
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
        $error = 'Invalid CSRF token.';
    } else {
        $identifier = trim($_POST['identifier'] ?? '');
        $password   = $_POST['password'] ?? '';
        $role_hint  = $_POST['role_hint'] ?? 'auto';
        $remember_me = !empty($_POST['remember_me']);

        $allowed_roles = ['auto', 'student', 'lecturer', 'admin'];
        if (!in_array($role_hint, $allowed_roles, true)) {
            $role_hint = 'auto';
        }

        if (empty($identifier) || empty($password)) {
            $error = 'Both fields are required.';
        } else {
            try {
                $user = null;

                // 1️⃣ Try login by email (for all users)
                $stmt = $pdo->prepare('SELECT id, name, password, role FROM users WHERE email = :identifier LIMIT 1');
                $stmt->execute(['identifier' => $identifier]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);

                // 2️⃣ If not found by email, check if it's a student_number
                if (!$user) {
                    $stmt = $pdo->prepare("
                        SELECT u.id, u.name, u.password, u.role 
                        FROM users u
                        INNER JOIN students s ON s.user_id = u.id
                        WHERE s.student_number = :student_number 
                        AND u.role = 'student'
                        LIMIT 1
                    ");
                    $stmt->execute(['student_number' => $identifier]);
                    $user = $stmt->fetch(PDO::FETCH_ASSOC);
                }

                // 3️⃣ Verify credentials
                if ($user && password_verify($password, $user['password'])) {
                    if ($role_hint !== 'auto' && $user['role'] !== $role_hint) {
                        $error = 'Role selection does not match this account.';
                    } else {
                    session_regenerate_id(true);
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['name']    = $user['name'];
                    $_SESSION['role']    = $user['role'];
                    $_SESSION['REMEMBER_ME'] = $remember_me;

                    if ($remember_me) {
                        $cookieParams = session_get_cookie_params();
                        setcookie(
                            session_name(),
                            session_id(),
                            time() + (60 * 60 * 24 * 30),
                            $cookieParams['path'],
                            $cookieParams['domain'],
                            !empty($_SERVER['HTTPS']),
                            true
                        );
                    }

                    switch ($user['role']) {
                        case 'admin':
                            redirect('../pages/admin/dashboard.php');
                            break;
                        case 'lecturer':
                            redirect('../pages/lecturer/dashboard.php');
                            break;
                        case 'student':
                            redirect('../pages/student/dashboard.php');
                            break;
                        default:
                            session_destroy();
                            redirect('login.php?error=unknown_role');
                    }
                    }
                } else {
                    $error = 'Invalid login credentials.';
                }

            } catch (PDOException $e) {
                error_log('Login error: ' . $e->getMessage());
                $error = 'An error occurred. Please try again later.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - EduTrack</title>
    <link rel="icon" type="image/png" href="<?= asset_url('assets/favicon.png') ?>">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <link rel="stylesheet" href="<?= asset_url('assets/css/style.css') ?>">
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
            <p class="eyebrow dark">Secure Access</p>
            <h1>Sign in to EduTrack</h1>
            <p>Access your role-based dashboard and manage attendance in real time.</p>
            <ul>
                <li>Use your student number or institutional email.</li>
                <li>Attendance records update instantly.</li>
                <li>Secure sessions with role-based access.</li>
            </ul>
        </section>
        <section class="auth-card">
            <h2>Welcome back</h2>
            <p class="auth-subtitle">Enter your credentials to continue.</p>

            <?php if ($error): ?>
                <div class="error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <form class="auth-form" method="POST" action="login.php" autocomplete="off">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(get_csrf_token()) ?>">

                <label for="role_hint">Role (optional)</label>
                <select name="role_hint" id="role_hint">
                    <option value="auto">Auto-detect</option>
                    <option value="student">Student</option>
                    <option value="lecturer">Lecturer</option>
                    <option value="admin">Admin</option>
                </select>

                <label for="identifier">Student Number or Email</label>
                <input type="text" name="identifier" id="identifier" required>

                <label for="password">Password</label>
                <input type="password" name="password" id="password" required>

                <div class="auth-options">
                    <label class="checkbox">
                        <input type="checkbox" name="remember_me">
                        <span>Remember me on this device</span>
                    </label>
                </div>

                <button type="submit" class="btn btn-success">Login</button>
            </form>

            <div class="auth-links">
                <a href="forgot_password.php">Forgot Password?</a>
                <span>Don’t have an account? <a href="register.php">Register Here</a></span>
            </div>
        </section>
    </div>
</main>

<?php require_once '../includes/footer.php'; ?>

</body>
</html>
