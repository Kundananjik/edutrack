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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    
    <link rel="icon" type="image/png" href="<?= asset_url('assets/favicon.png') ?>">
    <title>EduTrack Help - Student</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/student.css">
</head>
<body>

<?php require_once '../../includes/student_navbar.php'; ?>

<main class="container my-5">
    <h1 class="mb-3">Help & Support</h1>
    <p>If you're having trouble using EduTrack, you can start here:</p>
    <ul>
        <li><strong>Login Issues:</strong> Make sure your student number and password are correct.</li>
        <li><strong>Reset Password:</strong> Use the <a href="../../auth/forgot_password.php">Forgot Password</a> link on the login page.</li>
        <li><strong>Enrollment:</strong> Contact your admin if a course isn't visible under your dashboard.</li>
    </ul>
    <p>
        Still need help? Contact the system administrator at
        <a href="mailto:kundananjisimukonda@gmail.com">kundananjisimukonda@gmail.com</a>
    </p>
</main>

<?php require_once '../../includes/footer.php'; ?>

</body>
</html>
