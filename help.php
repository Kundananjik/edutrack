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
    <title>EduTrack Help</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome (optional) -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>


<!-- Main Content -->
<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-8">

            <header class="mb-4 text-center">
                <h1 class="display-5">Help & Support</h1>
                <p class="text-muted">Everything you need to get started with EduTrack</p>
            </header>

            <section class="mb-4">
                <p>If you're having trouble using EduTrack, you can start here:</p>
                <ul class="list-group list-group-flush">
                    <li class="list-group-item"><strong>Login Issues:</strong> Make sure your student number and password are correct.</li>
                    <li class="list-group-item"><strong>Reset Password:</strong> Use the <a href="forgot_password.php">Forgot Password</a> link on the login page.</li>
                    <li class="list-group-item"><strong>Enrollment:</strong> Contact your admin if a course isn't visible under your dashboard.</li>
                </ul>
                <p class="mt-3">Still need help? Contact the system administrator at 
                    <a href="mailto:kundananjisimukonda@gmail.com">kundananjisimukonda@gmail.com</a>
                </p>
            </section>

            <!-- Footer -->
            <?php require_once 'includes/footer.php'; ?>

        </div>
    </div>
</div>

<!-- Bootstrap JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
