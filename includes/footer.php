<?php
// includes/footer.php
$base_url = defined('BASE_URL') ? BASE_URL : '/';
$base_url = rtrim($base_url, '/') . '/';
$uri = $_SERVER['REQUEST_URI'] ?? '';
$is_role_page = (strpos($uri, '/pages/admin') !== false)
    || (strpos($uri, '/pages/lecturer') !== false)
    || (strpos($uri, '/pages/student') !== false);
$footer_class = $is_role_page ? 'site-footer compact' : 'site-footer';
?>
<footer class="<?= $footer_class ?>" role="contentinfo">
    <div class="footer-container">
        <div class="footer-brand">
            <div class="brand-title">EduTrack</div>
            <p class="brand-description">Modern academic attendance for universities and colleges. Fast setup, secure records, and real-time insight.</p>
        </div>
        <div class="footer-column">
            <h5>Product</h5>
            <ul>
                <li><a href="<?= $base_url ?>about.php">About</a></li>
                <li><a href="<?= $base_url ?>help.php">Help Center</a></li>
                <li><a href="<?= $base_url ?>auth/login.php">Sign In</a></li>
            </ul>
        </div>
        <div class="footer-column">
            <h5>Policies</h5>
            <ul>
                <li><a href="<?= $base_url ?>privacy-policy.php">Privacy Policy</a></li>
                <li><a href="<?= $base_url ?>terms-of-service.php">Terms of Service</a></li>
                <li><a href="<?= $base_url ?>contact.php">Contact Us</a></li>
            </ul>
        </div>
        <div class="footer-column">
            <h5>Contact</h5>
            <ul class="footer-contact">
                <li>
                    <i class="bi bi-person-badge" aria-hidden="true"></i>
                    <span>Kundananji Simukonda</span>
                </li>
                <li>
                    <i class="bi bi-envelope" aria-hidden="true"></i>
                    <a href="mailto:kundananjisimukonda@gmail.com">kundananjisimukonda@gmail.com</a>
                </li>
                <li>
                    <i class="bi bi-telephone" aria-hidden="true"></i>
                    <a href="tel:+260967591264">+260 967 591 264</a>
                </li>
                <li>
                    <i class="bi bi-telephone" aria-hidden="true"></i>
                    <a href="tel:+260971863462">+260 971 863 462</a>
                </li>
            </ul>
        </div>
    </div>
    <div class="footer-bottom">
        <p>&copy; <span id="year"><?php echo date('Y'); ?></span> EduTrack. All rights reserved.</p>
    </div>
</footer>
