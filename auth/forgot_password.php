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
require_once '../includes/header.php';
require_once '../includes/csrf.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
        $message = 'Invalid CSRF token.';
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
            $reset_link = BASE_URL . "auth/reset_password.php?token=$token";
            $message = "Reset link sent! <br><a href='$reset_link'>$reset_link</a>";
        } else {
            $message = 'No account found with that email or student number.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title> </title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

    <nav class="navbar">
        <div class="container">
            <div class="logo">
                <a href="../index.php">
                    <img src="../assets/logo.png" alt="EduTrack Logo" />
            <ul class="nav-links">
                <li><a href="../index.php">Home</a></li>
            </ul>
        </div>
    </nav>

<div class="container">
    <h2>Forgot Password</h2>

    <?php if ($message): ?>
        <div class="notice"><?php echo $message; ?></div>
    <?php endif; ?>

    <form method="POST" action="">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(get_csrf_token()) ?>">
        <label for="identifier">Student Number or Email:</label>
        <input type="text" name="identifier" required>
        <button type="submit">Send Reset Link</button>
    </form>

    <p><a href="login.php">Back to Login</a></p>
</div>

<?php require_once '../includes/footer.php'; ?>
