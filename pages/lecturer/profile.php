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
require_once '../../includes/auth_check.php';
require_once '../../includes/config.php';
require_once '../../includes/db.php';
require_login();
require_role(['lecturer']);

$user_id = $_SESSION['user_id'];
$message = '';
$error = '';

try {
    // Handle form submission (if POST request)
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $name = trim($_POST['name']);
        $phone = trim($_POST['phone']);

        // Validate input
        if (empty($name)) {
            $error = 'Name cannot be empty.';
        } else {
            // Prepare and execute the update query
            $stmt = $pdo->prepare('UPDATE users SET name = ?, phone = ? WHERE id = ?');
            $stmt->execute([$name, $phone, $user_id]);
            $message = 'Profile updated successfully!';
        }
    }

    // Fetch the current user data to populate the form
    $stmt = $pdo->prepare('SELECT name, email, phone FROM users WHERE id = ?');
    $stmt->execute([$user_id]);
    $user_data = $stmt->fetch(PDO::FETCH_ASSOC);

    // Set variables for the form
    $current_name = htmlspecialchars($user_data['name']);
    $current_email = htmlspecialchars($user_data['email']);
    $current_phone = htmlspecialchars($user_data['phone']);

} catch (Exception $e) {
    // Log the error for debugging and set a user-friendly message
    error_log('Database error in profile.php: ' . $e->getMessage());
    $error = 'An error occurred. Please try again later.';
    $current_name = '';
    $current_email = '';
    $current_phone = '';
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/dashboard.css">
</head>

<body>
<nav class="navbar">
    <div class="container">
        <div class="logo">
            <a href="../../index.php">
                <img src="../../assets/logo.png" alt="EduTrack Logo">
            </a>
        </div>
        <ul class="nav-links">
            <li><a href="../../index.php">Home</a></li>
            <li><a href="dashboard.php">Dashboard</a></li>
            <li><a href="../../logout.php">Logout</a></li>
        </ul>
    </div>
</nav>

<div class="dashboard-container">
    <a href="dashboard.php" class="back-link"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>
    <h1>My Profile</h1>

    <?php if ($message): ?>
        <p class="success-message"><?php echo $message; ?></p>
    <?php endif; ?>
    <?php if ($error): ?>
        <p class="error-message"><?php echo $error; ?></p>
    <?php endif; ?>

    <form action="profile.php" method="POST" class="profile-form">
        <div class="form-group">
            <label for="name">Name:</label>
            <input type="text" id="name" name="name" value="<?php echo $current_name; ?>" required>
        </div>
        <div class="form-group">
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" value="<?php echo $current_email; ?>" disabled>
            <p class="form-help-text">Email cannot be changed.</p>
        </div>
        <div class="form-group">
            <label for="phone">Phone Number:</label>
            <input type="text" id="phone" name="phone" value="<?php echo $current_phone; ?>">
        </div>
        <button type="submit" class="submit-btn">Update Profile</button>
    </form>
</div>

<?php require_once '../../includes/footer.php'; ?>
</body>
</html>
