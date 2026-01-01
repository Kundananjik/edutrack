<?php
// ============================================
// INDEX.PHP - EDUTRACK LANDING PAGE
// ============================================

define('APP_ROOT', __DIR__);
define('ASSET_VERSION', '1.0.2'); // Easy cache busting

// ============================================
// BOOTSTRAP & ERROR HANDLING
// ============================================
require_once APP_ROOT . '/includes/error_handlers.php';

$bootstrap = APP_ROOT . '/config/bootstrap.php';
if (file_exists($bootstrap)) {
    require_once $bootstrap;
} else {
    et_simple_error_page('Bootstrap missing', 'Expected config/bootstrap.php but it was not found.');
}

$dbconf = APP_ROOT . '/config/database.php';
if (file_exists($dbconf)) {
    require_once $dbconf;
}

// ============================================
// HELPER FUNCTIONS
// ============================================

/**
 * Generate versioned asset URL for cache busting
 * @param string $path Asset path
 * @return string Versioned URL
 */
function asset_url($path)
{
    return $path . '?v=' . ASSET_VERSION;
}
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
    <link href="http://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <link rel="stylesheet" href="<?= asset_url('assets/css/style.css') ?>">
    
    <!-- Bootstrap Icons (lighter alternative to Font Awesome) -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
</head>
<body>

<!-- ============================================
     NAVIGATION BAR
     ============================================ -->
<nav class="navbar navbar-light bg-white shadow-sm">
    <div class="container">
        <!-- Brand -->
        <a class="navbar-brand" href="index.php">
            <img src="<?= asset_url('assets/logo.png') ?>" alt="EduTrack Logo" height="40" width="auto">
        </a>

        <!-- Mobile Toggle -->
        <button class="navbar-toggler d-lg-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasNav" aria-controls="offcanvasNav" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <!-- Desktop Navigation -->
        <div class="d-none d-lg-flex ms-auto">
            <ul class="navbar-nav d-flex flex-row gap-2 align-items-center">
                <li class="nav-item">
                    <a class="nav-link active" aria-current="page" href="index.php">Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="about.php">About</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="help.php">Help</a>
                </li>
                <li class="nav-item">
                    <a class="btn btn-success ms-2" href="auth/login.php">Login</a>
                </li>
            </ul>
        </div>

        <!-- Mobile Offcanvas Menu -->
        <div class="offcanvas offcanvas-start" tabindex="-1" id="offcanvasNav" aria-labelledby="offcanvasNavLabel">
            <div class="offcanvas-header">
                <h5 class="offcanvas-title" id="offcanvasNavLabel">Menu</h5>
                <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
            </div>
            <div class="offcanvas-body">
                <ul class="navbar-nav flex-column gap-2">
                    <li class="nav-item">
                        <a class="nav-link active" aria-current="page" href="index.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="about.php">About</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="help.php">Help</a>
                    </li>
                </ul>
                <div class="mt-3">
                    <a href="auth/login.php" class="btn btn-success w-100">Login</a>
                </div>
            </div>
        </div>
    </div>
</nav>

<!-- ============================================
     HERO SECTION
     ============================================ -->
<section class="hero py-5 bg-light">
    <div class="container text-center">
        <h1 class="display-5 fw-bold">Welcome to EduTrack – University Attendance Tracking System</h1>
        <p class="lead mt-3">Mark attendance in seconds, track in real-time, and save valuable lecturing minutes in every course.</p>
        <p class="lead">Track, monitor, and manage your university attendance with ease. Access your dashboard from any device, anytime.</p>
    </div>
</section>

<!-- ============================================
     BENEFITS SECTION
     ============================================ -->
