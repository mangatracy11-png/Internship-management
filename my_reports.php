<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'intern') {
    header("Location: login.php");
    exit();
}

include 'db_connect.php';

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'] ?? 'Intern';

// Fetch intern's reports
$stmt = $conn->prepare("
    SELECT ir.*, 
           u.name as supervisor_name,
           (SELECT COUNT(*) FROM report_comments rc WHERE rc.report_id = ir.id) as comment_count
    FROM intern_reports ir 
LEFT JOIN users u ON ir.supervisor_id = u.id
    WHERE ir.intern_id = ?
    ORDER BY ir.submission_date DESC
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$reports = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Reports - CENADI Internship Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { background: #f8fafc; font-family: 'Segoe UI', sans-serif; }
        .container { max-width: 1000px; margin-top: 40px; }
        .card { border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); }
        .header { background: linear-gradient(135deg, #10b981, #059669); color: white; padding: 25px; border-radius: 16px 16px 0 0; }
        .status-badge { padding: 6px 12px; border-radius: 20px; font-size: 0.8rem; font-weight: 500; }
        .status-pending { background: #fef3c7; color: #92400e; }
        .status-reviewed { background: #dbeafe; color: #1e40af; }
        .status-feedback_sent { background: #ecfdf5; color: #065f46; }
        .report-item { border-left: 4px solid #e2e8f0; transition: all 0.3s; }
        .report-item:hover { border-left-color: #10b981; box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
        .comment-count { background: #f3f4f6; border-radius: 50%; width: 24px; height: 24px; display: flex; align-items: center; justify-content: center; font-size: 0.75rem; font-weight: 600; }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <div class="header text-center">
                <h2><i class="bi bi-file-earmark-texts me-3"></i>My Submitted Reports</h2>
                <p class="mb-0 opacity-90">Track your report status and supervisor feedback</p>
            </div>
            
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div><strong><?php echo count($reports); ?> reports submitted</strong></div>
                    <a href="submit_report.php" class="btn btn-success">
                        <i class="bi bi-plus-circle me-2"></i>New Report
                    </a>
                </div>
                
                <?php if (empty($reports)): ?>
                    <div class="text-center py-5 text-muted">
                        <i class="bi bi-file-earmark-text display-1 mb-4 opacity-25"></i>
                        <h5>No reports submitted yet</h5>
                        <p class="mb-4">Submit your first weekly progress report</p>
                        <a href="submit_report.php" class="btn btn-outline-success">Submit Report</a>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>

                                    <th>Week</th>
                                    <th>Title</th>
                                    <th>Submitted</th>
                                    <th>Feedback</th>
                                    <th>Action</th>

                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($reports as $report): ?>

                                <tr class="report-item">
                                    <td><strong>Week <?php echo $report['week_number']; ?></strong></td>
                                    <td><?php echo htmlspecialchars($report['title']); ?></td>
                                    <td>
                                        <small class="text-muted">
                                            <?php echo date('M j, Y', strtotime($report['submission_date'])); ?>
                                        </small>
                                    </td>
                                    <td>
                                        <?php if ($report['comment_count'] > 0): ?>
                                            <span class="comment-count"><?php echo $report['comment_count']; ?></span>
                                            <?php echo $report['comment_count']; ?> comment<?php echo $report['comment_count'] > 1 ? 's' : ''; ?>
                                        <?php else: ?>
                                            <span class="text-muted">No feedback</span>
                                        <?php endif; ?>
                                    </td>

                                    <td>
                                        <div class="d-flex gap-1">
                                            <?php if ($report['file_path']): ?>
                                                <a href="<?php echo htmlspecialchars($report['file_path']); ?>" 
                                                   class="btn btn-sm btn-outline-primary" target="_blank" title="Download">
                                                    <i class="bi bi-download"></i>
                                                </a>
                                            <?php endif; ?>
                                            <?php if ($report['comment_count'] > 0): ?>
                                                <a href="intern_view_report.php?id=<?php echo $report['id']; ?>" 
                                                   class="btn btn-sm btn-outline-success" title="View Feedback">
                                                    <i class="bi bi-chat-square-text"></i>
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
                
                <div class="text-center mt-4">
                    <a href="interndashboard.php" class="btn btn-outline-secondary">
                        <i class="bi bi-house-door me-2"></i>Back to Dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
