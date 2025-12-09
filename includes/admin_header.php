<?php
session_start();
require_once '../../includes/auth_check.php'; // ensure admin is logged in
?>
<header class="admin-header">
    <div class="logo">
        <h1>EduTrack Admin</h1>
    </div>
    <nav>
        <a href="../../includes/logout.php" class="logout-btn">Logout</a>
    </nav>
</header>
