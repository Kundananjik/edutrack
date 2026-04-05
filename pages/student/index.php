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
// index.php - EduTrack Landing Page

define('APP_ROOT', __DIR__);
require_once APP_ROOT . '/config/bootstrap.php';
require_once APP_ROOT . '/config/database.php';

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../../');
$dotenv->load();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EduTrack - Student Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/student.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
</head>
<body>

<?php require_once '../../includes/student_navbar.php'; ?>

<!-- HERO SECTION -->
<section class="hero">
    <div class="container">
        <h1 class="headline">Welcome to EduTrack – University Attendance Tracking System</h1>
        <p class="subheadline">
            Mark attendance in seconds, track in real-time, and save valuable lecturing minutes in every course.
        </p>
        <p class="subheadline">
            Track, monitor, and manage your university attendance with ease. Access your dashboard from any device, anytime.
        </p>
    </div>
</section>

<!-- BENEFITS SECTION -->
<section class="benefits">
    <div class="container">
        <h1>Why Choose EduTrack?</h1>
        <div class="benefit-items">
            <div class="benefit">
                <i class="bi bi-clock benefit-icon"></i>
                <h3>Save Time</h3>
                <p>Save up to 10 minutes each class by using automated QR attendance.</p>
                <p>Lecturers can focus on teaching rather than administrative tasks, ensuring high-quality learning.</p>
            </div>
            <div class="benefit">
                <i class="bi bi-lock benefit-icon"></i>
                <h3>Secure Data</h3>
                <p>Your student information is encrypted and protected under GDPR standards.</p>
                <p>We use industry-standard encryption protocols to safeguard personal and academic information, fully compliant with regulations.</p>
            </div>
            <div class="benefit">
                <i class="bi bi-file-earmark-text benefit-icon"></i>
                <h3>Instant Reports</h3>
                <p>Download attendance reports instantly for any course or date range.</p>
                <p>Comprehensive insights allow lecturers to track engagement and make informed decisions quickly.</p>
            </div>
        </div>
    </div>
</section>

<!-- SECURITY SECTION -->
<section class="security">
    <div class="container">
        <h1>Trusted</h1>
        <div class="badges">
            <i class="bi bi-shield-check benefit-icon" title="GDPR Compliant"></i>
        </div>
        <p>EduTrack prioritizes confidentiality and data integrity. All student and lecturer information is protected from unauthorized access and adheres to the highest privacy standards.</p>
    </div>
</section>

<!-- FOOTER -->
<?php require_once '../../includes/footer.php'; ?>

</body>
</html>

