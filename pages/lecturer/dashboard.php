<?php
// ============================================
// AUTO-LOAD PRELOAD FILE
// This block searches upward for /includes/preload.php
// so your script works no matter where it's placed.
// ============================================
$__et=__DIR__;
for($__i=0;$__i<6;$__i++){
    $__p=$__et . '/includes/preload.php';
    if (file_exists($__p)) { require_once $__p; break; }
    $__et=dirname($__et);
}
unset($__et,$__i,$__p);

// ============================================
// SESSION + AUTH + CONFIG LOADING
// ============================================
require_once '../../includes/auth_check.php';
require_once '../../includes/config.php';
require_once '../../includes/db.php';

require_login();                  // Ensure lecturer is logged in
require_role(['lecturer']);       // Restrict access to lecturers only

// ============================================
// GET CURRENT LECTURER
// ============================================
$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
    header("Location: ../../auth/login.php");
    exit();
}

// Vars for dashboard display
$lecturer = null;
$name = 'Lecturer';
$my_courses_count = 0;
$my_students_count = 0;
$active_sessions_count = 0;
$error_message = null;

try {
    // --------------------------------------------
    // FETCH LECTURER PROFILE DETAILS
    // --------------------------------------------
    $stmt = $pdo->prepare("SELECT id, name, email, phone FROM users WHERE id = :id LIMIT 1");
    $stmt->execute(['id' => $user_id]);
    $lecturer = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($lecturer) $name = $lecturer['name'];

    // --------------------------------------------
    // COUNT COURSES ASSIGNED TO THIS LECTURER
    // --------------------------------------------
    $stmt = $pdo->prepare("SELECT COUNT(DISTINCT course_id) FROM lecturer_courses WHERE lecturer_id = :id");
    $stmt->execute(['id' => $user_id]);
    $my_courses_count = (int) $stmt->fetchColumn();

    // --------------------------------------------
    // COUNT UNIQUE STUDENTS ENROLLED UNDER THIS LECTURER
    // --------------------------------------------
    $stmt = $pdo->prepare("
        SELECT COUNT(DISTINCT e.student_id)
        FROM enrollments e
        JOIN lecturer_courses lc ON e.course_id = lc.course_id
        WHERE lc.lecturer_id = :id
    ");
    $stmt->execute(['id' => $user_id]);
    $my_students_count = (int) $stmt->fetchColumn();

    // --------------------------------------------
    // COUNT CURRENTLY ACTIVE ATTENDANCE SESSIONS
    // --------------------------------------------
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM attendance_sessions WHERE is_active = 1 AND lecturer_id = :id");
    $stmt->execute(['id' => $user_id]);
    $active_sessions_count = (int) $stmt->fetchColumn();

} catch (PDOException $e) {
    // Log internal error and display user-friendly message
    error_log("Dashboard error: " . $e->getMessage());
    $error_message = "Failed to load dashboard data. Please try again later.";
}

// ============================================
// TIME-BASED GREETING FOR LECTURER
// ============================================
$hour = date('H');
if ($hour < 12) {
    $greeting = "Good Morning";
    $emoji = "☀️";
} elseif ($hour < 18) {
    $greeting = "Good Afternoon";
    $emoji = "🌤️";
} else {
    $greeting = "Good Evening";
    $emoji = "🌙";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>EduTrack - Lecturer Dashboard</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Dashboard CSS -->
    <link rel="stylesheet" href="css/dashboard.css">

    <!-- Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body>

<!-- ============================================
     NAVBAR
=============================================== -->
<nav class="navbar navbar-expand-lg" style="background-color:#2fa360;">
    <div class="container">
        <a class="navbar-brand text-white" href="../../index.php">
            <img src="../../assets/logo.png" alt="EduTrack Logo" height="40">
        </a>

        <!-- Menu Items -->
        <ul class="navbar-nav ms-auto">
            <li class="nav-item"><a class="nav-link text-white" href="../../index.php">Home</a></li>
            <li class="nav-item"><a class="nav-link text-white" href="../../logout.php">Logout</a></li>
        </ul>
    </div>
</nav>

<!-- ============================================
     MAIN DASHBOARD CONTENT
=============================================== -->
<div class="container mt-4">

    <!-- Lecturer Greeting -->
    <h1 class="mb-4" style="color:#2fa360;">
        <?= $greeting . ", " . htmlspecialchars($name) . "! " . $emoji ?>
    </h1>

    <?php if ($error_message): ?>
        <!-- Error Alert -->
        <div class="alert alert-danger"><?= htmlspecialchars($error_message) ?></div>

    <?php else: ?>

        <!-- ============================================
             OVERVIEW CARDS
        =============================================== -->
        <div class="row row-cols-1 row-cols-md-3 g-4 mb-4">
            <?php
            // Cards dynamically rendered from array
            $cards = [
                ['title'=>'My Courses', 'count'=>$my_courses_count, 'icon'=>'fa-book-open', 'link'=>'my_courses.php'],
                ['title'=>'My Students', 'count'=>$my_students_count, 'icon'=>'fa-user-graduate', 'link'=>'my_students.php'],
                ['title'=>'Active Sessions', 'count'=>$active_sessions_count, 'icon'=>'fa-chalkboard-teacher', 'link'=>'active_sessions.php']
            ];

            foreach($cards as $c):
            ?>
            <div class="col">
                <a href="<?= $c['link'] ?>" class="card h-100 text-center text-decoration-none" style="color:#2fa360; border-left:5px solid #2fa360;">
                    <div class="card-body">
                        <i class="fas <?= $c['icon'] ?> fa-2x mb-2"></i>
                        <h5 class="card-title"><?= $c['title'] ?></h5>
                        <p class="card-text display-6"><?= htmlspecialchars($c['count']) ?></p>
                    </div>
                </a>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- ============================================
             REPORTS SECTION
        =============================================== -->
        <h2 class="mt-4 mb-3" style="color:#2fa360;">Reports</h2>
        <div class="row row-cols-1 row-cols-md-3 g-4 mb-4">
            <div class="col">
                <a href="attendance_reports.php" class="card h-100 text-center text-decoration-none" style="color:#2fa360; border-left:5px solid #2fa360;">
                    <div class="card-body">
                        <i class="fas fa-chart-line fa-2x mb-2"></i>
                        <h5 class="card-title">Attendance Reports</h5>
                    </div>
                </a>
            </div>
        </div>

        <!-- ============================================
             COMMUNICATION SECTION
        =============================================== -->
        <h2 class="mt-4 mb-3" style="color:#2fa360;">Communication</h2>

        <div class="row row-cols-1 row-cols-md-3 g-4 mb-4">
            <div class="col">
                <a href="send_announcement.php" class="card h-100 text-center text-decoration-none" style="color:#2fa360; border-left:5px solid #2fa360;">
                    <div class="card-body">
                        <i class="fas fa-paper-plane fa-2x mb-2"></i>
                        <h5 class="card-title">Send Announcement</h5>
                    </div>
                </a>
            </div>

            <div class="col">
                <a href="view_announcements.php" class="card h-100 text-center text-decoration-none" style="color:#2fa360; border-left:5px solid #2fa360;">
                    <div class="card-body">
                        <i class="fas fa-bullhorn fa-2x mb-2"></i>
                        <h5 class="card-title">View Announcements</h5>
                    </div>
                </a>
            </div>
        </div>

        <!-- ============================================
             UPDATE PROFILE SECTION
        =============================================== -->
        <div class="mt-4 mb-4 text-center">
            <button id="update-profile-btn" class="btn btn-success">Update Profile</button>
        </div>

        <!-- Hidden Profile Form -->
        <div id="profile-page" class="card p-4 mb-4" style="display:none;">
            <form id="profile-form" method="post" action="update_profile.php">

                <!-- Name -->
                <div class="mb-3">
                    <label class="form-label">Name</label>
                    <input class="form-control" type="text" name="name" value="<?= htmlspecialchars($lecturer['name'] ?? '') ?>" required>
                </div>

                <!-- Email -->
                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input class="form-control" type="email" name="email" value="<?= htmlspecialchars($lecturer['email'] ?? '') ?>" required>
                </div>

                <!-- Phone -->
                <div class="mb-3">
                    <label class="form-label">Phone</label>
                    <input class="form-control" type="text" name="phone" value="<?= htmlspecialchars($lecturer['phone'] ?? '') ?>">
                </div>

                <!-- Password -->
                <div class="mb-3">
                    <label class="form-label">New Password</label>
                    <input class="form-control" type="password" name="password" placeholder="Leave blank to keep current">
                </div>

                <button type="submit" class="btn btn-success">Save Changes</button>
            </form>
        </div>

    <?php endif; ?>

</div>

<?php require_once(__DIR__ . '/../../includes/footer.php'); ?>

<!-- ============================================
     JS: PROFILE FORM TOGGLE
=============================================== -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const updateBtn = document.getElementById('update-profile-btn');
    const profileForm = document.getElementById('profile-page');

    // Defensive check in case elements are not present
    if (updateBtn && profileForm) {
        updateBtn.addEventListener('click', function() {
            const isHidden = profileForm.style.display === 'none' || profileForm.style.display === '';
            if (isHidden) {
                profileForm.style.display = 'block';
                updateBtn.textContent = 'Hide Profile';
            } else {
                profileForm.style.display = 'none';
                updateBtn.textContent = 'Update Profile';
            }
        });
    }
});
</script>
</body>
</html>
