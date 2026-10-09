<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'intern') {
    header("Location: login.php");
    exit();
}

include 'db_connect.php';

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'] ?? 'Intern';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: my_reports.php");
    exit();
}

$report_id = (int)$_GET['id'];

// Fetch report
$stmt = $conn->prepare("
    SELECT ir.*, u.name as supervisor_name
    FROM intern_reports ir
    LEFT JOIN users u ON ir.supervisor_id = u.id
    WHERE ir.id = ? AND ir.intern_id = ?
");
$stmt->bind_param("ii", $report_id, $user_id);
$stmt->execute();
$report = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$report) {
    header("Location: my_reports.php");
    exit();
}

// Fetch comments
$stmt = $conn->prepare("
    SELECT rc.comment, rc.created_at, u.name as supervisor_name
    FROM report_comments rc
    JOIN users u ON rc.supervisor_id = u.id
    WHERE rc.report_id = ?
    ORDER BY rc.created_at DESC
");
$stmt->bind_param("i", $report_id);
$stmt->execute();
$comments = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Report Feedback - CENADI Internship Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { background: #f8fafc; font-family: 'Segoe UI', sans-serif; }
        .container { max-width: 900px; margin-top: 40px; }
        .card { border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); }
        .header { background: linear-gradient(135deg, #10b981, #059669); color: white; padding: 25px; border-radius: 16px 16px 0 0; text-align: center; }
        .comment-item { background: #f8fafc; border-left: 4px solid #10b981; padding: 16px; border-radius: 8px; margin-bottom: 12px; }
        .comment-meta { font-size: 0.85rem; color: #64748b; margin-bottom: 8px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <div class="header">
                <h2><i class="bi bi-file-earmark-text me-3"></i><?php echo htmlspecialchars($report['title']); ?></h2>
                <p class="mb-0 opacity-90">Week <?php echo $report['week_number']; ?> - <?php echo date('M j, Y', strtotime($report['submission_date'])); ?></p>
            </div>
            
            <div class="card-body p-4">
                <?php if ($report['content']): ?>
                    <div class="mb-4">
                        <label class="form-label fw-semibold">Your Summary:</label>
                        <p class="p-3 bg-light rounded"><?php echo nl2br(htmlspecialchars($report['content'])); ?></p>
                    </div>
                <?php endif; ?>

                <?php if ($report['file_path'] && file_exists($report['file_path'])): ?>
                    <div class="mb-4">
                        <a href="<?php echo htmlspecialchars($report['file_path']); ?>" class="btn btn-outline-primary" target="_blank">
                            <i class="bi bi-download me-2"></i>Download Report File
                        </a>
                    </div>
                <?php endif; ?>

                <?php if (empty($comments)): ?>
                    <div class="text-center py-5 text-muted">
                        <i class="bi bi-chat-square-text display-4 mb-4 opacity-50"></i>
                        <h5>No feedback yet</h5>
                        <p>Your supervisor will provide feedback soon.</p>
                    </div>
                <?php else: ?>
                    <h5 class="mb-3"><i class="bi bi-chat-square-text me-2"></i>Supervisor Feedback (<?php echo count($comments); ?>)</h5>
                    <?php foreach ($comments as $comment): ?>
                        <div class="comment-item">
                            <div class="comment-meta">
                                <strong><?php echo htmlspecialchars($comment['supervisor_name']); ?></strong>
                                <small class="ms-2"><?php echo date('M j, Y g:i A', strtotime($comment['created_at'])); ?></small>
                            </div>
                            <div><?php echo nl2br(htmlspecialchars($comment['comment'])); ?></div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>

                <div class="d-flex gap-3 mt-4">
                    <a href="my_reports.php" class="btn btn-outline-secondary">
                        <i class="bi bi-list-ul me-2"></i>Back to My Reports
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

