<?php
// Preload (auto-locate includes/preload.php)
$__et=__DIR__;
for($__i=0;$__i<6;$__i++){
    $__p=$__et . '/includes/preload.php';
    if (file_exists($__p)) { require_once $__p; break; }
    $__et=dirname($__et);
}
unset($__et,$__i,$__p);
// auth/reset_password.php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';
require_once '../includes/header.php';
require_once '../includes/csrf.php';

$token = $_GET['token'] ?? '';
$error = '';
$success = '';

// Validate token on GET
if ($_SERVER['REQUEST_METHOD'] === 'GET' && $token) {
    $stmt = $pdo->prepare("SELECT email, expires_at FROM password_resets WHERE token = :token LIMIT 1");
    $stmt->execute(['token' => $token]);
    $reset = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$reset || strtotime($reset['expires_at']) < time()) {
        $error = "This reset link is invalid or has expired.";
        $token = '';
    }
}

// Handle new password submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
        $error = 'Invalid CSRF token.';
    }
    $token = $_POST['token'] ?? '';
    $new_password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if ($new_password !== $confirm_password) {
        $error = "Passwords do not match.";
    } elseif (strlen($new_password) < 6) {
        $error = "Password must be at least 6 characters.";
    } else {
        $stmt = $pdo->prepare("SELECT email FROM password_resets WHERE token = :token LIMIT 1");
        $stmt->execute(['token' => $token]);
        $reset = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($reset) {
            $hashed = password_hash($new_password, PASSWORD_DEFAULT);

            // Update user's password
            $update = $pdo->prepare("UPDATE users SET password = :pwd WHERE email = :email");
            $update->execute(['pwd' => $hashed, 'email' => $reset['email']]);

            // Remove token
            $pdo->prepare("DELETE FROM password_resets WHERE token = :token")->execute(['token' => $token]);

            $success = "Password updated successfully! <a href='login.php'>Login here</a>.";
            $token = ''; // Prevent reuse
        } else {
            $error = "Invalid reset request.";
        }
    }
}
?>

<div class="container">
    <h2>Reset Password</h2>

    <?php if ($error): ?>
        <div class="error"><?php echo $error; ?></div>
    <?php elseif ($success): ?>
        <div class="success"><?php echo $success; ?></div>
    <?php elseif ($token): ?>
        <form method="POST" action="">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(get_csrf_token()) ?>">
            <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">

            <label for="password">New Password:</label>
            <input type="password" name="password" required>

            <label for="confirm_password">Confirm Password:</label>
            <input type="password" name="confirm_password" required>

            <button type="submit">Reset Password</button>
        </form>
    <?php endif; ?>

    <p><a href="login.php">Back to Login</a></p>
</div>

<?php require_once '../includes/footer.php'; ?>
