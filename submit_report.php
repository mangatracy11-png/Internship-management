<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'intern') {
    header("Location: login.php");
    exit();
}

include 'db_connect.php';

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'] ?? 'Intern';

// Check if intern is approved
$app_sql = "SELECT status FROM applications WHERE intern_id = ? ORDER BY application_date DESC LIMIT 1";
$app_stmt = $conn->prepare($app_sql);
$app_stmt->bind_param("i", $user_id);
$app_stmt->execute();
$app_result = $app_stmt->get_result();
$is_approved = false;
if ($app_result->num_rows > 0) {
    $app_data = $app_result->fetch_assoc();
    $is_approved = ($app_data['status'] == 'approved');
}
$app_stmt->close();

if (!$is_approved) {
    $error = "You must be approved to submit reports.";
}

$success = '';
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!$is_approved) {
        $error = "You must be approved to submit reports.";
    } else {
        $title = trim($_POST['title']);
        $week_number = (int)$_POST['week_number'];
        $content = trim($_POST['content']);
        
        $file_path = null;
        if (isset($_FILES['report_file']) && $_FILES['report_file']['error'] == 0) {
            $allowed_types = ['application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/pdf'];
            if (in_array($_FILES['report_file']['type'], $allowed_types) && $_FILES['report_file']['size'] <= 10*1024*1024) {
                $upload_dir = 'uploads/reports/';
                if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
                $file_extension = pathinfo($_FILES['report_file']['name'], PATHINFO_EXTENSION);
                $file_name = $user_id . '_' . time() . '_' . $week_number . '.' . $file_extension;
                $file_path = $upload_dir . $file_name;
                if (move_uploaded_file($_FILES['report_file']['tmp_name'], $file_path)) {
                    // File uploaded successfully
                } else {
                    $error = "File upload failed.";
                }
            } else {
                $error = "Invalid file type or size too large (max 10MB, DOCX/PDF only).";
            }
        }
        
        if (empty($title) || empty($content) || $week_number < 1 || $week_number > 52) {
            $error = "Please fill all fields correctly.";
        } elseif (!isset($error)) {
            // Find supervisor
            
            // Insert report
            $supervisor_id = NULL;
            $stmt = $conn->prepare("INSERT INTO intern_reports (intern_id, supervisor_id, title, content, file_path, week_number) VALUES (?, ?, ?, ?, ?, ?)");
            if ($stmt->execute([$user_id, $supervisor_id, $title, $content, $file_path, $week_number])) {
                $success = "Report submitted successfully! Your supervisor will review it soon.";
            } else {
                $error = "Failed to submit report.";
                if ($file_path && file_exists($file_path)) unlink($file_path);
            }
            $stmt->close();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Submit Report - CENADI Internship Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
             min-height: 100vh; 
             font-family: 'Segoe UI', sans-serif; }
        .container { 
            max-width: 800px; 
            margin-top: 60px; }
        .card { 
            border-radius: 20px;
             box-shadow: 0 20px 40px rgba(0,0,0,0.1); 
             overflow: hidden; }
        .header { 
            background: linear-gradient(135deg, #2563eb, #1e40af);
             color: white; 
             padding: 30px;
              text-align: center; }
        .form-control, .form-select { 
            border-radius: 12px; 
            border: 2px solid #e2e8f0; 
            padding: 12px 16px; }
        .form-control:focus, .form-select:focus { 
            border-color: #2563eb; 
            box-shadow: 0 0 0 0.2rem rgba(37,99,235,0.25); }
        .btn-primary { background: #10b981;
         border: none; border-radius: 12px;
          padding: 12px 30px; 
          font-weight: 600; }
        .btn-primary:hover { background: #059669;
         transform: translateY(-2px); }
        .alert { border-radius: 12px; 
        border: none; }
        .back-btn { background: #6b7280; 
        border: none; 
        border-radius: 12px; color: white; 
        padding: 10px 20px; text-decoration: none; }
        .status-badge { 
            padding: 4px 12px;
             border-radius: 20px; 
             font-size: 0.8rem;
              font-weight: 500; }
        .status-approved { background: #d1fae5; color: #065f46; }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <div class="header">
                <h2><i class="bi bi-file-earmark-text-fill me-3"></i>Submit Weekly Report</h2>
                <p class="mb-0 opacity-75">Intern: <strong><?php echo htmlspecialchars($username); ?></strong></p>
                <?php if (!$is_approved): ?>
                    <span class="status-badge status-approved mt-2">Application Pending Approval</span>
                <?php endif; ?>
            </div>
            
            <div class="card-body p-5">
                <?php if (isset($success)): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                    <div class="text-center">
                        <a href="my_reports.php" class="btn btn-outline-success me-3">View My Reports</a>
                        <a href="interndashboard.php" class="btn btn-outline-secondary">Back to Dashboard</a>
                    </div>
                <?php elseif (isset($error)): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <?php if (!$success && $is_approved): ?>
                <form method="POST" enctype="multipart/form-data">
                    <div class="row">
                        <div class="col-md-6 mb-4">
                            <label class="form-label fw-semibold">Report Title *</label>
                            <input type="text" name="title" class="form-control" required placeholder="Week X Progress Report" maxlength="255">
                        </div>
                        <div class="col-md-6 mb-4">
                            <label class="form-label fw-semibold">Week Number *</label>
                            <input type="number" name="week_number" class="form-control" required min="1" max="52" placeholder="1">
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <label class="form-label fw-semibold">Report Content Summary *</label>
                        <textarea name="content" class="form-control" required rows="6" placeholder="Brief summary of your activities this week..."></textarea>
                    </div>
                    
                    <div class="mb-4">
                        <label class="form-label fw-semibold">Attach Report File (Optional)</label>
                        <input type="file" name="report_file" class="form-control" accept=".docx,.pdf">
                        <div class="form-text">DOCX or PDF only, max 10MB</div>
                    </div>
                    
                    <div class="d-flex gap-3">
                        <button type="submit" class="btn btn-primary flex-grow-1">
                            <i class="bi bi-upload me-2"></i>Submit Report
                        </button>
                        <a href="interndashboard.php" class="back-btn">
                            <i class="bi bi-arrow-left"></i> Cancel
                        </a>
                    </div>
                </form>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
