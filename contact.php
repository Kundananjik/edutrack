<?php
// ============================================
// CONTACT_US.PHP - EDUTRACK CONTACT PAGE
// ============================================

// Preload
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

$page_title = 'Contact Us - EduTrack';
$page_description = 'Contact EduTrack support for inquiries, feedback, or technical assistance.';

$success = isset($_GET['success']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title><?= htmlspecialchars($page_title) ?></title>
    <meta name="description" content="<?= htmlspecialchars($page_description) ?>">

    <link rel="icon" type="image/png" href="<?= asset_url('assets/favicon.png') ?>">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <link rel="stylesheet" href="<?= asset_url('assets/css/style.css') ?>">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
</head>
<body class="contact-page">
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

    <header class="page-hero contact-hero">
        <div class="container page-hero-inner">
            <div>
                <p class="eyebrow dark">Contact Us</p>
                <h1>Formal support and service inquiries.</h1>
                <p>Submit requests for technical support, operational guidance, or administrative assistance.</p>
            </div>
            <div class="page-hero-card">
                <div class="page-hero-card-row">
                    <i class="bi bi-envelope" aria-hidden="true"></i>
                    <div>
                        <h4>Email Support</h4>
                        <p>Standard response within one business day.</p>
                    </div>
                </div>
                <div class="page-hero-card-row">
                    <i class="bi bi-telephone" aria-hidden="true"></i>
                    <div>
                        <h4>Phone Support</h4>
                        <p>Weekdays, 08:00 – 17:00 (local time).</p>
                    </div>
                </div>
                <div class="page-hero-card-row">
                    <i class="bi bi-clipboard-check" aria-hidden="true"></i>
                    <div>
                        <h4>Request Tracking</h4>
                        <p>Requests are logged and tracked to completion.</p>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <main class="page-section">
        <div class="container contact-layout">
            <div class="contact-card">
                <h2>Contact Information</h2>
                <ul class="contact-list">
                    <li>
                        <i class="bi bi-envelope" aria-hidden="true"></i>
                        <div>
                            <strong>Email</strong>
                            <a href="mailto:kundananjisimukonda@gmail.com">kundananjisimukonda@gmail.com</a>
                        </div>
                    </li>
                    <li>
                        <i class="bi bi-telephone" aria-hidden="true"></i>
                        <div>
                            <strong>Phone</strong>
                            <a href="tel:+260967591264">+260 967 591 264</a>
                            <a href="tel:+260971863462">+260 971 863 462</a>
                        </div>
                    </li>
                    <li>
                        <i class="bi bi-clock" aria-hidden="true"></i>
                        <div>
                            <strong>Office Hours</strong>
                            <span>Monday to Friday, 08:00 – 17:00</span>
                        </div>
                    </li>
                </ul>
            </div>

            <div class="contact-card">
                <h2>Submit a Request</h2>
                <?php if ($success): ?>
                    <div class="alert alert-success">
                        Your message has been submitted successfully. We will respond shortly.
                    </div>
                <?php endif; ?>
                <form method="post" action="process_contact.php" class="contact-form" novalidate>
                    <label>Full Name</label>
                    <input type="text" name="name" required>

                    <label>Email Address</label>
                    <input type="email" name="email" required>

                    <label>Subject</label>
                    <input type="text" name="subject" required>

                    <label>Message</label>
                    <textarea name="message" rows="5" required></textarea>

                    <button type="submit" class="btn btn-success">Send Message</button>
                </form>
            </div>
        </div>
    </main>

<?php require_once 'includes/footer.php'; ?>

</body>
</html>
