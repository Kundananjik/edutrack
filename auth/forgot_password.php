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
// auth/forgot_password.php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';
require_once '../includes/csrf.php';

$message = '';
$message_class = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
        $message = 'Invalid CSRF token.';
        $message_class = 'error';
    } else {
        $identifier = cleanInput($_POST['identifier'] ?? '');

        // Check if user exists
        $stmt = $pdo->prepare('SELECT email FROM users WHERE student_number = :id OR email = :id LIMIT 1');
        $stmt->execute(['id' => $identifier]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            $email = $user['email'];
            $token = bin2hex(random_bytes(32));
            $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));

            // Store reset token
            $insert = $pdo->prepare('INSERT INTO password_resets (email, token, expires_at)
                                 VALUES (:email, :token, :expires)');
            $insert->execute([
                'email' => $email,
                'token' => $token,
                'expires' => $expires
            ]);

            // Simulate email (replace with mail() or PHPMailer in prod)
            $message = 'If an account exists for that email or student number, a reset link has been sent.';
            $message_class = 'success';
        } else {
            $message = 'If an account exists for that email or student number, a reset link has been sent.';
            $message_class = 'success';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - EduTrack</title>
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
            <p class="eyebrow dark">Password Reset</p>
            <h1>Recover access in minutes.</h1>
            <p>Enter your student number or email to receive a secure reset link.</p>
            <ul>
                <li>Links expire after one hour for security.</li>
                <li>Check your inbox and spam folder.</li>
                <li>Contact support if you do not receive it.</li>
            </ul>
        </section>
        <section class="auth-card">
            <h2>Forgot Password</h2>
            <p class="auth-subtitle">We’ll help you get back in.</p>

            <?php if ($message): ?>
                <div class="<?= htmlspecialchars($message_class) ?>">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <form class="auth-form" method="POST" action="" autocomplete="off">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(get_csrf_token()) ?>">
                <label for="identifier">Student Number or Email</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-person-badge" aria-hidden="true"></i></span>
                    <input type="text" name="identifier" id="identifier" required>
                </div>
                <button type="submit" class="btn btn-success">Send Reset Link</button>
            </form>

            <div class="auth-links">
                <a href="login.php">Back to Login</a>
            </div>
        </section>
    </div>
</main>

<?php require_once '../includes/footer.php'; ?>
