<?php
// Preload (auto-locate includes/preload.php)
$__et=__DIR__;
for($__i=0;$__i<6;$__i++){
    $__p=$__et . '/includes/preload.php';
    if (file_exists($__p)) { require_once $__p; break; }
    $__et=dirname($__et);
}
unset($__et,$__i,$__p);
// send_messages.php (root) — Save contact and send email using PDO + PHPMailer with .env settings

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/vendor/autoload.php';

$statusMsg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');

    if ($name && $email && filter_var($email, FILTER_VALIDATE_EMAIL) && $subject && $message) {
        try {
            // Save message in DB via PDO
            $stmt = $pdo->prepare('INSERT INTO contact_messages (name, email, subject, message, created_at) VALUES (:n, :e, :s, :m, NOW())');
            $stmt->execute([':n' => $name, ':e' => $email, ':s' => $subject, ':m' => $message]);

            // Send email via SMTP (env-driven)
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
            $mail->Body    = "New message from EduTrack Contact Form:\n\nName: $name\nEmail: $email\nSubject: $subject\nMessage:\n$message\n";

            if ($mail->Username && $mail->Password) {
                $mail->send();
            }
            $statusMsg = "<div class='alert alert-success'>Message saved successfully ✅</div>";
        } catch (Exception $e) {
            error_log('Mailer error: ' . $e->getMessage());
            $statusMsg = "<div class='alert alert-warning'>Message saved, but email failed ❌</div>";
        } catch (PDOException $e) {
            error_log('DB error: ' . $e->getMessage());
            $statusMsg = "<div class='alert alert-danger'>Database error ❌: Could not save message</div>";
        }
    } else {
        $statusMsg = "<div class='alert alert-danger'>Invalid input ❌</div>";
    }
}
?>

<!-- Display status message -->
<?php if ($statusMsg) echo $statusMsg; ?>
