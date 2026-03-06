<?php
// ============================================
// INDEX.PHP - EDUTRACK LANDING PAGE
// ============================================

define('APP_ROOT', __DIR__);
define('ASSET_VERSION', '1.0.2'); // Easy cache busting

// ============================================
// BOOTSTRAP & ERROR HANDLING
// ============================================
require_once APP_ROOT . '/includes/preload.php';

// ============================================
// HELPER FUNCTIONS
// ============================================
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <!-- SEO Meta Tags -->
    <title>EduTrack - Smart University Attendance Tracking System</title>
    <meta name="description" content="EduTrack is a smart QR-based attendance tracking system for universities. Save up to 10 minutes per class with automated attendance and real-time reporting.">
    <meta name="keywords" content="attendance system, university attendance, QR code attendance, education technology, student tracking">
    <meta name="author" content="EduTrack">
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="<?= asset_url('assets/favicon.png') ?>">
    
    <!-- Stylesheets -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <link rel="stylesheet" href="<?= asset_url('assets/css/style.css') ?>">
    
    <!-- Bootstrap Icons (lighter alternative to Font Awesome) -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
</head>
<body class="landing">

<!-- ============================================
     NAVIGATION BAR
     ============================================ -->
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

<!-- ============================================
     HERO SECTION
     ============================================ -->
<section class="hero">
    <div class="container hero-inner">
        <div class="hero-copy">
            <p class="eyebrow">Modern Academic Attendance</p>
            <h1 class="hero-title">EduTrack powers smart, QR-based attendance for universities.</h1>
            <p class="hero-subtitle">Reduce roll-call time, increase accountability, and give every lecturer a real-time view of engagement.</p>
            <div class="hero-actions">
                <a class="btn btn-success" href="auth/login.php">Sign In</a>
                <a class="btn btn-outline-light" href="about.php">Learn More</a>
            </div>
            <div class="hero-meta">
                <span><i class="bi bi-shield-check" aria-hidden="true"></i> Secure & role-based</span>
                <span><i class="bi bi-phone" aria-hidden="true"></i> Mobile-first</span>
                <span><i class="bi bi-graph-up-arrow" aria-hidden="true"></i> Instant analytics</span>
            </div>
        </div>
        <div class="hero-card">
            <div class="hero-card-header">
                <span class="pill">Instant Insights</span>
                <span class="muted">Attendance Overview</span>
            </div>
            <div class="hero-card-body">
                <p class="hero-card-title">Attendance Snapshot</p>
                <div class="hero-card-grid">
                    <div>
                        <p class="stat">86%</p>
                        <p class="stat-label">Present</p>
                    </div>
                    <div>
                        <p class="stat">12</p>
                        <p class="stat-label">Late</p>
                    </div>
                    <div>
                        <p class="stat">4</p>
                        <p class="stat-label">Absent</p>
                    </div>
                </div>
                <div class="hero-card-footer">
                    <span class="dot"></span>
                    <span>Real-time updates</span>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="stats">
    <div class="container stats-grid">
        <div class="stat-card">
            <p class="stat-number">10 min</p>
            <p class="stat-text">Average class time saved</p>
        </div>
        <div class="stat-card">
            <p class="stat-number">3 roles</p>
            <p class="stat-text">Student, Lecturer, Admin views</p>
        </div>
        <div class="stat-card">
            <p class="stat-number">100%</p>
            <p class="stat-text">QR attendance accuracy</p>
        </div>
    </div>
</section>

<section class="trust">
    <div class="container trust-inner">
        <div class="trust-copy">
            <p class="eyebrow dark">Built for academic operations</p>
            <h2>Trusted by lecturers, admins, and student services teams.</h2>
            <p>EduTrack supports the daily academic workflow with attendance you can audit, verify, and report with confidence.</p>
        </div>
        <div class="trust-logos">
            <div class="logo-tile">College of Science</div>
            <div class="logo-tile">Faculty of Education</div>
            <div class="logo-tile">School of Business</div>
            <div class="logo-tile">Student Services</div>
        </div>
    </div>
