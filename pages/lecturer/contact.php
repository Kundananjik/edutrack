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
// pages/lecturer/contact.php
require_once '../../includes/auth_check.php';
require_once '../../includes/config.php';
require_once '../../includes/db.php';
require_once '../../includes/csrf.php';

require_login();
require_role(['lecturer']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Contact Us - EduTrack</title>
<meta name="csrf-token" content="<?= htmlspecialchars(get_csrf_token()) ?>">

<!-- Bootstrap CSS -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

<!-- Font Awesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<!-- EduTrack Custom CSS -->
<link rel="stylesheet" href="css/dashboard.css">
<style>
/* Lecturer Theme Overrides */
body { background-color: #f8f9fa; }
h2 { color: #2fa360; }
.card { border-radius: 12px; box-shadow: 0 2px 6px rgba(0,0,0,0.1); }
.btn-back { background-color: #2fa360; border-color: #2fa360; color: #fff; border-radius: 8px; }
.btn-back:hover { background-color: #27a04c; border-color: #27a04c; color: #fff; }
.btn-submit { background-color: #2fa360; border-color: #2fa360; border-radius: 8px; }
.btn-submit:hover { background-color: #27a04c; border-color: #27a04c; }
</style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-light bg-light mb-4">
    <div class="container">
        <a class="navbar-brand" href="../../index.php">
            <img src="../../assets/logo.png" alt="EduTrack Logo" height="40">
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item"><a class="nav-link" href="dashboard.php">Dashboard</a></li>
                <li class="nav-item"><a class="nav-link" href="../../logout.php">Logout</a></li>
            </ul>
        </div>
    </div>
</nav>

<div class="container my-5">

    <a href="dashboard.php" class="btn btn-back mb-3"><i class="fas fa-arrow-left me-1"></i> Back to Dashboard</a>

    <h2 class="mb-3"><i class="fas fa-envelope me-2"></i>Contact Us</h2>
    <p class="mb-4">If you have any questions or need support, feel free to reach out using the form below.</p>

    <div class="card p-4 bg-white">
<form id="contactForm" method="POST" class="contact-form">
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

            <div class="mb-4">
                <label for="message" class="form-label">Message:</label>
                <textarea id="message" name="message" rows="5" required placeholder="Your message..." class="form-control"></textarea>
            </div>

            <button type="submit" class="btn btn-submit"><i class="fas fa-paper-plane me-2"></i>Send Message</button>
        </form>
    </div>
</div>

<!-- Bootstrap Modal -->
<div class="modal fade" id="successModal" tabindex="-1" aria-labelledby="successModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-success">
      <div class="modal-header bg-success text-white">
        <h5 class="modal-title" id="successModalLabel">Message Sent</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        Your message has been successfully sent! We will get back to you shortly.
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-success" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<!-- Bootstrap JS & dependencies -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script <?= et_csp_attr('script') ?>>
document.getElementById('contactForm').addEventListener('submit', async function(e) {
    e.preventDefault();

    const formData = new FormData(this);
    const meta = document.querySelector('meta[name="csrf-token"]');
    if (meta) { formData.append('csrf_token', meta.getAttribute('content')); }

    try {
        const response = await fetch('../../send_message.php', {
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

<?php require_once(__DIR__ . '/../../includes/footer.php'); ?>

</body>
</html>
