<?php
// Preload (auto-locate includes/preload.php)
$__et=__DIR__;
for($__i=0;$__i<6;$__i++){
    $__p=$__et . '/includes/preload.php';
    if (file_exists($__p)) { require_once $__p; break; }
    $__et=dirname($__et);
}
unset($__et,$__i,$__p);
// pages/admin/delete_student.php

require_once '../../includes/auth_check.php';
require_once '../../includes/config.php';
require_once '../../includes/db.php';
require_once '../../includes/functions.php';

require_login();
require_role(['admin']);

// Ensure it's a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error_message'] = 'Invalid request method.';
    redirect('manage_students.php');
}

$id = intval($_POST['id'] ?? 0);

if ($id <= 0) {
    $_SESSION['error_message'] = 'Invalid student ID.';
    redirect('manage_students.php');
}

try {
    // First delete from students table (foreign key)
    $stmt = $pdo->prepare("DELETE FROM students WHERE user_id = ?");
    $stmt->execute([$id]);

    // Then delete the linked user record
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ? AND role = 'student'");
    $stmt->execute([$id]);

    $_SESSION['success_message'] = "Student deleted successfully.";
} catch (PDOException $e) {
    error_log("Error deleting student ID $id: " . $e->getMessage());
    $_SESSION['error_message'] = "Failed to delete student. Please try again later.";
}

redirect('manage_students.php');
?>
