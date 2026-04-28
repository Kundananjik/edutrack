<?php
// Preload (auto-locate includes/preload.php)
$__et = __DIR__;
for ($__i = 0; $__i < 6; $__i++) {
    $__p = $__et . '/includes/preload.php';
    if (file_exists($__p)) {
        require_once $__p;
        break;
    }
    $__et = dirname($__et);
}
unset($__et, $__i, $__p);

require_once '../../includes/functions.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/png" href="<?= asset_url('assets/favicon.png') ?>">
    <title>Help - EduTrack Admin</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="css/dashboard.css">
</head>
<body class="bg-light d-flex flex-column min-vh-100">

<?php require_once '../../includes/admin_navbar.php'; ?>

<main class="container py-5 flex-grow-1">
    <div class="mb-4">
        <h1 class="fw-bold mb-2"><i class="bi bi-life-preserver me-2"></i>Help & Support</h1>
        <p class="text-muted mb-0">Common admin-side support topics for EduTrack operations.</p>
    </div>

    <section class="card shadow-sm rounded-4">
        <div class="card-body">
            <p>If you're having trouble using <strong>EduTrack</strong>, review the topics below.</p>

            <div class="accordion" id="faqAccordion">
                <div class="accordion-item">
                    <h2 class="accordion-header" id="headingLogin">
                        <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseLogin" aria-expanded="true" aria-controls="collapseLogin">
                            <i class="bi bi-box-arrow-in-right me-2"></i> Login Issues
                        </button>
                    </h2>
                    <div id="collapseLogin" class="accordion-collapse collapse show" aria-labelledby="headingLogin" data-bs-parent="#faqAccordion">
                        <div class="accordion-body">
                            Make sure credentials are correct, caps lock is off, and browser autofill is not overriding the intended account.
                        </div>
                    </div>
                </div>

                <div class="accordion-item">
                    <h2 class="accordion-header" id="headingReset">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseReset" aria-expanded="false" aria-controls="collapseReset">
                            <i class="bi bi-key me-2"></i> Reset Password
                        </button>
                    </h2>
                    <div id="collapseReset" class="accordion-collapse collapse" aria-labelledby="headingReset" data-bs-parent="#faqAccordion">
                        <div class="accordion-body">
                            Password resets should be handled through the login flow or by updating the affected account from the relevant admin management page.
                        </div>
                    </div>
                </div>

                <div class="accordion-item">
                    <h2 class="accordion-header" id="headingEnroll">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseEnroll" aria-expanded="false" aria-controls="collapseEnroll">
                            <i class="bi bi-mortarboard me-2"></i> Enrollment Visibility
                        </button>
                    </h2>
                    <div id="collapseEnroll" class="accordion-collapse collapse" aria-labelledby="headingEnroll" data-bs-parent="#faqAccordion">
                        <div class="accordion-body">
                            If a course is missing from a user dashboard, confirm the student enrollment, course status, and lecturer assignment from the admin side.
                        </div>
                    </div>
                </div>

                <div class="accordion-item">
                    <h2 class="accordion-header" id="headingSupport">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseSupport" aria-expanded="false" aria-controls="collapseSupport">
                            <i class="bi bi-envelope me-2"></i> Contact Support
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
        </div>
    </section>
</main>

<?php require_once '../../includes/footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
