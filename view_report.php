<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'supervisor') {
    header("Location: login.php");
    exit();
}

include 'db_connect.php';

$supervisor_id = $_SESSION['user_id'];

// Handle comment submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_comment'])) {
    $report_id = (int)$_POST['report_id'];
    $comment = trim($_POST['comment']);
    
    if (!empty($comment)) {
        $stmt = $conn->prepare("INSERT INTO report_comments (report_id, supervisor_id, comment) VALUES (?, ?, ?)");
        $stmt->bind_param("iis", $report_id, $supervisor_id, $comment);
        if ($stmt->execute()) {
// Update report status and assign supervisor if not assigned
            $update_stmt = $conn->prepare("UPDATE intern_reports SET status = 'feedback_sent', supervisor_id = COALESCE(supervisor_id, ?) WHERE id = ?");
            $update_stmt->bind_param("ii", $supervisor_id, $report_id);
            $update_stmt->execute();
            $success = "Feedback added successfully!";
        } else {
            $error = "Failed to add comment.";
        }
        $stmt->close();
    } else {
        $error = "Comment cannot be empty.";
    }
}

// Fetch reports for this supervisor - FIXED: Simple query that works
$stmt = $conn->prepare("
    SELECT ir.*, u.name as intern_name, 
           (SELECT COUNT(*) FROM report_comments rc WHERE rc.report_id = ir.id) as comment_count
    FROM intern_reports ir 
    JOIN users u ON ir.intern_id = u.id
WHERE ir.supervisor_id = ? OR ir.supervisor_id IS NULL
    ORDER BY ir.submission_date DESC
");
$stmt->bind_param("i", $supervisor_id);
$stmt->execute();
$reports = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Intern Reports - Supervisor Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { background: #f8fafc; font-family: 'Segoe UI', sans-serif; }
        .container { max-width: 1200px; margin-top: 40px; }
        .card { border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); }
        .header { background: linear-gradient(135deg, #2563eb, #1e40af); color: white; padding: 25px; border-radius: 16px 16px 0 0; }
        .status-badge { padding: 6px 12px; border-radius: 20px; font-size: 0.8rem; font-weight: 500; }
        .status-pending { background: #fef3c7; color: #92400e; }
        .status-reviewed { background: #dbeafe; color: #1e40af; }
        .status-feedback_sent { background: #ecfdf5; color: #065f46; }
        .report-card { transition: transform 0.2s, box-shadow 0.2s; border-left: 5px solid #e2e8f0; }
        .report-card:hover { transform: translateY(-2px); box-shadow: 0 8px 25px rgba(0,0,0,0.15); border-left-color: #2563eb; }
        .comment-count { background: #f3f4f6; border-radius: 50%; width: 28px; height: 28px; display: flex; align-items: center; justify-content: center; font-weight: 600; }
        .modal-header { background: #2563eb; color: white; border-radius: 12px 12px 0 0 !important; }
        .modal-content { border-radius: 12px; border: none; }
        textarea.form-control { resize: vertical; min-height: 120px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <div class="header text-center">
                <h2><i class="bi bi-file-earmark-texts me-3"></i>Intern Reports</h2>
                <p class="mb-0 opacity-90">Review and provide feedback on intern submissions (<?php echo count($reports); ?> total)</p>
            </div>
            
            <div class="card-body p-4">
                <?php if (isset($success)): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?php echo $success; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php elseif (isset($error)): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?php echo $error; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <?php if (empty($reports)): ?>
                    <div class="text-center py-5 text-muted">
                        <i class="bi bi-file-earmark-x display-1 mb-4 opacity-25"></i>
                        <h5>No reports to review</h5>
                        <p class="mb-4">Check back later for new intern submissions or assign interns first</p>
                        <a href="supervisordashboard.php" class="btn btn-primary">Dashboard</a>
                    </div>
                <?php else: ?>
                    <div class="row g-4">
                        <?php foreach ($reports as $report): ?>
                        <div class="col-lg-6 col-xl-4">
                            <div class="card report-card h-100">
                                <div class="card-body p-4">
                                    <div class="d-flex justify-content-between align-items-start mb-3">
                                        <h6 class="mb-2"><?php echo htmlspecialchars($report['title']); ?></h6>
                                        <span class="status-badge status-<?php echo strtolower($report['status']); ?>">
                                            <?php echo ucfirst($report['status']); ?>
                                        </span>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <small class="text-muted">
                                            <i class="bi bi-person me-1"></i><?php echo htmlspecialchars($report['intern_name']); ?>
                                        </small>
                                    </div>
                                    
                                    <p class="text-muted small mb-3"><?php echo substr($report['content'], 0, 100); ?>...</p>
                                    
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <small class="text-muted">
                                            <i class="bi bi-calendar3 me-1"></i>
                                            <?php echo date('M j, Y', strtotime($report['submission_date'])); ?>
                                            | Week <?php echo $report['week_number']; ?>
                                        </small>
                                        <?php if ((int)$report['comment_count'] > 0): ?>
                                            <span class="comment-count"><?php echo $report['comment_count']; ?></span>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <div class="d-flex gap-2">
                                        <?php if ($report['file_path'] && file_exists($report['file_path'])): ?>
                                            <a href="<?php echo htmlspecialchars($report['file_path']); ?>" class="btn btn-sm btn-outline-primary" target="_blank">
                                                <i class="bi bi-download"></i> Download
                                            </a>
                                        <?php endif; ?>
                                        <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#commentModal<?php echo $report['id']; ?>">
                                            <i class="bi bi-chat-square-text me-1"></i>Add Feedback
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Comment Modal -->
                        <div class="modal fade" id="commentModal<?php echo $report['id']; ?>" tabindex="-1">
                            <div class="modal-dialog modal-lg">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Feedback for "<?php echo htmlspecialchars($report['title']); ?>"</h5>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                    </div>
                                    <form method="POST">
                                        <div class="modal-body">
                                            <input type="hidden" name="report_id" value="<?php echo $report['id']; ?>">
                                            <input type="hidden" name="add_comment" value="1">
                                            
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Intern: <?php echo htmlspecialchars($report['intern_name']); ?></label>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="form-label">Your Feedback *</label>
                                                <textarea name="comment" class="form-control" required 
                                                          placeholder="Provide constructive feedback, suggestions, or approval..."></textarea>
                                            </div>
                                            
                                            <?php if ($report['file_path']): ?>
                                            <div class="mb-3 p-3 bg-light rounded">
                                                <label class="form-label fw-semibold mb-2 d-block">Attached Report:</label>
                                                <a href="<?php echo htmlspecialchars($report['file_path']); ?>" class="btn btn-sm btn-outline-primary" target="_blank">
                                                    <i class="bi bi-file-earmark-down"></i> <?php echo basename($report['file_path']); ?>
                                                </a>
                                            </div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                            <button type="submit" class="btn btn-success">
                                                <i class="bi bi-check-circle me-2"></i>Save Feedback
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                
                <div class="text-center mt-5">
                    <a href="supervisordashboard.php" class="btn btn-outline-primary">
                        <i class="bi bi-house-door me-2"></i>Back to Dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
