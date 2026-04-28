<?php
// Preload (auto-locate includes/preload.php)
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

require_once '../../includes/auth_check.php';
require_once '../../includes/config.php';
require_once '../../includes/db.php';
require_once '../../includes/functions.php';

require_login();
require_role(['admin']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/png" href="<?= asset_url('assets/favicon.png') ?>">
    <title>Contact Support - EduTrack Admin</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="css/dashboard.css">
</head>
<body class="bg-light d-flex flex-column min-vh-100">

<?php require_once '../../includes/admin_navbar.php'; ?>

<main class="container py-5 flex-grow-1">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <h1 class="fw-bold mb-2"><i class="bi bi-envelope me-2"></i>Contact Support</h1>
            <p class="text-muted mb-0">Send an admin-side support message from within EduTrack.</p>
        </div>
        <a href="dashboard.php" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Back to Dashboard
        </a>
    </div>

    <section class="card shadow-sm rounded-4">
        <div class="card-body">
            <form id="contactForm" method="POST" class="row g-3">
                <div class="col-md-6">
                    <label for="name" class="form-label">Full Name</label>
                    <input type="text" id="name" name="name" required placeholder="Your name..." class="form-control">
                </div>

                <div class="col-md-6">
                    <label for="email" class="form-label">Email Address</label>
                    <input type="email" id="email" name="email" required placeholder="your@email.com" class="form-control">
                </div>

                <div class="col-12">
                    <label for="subject" class="form-label">Subject</label>
                    <input type="text" id="subject" name="subject" required placeholder="Message subject..." class="form-control">
                </div>

                <div class="col-12">
                    <label for="message" class="form-label">Message</label>
                    <textarea id="message" name="message" rows="5" required placeholder="Your message..." class="form-control"></textarea>
                </div>

                <div class="col-12">
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-send me-2"></i>Send Message
                    </button>
                </div>
            </form>
        </div>
    </section>
</main>

<div class="modal fade" id="successModal" tabindex="-1" aria-labelledby="successModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-success">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="successModalLabel">Message Sent</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Your message has been successfully sent. Support can follow up from the admin message tools.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-success" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script <?= et_csp_attr('script') ?>>
document.getElementById('contactForm').addEventListener('submit', async function (e) {
    e.preventDefault();

    const formData = new FormData(this);

    try {
        const response = await fetch('send_message.php', {
            method: 'POST',
            body: formData
        });

        if (response.ok) {
            const modal = new bootstrap.Modal(document.getElementById('successModal'));
            modal.show();
            this.reset();
        } else {
            alert('Error sending message. Please try again.');
        }
    } catch (err) {
        alert('Network error: ' + err.message);
    }
});
</script>
</body>
</html>
