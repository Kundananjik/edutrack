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
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>About EduTrack</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="wrapper-main">
        <header>
            <h1>About EduTrack</h1>
        </header>
        
        <section>
            <p>EduTrack is a modern academic management system built to simplify and enhance how institutions track and manage student learning. It provides tools for attendance tracking, course management, performance analytics, and user collaboration.</p>
            
            <p>Our mission is to empower educators and administrators with intuitive, flexible, and scalable tools that streamline educational workflows.</p>
        </section>
    </div>

    <?php require_once 'includes/footer.php'; ?>
</body>
</html>
