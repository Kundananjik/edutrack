<?php
// ============================================
// ADMIN - CONTACT MESSAGES
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

require_login();
require_role(['admin']);

$search = $_GET['search'] ?? '';
$searchSQL = '';
$params = [];

if ($search) {
    $searchSQL = 'WHERE name LIKE :search OR email LIKE :search OR subject LIKE :search';
    $params[':search'] = "%$search%";
}

$stmt = $pdo->prepare("SELECT * FROM contact_messages $searchSQL ORDER BY created_at DESC");
$stmt->execute($params);
$messages = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Messages - EduTrack Admin</title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- EduTrack Custom CSS -->
    <link rel="stylesheet" href="css/dashboard.css">
</head>
<body class="d-flex flex-column min-vh-100">

<?php require_once '../../includes/admin_navbar.php'; ?>

<!-- Main container -->
<main class="container dashboard-container mt-5 pt-4 flex-grow-1">
    <div class="card shadow-sm rounded-4">
        <div class="card-body">
            <div class="d-flex justify-content-between mb-3">
                <a href="dashboard.php" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Dashboard
                </a>
            </div>
            
            <h1 class="mb-4 text-success">Contact Messages</h1>

            <!-- Search form -->
            <form method="GET" action="" class="row g-2 mb-4">
                <div class="col-md-8">
                    <input type="text" name="search" class="form-control" placeholder="Search messages..." value="<?= htmlspecialchars($search) ?>" aria-label="Search messages">
                </div>
                <div class="col-md-4">
                    <button type="submit" class="btn btn-success w-100">
                        <i class="fas fa-search"></i> Search
                    </button>
                </div>
            </form>

            <!-- Messages Table -->
            <div class="table-responsive">
                <table class="table table-striped table-bordered align-middle">
                    <thead class="table-success">
                        <tr>
                            <th scope="col">Name</th>
                            <th scope="col">Email</th>
                            <th scope="col">Subject</th>
                            <th scope="col">Message</th>
                            <th scope="col">Sent At</th>
                            <th scope="col">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($messages): ?>
                            <?php foreach ($messages as $msg): ?>
                                <tr>
                                    <td><?= htmlspecialchars($msg['name']) ?></td>
                                    <td><?= htmlspecialchars($msg['email']) ?></td>
                                    <td><?= htmlspecialchars($msg['subject']) ?></td>
                                    <td><?= htmlspecialchars(substr($msg['message'], 0, 50)) ?>...</td>
                                    <td><?= htmlspecialchars($msg['created_at']) ?></td>
                                    <td>
                                        <button class="btn btn-primary btn-sm view-btn" 
                                                data-id="<?= htmlspecialchars($msg['id']) ?>" 
                                                data-name="<?= htmlspecialchars($msg['name']) ?>" 
                                                data-email="<?= htmlspecialchars($msg['email']) ?>" 
                                                data-subject="<?= htmlspecialchars($msg['subject']) ?>" 
                                                data-message="<?= htmlspecialchars($msg['message']) ?>"
                                                aria-label="View message from <?= htmlspecialchars($msg['name']) ?>">
                                            <i class="fas fa-eye"></i> View
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center text-muted">No messages found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>

<!-- Modal -->
<div class="modal fade" id="messageModal" tabindex="-1" aria-labelledby="messageModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-success">
            <div class="modal-header bg-success text-light">
                <h5 class="modal-title" id="messageModalLabel"></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <h6 id="modal-name" class="text-muted"></h6>
                <p id="modal-message"></p>

                <hr>
                <h5>Reply to Message</h5>
                <form id="reply-form">
                    <div class="mb-3">
                        <label for="reply_message" class="form-label visually-hidden">Reply message</label>
                        <textarea name="reply_message" id="reply_message" rows="5" class="form-control" placeholder="Type your reply here..." required></textarea>
                        <input type="hidden" name="message_id" id="message_id">
                        <input type="hidden" name="recipient_email" id="recipient_email">
                    </div>
                    <button type="submit" class="btn btn-success">Send Reply</button>
                </form>
                <div id="reply-status" class="mt-2" role="status" aria-live="polite"></div>
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

<!-- Bootstrap 5 JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script <?= et_csp_attr('script') ?>>
const modal = new bootstrap.Modal(document.getElementById('messageModal'));
const modalTitle = document.getElementById('messageModalLabel');
const modalName = document.getElementById('modal-name');
const modalMessage = document.getElementById('modal-message');
const replyForm = document.getElementById('reply-form');
const replyStatus = document.getElementById('reply-status');
const recipientEmailInput = document.getElementById('recipient_email');
const messageIdInput = document.getElementById('message_id');

document.querySelectorAll('.view-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        modalTitle.textContent = btn.dataset.subject;
        modalName.textContent = 'From: ' + btn.dataset.name + ' (' + btn.dataset.email + ')';
        modalMessage.textContent = btn.dataset.message;

        recipientEmailInput.value = btn.dataset.email;
        messageIdInput.value = btn.dataset.id;

        replyStatus.textContent = '';
        replyForm.reset();

        modal.show();
    });
});

replyForm.addEventListener('submit', async (e) => {
    e.preventDefault();
    replyStatus.textContent = "Sending...";
    replyStatus.className = "text-primary mt-2";

    try {
        const formData = new FormData(replyForm);
        const response = await fetch('reply_message.php', { method: 'POST', body: formData });
        const result = await response.text();

        if (response.ok) {
            replyStatus.className = "text-success mt-2";
            replyStatus.textContent = "Reply sent successfully!";
            replyForm.reset();
        } else {
            replyStatus.className = "text-danger mt-2";
            replyStatus.textContent = "Error: " + result;
        }

        setTimeout(() => { replyStatus.textContent = ''; }, 3000);

    } catch (error) {
        replyStatus.className = "text-danger mt-2";
        replyStatus.textContent = "Network error: " + error.message;
    }
});
</script>

</body>
</html>