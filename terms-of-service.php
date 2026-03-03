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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Terms of Service</title>
    <link rel="icon" type="image/png" href="<?= asset_url('assets/favicon.png') ?>">
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
                <p class="eyebrow dark">Terms of Service</p>
                <h1>Terms governing authorized academic use.</h1>
                <p>These Terms define permitted use of the EduTrack Service by students, lecturers, and authorized staff.</p>
                <p class="policy-date">Effective date: August 4, 2025</p>
            </div>
            <div class="page-hero-card">
                <div class="page-hero-card-row">
                    <i class="bi bi-person-check" aria-hidden="true"></i>
                    <div>
                        <h4>Authorized Use</h4>
                        <p>Use limited to academic attendance functions.</p>
                    </div>
                </div>
                <div class="page-hero-card-row">
                    <i class="bi bi-shield-exclamation" aria-hidden="true"></i>
                    <div>
                        <h4>Policy Compliance</h4>
                        <p>Subject to institutional rules and applicable law.</p>
                    </div>
                </div>
                <div class="page-hero-card-row">
                    <i class="bi bi-person-lock" aria-hidden="true"></i>
                    <div>
                        <h4>Account Security</h4>
                        <p>Users are responsible for account protection.</p>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <main class="page-section">
        <div class="container policy-layout">
            <section class="policy-card">
                <h2>1. Acceptance of Terms</h2>
                <p>By accessing or using EduTrack, you agree to be bound by these Terms. If you do not agree, you must not use the Service.</p>
            </section>
            <section class="policy-card">
                <h2>2. Use of the Service</h2>
                <ul class="policy-list">
                    <li>Only for students, lecturers, and authorized staff.</li>
                    <li>Use for lawful purposes and in accordance with university policies.</li>
                    <li>Maintain confidentiality of credentials and account activity.</li>
                </ul>
            </section>
            <section class="policy-card">
                <h2>3. User Responsibilities</h2>
                <ul class="policy-list">
                    <li>No unlawful, harmful, or disruptive use.</li>
                    <li>No unauthorized access attempts.</li>
                    <li>No interference with system security, integrity, or availability.</li>
                </ul>
            </section>
            <section class="policy-card">
                <h2>4. Intellectual Property</h2>
                <p>All software, designs, text, graphics, and content are the property of EduTrack and its licensors. Unauthorized use is prohibited.</p>
            </section>
            <section class="policy-card">
                <h2>5. Privacy</h2>
                <p>Your use of EduTrack is governed by the <a href="privacy-policy.php">Privacy Policy</a>, which is incorporated by reference.</p>
            </section>
            <section class="policy-card">
                <h2>6. Limitation of Liability</h2>
                <p>EduTrack is provided "as is" without warranties. To the maximum extent permitted by law, EduTrack is not liable for indirect, incidental, consequential, or punitive damages.</p>
            </section>
            <section class="policy-card">
                <h2>7. Termination</h2>
                <p>We may suspend or terminate access for violations of these Terms or conduct harmful to the Service or users.</p>
            </section>
            <section class="policy-card">
                <h2>8. Changes to Terms</h2>
                <p>We may update these Terms. Continued use after changes constitutes acceptance of the updated Terms.</p>
            </section>
            <section class="policy-card">
                <h2>9. Contact Information</h2>
                <ul class="policy-list">
                    <li>Email: <a href="mailto:kundananjisimukonda@gmail.com">kundananjisimukonda@gmail.com</a></li>
                    <li>Phone: <a href="tel:+260967591264">+260 967 591 264</a> / <a href="tel:+260971863462">+260 971 863 462</a></li>
                </ul>
            </section>
        </div>
    </main>

    <?php require_once 'includes/footer.php'; ?>
</body>
</html>
