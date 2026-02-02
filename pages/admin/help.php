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
<?php require_once('../../includes/footer.php'); // Keep footer include?>
<!DOCTYPE html>
<html lang="en">

</head>
<body>
<?php require_once '../../includes/admin_navbar.php'; ?>

<div class="container my-5">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<div class="container help-container my-5 p-4 bg-white rounded shadow">
    <header class="mb-4">
        <h1 class="text-success"><i class="fas fa-life-ring me-2"></i> Help & Support</h1>
    </header>

    <section>
        <p>If you're having trouble using <strong>EduTrack</strong>, explore the topics below:</p>

        <div class="accordion" id="faqAccordion">

            <div class="accordion-item">
                <h2 class="accordion-header" id="headingLogin">
                    <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseLogin" aria-expanded="true" aria-controls="collapseLogin">
                        <i class="fas fa-sign-in-alt me-2"></i> Login Issues
                    </button>
                </h2>
                <div id="collapseLogin" class="accordion-collapse collapse show" aria-labelledby="headingLogin" data-bs-parent="#faqAccordion">
                    <div class="accordion-body">
                        Make sure your student number and password are entered correctly. Check for caps lock and browser autofill issues.
                    </div>
                </div>
            </div>

            <div class="accordion-item">
                <h2 class="accordion-header" id="headingReset">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseReset" aria-expanded="false" aria-controls="collapseReset">
                        <i class="fas fa-key me-2"></i> Reset Password
                    </button>
                </h2>
                <div id="collapseReset" class="accordion-collapse collapse" aria-labelledby="headingReset" data-bs-parent="#faqAccordion">
                    <div class="accordion-body">
                        Use the <a href="forgot_password.php">Forgot Password</a> link on the login page to reset your password. Follow the instructions sent to your email.
                    </div>
                </div>
            </div>

            <div class="accordion-item">
                <h2 class="accordion-header" id="headingEnroll">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseEnroll" aria-expanded="false" aria-controls="collapseEnroll">
                        <i class="fas fa-user-graduate me-2"></i> Enrollment
                    </button>
                </h2>
                <div id="collapseEnroll" class="accordion-collapse collapse" aria-labelledby="headingEnroll" data-bs-parent="#faqAccordion">
                    <div class="accordion-body">
                        If a course isn’t visible under your dashboard, contact your admin. Only enrolled courses will appear in your dashboard.
                    </div>
                </div>
            </div>

            <div class="accordion-item">
                <h2 class="accordion-header" id="headingSupport">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseSupport" aria-expanded="false" aria-controls="collapseSupport">
                        <i class="fas fa-envelope me-2"></i> Contact Admin
                    </button>
                </h2>
                <div id="collapseSupport" class="accordion-collapse collapse" aria-labelledby="headingSupport" data-bs-parent="#faqAccordion">
                    <div class="accordion-body">
                        Still need help? Contact the system administrator at 
                        <a href="mailto:kundananjisimukonda@gmail.com">kundananjisimukonda@gmail.com</a>.
                    </div>
                </div>
            </div>

        </div>
    </section>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
