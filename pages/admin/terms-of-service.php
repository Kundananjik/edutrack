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
    <title>Terms of Service - EduTrack Admin</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="css/dashboard.css">
</head>
<body class="bg-light d-flex flex-column min-vh-100">

<?php require_once '../../includes/admin_navbar.php'; ?>

<main class="container py-5 flex-grow-1">
    <div class="mb-4">
        <h1 class="fw-bold mb-2"><i class="bi bi-file-earmark-text me-2"></i>Terms of Service</h1>
        <p class="text-muted mb-0">Effective date: August 4, 2025</p>
    </div>

    <section class="card shadow-sm rounded-4">
        <div class="card-body">
            <section class="mb-3">
                <h2>1. Acceptance of Terms</h2>
                <p>By accessing or using EduTrack, you agree to comply with these terms. If you do not agree, you should not use the service.</p>
            </section>

            <section class="mb-3">
                <h2>2. Use of the Service</h2>
                <ul>
                    <li>The service is intended for authorized academic attendance and administrative use.</li>
                    <li>Use must remain lawful and consistent with university policy.</li>
                    <li>Users are responsible for the confidentiality of their credentials.</li>
                </ul>
            </section>

            <section class="mb-3">
                <h2>3. User Responsibilities</h2>
                <ul>
                    <li>Do not use the service for unlawful or harmful activity.</li>
                    <li>Do not attempt unauthorized access to accounts or infrastructure.</li>
                    <li>Do not interfere with service availability or security.</li>
                </ul>
            </section>

            <section class="mb-3">
                <h2>4. Intellectual Property</h2>
                <p>All software, design, text, graphics, logos, and related functionality remain the property of EduTrack and its licensors unless otherwise stated.</p>
            </section>

            <section class="mb-3">
                <h2>5. Privacy</h2>
                <p>Use of the service is also governed by the <a href="privacy-policy.php">Privacy Policy</a>.</p>
            </section>

            <section class="mb-3">
                <h2>6. Limitation of Liability</h2>
                <p>EduTrack is provided as-is. We do not guarantee uninterrupted or error-free operation and are not liable for damages arising from normal use of the service to the extent permitted by law.</p>
            </section>

            <section class="mb-3">
                <h2>7. Termination</h2>
                <p>Access may be suspended or terminated for conduct that violates these terms or creates risk for the institution or other users.</p>
            </section>

            <section class="mb-3">
                <h2>8. Changes to Terms</h2>
                <p>These terms may be updated periodically. Continued use of the service after updates indicates acceptance of the revised version.</p>
            </section>

            <section>
                <h2>9. Contact Information</h2>
                <ul class="mb-0">
                    <li>Email: <a href="mailto:kundananjisimukonda@gmail.com">kundananjisimukonda@gmail.com</a></li>
                    <li>Phone: +260 967 591 264 / +260 971 863 462</li>
                </ul>
            </section>
        </div>
    </section>
</main>

<?php require_once '../../includes/footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
