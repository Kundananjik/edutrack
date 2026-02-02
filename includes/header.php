<?php
// includes/header.php
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>EduTrack System</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <!-- Optional meta -->
    <meta name="description" content="EduTrack Attendance Management System">
    <meta name="author" content="Kundananji Simukonda">

    <!-- Link to global CSS -->
    <link rel="stylesheet" href="../public/assets/css/style.css">
</head>
<body>

<!-- Optional Navbar (modular) -->
<header role="banner">
    <div class="container">
        <h1>EduTrack System</h1>
        <!-- You can add logo, navigation, toggle, etc. here -->
    </div>
</header>
<main role="main">
    <div class="container">
        <!-- Main content will be injected here -->
        <?php if (isset($content)) {
            echo $content;
        } ?>
    </div>