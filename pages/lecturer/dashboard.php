<?php
// ============================================
// AUTO-LOAD PRELOAD FILE
// This block searches upward for /includes/preload.php
// so your script works no matter where it's placed.
// ============================================
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
    header('Location: ../../auth/login.php');
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
    $stmt = $pdo->prepare('SELECT id, name, email, phone FROM users WHERE id = :id LIMIT 1');
    $stmt->execute(['id' => $user_id]);
    $lecturer = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($lecturer) {
        $name = $lecturer['name'];
    }

    // --------------------------------------------
    // COUNT COURSES ASSIGNED TO THIS LECTURER
    // --------------------------------------------
    $stmt = $pdo->prepare('SELECT COUNT(DISTINCT course_id) FROM lecturer_courses WHERE lecturer_id = :id');
    $stmt->execute(['id' => $user_id]);
    $my_courses_count = (int) $stmt->fetchColumn();

    // --------------------------------------------
    // COUNT UNIQUE STUDENTS ENROLLED UNDER THIS LECTURER
    // --------------------------------------------
    $stmt = $pdo->prepare('
        SELECT COUNT(DISTINCT e.student_id)
        FROM enrollments e
        JOIN lecturer_courses lc ON e.course_id = lc.course_id
        WHERE lc.lecturer_id = :id
    ');
    $stmt->execute(['id' => $user_id]);
    $my_students_count = (int) $stmt->fetchColumn();

    // --------------------------------------------
    // COUNT CURRENTLY ACTIVE ATTENDANCE SESSIONS
    // --------------------------------------------
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM attendance_sessions WHERE is_active = 1 AND lecturer_id = :id');
    $stmt->execute(['id' => $user_id]);
    $active_sessions_count = (int) $stmt->fetchColumn();

} catch (PDOException $e) {
    // Log internal error and display user-friendly message
    error_log('Dashboard error: ' . $e->getMessage());
    $error_message = 'Failed to load dashboard data. Please try again later.';
}

// ============================================
// TIME-BASED GREETING FOR LECTURER
// ============================================
$hour = date('H');
if ($hour < 12) {
    $greeting = 'Good Morning';
    $emoji = '☀️';
} elseif ($hour < 18) {
    $greeting = 'Good Afternoon';
    $emoji = '🌤️';
} else {
    $greeting = 'Good Evening';
    $emoji = '🌙';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    
    <link rel="icon" type="image/png" href="<?= asset_url('assets/favicon.png') ?>">
    <title>EduTrack - Lecturer Dashboard</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Dashboard CSS -->
    <link rel="stylesheet" href="css/dashboard.css">

    <!-- Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
</head>

<body>

<?php require_once '../../includes/lecturer_navbar.php'; ?>

<!-- ============================================
     MAIN DASHBOARD CONTENT
=============================================== -->
<div class="container mt-4">

    <!-- Lecturer Greeting -->
    <h1 class="mb-4" style="color:#2fa360;">
        <?= $greeting . ', ' . htmlspecialchars($name) . '! ' . $emoji ?>
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
                ['title' => 'My Courses', 'count' => $my_courses_count, 'icon' => 'bi-book', 'link' => 'my_courses.php'],
                ['title' => 'My Students', 'count' => $my_students_count, 'icon' => 'bi-mortarboard', 'link' => 'my_students.php'],
                ['title' => 'Active Sessions', 'count' => $active_sessions_count, 'icon' => 'bi-easel', 'link' => 'active_sessions.php']
            ];

        foreach ($cards as $c):
            ?>
            <div class="col">
                <a href="<?= $c['link'] ?>" class="card h-100 text-center text-decoration-none" style="color:#2fa360; border-left:5px solid #2fa360;">
                    <div class="card-body">
                        <i class="bi <?= $c['icon'] ?> fs-2 mb-2"></i>
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
                        <i class="bi bi-graph-up fs-2 mb-2"></i>
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
                        <i class="bi bi-send fs-2 mb-2"></i>
                        <h5 class="card-title">Send Announcement</h5>
                    </div>
                </a>
            </div>

            <div class="col">
                <a href="view_announcements.php" class="card h-100 text-center text-decoration-none" style="color:#2fa360; border-left:5px solid #2fa360;">
                    <div class="card-body">
                        <i class="bi bi-megaphone fs-2 mb-2"></i>
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
            <a href="../account/change_password.php" class="btn btn-outline-success ms-2">Change Password</a>
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
<script <?= et_csp_attr('script') ?>>
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

