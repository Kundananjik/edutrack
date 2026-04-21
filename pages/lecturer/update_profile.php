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
session_start();
require_once '../../includes/config.php';
require_once '../../includes/db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$user_id = $_SESSION['user_id'];
$name = $_POST['name'] ?? '';
$email = $_POST['email'] ?? '';
$phone = $_POST['phone'] ?? '';


if (!$name || !$email) {
    echo json_encode(['success' => false, 'message' => 'Name and Email are required']);
    exit();
}

try {
    $sql = 'UPDATE users SET name = :name, email = :email, phone = :phone WHERE id = :id';
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':name', $name);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':phone', $phone);
    $stmt->bindParam(':id', $user_id);

    $stmt->execute();

    echo json_encode(['success' => true, 'message' => 'Profile updated successfully']);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Error updating profile: ' . $e->getMessage()]);
}
