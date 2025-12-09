<?php
// Preload (auto-locate includes/preload.php)
$__et=__DIR__;
for($__i=0;$__i<6;$__i++){
    $__p=$__et . '/includes/preload.php';
    if (file_exists($__p)) { require_once $__p; break; }
    $__et=dirname($__et);
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

        if (empty($identifier) || empty($password)) {
            $error = "Both fields are required.";
        } else {
            try {
                $user = null;

                // 1️⃣ Try login by email (for all users)
                $stmt = $pdo->prepare("SELECT id, name, password, role FROM users WHERE email = :identifier LIMIT 1");
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
                    session_regenerate_id(true);
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['name']    = $user['name'];
                    $_SESSION['role']    = $user['role'];

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
                } else {
                    $error = "Invalid login credentials.";
                }

            } catch (PDOException $e) {
                error_log("Login error: " . $e->getMessage());
                $error = "An error occurred. Please try again later.";
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
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-light bg-light">
    <div class="container justify-content-center">
        <!-- Logo -->
        <a class="navbar-brand" href="../index.php">
            <img src="../assets/logo.png" alt="EduTrack Logo" height="50">
        </a>

        <!-- Toggler for mobile -->
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <!-- Links -->
        <div class="collapse navbar-collapse justify-content-center" id="navbarNav">
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" href="../index.php">Home</a>
                </li>
            </ul>
        </div>
    </div>
</nav>


    <div class="container">
        <h2>EduTrack Login</h2>

        <?php if ($error): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="POST" action="login.php" autocomplete="off">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(get_csrf_token()) ?>">
            <label for="identifier">Student Number or Email:</label>
            <input type="text" name="identifier" id="identifier" required>

            <label for="password">Password:</label>
            <input type="password" name="password" id="password" required>

            <button type="submit">Login</button>
        </form>

        <p>Don’t have an account? <a href="register.php">Register Here</a></p>
        <p><a href="forgot_password.php">Forgot Password?</a></p>
    </div>

    <?php require_once '../includes/footer.php'; ?>

</body>
</html>
