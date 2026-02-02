<?php
// ============================================
// PRIVACY-POLICY.PHP - EDUTRACK PRIVACY POLICY
// ============================================

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

// Page-specific variables
$page_title = 'Privacy Policy - EduTrack';
$page_description = 'Learn how EduTrack collects, uses, and protects your personal information. Our commitment to data security and privacy.';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <!-- SEO Meta Tags -->
    <title><?= htmlspecialchars($page_title) ?></title>
    <meta name="description" content="<?= htmlspecialchars($page_description) ?>">
    <meta name="robots" content="index, follow">
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="assets/favicon.png">
    
    <!-- Stylesheets -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css?v=1.0.2">
</head>
<body>

<!-- Navigation (if you have a navbar include, add it here) -->
<?php
$navbar = __DIR__ . '/includes/navbar.php';
if (file_exists($navbar)) {
    require_once $navbar;
}
?>

<!-- Main Content -->
<main class="container my-5">
    <div class="row">
        <div class="col-lg-10 mx-auto">
            <h1 class="mb-3">Privacy Policy</h1>
            <p class="text-muted">Effective date: August 4, 2025</p>
            
            <section class="my-4">
                <h2>1. Introduction</h2>
                <p>
                    EduTrack ("we", "our", or "us") is committed to protecting and respecting your privacy.
                    This Privacy Policy explains how we collect, use, and safeguard your personal information when you use our attendance tracking system.
                </p>
            </section>
            
            <section class="my-4">
                <h2>2. Information We Collect</h2>
                <ul>
                    <li><strong>Personal Identification Information:</strong> Name, email address, student or lecturer ID, and contact details.</li>
                    <li><strong>Usage Data:</strong> Attendance records, login times, device information, and interaction with the system.</li>
                    <li><strong>Cookies and Tracking:</strong> We use cookies to improve your user experience and analyze site traffic.</li>
                </ul>
            </section>
            
            <section class="my-4">
                <h2>3. How We Use Your Information</h2>
                <p>We use your data to:</p>
                <ul>
                    <li>Manage and track attendance accurately.</li>
                    <li>Provide personalized access to your dashboard.</li>
                    <li>Improve our services and system security.</li>
                    <li>Communicate important updates, notifications, and support.</li>
                </ul>
            </section>
            
            <section class="my-4">
                <h2>4. Data Security</h2>
                <p>
                    We implement industry-standard security measures to protect your data against unauthorized access, alteration, disclosure, or destruction.
                    However, no internet transmission is completely secure. Please use strong passwords and keep your login credentials confidential.
                </p>
            </section>
            
            <section class="my-4">
                <h2>5. Sharing Your Information</h2>
                <p>
                    We do not sell, trade, or rent your personal information to third parties.
                    We may share your information with authorized university staff or service providers strictly for attendance management and system maintenance.
                </p>
            </section>
            
            <section class="my-4">
                <h2>6. Your Rights</h2>
                <p>
                    You have the right to access, update, or request deletion of your personal information.
                    To exercise these rights, please contact us using the information provided below.
                </p>
            </section>
            
            <section class="my-4">
                <h2>7. Cookies Policy</h2>
                <p>
                    EduTrack uses cookies to enhance your experience. You can set your browser to refuse cookies or alert you when cookies are being sent.
                    Note that some parts of the system may not function properly without cookies.
                </p>
            </section>
            
            <section class="my-4">
                <h2>8. Changes to This Privacy Policy</h2>
                <p>
                    We may update this Privacy Policy periodically. We encourage you to review this page regularly for any changes.
                    Continued use of EduTrack after changes constitutes your acceptance of the updated policy.
                </p>
            </section>
            
            <section class="my-4">
                <h2>9. Contact Us</h2>
                <p>If you have questions or concerns about this Privacy Policy, please contact us:</p>
                <ul>
                    <li>Email: <a href="mailto:kundananjisimukonda@gmail.com">kundananjisimukonda@gmail.com</a></li>
                    <li>Phone: <a href="tel:+260967591264">+260 967 591 264</a> / <a href="tel:+260971863462">+260 971 863 462</a></li>
                </ul>
            </section>
        </div>
    </div>
</main>

<!-- Footer -->
<?php
$footer = __DIR__ . '/includes/footer.php';
if (file_exists($footer)) {
    require_once $footer;
} else {
    echo '<footer class="text-center text-muted py-4">
            <p>&copy; ' . date('Y') . ' EduTrack. All rights reserved.</p>
          </footer>';
}
?>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<!-- Year Update Script -->
<script <?= et_csp_attr('script') ?>>
    const yearEl = document.getElementById('year');
    if (yearEl) yearEl.textContent = new Date().getFullYear();
</script>

</body>
</html>