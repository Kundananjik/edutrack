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
    <link rel="icon" type="image/png" href="<?= asset_url('assets/favicon.png') ?>">
    
    <!-- Stylesheets -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <link rel="stylesheet" href="<?= asset_url('assets/css/style.css') ?>">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
</head>
<body class="policy-page">
    <nav class="navbar navbar-light landing-navbar">
        <div class="container landing-nav">
            <!-- Brand (Centered) -->
            <a class="navbar-brand mx-auto" href="index.php">
                <img src="<?= asset_url('assets/logo.png') ?>" alt="EduTrack Logo" height="40" width="auto">
            </a>

            <!-- Desktop Navigation -->
            <div class="d-none d-lg-flex ms-auto landing-nav-actions">
                <ul class="navbar-nav d-flex flex-row gap-2 align-items-center">
                    <li class="nav-item">
                        <a class="btn btn-success ms-2" href="auth/login.php">Login</a>
                    </li>
                </ul>
            </div>
            <div class="d-lg-none ms-auto landing-nav-actions">
                <a href="auth/login.php" class="btn btn-success w-100">Login</a>
            </div>
        </div>
    </nav>

    <header class="page-hero policy-hero">
        <div class="container page-hero-inner">
            <div>
                <p class="eyebrow dark">Privacy Policy</p>
                <h1>Privacy and data protection notice.</h1>
                <p>This Privacy Policy describes the categories of data we collect, the purposes of processing, and the safeguards applied.</p>
                <p class="policy-date">Effective date: August 4, 2025</p>
            </div>
            <div class="page-hero-card">
                <div class="page-hero-card-row">
                    <i class="bi bi-shield-check" aria-hidden="true"></i>
                    <div>
                        <h4>Security Controls</h4>
                        <p>Role-based access, auditability, and protective measures.</p>
                    </div>
                </div>
                <div class="page-hero-card-row">
                    <i class="bi bi-person-lock" aria-hidden="true"></i>
                    <div>
                        <h4>Purpose Limitation</h4>
                        <p>Processing restricted to academic attendance and administration.</p>
                    </div>
                </div>
                <div class="page-hero-card-row">
                    <i class="bi bi-envelope-check" aria-hidden="true"></i>
                    <div>
                        <h4>Data Subject Rights</h4>
                        <p>Requests are handled in accordance with applicable policy.</p>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <main class="page-section">
        <div class="container policy-layout">
            <section class="policy-card">
                <h2>1. Introduction</h2>
                <p>EduTrack ("we", "our", or "us") is committed to protecting personal information. This policy explains how we collect, use, disclose, and safeguard information in connection with the Service.</p>
            </section>
            <section class="policy-card">
                <h2>2. Information We Collect</h2>
                <ul class="policy-list">
                    <li><strong>Personal Identification:</strong> Name, email address, student or lecturer ID, and contact details.</li>
                    <li><strong>Usage Data:</strong> Attendance records, login times, device information, and system interactions.</li>
                    <li><strong>Cookies:</strong> Used for system functionality and analytics.</li>
                </ul>
            </section>
            <section class="policy-card">
                <h2>3. How We Use Your Information</h2>
                <ul class="policy-list">
                    <li>Provide and operate the Service, including attendance tracking.</li>
                    <li>Authenticate users and manage role-based access.</li>
                    <li>Maintain security, integrity, and auditability.</li>
                    <li>Communicate operational notices and support responses.</li>
                </ul>
            </section>
            <section class="policy-card">
                <h2>4. Data Security</h2>
                <p>We implement reasonable administrative, technical, and physical safeguards. However, no method of transmission or storage is fully secure; users are responsible for maintaining credential confidentiality.</p>
            </section>
            <section class="policy-card">
                <h2>5. Sharing Your Information</h2>
                <p>We do not sell personal information. We may disclose data to authorized university personnel or service providers for attendance management, system maintenance, or compliance purposes.</p>
            </section>
            <section class="policy-card">
                <h2>6. Your Rights</h2>
                <p>Subject to applicable law and institutional policy, you may request access, correction, or deletion of personal information.</p>
            </section>
            <section class="policy-card">
                <h2>7. Cookies Policy</h2>
                <p>You may disable cookies in your browser; certain features may be unavailable or impaired.</p>
            </section>
            <section class="policy-card">
                <h2>8. Changes to This Policy</h2>
                <p>We may modify this policy from time to time. Continued use of the Service after publication of changes constitutes acceptance.</p>
            </section>
            <section class="policy-card">
                <h2>9. Contact Us</h2>
                <ul class="policy-list">
                    <li>Email: <a href="mailto:kundananjisimukonda@gmail.com">kundananjisimukonda@gmail.com</a></li>
                    <li>Phone: <a href="tel:+260967591264">+260 967 591 264</a> / <a href="tel:+260971863462">+260 971 863 462</a></li>
                </ul>
            </section>
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

</body>
</html>
