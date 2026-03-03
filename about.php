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
    <title>About EduTrack</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="<?= asset_url('assets/favicon.png') ?>">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <link rel="stylesheet" href="<?= asset_url('assets/css/style.css') ?>">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
</head>
<body class="about-page">
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

    <header class="page-hero">
        <div class="container page-hero-inner">
            <div>
                <p class="eyebrow dark">About EduTrack</p>
                <h1>Modern academic attendance, built for clarity and trust.</h1>
                <p>EduTrack helps universities reduce manual roll calls, improve reporting, and keep every stakeholder aligned with accurate attendance data.</p>
            </div>
            <div class="page-hero-card">
                <div class="page-hero-card-row">
                    <i class="bi bi-mortarboard" aria-hidden="true"></i>
                    <div>
                        <h4>Academic-First</h4>
                        <p>Designed around real campus workflows and roles.</p>
                    </div>
                </div>
                <div class="page-hero-card-row">
                    <i class="bi bi-shield-check" aria-hidden="true"></i>
                    <div>
                        <h4>Secure by Default</h4>
                        <p>Role-based access and tamper-aware records.</p>
                    </div>
                </div>
                <div class="page-hero-card-row">
                    <i class="bi bi-graph-up-arrow" aria-hidden="true"></i>
                    <div>
                        <h4>Actionable Insight</h4>
                        <p>Real-time dashboards that highlight trends.</p>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <section class="page-section">
        <div class="container two-col">
            <div>
                <h2>Our Mission</h2>
                <p>We equip institutions with intuitive, scalable tools that simplify attendance and academic operations while protecting student data.</p>
            </div>
            <div>
                <h2>What We Deliver</h2>
                <ul class="about-list">
                    <li>QR-based attendance in seconds.</li>
                    <li>Role-specific dashboards for staff and students.</li>
                    <li>Instant reporting with export-ready records.</li>
                    <li>Mobile-first access across campus devices.</li>
                </ul>
            </div>
        </div>
    </section>

    <section class="page-section alt">
        <div class="container">
            <div class="section-header">
                <h2>Built for Every Role</h2>
                <p>EduTrack brings together academic leaders, lecturers, and students with shared data and clear insights.</p>
            </div>
            <div class="feature-cards">
                <article class="feature-card">
                    <i class="bi bi-person-badge" aria-hidden="true"></i>
                    <h3>Admins</h3>
                    <p>Monitor attendance trends, export reports, and manage academic structures.</p>
                </article>
                <article class="feature-card">
                    <i class="bi bi-person-video3" aria-hidden="true"></i>
                    <h3>Lecturers</h3>
                    <p>Start sessions, track attendance instantly, and stay focused on teaching.</p>
                </article>
                <article class="feature-card">
                    <i class="bi bi-person-check" aria-hidden="true"></i>
                    <h3>Students</h3>
                    <p>Scan and confirm attendance quickly from any smartphone.</p>
                </article>
            </div>
        </div>
    </section>

    <?php require_once 'includes/footer.php'; ?>
</body>
</html>
