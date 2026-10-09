<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

require_once 'config.php';

$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['role'];

$error = $_SESSION['chat_error'] ?? '';
$success = $_SESSION['chat_success'] ?? '';
unset($_SESSION['chat_error'], $_SESSION['chat_success']);

$chat_target = null;
$interns = [];
$messages = [];
$reports = [];


// Handle message send with error handling
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $message = trim($_POST['message'] ?? '');
    $target_id = intval($_POST['target_id'] ?? intval($_POST['intern_id'] ?? $_POST['supervisor_id'] ?? 0));
    
    $redirect_url = 'chat.php?' . ($_GET['intern_id'] ?? $_GET['supervisor_id'] ?? 'intern_id=' . $target_id);
    
    if (!$target_id) {
        $_SESSION['chat_error'] = 'No recipient selected.';
    } elseif (!$message) {
        $_SESSION['chat_error'] = 'Message cannot be empty.';
    } else {
        try {
            // Role-aware validation: check users OR intern table
            $valid_receiver = false;
            // Check users table
            $check_stmt = $pdo->prepare("SELECT id FROM users WHERE id = ?");
            $check_stmt->execute([$target_id]);
            if ($check_stmt->fetch()) {
                $valid_receiver = true;
            } else {
                // For supervisors, also check intern table
                if ($user_role === 'supervisor') {
                    $check_stmt = $pdo->prepare("SELECT intern_id FROM intern WHERE intern_id = ?");
                    $check_stmt->execute([$target_id]);
                    if ($check_stmt->fetch()) {
                        $valid_receiver = true;
                    }
                }
            }
            if (!$valid_receiver) {
                throw new PDOException("Invalid recipient ID: {$target_id} (not found in users or intern table)");
            }
            
    $stmt = $pdo->prepare("INSERT INTO messages (sender_id, receiver_id, message, is_read, created_at) VALUES (?, ?, ?, 0, NOW())");
    $stmt->execute([$user_id, $target_id, $message]);
            $_SESSION['chat_success'] = 'Message sent successfully!';

        } catch (PDOException $e) {
            $_SESSION['chat_error'] = 'Send failed: ' . $e->getMessage();
        }
    }
    header("Location: " . $redirect_url);
    exit();
}


// Get chat target - role aware
if (isset($_GET['chat_id'])) {
    $chat_id = intval($_GET['chat_id']);
    if ($user_role === 'supervisor') {
        // Load intern details via users.id = intern.id
        $stmt = $pdo->prepare("SELECT u.id as chat_id, i.Name FROM users u JOIN intern i ON u.id = i.id WHERE u.id = ?");
        $stmt->execute([$chat_id]);
    } else {
        // Load supervisor details
        $stmt = $pdo->prepare("SELECT u.id as chat_id, u.Name FROM users u WHERE u.id = ? AND u.role = 'supervisor'");
        $stmt->execute([$chat_id]);
    }
    $chat_target = $stmt->fetch();
    if ($chat_target) $chat_target['Name'] = $chat_target['Name'] ?? 'User';
}

// Load contacts
if ($user_role === 'supervisor') {
    // Use intern's users.id for consistent chat IDs
    $stmt = $pdo->prepare("SELECT DISTINCT u.id as chat_id, i.Name, COUNT(CASE WHEN m.is_read = 0 AND m.receiver_id = u.id THEN 1 END) as unread_count, m.message as last_message, m.created_at as last_time FROM intern i JOIN users u ON i.id = u.id JOIN assignments a ON u.id = a.intern_id JOIN (SELECT receiver_id, MAX(created_at) as max_time, message FROM messages WHERE receiver_id > 0 GROUP BY receiver_id) lm ON lm.receiver_id = u.id LEFT JOIN messages m ON m.receiver_id = u.id AND m.created_at = lm.max_time WHERE a.supervisor_id = ? AND a.status = 'active' GROUP BY u.id, i.Name ORDER BY lm.max_time DESC");
    $stmt->execute([$user_id]);
    $interns = $stmt->fetchAll(PDO::FETCH_ASSOC);
} elseif ($user_role === 'intern') {
    // Get assigned supervisor - already uses users.id
    $stmt = $pdo->prepare("SELECT u.id as chat_id, u.Name FROM users u JOIN assignments a ON u.id = a.supervisor_id JOIN intern i ON i.id = a.intern_id AND i.id = ? WHERE a.status = 'active' LIMIT 1");
    $stmt->execute([$user_id]);
    $interns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $chat_target = $interns[0] ?? null;
} elseif ($user_role === 'admin') {
    $interns = [['chat_id' => 0, 'Name' => 'SYSTEM BROADCAST']];
    $chat_target = $interns[0];
}

