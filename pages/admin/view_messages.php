<?php
// ============================================
// ADMIN - MANAGE MESSAGES
// ============================================

// Preload (auto-locate includes/preload.php)
$__et = __DIR__;
for ($__i = 0; $__i < 6; $__i++) {
    $__p = $__et . '/includes/preload.php';
    if (file_exists($__p)) {
        require_once $__p;
        break;
    }
    $__et = dirname($__et);
}
unset($__et, $__i, $__p);

require_once '../../includes/auth_check.php';
require_once '../../includes/config.php';
require_once '../../includes/db.php';
require_once '../../includes/csrf.php';

require_login();
require_role(['admin']);

// Generate CSRF token
$csrf_token = get_csrf_token();

try {
    $sql = 'SELECT cm.id, cm.name, cm.email, cm.message, cm.created_at AS message_date,
                   cr.reply_message AS reply_text, cr.created_at AS reply_date, u.name AS responder_name
            FROM contact_messages cm
            LEFT JOIN contact_replies cr ON cm.id = cr.message_id
            LEFT JOIN users u ON cr.responder_id = u.id
            ORDER BY cm.created_at DESC';
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log('Database query error in manage_messages.php: ' . $e->getMessage());
    $_SESSION['error_message'] = 'Failed to load messages.';
    $messages = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Messages - EduTrack Admin</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="css/dashboard.css">
</head>
<body class="d-flex flex-column min-vh-100">

<?php require_once '../../includes/admin_navbar.php'; ?>

<main class="container-fluid py-4 flex-grow-1">
    <div class="card shadow-sm">
        <div class="card-body">

            <div class="d-flex justify-content-between mb-3">
                <a href="dashboard.php" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Dashboard
                </a>
            </div>

            <h1 class="mb-4 text-success fw-bold">Manage Messages</h1>

            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle">
                    <thead class="table-success text-center">
                        <tr>
                            <th scope="col">From</th>
                            <th scope="col">Email</th>
                            <th scope="col">Message</th>
                            <th scope="col">Reply</th>
                            <th scope="col">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if ($messages): ?>
                        <?php foreach ($messages as $msg): ?>
                            <tr>
                                <td><?= htmlspecialchars($msg['name']) ?></td>
                                <td><?= htmlspecialchars($msg['email']) ?></td>
                                <td>
                                    <?= nl2br(htmlspecialchars($msg['message'])) ?>
                                    <br><small class="text-muted"><?= htmlspecialchars($msg['message_date']) ?></small>
                                </td>
                                <td>
                                    <?php if (!empty($msg['reply_text'])): ?>
                                        <?= nl2br(htmlspecialchars($msg['reply_text'])) ?><br>
                                        <small class="text-muted">
                                            By: <?= htmlspecialchars($msg['responder_name'] ?? 'Admin') ?> 
                                            at <?= htmlspecialchars($msg['reply_date']) ?>
                                        </small>
                                    <?php else: ?>
                                        <em class="text-muted">No reply yet</em>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <button class="btn btn-outline-primary btn-sm view-btn" 
                                            data-id="<?= htmlspecialchars($msg['id']) ?>" 
                                            data-email="<?= htmlspecialchars($msg['email']) ?>" 
                                            data-name="<?= htmlspecialchars($msg['name']) ?>"
                                            aria-label="Reply to <?= htmlspecialchars($msg['name']) ?>">
                                        <i class="fas fa-reply"></i> Reply
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center text-muted">
                                <em>No messages found.</em>
                            </td>
                        </tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>

        </div>
    </div>
</main>

<!-- Bootstrap Modal -->
<div class="modal fade" id="replyModal" tabindex="-1" aria-labelledby="replyModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="replyModalLabel">
                    Reply to <span id="modal-name"></span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="reply-form">
                    <div class="mb-3">
                        <label for="reply_message" class="form-label">Your Reply:</label>
                        <textarea class="form-control" name="reply_message" id="reply_message" rows="5" placeholder="Type your reply..." required></textarea>
                    </div>
                    <input type="hidden" name="message_id" id="message_id">
                    <input type="hidden" name="recipient_email" id="recipient_email">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                    <div id="reply-status" class="mb-2" role="status" aria-live="polite"></div>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-paper-plane"></i> Send Reply
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Footer -->
<?php
$footer = __DIR__ . '/../../includes/footer.php';
if (file_exists($footer)) {
    require_once $footer;
} else {
    echo '<footer class="mt-auto py-4 bg-white border-top">
            <div class="container text-center text-muted">
                <p class="mb-0">&copy; ' . date('Y') . ' EduTrack. All rights reserved.</p>
            </div>
          </footer>';
}
?>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script <?= et_csp_attr('script') ?>>
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
    replyStatus.className = "text-primary mb-2";
    
    const formData = new FormData(replyForm);
    
    try {
        const response = await fetch('reply_message.php', { method: 'POST', body: formData });
        const result = await response.text();
        
        if (response.ok) {
            replyStatus.className = "text-success mb-2";
            replyStatus.innerHTML = result || "Reply sent successfully!";
            setTimeout(() => {
                replyModal.hide();
                location.reload();
            }, 1500);
        } else {
            replyStatus.className = "text-danger mb-2";
            replyStatus.innerHTML = result || "Error sending reply.";
        }
    } catch (err) {
        replyStatus.className = "text-danger mb-2";
        replyStatus.innerHTML = "Network error. Please try again.";
    }
});
</script>

</body>
</html>