</section>

<!-- ============================================
     BENEFITS SECTION
     ============================================ -->
<section class="benefits">
    <div class="container">
        <div class="section-header">
            <h2>Why Universities Choose EduTrack</h2>
            <p>Academic tools designed to reduce manual work and improve student outcomes.</p>
        </div>
        <div class="feature-cards">
            <article class="feature-card">
                <i class="bi bi-clock-history" aria-hidden="true"></i>
                <h3>Save Lecturing Time</h3>
                <p>Capture attendance in seconds so every class starts on time.</p>
            </article>
            <article class="feature-card">
                <i class="bi bi-shield-lock" aria-hidden="true"></i>
                <h3>Secure by Design</h3>
                <p>Role-based access and encrypted records keep student data protected.</p>
            </article>
            <article class="feature-card">
                <i class="bi bi-file-earmark-text" aria-hidden="true"></i>
                <h3>Instant Reports</h3>
                <p>Generate course-level summaries and export in seconds.</p>
            </article>
        </div>
    </div>
</section>

<!-- ============================================
     HOW IT WORKS SECTION
     ============================================ -->
<section class="how-it-works">
    <div class="container">
        <div class="section-header">
            <h2>How EduTrack Works</h2>
            <p>Built for campuses that want speed, clarity, and accountability.</p>
        </div>
        <div class="steps-grid">
            <div class="step-card">
                <div class="step-number">01</div>
                <i class="bi bi-qr-code" aria-hidden="true"></i>
                <h4>Generate QR Code</h4>
                <p>Lecturers launch a session and share a secure QR instantly.</p>
            </div>
            <div class="step-card">
                <div class="step-number">02</div>
                <i class="bi bi-camera" aria-hidden="true"></i>
                <h4>Scan & Attend</h4>
                <p>Students scan with any smartphone and mark attendance.</p>
            </div>
            <div class="step-card">
                <div class="step-number">03</div>
                <i class="bi bi-graph-up-arrow" aria-hidden="true"></i>
                <h4>Track & Analyze</h4>
                <p>Dashboards update in real time for lecturers and admins.</p>
            </div>
            <div class="step-card">
                <div class="step-number">04</div>
                <i class="bi bi-download" aria-hidden="true"></i>
                <h4>Export Reports</h4>
                <p>Download verified attendance records for every course.</p>
            </div>
        </div>
    </div>
</section>

<!-- ============================================
     FEATURES SECTION
     ============================================ -->
<section class="features">
    <div class="container">
        <div class="section-header">
            <h2>Everything You Need</h2>
            <p>Simple workflows for staff, clear experiences for students.</p>
        </div>
        <div class="feature-grid">
            <div class="feature-tile">
                <i class="bi bi-phone" aria-hidden="true"></i>
                <h4>Mobile-First</h4>
                <p>Designed for quick scanning and on-the-go access.</p>
            </div>
            <div class="feature-tile">
                <i class="bi bi-lightning-charge" aria-hidden="true"></i>
                <h4>Real-Time Updates</h4>
                <p>Attendance syncs instantly across every dashboard.</p>
            </div>
            <div class="feature-tile">
                <i class="bi bi-people" aria-hidden="true"></i>
                <h4>Multi-Role Access</h4>
                <p>Dedicated views for Admins, Lecturers, and Students.</p>
            </div>
            <div class="feature-tile">
                <i class="bi bi-cloud" aria-hidden="true"></i>
                <h4>Cloud Ready</h4>
                <p>Secure access from any campus or device.</p>
            </div>
        </div>
    </div>
</section>


<!-- ============================================
     FOOTER
     ============================================ -->
    <?php require_once 'includes/footer.php'; ?>

<!-- ============================================
     SCRIPTS
     ============================================ -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>
<!-- Jotform AI Agent (floating) -->
<script src='https://cdn.jotfor.ms/agent/embedjs/019cc3659a2c7b37973b9619070ccc221479/embed.js'></script>

</body>
</html>
