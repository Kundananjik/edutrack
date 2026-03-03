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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="<?= asset_url('assets/favicon.png') ?>">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <link rel="stylesheet" href="<?= asset_url('assets/css/style.css') ?>">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
</head>
<body class="help-page">
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

    <header class="page-hero help-hero">
        <div class="container page-hero-inner">
            <div>
                <p class="eyebrow dark">Help Center</p>
                <h1>Get support fast and keep attendance flowing.</h1>
                <p>Find answers to common questions or contact the EduTrack administrator for help.</p>
            </div>
            <div class="page-hero-card">
                <div class="page-hero-card-row">
                    <i class="bi bi-question-circle" aria-hidden="true"></i>
                    <div>
                        <h4>Quick Answers</h4>
                        <p>Resolve common login and attendance issues quickly.</p>
                    </div>
                </div>
                <div class="page-hero-card-row">
                    <i class="bi bi-envelope" aria-hidden="true"></i>
                    <div>
                        <h4>Contact Support</h4>
                        <p>Email the administrator for urgent assistance.</p>
                    </div>
                </div>
                <div class="page-hero-card-row">
                    <i class="bi bi-phone" aria-hidden="true"></i>
                    <div>
                        <h4>Mobile Guidance</h4>
                        <p>Tips for scanning QR codes on any device.</p>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <section class="page-section">
        <div class="container help-grid">
            <div class="help-card">
                <h3><i class="bi bi-box-arrow-in-right" aria-hidden="true"></i> Login Issues</h3>
                <p>Confirm your student number or staff ID and password are correct. If access still fails, contact your admin.</p>
            </div>
            <div class="help-card">
                <h3><i class="bi bi-key" aria-hidden="true"></i> Reset Password</h3>
                <p>Use the <a href="auth/forgot_password.php">Forgot Password</a> link on the login screen to reset your credentials.</p>
            </div>
            <div class="help-card">
                <h3><i class="bi bi-clipboard-check" aria-hidden="true"></i> Attendance Not Showing</h3>
                <p>Ask your lecturer or admin to confirm your enrollment in the course session.</p>
            </div>
            <div class="help-card">
                <h3><i class="bi bi-qr-code-scan" aria-hidden="true"></i> QR Scan Tips</h3>
                <p>Increase screen brightness and align the QR code fully within your camera frame.</p>
            </div>
        </div>
    </section>

    <section class="page-section faq-section">
        <div class="container">
            <div class="section-header">
                <h2>Frequently Asked Questions</h2>
                <p>Quick answers to the most common EduTrack questions.</p>
            </div>
            <div class="faq-list">
                <details class="faq-item">
                    <summary>How do I mark attendance with a QR code?</summary>
                    <p>Open your phone camera or QR scanner, scan the class code, and confirm your attendance when prompted.</p>
                </details>
                <details class="faq-item">
                    <summary>My QR scan isn’t working—what should I try first?</summary>
                    <p>Increase screen brightness, keep the QR code fully in frame, and make sure your camera lens is clean.</p>
                </details>
                <details class="faq-item">
                    <summary>Can I mark attendance without internet?</summary>
                    <p>Attendance requires a connection to verify the scan in real time. Please connect to campus Wi-Fi or data.</p>
                </details>
                <details class="faq-item">
                    <summary>How long is a QR code valid for?</summary>
                    <p>Each QR code is time-bound to the class session and expires when the lecturer closes the session.</p>
                </details>
                <details class="faq-item">
                    <summary>I can’t see my course—who fixes that?</summary>
                    <p>Contact your department admin or lecturer to confirm your enrollment is active in the system.</p>
                </details>
                <details class="faq-item">
                    <summary>How do I export attendance reports?</summary>
                    <p>Lecturers and admins can export reports from the Attendance Reports page in CSV or PDF format.</p>
                </details>
                <details class="faq-item">
                    <summary>How are late arrivals handled?</summary>
                    <p>Lecturers can set attendance windows; scans after the cut-off may be marked late or absent based on policy.</p>
                </details>
            </div>
        </div>
    </section>

    <section class="page-section alt">
        <div class="container help-contact">
            <div>
                <h2>Need more help?</h2>
                <p>Contact the system administrator and include your full name, role, and course for faster support.</p>
            </div>
            <div class="help-contact-actions">
                <a class="btn btn-success" href="mailto:kundananjisimukonda@gmail.com">Email Support</a>
                <a class="btn btn-outline-dark" href="contact.php">Contact Form</a>
            </div>
        </div>
    </section>

    <?php require_once 'includes/footer.php'; ?>
</body>
</html>
