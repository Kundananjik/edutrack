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
    <title>Privacy Policy - EduTrack Admin</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="css/dashboard.css">
</head>
<body class="bg-light d-flex flex-column min-vh-100">

<?php require_once '../../includes/admin_navbar.php'; ?>

<main class="container py-5 flex-grow-1">
    <div class="mb-4">
        <h1 class="fw-bold mb-2"><i class="bi bi-shield-lock me-2"></i>Privacy Policy</h1>
        <p class="text-muted mb-0"><strong>Effective date:</strong> August 4, 2025</p>
    </div>

    <section class="card shadow-sm rounded-4">
        <div class="card-body">
            <section class="mb-4">
                <h2>1. Introduction</h2>
                <p>EduTrack is committed to protecting and respecting your privacy. This policy explains how we collect, use, and safeguard personal information within the attendance system.</p>
            </section>

            <section class="mb-4">
                <h2>2. Information We Collect</h2>
                <ul>
                    <li><strong>Personal identification information:</strong> names, emails, student or lecturer IDs, and contact details.</li>
                    <li><strong>Usage data:</strong> attendance records, login times, device information, and system interactions.</li>
                    <li><strong>Cookies and tracking:</strong> limited use to improve experience and analyze traffic.</li>
                </ul>
            </section>

            <section class="mb-4">
                <h2>3. How We Use Your Information</h2>
                <ul>
                    <li>Manage and track attendance accurately.</li>
                    <li>Provide dashboard access appropriate to each user role.</li>
                    <li>Improve service quality and platform security.</li>
                    <li>Communicate updates, notifications, and support messages.</li>
                </ul>
            </section>

            <section class="mb-4">
                <h2>4. Data Security</h2>
                <p>We use reasonable safeguards to protect data against unauthorized access, alteration, disclosure, or destruction. No network transmission is fully secure, so strong credential hygiene remains important.</p>
            </section>

            <section class="mb-4">
                <h2>5. Sharing Your Information</h2>
                <p>We do not sell or rent personal information. Access is limited to authorized university staff or service providers strictly involved in attendance management and maintenance.</p>
            </section>

            <section class="mb-4">
                <h2>6. Your Rights</h2>
                <p>Users may request access to, updates to, or deletion of their personal information, subject to institutional and legal requirements.</p>
            </section>

            <section class="mb-4">
                <h2>7. Cookies Policy</h2>
                <p>Cookies are used to support the user experience. Disabling them may affect some features of the system.</p>
            </section>

            <section class="mb-4">
                <h2>8. Changes to This Policy</h2>
                <p>We may revise this policy periodically. Continued use of EduTrack after updates indicates acceptance of the revised version.</p>
            </section>

            <section>
                <h2>9. Contact Us</h2>
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
