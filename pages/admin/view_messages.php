<?php
// Preload (auto-locate includes/preload.php)
$__et=__DIR__;
for($__i=0;$__i<6;$__i++){
    $__p=$__et . '/includes/preload.php';
    if (file_exists($__p)) { require_once $__p; break; }
    $__et=dirname($__et);
}
unset($__et,$__i,$__p);
require_once '../../includes/auth_check.php';
require_once '../../includes/config.php';
require_once '../../includes/db.php';
require_once '../../includes/csrf.php';

require_login();
require_role(['admin']);

// Generate CSRF token
$csrf_token = get_csrf_token();

try {
    $sql = "SELECT cm.id, cm.name, cm.email, cm.message, cm.created_at AS message_date,
                   cr.reply_message AS reply_text, cr.created_at AS reply_date, u.name AS responder_name
            FROM contact_messages cm
            LEFT JOIN contact_replies cr ON cm.id = cr.message_id
            LEFT JOIN users u ON cr.responder_id = u.id
            ORDER BY cm.created_at DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Database query error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">

</head>
<body>
<?php require_once '../../includes/admin_navbar.php'; ?>

<main class="container py-4">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="stylesheet" href="css/dashboard.css">
</head>
<body>

<div class="container-fluid py-4">
    <div class="card shadow-sm">
        <div class="card-body">

            <div class="d-flex justify-content-between mb-3">
                <a href="dashboard.php" class="btn btn-outline-secondary"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>
            </div>

            <h1 class="mb-4 text-success fw-bold">Manage Messages</h1>

            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle">
                    <thead class="table-success text-center">
                        <tr>
                            <th>From</th>
                            <th>Email</th>
                            <th>Message</th>
                            <th>Reply</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if ($messages): ?>
                        <?php foreach ($messages as $msg): ?>
                            <tr>
                                <td><?= htmlspecialchars($msg['name']) ?></td>
                                <td><?= htmlspecialchars($msg['email']) ?></td>
                                <td><?= nl2br(htmlspecialchars($msg['message'])) ?><br><small><?= htmlspecialchars($msg['message_date']) ?></small></td>
                                <td>
                                    <?php if (!empty($msg['reply_text'])): ?>
                                        <?= nl2br(htmlspecialchars($msg['reply_text'])) ?><br>
                                        <small>By: <?= htmlspecialchars($msg['responder_name'] ?? 'Admin') ?> at <?= htmlspecialchars($msg['reply_date']) ?></small>
                                    <?php else: ?>
                                        <em>No reply yet</em>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <button class="btn btn-outline-primary btn-sm view-btn" 
                                            data-id="<?= htmlspecialchars($msg['id']) ?>" 
                                            data-email="<?= htmlspecialchars($msg['email']) ?>" 
                                            data-name="<?= htmlspecialchars($msg['name']) ?>">
                                        <i class="fas fa-reply"></i> Reply
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center"><em>No messages found.</em></td>
                        </tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>

        </div>
    </div>
</div>

<!-- Bootstrap Modal -->
<div class="modal fade" id="replyModal" tabindex="-1" aria-labelledby="replyModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title text-success" id="replyModalLabel">Reply to <span id="modal-name"></span></h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="reply-form">
          <div class="mb-3">
            <textarea class="form-control" name="reply_message" id="reply_message" rows="5" placeholder="Type your reply..." required></textarea>
          </div>
          <input type="hidden" name="message_id" id="message_id">
          <input type="hidden" name="recipient_email" id="recipient_email">
          <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
          <div id="reply-status" class="mb-2"></div>
          <button type="submit" class="btn btn-success"><i class="fas fa-paper-plane"></i> Send Reply</button>
        </form>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
const replyModal = new bootstrap.Modal(document.getElementById('replyModal'));
const modalName = document.getElementById('modal-name');
const replyForm = document.getElementById('reply-form');
const replyStatus = document.getElementById('reply-status');
const recipientEmailInput = document.getElementById('recipient_email');
const messageIdInput = document.getElementById('message_id');

document.querySelectorAll('.view-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        modalName.textContent = btn.dataset.name;
        recipientEmailInput.value = btn.dataset.email;
        messageIdInput.value = btn.dataset.id;
        replyForm.reset();
        replyStatus.textContent = '';
        replyModal.show();
    });
});

replyForm.addEventListener('submit', async (e) => {
    e.preventDefault();
    replyStatus.textContent = "Sending...";
    const formData = new FormData(replyForm);
    
    try {
        const response = await fetch('reply_message.php', { method: 'POST', body: formData });
        const result = await response.text();
        replyStatus.innerHTML = result;
    } catch (err) {
        replyStatus.innerHTML = "<span class='text-danger'>Error sending reply. Try again.</span>";
    }
});
</script>
<?php require_once '../../includes/footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
