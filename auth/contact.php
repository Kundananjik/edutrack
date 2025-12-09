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
<?php require_once(__DIR__ . '/../includes/csrf.php'); ?>
<!-- Link to main stylesheet -->
<link rel="stylesheet" href="../assets/css/style.css">

<div class="main-content">
    <h2>Contact Us</h2>
    <p>If you have any questions or need support, feel free to reach out using the form below.</p>

    <form action="send_message.php" method="POST" class="contact-form">
        <div class="form-group">
            <label for="name">Full Name:</label>
            <input type="text" id="name" name="name" required placeholder="Your name..." />
        </div>

        <div class="form-group">
            <label for="email">Email Address:</label>
            <input type="email" id="email" name="email" required placeholder="your@email.com" />
        </div>

        <div class="form-group">
            <label for="subject">Subject:</label>
            <input type="text" id="subject" name="subject" required placeholder="Message subject..." />
        </div>

        <div class="form-group">
            <label for="message">Message:</label>
            <textarea id="message" name="message" rows="5" required placeholder="Your message..."></textarea>
        </div>

        <button type="submit" class="btn-submit">Send Message</button>
    </form>
</div>
<?php 
require_once(__DIR__ . '/../includes/footer.php');
?>
