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
// Auth/send_message — Save contact and send email using env-driven SMTP

use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../vendor/autoload.php';

$status = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');

    if ($name && $email && filter_var($email, FILTER_VALIDATE_EMAIL) && $subject && $message) {
        try {
            // Save message in DB using PDO
            $stmt = $pdo->prepare('INSERT INTO contact_messages (name, email, subject, message, created_at) VALUES (:name, :email, :subject, :message, NOW())');
            $stmt->execute([
                ':name' => $name,
                ':email' => $email,
                ':subject' => $subject,
                ':message' => $message
            ]);

            // Send email via SMTP using .env values
            $mail = new PHPMailer(true);
            $mail->isSMTP();
            $mail->Host       = $_ENV['SMTP_HOST'] ?? 'smtp.mailtrap.io';
            $mail->SMTPAuth   = true;
            $mail->Username   = $_ENV['SMTP_USERNAME'] ?? '';
            $mail->Password   = $_ENV['SMTP_PASSWORD'] ?? '';
            $mail->SMTPSecure = $_ENV['SMTP_ENCRYPTION'] ?? 'tls';
            $mail->Port       = (int)($_ENV['SMTP_PORT'] ?? 2525);

            $fromEmail = $_ENV['SMTP_FROM'] ?? 'support@edutrack.local';
            $fromName  = $_ENV['SMTP_FROM_NAME'] ?? 'EduTrack Support';
            $toEmail   = $_ENV['SUPPORT_TO'] ?? 'support@example.com';

            $mail->setFrom($fromEmail, $fromName);
            $mail->addReplyTo($email, $name);
            $mail->addAddress($toEmail, 'EduTrack Support');
            $mail->isHTML(false);
            $mail->Subject = $subject;
            $mail->Body    = "New message from EduTrack Contact Form:\n\nName: $name\nEmail: $email\nSubject: $subject\nMessage:\n$message";

            if ($mail->Username && $mail->Password) {
                $mail->send();
            }
            $status = 'success';

        } catch (Exception $e) {
            $status = 'email_failed';
            error_log('PHPMailer Error: ' . $e->getMessage());
        } catch (PDOException $e) {
            $status = 'db_error';
            error_log('DB Error: ' . $e->getMessage());
        }
    } else {
        $status = 'invalid_input';
    }

    header('Location: ../contact.php?status=' . urlencode($status));
    exit;
}
