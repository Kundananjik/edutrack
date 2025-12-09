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
<title>Terms of Service - EduTrack</title>

<!-- Bootstrap CSS -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

<!-- Font Awesome for icons -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<!-- Custom CSS -->
<link rel="stylesheet" href="../../assets/css/style.css">

</head>
<body>
<?php require_once '../../includes/admin_navbar.php'; ?>

<div class="container my-5">
    <h1 class="mb-4 text-success"><i class="fas fa-file-contract me-2"></i>Terms of Service</h1>
    <p class="text-muted">Effective date: August 4, 2025</p>

    <div class="card p-4 shadow-sm bg-white">
        <section class="mb-3">
            <h2>1. Acceptance of Terms</h2>
            <p>
                By accessing or using EduTrack ("the Service"), you agree to comply with and be bound by these Terms of Service.
                If you do not agree with any part of these terms, you must not use the Service.
            </p>
        </section>

        <section class="mb-3">
            <h2>2. Use of the Service</h2>
            <ul>
                <li>The Service is provided exclusively for students, lecturers, and authorized university staff to monitor and manage attendance.</li>
                <li>You agree to use the Service only for lawful purposes and in accordance with all applicable laws and university policies.</li>
                <li>You are responsible for maintaining the confidentiality of your login credentials and for all activities conducted under your account.</li>
            </ul>
        </section>

        <section class="mb-3">
            <h2>3. User Responsibilities</h2>
            <ul>
                <li>Use the Service only for lawful purposes and not for transmitting unlawful or harmful content.</li>
                <li>Do not attempt unauthorized access to accounts or systems.</li>
                <li>Do not interfere with the proper functioning or security of the Service.</li>
            </ul>
        </section>

        <section class="mb-3">
            <h2>4. Intellectual Property</h2>
            <p>
                All content, features, and functionality of EduTrack, including software, design, text, graphics, logos, and data, are the exclusive property of EduTrack and its licensors.
                Unauthorized use or reproduction of these materials is prohibited.
            </p>
        </section>

        <section class="mb-3">
            <h2>5. Privacy</h2>
            <p>
                Your use of the Service is also governed by our <a href="privacy-policy.php">Privacy Policy</a>, which explains how we collect and use your personal information.
            </p>
        </section>

        <section class="mb-3">
            <h2>6. Limitation of Liability</h2>
            <p>
                EduTrack provides the Service "as is" and does not guarantee uninterrupted or error-free operation.
                To the maximum extent permitted by law, EduTrack shall not be liable for any damages arising out of your use of the Service.
            </p>
        </section>

        <section class="mb-3">
            <h2>7. Termination</h2>
            <p>
                We reserve the right to suspend or terminate your access to the Service at our sole discretion, without prior notice, for conduct that violates these Terms or is harmful to others.
            </p>
        </section>

        <section class="mb-3">
            <h2>8. Changes to Terms</h2>
            <p>
                We may update these Terms of Service from time to time.
                Continued use of the Service after changes indicates your acceptance of the updated terms.
                We encourage you to review this page regularly.
            </p>
        </section>

        <section class="mb-3">
            <h2>9. Contact Information</h2>
            <p>If you have any questions or concerns about these Terms, please contact us at:</p>
            <ul>
                <li>Email: <a href="mailto:kundananjisimukonda@gmail.com">kundananjisimukonda@gmail.com</a></li>
                <li>Phone: +260 967 591 264 / +260 971 863 462</li>
            </ul>
        </section>
    </div>
</div>

<?php require_once(__DIR__ . '/../../includes/footer.php'); ?>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
