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
// pages/student/contact.php
require_once(__DIR__ . '/../../includes/csrf.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    
    <link rel="icon" type="image/png" href="<?= asset_url('assets/favicon.png') ?>">
    <title>Contact Us - EduTrack Student</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/student.css">
</head>
<body>

<?php require_once '../../includes/student_navbar.php'; ?>

<main class="container my-5">
    <h2 class="mb-3">Contact Us</h2>
    <p>If you have any questions or need support, feel free to reach out using the form below.</p>

    <form action="../../send_message.php" method="POST" class="contact-form mt-4">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(get_csrf_token()) ?>">
        <div class="mb-3">
            <label for="name" class="form-label">Full Name:</label>
            <input type="text" id="name" name="name" required placeholder="Your name..." class="form-control" />
        </div>

        <div class="mb-3">
            <label for="email" class="form-label">Email Address:</label>
            <input type="email" id="email" name="email" required placeholder="your@email.com" class="form-control" />
        </div>

        <div class="mb-3">
            <label for="subject" class="form-label">Subject:</label>
            <input type="text" id="subject" name="subject" required placeholder="Message subject..." class="form-control" />
        </div>

        <div class="mb-3">
            <label for="message" class="form-label">Message:</label>
            <textarea id="message" name="message" rows="5" required placeholder="Your message..." class="form-control"></textarea>
        </div>

        <button type="submit" class="btn btn-success">Send Message</button>
    </form>
</main>

<!-- Optional Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<?php require_once '../../includes/footer.php'; ?>

</body>
</html>