// Load messages - improved
if ($chat_target) {
    $target_id = $chat_target['chat_id'] ?? 0;
    $stmt = $pdo->prepare("SELECT m.*, COALESCE(i.Name, u.Name, CONCAT('User ', m.sender_id)) as sender_name FROM messages m LEFT JOIN intern i ON m.sender_id = i.intern_id LEFT JOIN users u ON m.sender_id = u.id WHERE (m.receiver_id = ? OR m.sender_id = ?) ORDER BY m.created_at ASC LIMIT 50");
    $stmt->execute([$target_id, $target_id]);
    $messages = $stmt->fetchAll();
    
    // Add system broadcasts
    if ($user_role !== 'admin') {
        $stmt = $pdo->prepare("SELECT m.*, 'System' as sender_name FROM messages m WHERE m.receiver_id = 0 ORDER BY m.created_at DESC LIMIT 10");
        $stmt->execute();
        $broadcasts = $stmt->fetchAll();
        $messages = array_merge($messages, $broadcasts);
        usort($messages, function($a, $b) { return strtotime($a['created_at']) - strtotime($b['created_at']); });
    }

}


// Load reports
if ($user_role === 'supervisor' && $chat_target) {
    $stmt = $pdo->prepare("SELECT * FROM intern_reports WHERE intern_id = ? ORDER BY submission_date DESC LIMIT 5");
    $stmt->execute([$chat_target['intern_id']]);
    $reports = $stmt->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CENADI Chat</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
:root { --chat-bg: #f8fafc; --msg-sent: #10b981; --msg-rec: #f3f4f6; }
        body { font-family: 'Inter', sans-serif; background: var(--chat-bg); }
        .chat-app { height: 100vh; display: flex; flex-direction: column; }
        .chat-header { background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white; padding: 1.5rem; }
        .chat-header h5 { margin: 0; }
        .chat-sidebar { background: white; border-right: 1px solid #e2e8f0; overflow-y: auto; }
        .chat-main { flex: 1; display: flex; flex-direction: column; }
        .messages { flex: 1; overflow-y: auto; padding: 1rem; background: var(--chat-bg); }
        .message { margin-bottom: 1rem; display: flex; }
        .message.sent { flex-direction: row-reverse; }
        .message-bubble { max-width: 70%; padding: 0.75rem 1rem; border-radius: 1.25rem; }
        .msg-sent { background: var(--msg-sent); color: white; }
        .msg-rec { background: var(--msg-rec); color: #374151; }
        .message-time { font-size: 0.75rem; opacity: 0.7; margin-top: 0.25rem; }
        .chat-input { border-top: 1px solid #e2e8f0; padding: 1rem; background: white; }
        .user-list a { padding: 0.75rem 1rem; border-radius: 0.5rem; text-decoration: none; color: #374151; transition: all 0.2s; display: flex; align-items: center; justify-content: space-between; }
        .user-list a.active, .user-list a:hover { background: #d1fae5; color: #065f46; }
        .report-item { border-left: 3px solid #10b981; }
    </style>
</head>
<body>
    <div class="chat-app">
        <div class="chat-header d-flex align-items-center justify-content-between">
            <div>
                <i class="bi bi-chat-dots fs-4 me-2"></i>
<h5 id="chat-title"><?php echo $chat_target ? htmlspecialchars($chat_target['Name']) : ($user_role === 'supervisor' ? 'Select Intern' : ($user_role === 'intern' ? 'Your Supervisor' : 'System Broadcast')); ?></h5>
            </div>
            <a href="supervisordashboard.php" class="btn btn-sm btn-light">
                <i class="bi bi-arrow-left"></i>
            </a>
        </div>
        
        <?php if ($error): ?>
        <div class="alert alert-danger alert-dismissible fade show position-fixed" style="top: 100px; right: 20px; z-index: 1050;" role="alert">
            <?php echo htmlspecialchars($error); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>
        
        <?php if ($success): ?>
        <div class="alert alert-success alert-dismissible fade show position-fixed" style="top: 100px; right: 20px; z-index: 1050;" role="alert">
            <?php echo htmlspecialchars($success); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <div class="d-flex flex-grow-1">
            <!-- Sidebar -->

            <div class="chat-sidebar p-0" style="width: 320px;">
                <div class="p-3 border-bottom">
    <h6 class="mb-3"><i class="bi bi-people"></i> <?php echo $user_role === 'supervisor' ? 'My Interns' : ($user_role === 'intern' ? 'My Supervisor' : 'System Broadcast'); ?> </h6>

                    <?php foreach ($interns as $intern): ?>
    <a href="?chat_id=<?php echo $intern['chat_id']; ?>" class="user-list d-block mb-1 <?php echo $chat_target && $chat_target['chat_id'] == $intern['chat_id'] ? 'active' : ($intern['unread_count'] > 0 ? 'border-start border-danger border-3' : ''); ?>">

                            <span class="d-flex flex-column pe-2">
                                <small class="fw-bold"><?php echo htmlspecialchars($intern['Name']); ?></small>
                                <?php if ($intern['last_message']): ?>
                                    <small class="text-muted small"><?php echo substr(htmlspecialchars($intern['last_message']), 0, 30) . '...'; ?></small>
                                <?php endif; ?>
                            </span>
                            <?php if ($intern['unread_count'] > 0): ?>
                                <span class="badge bg-danger"><?php echo $intern['unread_count']; ?></span>
                            <?php endif; ?>

                        </a>
                    <?php endforeach; ?>

                </div>
            </div>

            <!-- Main Chat -->
            <div class="flex-grow-1 d-flex flex-column">
                <div class="messages" id="messages">
                    <?php if (!$chat_target): ?>
                        <div class="text-center py-5 text-muted">
                            <i class="bi bi-chat-square-dots display-1 mb-3 opacity-50"></i>
                            <h5>Select an intern to chat</h5>
                        </div>
                    <?php else: ?>
                        <?php foreach (array_reverse($messages) as $msg): ?>
                            <div class="message <?php echo $msg['sender_id'] == $user_id ? 'sent' : 'received'; ?>">
                                <div class="message-bubble <?php echo $msg['sender_id'] == $user_id ? 'msg-sent' : 'msg-rec'; ?>">
                                    <?php echo nl2br(htmlspecialchars($msg['message'])); ?>
                                    <div class="message-time"><?php echo date('g:i a', strtotime($msg['created_at'])); ?></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <!-- Input -->
                <div class="chat-input">
                    <form method="POST" id="chatForm">
<input type="hidden" name="target_id" value="<?php echo $chat_target['chat_id'] ?? 0; ?>">
                        <div class="input-group">
    <textarea name="message" id="messageInput" class="form-control rounded-4" rows="1" placeholder="Type message (Enter to send)"></textarea>
                            <button type="button" id="sendBtn" class="btn btn-success rounded-4" onclick="sendMessage()">
                                <span class="send-icon"><i class="bi bi-send-fill"></i></span>
                                <span class="spinner-border spinner-border-sm send-spinner d-none" role="status"></span>
                            </button>
                        </div>
                    </form>
                </div>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Elements
        const messagesEl = document.getElementById('messages');
        const form = document.getElementById('chatForm');
        const messageInput = document.getElementById('messageInput');
        const sendBtn = document.getElementById('sendBtn');
        const sendIcon = document.querySelector('.send-icon');
        const sendSpinner = document.querySelector('.send-spinner');
        const reportsBtn = document.getElementById('reportsBtn');
        const reportsTab = document.getElementById('reportsTab');
        const chatMain = document.querySelector('.flex-grow-1.d-flex.flex-column');
        
        function scrollToBottom() {
            messagesEl.scrollTop = messagesEl.scrollHeight;
        }
        scrollToBottom();

        // Toggle reports
        function toggleReports() {
            reportsTab.classList.toggle('d-none');
            if (!reportsTab.classList.contains('d-none')) {
                reportsTab.scrollIntoView({ behavior: 'smooth' });
            }
        }

        function sendMessage() {
            const msg = messageInput.value.trim();
            if (!msg) {
                alert('Message cannot be empty!');
                messageInput.focus();
                return;
            }
            
            // Show loading
            sendBtn.disabled = true;
            sendIcon.classList.add('d-none');
            sendSpinner.classList.remove('d-none');
            
            // Optimistic (optional)
            const optimisticMsg = createMessageBubble(msg, true);
            messagesEl.appendChild(optimisticMsg);
            scrollToBottom();
            
            // Submit form
            form.submit();
        }


        function createMessageBubble(text, isSent, isTemp = false) {
            const div = document.createElement('div');
            div.className = `message ${isSent ? 'sent' : 'received'}`;
            div.innerHTML = `
                <div class="message-bubble ${isSent ? 'msg-sent' : 'msg-rec'} ${isTemp ? 'opacity-75' : ''}">
                    ${text.replace(/\n/g, '<br>')}
                    <div class="message-time">${isTemp ? 'Sending...' : new Date().toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})}</div>
                </div>
            `;
            return div;
        }

        // Events
        messageInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                sendMessage();
            }
        });

        let isTyping = false;
        messageInput.addEventListener('input', () => isTyping = true);
        messageInput.addEventListener('blur', () => setTimeout(() => isTyping = false, 2000));
        
        // Real-time poll for new messages (every 3s)
        setInterval(() => {
            if (window.location.search.includes('chat_id')) {
                location.reload();
            }
        }, 3000);
    </script>
</body>
</html>