<section class="benefits py-5">
    <div class="container">
        <h2 class="text-center mb-5">Why Choose EduTrack?</h2>
        <div class="row g-4 text-center">
            <div class="col-md-4">
                <i class="bi bi-clock-history text-success mb-3" style="font-size: 3rem;" aria-hidden="true"></i>
                <h4>Save Time</h4>
                <p>Save up to 10 minutes each class using automated QR attendance. Lecturers can focus on teaching rather than admin tasks.</p>
            </div>
            <div class="col-md-4">
                <i class="bi bi-shield-lock text-success mb-3" style="font-size: 3rem;" aria-hidden="true"></i>
                <h4>Secure Data</h4>
                <p>Your student information is encrypted and GDPR-compliant. Industry-standard encryption protects personal and academic info.</p>
            </div>
            <div class="col-md-4">
                <i class="bi bi-file-earmark-text text-success mb-3" style="font-size: 3rem;" aria-hidden="true"></i>
                <h4>Instant Reports</h4>
                <p>Download attendance reports instantly for any course or date range. Track engagement and make informed decisions.</p>
            </div>
        </div>
    </div>
</section>

<!-- ============================================
     HOW IT WORKS SECTION
     ============================================ -->
<section class="how-it-works py-5 bg-light">
    <div class="container">
        <h2 class="text-center mb-5">How EduTrack Works</h2>
        <div class="row g-4 text-center">
            <div class="col-md-3">
                <div class="p-3 border rounded bg-white h-100">
                    <div class="mb-2 fw-bold fs-4">1</div>
                    <i class="bi bi-qr-code text-success mb-2" style="font-size: 2rem;" aria-hidden="true"></i>
                    <h5>Generate QR Code</h5>
                    <p>Lecturers create a unique QR code for each class session with one click.</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="p-3 border rounded bg-white h-100">
                    <div class="mb-2 fw-bold fs-4">2</div>
                    <i class="bi bi-camera text-success mb-2" style="font-size: 2rem;" aria-hidden="true"></i>
                    <h5>Scan & Attend</h5>
                    <p>Students scan the QR code using their smartphone cameras to mark attendance instantly.</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="p-3 border rounded bg-white h-100">
                    <div class="mb-2 fw-bold fs-4">3</div>
                    <i class="bi bi-graph-up text-success mb-2" style="font-size: 2rem;" aria-hidden="true"></i>
                    <h5>Track & Analyze</h5>
                    <p>View real-time attendance data and generate comprehensive reports for analysis.</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="p-3 border rounded bg-white h-100">
                    <div class="mb-2 fw-bold fs-4">4</div>
                    <i class="bi bi-download text-success mb-2" style="font-size: 2rem;" aria-hidden="true"></i>
                    <h5>Export Reports</h5>
                    <p>Download attendance records in CSV format for administrative purposes and record-keeping.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ============================================
     FEATURES SECTION
     ============================================ -->
<section class="features py-5">
    <div class="container">
        <h2 class="text-center mb-5">Powerful Features</h2>
        <div class="row g-4 text-center">
            <div class="col-md-3">
                <i class="bi bi-phone text-success mb-2" style="font-size: 2rem;" aria-hidden="true"></i>
                <h5>Mobile-Friendly</h5>
                <p>Works seamlessly on all devices – smartphones, tablets, and computers.</p>
            </div>
            <div class="col-md-3">
                <i class="bi bi-lightning-charge text-success mb-2" style="font-size: 2rem;" aria-hidden="true"></i>
                <h5>Real-Time Updates</h5>
                <p>See attendance updates instantly as students scan the QR codes.</p>
            </div>
            <div class="col-md-3">
                <i class="bi bi-people text-success mb-2" style="font-size: 2rem;" aria-hidden="true"></i>
                <h5>Multi-Role Access</h5>
                <p>Different dashboards for students, lecturers, and administrators.</p>
            </div>
            <div class="col-md-3">
                <i class="bi bi-cloud text-success mb-2" style="font-size: 2rem;" aria-hidden="true"></i>
                <h5>Cloud-Based</h5>
                <p>Access your data from anywhere with our secure cloud infrastructure.</p>
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
<script src="http://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>

</body>
</html>