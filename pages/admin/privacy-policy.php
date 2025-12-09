<?php
// Preload (auto-locate includes/preload.php)
$__et=__DIR__;
for($__i=0;$__i<6;$__i++){
    $__p=$__et . '/includes/preload.php';
    if (file_exists($__p)) { require_once $__p; break; }
    $__et=dirname($__et);
}
unset($__et,$__i,$__p);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Privacy Policy - EduTrack</title>

<!-- Bootstrap CSS -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

<!-- Font Awesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<!-- Custom CSS -->
<link rel="stylesheet" href="../../assets/css/style.css">

</head>
<body>
<?php require_once '../../includes/admin_navbar.php'; ?>

<div class="container my-5">
    <h1 class="mb-4 text-success"><i class="fas fa-user-shield me-2"></i>Privacy Policy</h1>
    <p class="text-muted"><strong>Effective date:</strong> August 4, 2025</p>

    <div class="card p-4 shadow-sm bg-white">

        <section class="mb-4">
            <h2>1. Introduction</h2>
            <p>
                EduTrack ("we", "our", or "us") is committed to protecting and respecting your privacy.
                This Privacy Policy explains how we collect, use, and safeguard your personal information when you use our attendance tracking system.
            </p>
        </section>

        <section class="mb-4">
            <h2>2. Information We Collect</h2>
            <ul>
                <li><strong>Personal Identification Information:</strong> Name, email address, student or lecturer ID, and contact details.</li>
                <li><strong>Usage Data:</strong> Attendance records, login times, device information, and interaction with the system.</li>
                <li><strong>Cookies and Tracking:</strong> We use cookies to improve your user experience and analyze site traffic.</li>
            </ul>
        </section>

        <section class="mb-4">
            <h2>3. How We Use Your Information</h2>
            <ul>
                <li>Manage and track attendance accurately.</li>
                <li>Provide personalized access to your dashboard.</li>
                <li>Improve our services and system security.</li>
                <li>Communicate important updates, notifications, and support.</li>
            </ul>
        </section>

        <section class="mb-4">
            <h2>4. Data Security</h2>
            <p>
                We implement industry-standard security measures to protect your data against unauthorized access, alteration, disclosure, or destruction.
                However, no internet transmission is completely secure. Please use strong passwords and keep your login credentials confidential.
            </p>
        </section>

        <section class="mb-4">
            <h2>5. Sharing Your Information</h2>
            <p>
                We do not sell, trade, or rent your personal information to third parties.
                We may share your information with authorized university staff or service providers strictly for attendance management and system maintenance.
            </p>
        </section>

        <section class="mb-4">
            <h2>6. Your Rights</h2>
            <p>
                You have the right to access, update, or request deletion of your personal information.
                To exercise these rights, please contact us using the information provided below.
            </p>
        </section>

        <section class="mb-4">
            <h2>7. Cookies Policy</h2>
            <p>
                EduTrack uses cookies to enhance your experience. You can set your browser to refuse cookies or alert you when cookies are being sent.
                Note that some parts of the system may not function properly without cookies.
            </p>
        </section>

        <section class="mb-4">
            <h2>8. Changes to This Privacy Policy</h2>
            <p>
                We may update this Privacy Policy periodically. We encourage you to review this page regularly for any changes.
                Continued use of EduTrack after changes constitutes your acceptance of the updated policy.
            </p>
        </section>

        <section class="mb-4">
            <h2>9. Contact Us</h2>
            <p>If you have questions or concerns about this Privacy Policy, please contact us:</p>
            <ul>
                <li>Email: <a href="mailto:kundananjisimukonda@gmail.com">kundananjisimukonda@gmail.com</a></li>
                <li>Phone: +260 967 591 264 / +260 971 863 462</li>
            </ul>
        </section>

    </div>

    <div class="mt-4">
        <a href="../../index.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Back to Home</a>
    </div>
</div>

<?php require_once(__DIR__ . '/../../includes/footer.php'); // Keep footer ?>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
