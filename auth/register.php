<?php
// Preload (auto-locate includes/preload.php)
$__et=__DIR__;
for($__i=0;$__i<6;$__i++){
    $__p=$__et . '/includes/preload.php';
    if (file_exists($__p)) { require_once $__p; break; }
    $__et=dirname($__et);
}
unset($__et,$__i,$__p);
// auth/register.php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/session.php';
require_once '../includes/functions.php';
require_once '../includes/csrf.php';

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
        $errors[] = 'Invalid CSRF token.';
    }
    $name = cleanInput($_POST['name'] ?? '');
    $student_number = cleanInput($_POST['student_number'] ?? '');
    $email = cleanInput($_POST['email'] ?? '');
    $programme_id = cleanInput($_POST['programme'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Validation
    if (empty($name) || empty($student_number) || empty($email) || empty($programme_id) || empty($password)) {
        $errors[] = "All fields are required.";
    }

    if ($password !== $confirm_password) {
        $errors[] = "Passwords do not match.";
    }

    // Check duplicate email
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = :em");
    $stmt->execute(['em' => $email]);
    if ($stmt->rowCount() > 0) {
        $errors[] = "Email already registered.";
    }

    // Check duplicate student_number
    $stmt = $pdo->prepare("SELECT user_id FROM students WHERE student_number = :sn");
    $stmt->execute(['sn' => $student_number]);
    if ($stmt->rowCount() > 0) {
        $errors[] = "Student Number already registered.";
    }

    if (empty($errors)) {
        try {
            $pdo->beginTransaction();

            // Insert into users table
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("
                INSERT INTO users (name, email, password, role, status, created_at)
                VALUES (:name, :email, :password, 'student', 'active', CURRENT_TIMESTAMP)
            ");
            $stmt->execute([
                'name' => $name,
                'email' => $email,
                'password' => $hashed
            ]);

            $user_id = $pdo->lastInsertId();

            // Insert into students table
            $stmt = $pdo->prepare("
                INSERT INTO students (user_id, student_number, programme_id)
                VALUES (:user_id, :student_number, :programme_id)
            ");
            $stmt->execute([
                'user_id' => $user_id,
                'student_number' => $student_number,
                'programme_id' => $programme_id
            ]);

            $pdo->commit();
            $success = "Registration successful! You can now <a href='login.php'>login here</a>.";
        } catch (PDOException $e) {
            $pdo->rollBack();
            error_log("Registration error: " . $e->getMessage());
            $errors[] = "Database error: Could not register.";
        }
    }
}

// Fetch programmes for dropdown
$programmes = [];
try {
    $stmt = $pdo->query("SELECT id, name FROM programmes ORDER BY name ASC");
    $programmes = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Failed to fetch programmes: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Registration</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
        
    <link rel="stylesheet" href="../assets/css/style.css">

</head>
<body>
<nav class="navbar">
    <div class="container">
        <div class="logo">
            <a href="../index.php"><img src="../assets/logo.png" alt="EduTrack Logo" /></a>
        </div>
        <ul class="nav-links">
            <li><a href="../index.php">Home</a></li>
        </ul>
    </div>
</nav>

<div class="container">
    <h2>Student Registration</h2>

    <?php if (!empty($errors)): ?>
        <div class="error">
            <ul><?php foreach ($errors as $e) echo "<li>$e</li>"; ?></ul>
        </div>
    <?php elseif ($success): ?>
        <div class="success"><?php echo $success; ?></div>
    <?php endif; ?>

    <form method="POST" action="">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(get_csrf_token()) ?>">
        <label for="name">Full Name:</label>
        <input type="text" name="name" required>

        <label for="student_number">Student Number:</label>
        <input type="text" name="student_number" required>

        <label for="email">Email:</label>
        <input type="email" name="email" required>

        <label for="programme">Programme:</label>
        <select name="programme" required>
            <option value="">-- Select Programme --</option>
            <?php foreach ($programmes as $p): ?>
                <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['name']) ?></option>
            <?php endforeach; ?>
        </select>

        <label for="password">Password:</label>
        <input type="password" name="password" required>

        <label for="confirm_password">Confirm Password:</label>
        <input type="password" name="confirm_password" required>

        <button type="submit">Register</button>
    </form>

    <p><a href="login.php">Already registered? Login here</a></p>
</div>
<?php require_once '../includes/footer.php'; ?>
</body>
</html>
