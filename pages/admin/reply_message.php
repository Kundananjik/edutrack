<?php
// Preload (auto-locate includes/preload.php)
$__et=__DIR__;
for($__i=0;$__i<6;$__i++){
    $__p=$__et . '/includes/preload.php';
    if (file_exists($__p)) { require_once $__p; break; }
    $__et=dirname($__et);
}
unset($__et,$__i,$__p);
require_once '../../includes/config.php';
require_once '../../includes/db.php';
require_once '../../vendor/autoload.php';
require_once '../../includes/auth_check.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_login();
require_role(['admin']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $recipient = filter_var($_POST['recipient_email'] ?? '', FILTER_VALIDATE_EMAIL);
    $messageBody = trim($_POST['reply_message'] ?? '');
    $messageId = (int)($_POST['message_id'] ?? 0);
    $responderId = $_SESSION['user_id']; // Use a more descriptive variable name

    if ($recipient && $messageBody && $messageId) {
        $mail = new PHPMailer(true);
        try {
            // Server settings
            $mail->isSMTP();
            $mail->Host       = 'smtp.mailtrap.io';
            $mail->SMTPAuth   = true;
            $mail->Username   = '59e8f01dba1a8e';
            $mail->Password   = '309378b1c9b4ed';
            $mail->SMTPSecure = 'tls';
            $mail->Port       = 2525;

            // Recipients
            $mail->setFrom('support@edutrack.com', 'EduTrack Admin');
            $mail->addAddress($recipient);

            // Content
            $mail->isHTML(true);
            $mail->Subject = 'Reply from EduTrack Support';
            $mail->Body    = "<p>Hello,</p><p>" . htmlspecialchars($messageBody) . "</p><p>-- EduTrack Team</p>";
            $mail->AltBody = "Hello,\n\n" . $messageBody . "\n\n-- EduTrack Team"; // Plain text version for better compatibility

            $mail->send();

            // Log reply in database
            // CORRECTED: changed 'admin_id' to 'responder_id'
            $stmt = $pdo->prepare("INSERT INTO contact_replies (message_id, responder_id, reply_message) VALUES (?, ?, ?)");
            $stmt->execute([$messageId, $responderId, $messageBody]);

            echo "<span style='color:green;'>Reply sent and logged successfully ✅</span>";
        } catch (Exception $e) {
            echo "<span style='color:red;'>Failed to send reply ❌ ({$mail->ErrorInfo})</span>";
            // For debugging, you could also log this to a file:
            // error_log("PHPMailer error: " . $e->getMessage());
        }
    } else {
        echo "<span style='color:red;'>Invalid recipient, message, or message ID ❌</span>";
    }
}
?>