<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'supervisor') {
    header("Location: login.php");
    exit();
}

include 'db_connect.php';

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'] ?? 'Supervisor';

// No profile handling

// Get intern assignment count from assignments table
$count_result = $conn->query("SELECT COUNT(DISTINCT intern_id) as intern_count FROM assignments WHERE supervisor_id = $user_id AND status = 'active'")->fetch_assoc();
$total_interns_count = $count_result['intern_count'] ?? 0;

// Get approved/active interns
$approved_count_result = $conn->query("SELECT COUNT(DISTINCT a.intern_id) as approved_count FROM assignments a JOIN intern i ON a.intern_id = i.intern_id WHERE a.supervisor_id = $user_id AND a.status = 'active' AND (i.status IS NULL OR i.status = 'approved')")->fetch_assoc();
$approved_count = $approved_count_result['approved_count'] ?? 0;

// Fetch assigned interns list for table (from assignments)
$stmt = $conn->prepare("
    SELECT DISTINCT i.intern_id, i.Name as name, u.email, i.status
    FROM assignments a
    JOIN intern i ON a.intern_id = i.intern_id
    LEFT JOIN users u ON i.intern_id = u.id
    WHERE a.supervisor_id = ? AND a.status = 'active'
    ORDER BY i.Name
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$assigned_interns_result = $stmt->get_result();
$assigned_interns = $assigned_interns_result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Pending reports count
$pending_reports = $conn->query("SELECT COUNT(*) as pending FROM intern_reports WHERE supervisor_id = $user_id AND status = 'pending'")->fetch_assoc()['pending'] ?? 0;

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Supervisor Settings - CENADI Internship Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { background: #f8fafc; font-family: 'Segoe UI', sans-serif; }
        .container { max-width: 1000px; margin-top: 40px; }
        .card { border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); margin-bottom: 30px; }
        .header { background: linear-gradient(135deg, #2563eb, #1e40af); color: white; padding: 25px; border-radius: 16px 16px 0 0; text-align: center; }
        .form-control:focus { border-color: #2563eb; box-shadow: 0 0 0 0.2rem rgba(37,99,235,0.25); }
        .btn-primary { background: #2563eb; border: none; }
        .table th { border-top: none; font-weight: 600; color: #1e293b; }
        .intern-status { padding: 4px 8px; border-radius: 12px; font-size: 0.8rem; font-weight: 500; }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <div class="header">
                <h2><i class="bi bi-gear me-3"></i>Supervisor Settings</h2>
                <p class="mb-0 opacity-90"><?php echo htmlspecialchars($username); ?></p>
            </div>
            
            <div class="card-body p-4">
                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="card h-100 text-center border-0 shadow-sm bg-primary text-white">
                            <div class="card-body">
                                <i class="bi bi-people-fill display-4 opacity-75 mb-3"></i>
                                <h3 class="mb-1"><?php echo count($assigned_interns); ?></h3>
                                <p class="mb-0">Interns Assigned</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card h-100 text-center border-0 shadow-sm bg-success text-white">
                            <div class="card-body">
                                <i class="bi bi-check-circle display-4 opacity-75 mb-3"></i>
                                <h3 class="mb-1"><?php 
                                    $approved_count = 0;
                                    foreach ($assigned_interns as $intern) {
                                        if ($intern['status'] == 'approved') $approved_count++;
                                    }
                                    echo $approved_count;
                                ?></h3>
                                <p class="mb-0">Active Interns</p>
                            </div>
                        </div>
                    </div>
                <div class="col-md-4">
                        <div class="card h-100 text-center border-0 shadow-sm bg-info text-white">
                            <div class="card-body">
                                <i class="bi bi-clock-history display-4 opacity-75 mb-3"></i>
                                <h3 class="mb-1"><?php echo $pending_reports; ?></h3>
                                <p class="mb-0">Pending Reports</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Assigned Interns -->
        <div class="card">
            <div class="header">
                <h2><i class="bi bi-people me-3"></i>Assigned Interns (<?php echo count($assigned_interns); ?>)</h2>
            </div>
            <div class="card-body p-4">
                <?php if (empty($assigned_interns)): ?>
                    <div class="text-center py-5 text-muted">
                        <i class="bi bi-person-plus display-4 mb-4 opacity-50"></i>
                        <h5>No interns assigned</h5>
                        <p>Interns will appear here once assigned to you by admin.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Name</th>
                                    <th>Last Report</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($assigned_interns as $intern): ?>
                                    <tr>
                                        <td><strong><?php echo htmlspecialchars($intern['name']); ?></strong></td>
                                        <td>
                                            <?php echo isset($intern['report_status']) ? ucfirst($intern['report_status']) : 'No report'; ?>
                                        </td>
                                        <td>
                                            <a href="view_report.php?intern_id=<?php echo $intern['intern_id']; ?>" class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-file-earmark-text"></i> Reports
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Quick Links -->
        <div class="card">
            <div class="header">
                <h2><i class="bi bi-grid me-3"></i>Quick Links</h2>
            </div>
            <div class="card-body p-4">
                <div class="row g-3">
                    <div class="col-md-3">
                        <a href="view_report.php" class="btn btn-outline-primary w-100">
                            <i class="bi bi-file-earmark-texts me-2"></i>Review Reports
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="schedulemeeting.php" class="btn btn-outline-success w-100">
                            <i class="bi bi-calendar-event me-2"></i>Schedule Meeting
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="supervisordashboard.php" class="btn btn-outline-secondary w-100">
                            <i class="bi bi-house-door me-2"></i>Dashboard
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="logout.php" class="btn btn-outline-danger w-100">
                            <i class="bi bi-box-arrow-right me-2"></i>Logout
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

