<?php
// Preload (auto-locate includes/preload.php)
$__et=__DIR__;
for($__i=0;$__i<6;$__i++){
    $__p=$__et . '/includes/preload.php';
    if (file_exists($__p)) { require_once $__p; break; }
    $__et=dirname($__et);
}
unset($__et,$__i,$__p);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>EduTrack Help</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="wrapper-main">
        <header>
            <h1>Help & Support</h1>
        </header>

        <section>
            <p>If you're having trouble using EduTrack, you can start here:</p>
            <ul>
                <li><strong>Login Issues:</strong> Make sure your student number and password are correct.</li>
                <li><strong>Reset Password:</strong> Use the <a href="forgot_password.php">Forgot Password</a> link on the login page.</li>
                <li><strong>Enrollment:</strong> Contact your admin if a course isn't visible under your dashboard.</li>
            </ul>
            <p>Still need help? Contact the system administrator at 
                <a href="mailto:kundananjisimukonda@gmail.com">kundananjisimukonda@gmail.com</a>
            </p>
        </section>

        <?php require_once 'includes/footer.php'; ?>
    </div>
</body>
</html>
