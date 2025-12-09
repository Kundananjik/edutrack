<?php
// Preload (auto-locate includes/preload.php)
$__et=__DIR__;
for($__i=0;$__i<6;$__i++){
    $__p=$__et . '/includes/preload.php';
    if (file_exists($__p)) { require_once $__p; break; }
    $__et=dirname($__et);
}
unset($__et,$__i,$__p);
// File: public/send_message.php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once 'includes/config.php';
require_once 'includes/db.php';
require 'vendor/autoload.php';

$status = '';

// Check POST
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    
    // Sanitize input
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');
    
    if ($name && $email && filter_var($email, FILTER_VALIDATE_EMAIL) && $subject && $message) {
        try {
            // 1. Save message in DB using PDO
            $stmt = $pdo->prepare("INSERT INTO contact_messages (name, email, subject, message, created_at)
                                   VALUES (:name, :email, :subject, :message, NOW())");
            $stmt->execute([
                ':name' => $name,
                ':email' => $email,
                ':subject' => $subject,
                ':message' => $message
            ]);

            // 2. Send email via Mailtrap
            $mail = new PHPMailer(true);
            $mail->isSMTP();
            $mail->Host       = 'smtp.mailtrap.io';
            $mail->SMTPAuth   = true;
            $mail->Username   = '59e8f01dba1a8e'; // Mailtrap username
            $mail->Password   = '309378b1c9b4ed'; // Mailtrap password
            $mail->SMTPSecure = 'tls';
            $mail->Port       = 2525;

            $mail->setFrom('support@edutrack.com', 'EduTrack Support'); // Allowed by SMTP
            $mail->addReplyTo($email, $name); // User's email for reply
            $mail->addAddress('kundananjisimukonda@gmail.com', 'EduTrack Support');

            $mail->isHTML(false);
            $mail->Subject = $subject;
            $mail->Body    = "New message from EduTrack Contact Form:\n\nName: $name\nEmail: $email\nSubject: $subject\nMessage:\n$message";

            $mail->send();
            $status = 'success';

        } catch (Exception $e) {
            // Email failed but message is stored
            $status = 'email_failed';
            // Optional: log error
            error_log("PHPMailer Error: " . $mail->ErrorInfo);
        } catch (PDOException $e) {
            $status = 'db_error';
            error_log("DB Error: " . $e->getMessage());
        }
    } else {
        $status = 'invalid_input';
    }

    // Redirect back to contact page with status
    header("Location: ../contact.php?status=$status");
    exit;
}
?>
