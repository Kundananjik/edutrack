<?php
// Reusable admin navbar include
?>
<!-- Admin Navbar -->
<nav class="navbar navbar-light bg-light">
    <div class="container">
        <a class="navbar-brand" href="../../index.php">
            <img src="../../assets/logo.png" alt="EduTrack Logo" height="40">
        </a>

        <!-- Offcanvas toggle for small screens -->
        <button class="navbar-toggler d-lg-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasNavbar" aria-controls="offcanvasNavbar" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <!-- Desktop menu (visible on large screens) -->
        <div class="d-none d-lg-flex ms-auto">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0 d-flex flex-row gap-2 align-items-center">
                <li class="nav-item"><a class="nav-link" href="../../index.php">Home</a></li>
                <?php if (function_exists('is_logged_in') && is_logged_in()): ?>
                    <li class="nav-item"><a class="nav-link" href="../../logout.php">Logout</a></li>
                <?php else: ?>
                    <li class="nav-item"><a class="nav-link" href="../../auth/login.php">Login</a></li>
                <?php endif; ?>
            </ul>
        </div>

        <!-- Offcanvas menu for mobile -->
        <div class="offcanvas offcanvas-start" tabindex="-1" id="offcanvasNavbar" aria-labelledby="offcanvasNavbarLabel">
            <div class="offcanvas-header">
                <h5 class="offcanvas-title" id="offcanvasNavbarLabel">Menu</h5>
                <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
            </div>
            <div class="offcanvas-body">
                <ul class="navbar-nav justify-content-start flex-grow-1 pe-3">
                    <li class="nav-item"><a class="nav-link" href="../../index.php">Home</a></li>
                    <?php if (function_exists('is_logged_in') && is_logged_in()): ?>
                        <li class="nav-item"><a class="nav-link" href="../../logout.php">Logout</a></li>
                    <?php else: ?>
                        <li class="nav-item"><a class="nav-link" href="../../auth/login.php">Login</a></li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </div>
</nav>
