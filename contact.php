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

$page_title = "Contact Us - EduTrack";
$page_description = "Contact EduTrack support for inquiries, feedback, or technical assistance.";

$success = isset($_GET['success']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title><?= htmlspecialchars($page_title) ?></title>
    <meta name="description" content="<?= htmlspecialchars($page_description) ?>">

    <link rel="icon" type="image/png" href="assets/favicon.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css?v=1.0.2">
</head>
<body>

<?php
$navbar = __DIR__ . '/includes/navbar.php';
if (file_exists($navbar)) require_once $navbar;
?>

<main class="container my-5">
    <div class="row">
        <div class="col-lg-9 mx-auto">

            <h1 class="mb-2">Contact Us</h1>
            <p class="text-muted">
                Use the form below to contact EduTrack administration or technical support.
            </p>

            <?php if ($success): ?>
                <div class="alert alert-success">
                    Your message has been submitted successfully. We will respond shortly.
                </div>
            <?php endif; ?>

            <div class="row mt-4">
                <div class="col-md-5 mb-4">
                    <h5>Contact Information</h5>
                    <ul class="list-unstyled mt-3">
                        <li class="mb-2">
                            <strong>Email</strong><br>
                            <a href="mailto:kundananjisimukonda@gmail.com">
                                kundananjisimukonda@gmail.com
                            </a>
                        </li>
                        <li class="mb-2">
                            <strong>Phone</strong><br>
                            <a href="tel:+260967591264">+260 967 591 264</a><br>
                            <a href="tel:+260971863462">+260 971 863 462</a>
                        </li>
                        <li class="mb-2">
                            <strong>Office Hours</strong><br>
                            Monday to Friday, 08:00 – 17:00
                        </li>
                    </ul>
                </div>

                <div class="col-md-7">
                    <h5>Send a Message</h5>

                    <form method="post" action="process_contact.php" novalidate>
                        <div class="mb-3">
                            <label class="form-label">Full Name</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Email Address</label>
                            <input type="email" name="email" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Subject</label>
                            <input type="text" name="subject" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Message</label>
                            <textarea name="message" rows="5" class="form-control" required></textarea>
                        </div>

                        <button type="submit" class="btn btn-primary">
                            Send Message
                        </button>
                    </form>
                </div>
            </div>

        </div>
    </div>
</main>

<?php
$footer = __DIR__ . '/includes/footer.php';
if (file_exists($footer)) {
    require_once $footer;
} else {
    echo '<footer class="text-center text-muted py-4">
            <p>&copy; ' . date('Y') . ' EduTrack. All rights reserved.</p>
          </footer>';
}
?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script <?= et_csp_attr('script') ?>>
    const yearEl = document.getElementById('year');
    if (yearEl) yearEl.textContent = new Date().getFullYear();
</script>

</body>
</html>
