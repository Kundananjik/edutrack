<?php
// includes/footer.php
?>

</main> <!-- End of main content -->

<!-- Footer -->
<footer class="site-footer" role="contentinfo">
    <div class="footer-container">
        <div class="footer-info">
            <h4>EduTrack System</h4>
            <p><strong>Developed By:</strong> Kundananji Simukonda</p>
            <p>Email: <a href="mailto:kundananjisimukonda@gmail.com">kundananjisimukonda@gmail.com</a></p>
            <p>Contact: 
                <a href="tel:+260967591264">+260 967 591 264</a> /
                <a href="tel:+260971863462">+260 971 863 462</a>
            </p>
        </div>

        <div class="footer-links">
            <ul>
                <li><a href="privacy-policy.php">Privacy Policy</a></li>
                <li><a href="terms-of-service.php">Terms of Service</a></li>
                <li><a href="contact.php">Contact Us</a></li>
            </ul>
        </div>
    </div>

    <div class="footer-bottom">
        <p>&copy; <span id="year"></span> EduTrack. All rights reserved.</p>
    </div>
</footer>

<!-- Auto-update Year -->
<script <?= et_csp_attr('script') ?>>
    document.getElementById('year').textContent = new Date().getFullYear();
</script>

</body>
</html>
