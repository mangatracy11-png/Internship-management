<?php
// monitor.php - All-in-one supervisor monitoring system

include 'db_connect.php';
session_start();

// Check if user is supervisor

$supervisor_id = $_SESSION['user_id'];
$message = '';

// Handle quick status update
if (isset($_POST['update_status'])) {
    $intern_id = intval($_POST['intern_id']);
    $new_status = $_POST['status'];
    
    $sql = "UPDATE intern_profiles SET status = ? WHERE user_id = ? AND supervisor_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sii", $new_status, $intern_id, $supervisor_id);
    
    if ($stmt->execute()) {
        $message = '<div class="alert alert-success">Status updated successfully!</div>';
    }
}

// Handle add note
if (isset($_POST['add_note'])) {
    $intern_id = intval($_POST['intern_id']);
    $note_type = $_POST['note_type'];
    $note_title = $_POST['note_title'];
    $note_content = $_POST['note_content'];
    $is_private = isset($_POST['is_private']) ? 1 : 0;
    
    // Check if table exists first
    $table_check = $conn->query("SHOW TABLES LIKE 'supervisor_notes'");
    if ($table_check->num_rows > 0) {
        $sql = "INSERT INTO supervisor_notes (intern_id, supervisor_id, note_date, note_type, note_title, note_content, is_private) 
                VALUES (?, ?, CURDATE(), ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iisssi", $intern_id, $supervisor_id, $note_type, $note_title, $note_content, $is_private);
        
        if ($stmt->execute()) {
            $message = '<div class="alert alert-success">Note added successfully!</div>';
        }
    }
}

// Get all interns assigned to this supervisor
$interns_sql = "SELECT 
    u.id,
    u.name,
    u.email,
    ip.full_name,
    ip.phone,
    ip.school,
    ip.department,
    ip.status,
    ip.internship_start_date,
    ip.internship_end_date
FROM users u
LEFT JOIN intern_profiles ip ON u.id = ip.user_id
WHERE u.role = 'intern' AND ip.supervisor_id = ?
ORDER BY u.name";

$stmt = $conn->prepare($interns_sql);
$stmt->bind_param("i", $supervisor_id);
$stmt->execute();
$interns = $stmt->get_result();

// Get attendance stats for each intern (will be fetched in loop)
// Get report stats for each intern (will be fetched in loop)

// Get statistics
$stats = [
    'total_interns' => 0,
    'active_interns' => 0,
    'pending_reports' => 0,
    'overdue_tasks' => 0
];

// Count total interns
$count_sql = "SELECT COUNT(*) as total FROM intern_profiles WHERE supervisor_id = ?";
$stmt = $conn->prepare($count_sql);
$stmt->bind_param("i", $supervisor_id);
$stmt->execute();
$result = $stmt->get_result();
if ($row = $result->fetch_assoc()) {
    $stats['total_interns'] = $row['total'];
}

// Count active interns
$active_sql = "SELECT COUNT(*) as active FROM intern_profiles WHERE supervisor_id = ? AND status = 'Active'";
$stmt = $conn->prepare($active_sql);
$stmt->bind_param("i", $supervisor_id);
$stmt->execute();
$result = $stmt->get_result();
if ($row = $result->fetch_assoc()) {
    $stats['active_interns'] = $row['active'];
}

// Count pending reports (if table exists)
$table_check = $conn->query("SHOW TABLES LIKE 'intern_reports'");
if ($table_check->num_rows > 0) {
    $reports_sql = "SELECT COUNT(*) as pending FROM intern_reports WHERE supervisor_id = ? AND status = 'pending'";
    $stmt = $conn->prepare($reports_sql);
    $stmt->bind_param("i", $supervisor_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $stats['pending_reports'] = $row['pending'];
    }
}

// Count overdue tasks (if table exists)
$table_check = $conn->query("SHOW TABLES LIKE 'intern_tasks'");
if ($table_check->num_rows > 0) {
    $tasks_sql = "SELECT COUNT(*) as overdue FROM intern_tasks WHERE supervisor_id = ? AND status = 'overdue'";
    $stmt = $conn->prepare($tasks_sql);
    $stmt->bind_param("i", $supervisor_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $stats['overdue_tasks'] = $row['overdue'];
    }
}

// Function to get intern stats
function getInternStats($conn, $intern_id) {
    $stats = [
        'total_days' => 0,
        'days_present' => 0,
        'days_late' => 0,
        'days_absent' => 0,
        'total_reports' => 0,
        'pending_reports' => 0,
        'total_tasks' => 0,
        'completed_tasks' => 0,
        'avg_performance' => null
    ];
    
    // Attendance
    $table_check = $conn->query("SHOW TABLES LIKE 'attendance'");
    if ($table_check->num_rows > 0) {
        $att_sql = "SELECT 
            COUNT(*) as total,
            SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) as present,
            SUM(CASE WHEN status = 'late' THEN 1 ELSE 0 END) as late,
            SUM(CASE WHEN status = 'absent' THEN 1 ELSE 0 END) as absent
            FROM attendance WHERE intern_id = ?";
        $stmt = $conn->prepare($att_sql);
        $stmt->bind_param("i", $intern_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            $stats['total_days'] = $row['total'];
            $stats['days_present'] = $row['present'];
            $stats['days_late'] = $row['late'];
            $stats['days_absent'] = $row['absent'];
        }
    }
    
    // Reports
    $table_check = $conn->query("SHOW TABLES LIKE 'intern_reports'");
    if ($table_check->num_rows > 0) {
        $rep_sql = "SELECT COUNT(*) as total, 
                    SUM(CASE WHEN review_status = 'pending' THEN 1 ELSE 0 END) as pending
                    FROM intern_reports WHERE intern_id = ?";
        $stmt = $conn->prepare($rep_sql);
        $stmt->bind_param("i", $intern_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            $stats['total_reports'] = $row['total'];
            $stats['pending_reports'] = $row['pending'];
        }
    }
    
    // Tasks
    $table_check = $conn->query("SHOW TABLES LIKE 'intern_tasks'");
    if ($table_check->num_rows > 0) {
        $task_sql = "SELECT COUNT(*) as total,
                     SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed
                     FROM intern_tasks WHERE intern_id = ?";
        $stmt = $conn->prepare($task_sql);
        $stmt->bind_param("i", $intern_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            $stats['total_tasks'] = $row['total'];
            $stats['completed_tasks'] = $row['completed'];
        }
    }
    
    // Performance
    $table_check = $conn->query("SHOW TABLES LIKE 'intern_performance'");
    if ($table_check->num_rows > 0) {
        $perf_sql = "SELECT AVG(overall_performance) as avg_perf FROM intern_performance WHERE intern_id = ?";
        $stmt = $conn->prepare($perf_sql);
        $stmt->bind_param("i", $intern_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            $stats['avg_performance'] = $row['avg_perf'];
        }
    }
    
    return $stats;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Monitor Interns - Supervisor Portal</title>
    
    <!-- External CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
            padding: 30px 20px;
        }
        .page-title {
            color: white;
            text-align: center;
            margin-bottom: 30px;
            font-size: 2.5rem;
            font-weight: bold;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.2);
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .stat-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            text-align: center;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }
        .stat-card .icon {
            font-size: 2.5rem;
            margin-bottom: 10px;
        }
        .stat-card h3 {
            font-size: 2.5rem;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .stat-total .icon { color: #007bff; }
        .stat-active .icon { color: #28a745; }
        .stat-reports .icon { color: #ffc107; }
        .stat-tasks .icon { color: #dc3545; }
        
        .card {
            border: none;
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            margin-bottom: 25px;
            background: white;
        }
        .card-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            border-radius: 20px 20px 0 0;
        }
        .intern-card {
            background: white;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 3px 15px rgba(0,0,0,0.1);
        }
        .intern-name {
            font-size: 1.4rem;
            font-weight: bold;
            color: #333;
        }
        .status-badge {
            padding: 6px 16px;
            border-radius: 20px;
            font-weight: bold;
            font-size: 0.85rem;
        }
        .status-active { background: #d4edda; color: #155724; }
        .status-inactive { background: #f8d7da; color: #721c24; }
        .metrics-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
            margin: 15px 0;
        }
        .metric-item {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 10px;
            text-align: center;
        }
        .metric-value {
            font-size: 1.8rem;
            font-weight: bold;
            color: #667eea;
        }
        .metric-label {
            font-size: 0.85rem;
            color: #6c757d;
        }
        .btn-action {
            padding: 8px 20px;
            border-radius: 20px;
            border: none;
            font-weight: 600;
            margin: 5px;
            cursor: pointer;
            display: inline-block;
            text-decoration: none;
        }
        .btn-view {
            background: #007bff;
            color: white;
        }
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #6c757d;
        }
        .empty-state i {
            font-size: 4rem;
            color: #dee2e6;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container" style="max-width: 1400px;">
        <!-- Page Title -->
        <h1 class="page-title">
            <i class="fas fa-chart-line"></i> Monitor My Interns
        </h1>
        
        <!-- Messages -->
        <?php echo $message; ?>
        
        <!-- Statistics -->
        <div class="stats-grid">
            <div class="stat-card stat-total">
                <div class="icon"><i class="fas fa-users"></i></div>
                <h3><?php echo $stats['total_interns']; ?></h3>
                <p>Total Interns</p>
            </div>
            <div class="stat-card stat-active">
                <div class="icon"><i class="fas fa-user-check"></i></div>
                <h3><?php echo $stats['active_interns']; ?></h3>
                <p>Active Interns</p>
            </div>
            <div class="stat-card stat-reports">
                <div class="icon"><i class="fas fa-file-alt"></i></div>
                <h3><?php echo $stats['pending_reports']; ?></h3>
                <p>Pending Reports</p>
            </div>
            <div class="stat-card stat-tasks">
                <div class="icon"><i class="fas fa-exclamation-triangle"></i></div>
                <h3><?php echo $stats['overdue_tasks']; ?></h3>
                <p>Overdue Tasks</p>
            </div>
        </div>
        
        <!-- Interns List -->
        <div class="card">
            <div class="card-header">
                <h5><i class="fas fa-users-cog"></i> My Interns - Monitoring Dashboard</h5>
            </div>
            <div class="card-body">
                <?php if ($interns->num_rows > 0): ?>
                    <?php while ($intern = $interns->fetch_assoc()): 
                        $intern_stats = getInternStats($conn, $intern['id']);
                    ?>
                        <div class="intern-card">
                            <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 15px;">
                                <div>
                                    <div class="intern-name">
                                        <i class="fas fa-user-graduate"></i>
                                        <?php echo htmlspecialchars($intern['full_name'] ?? $intern['name']); ?>
                                    </div>
                                    <div style="color: #6c757d; font-size: 0.9rem;">
                                        <i class="fas fa-envelope"></i>
                                        <?php echo htmlspecialchars($intern['email']); ?>
                                    </div>
                                </div>
                                <span class="status-badge status-<?php echo strtolower($intern['status'] ?? 'active'); ?>">
                                    <?php echo strtoupper($intern['status'] ?? 'ACTIVE'); ?>
                                </span>
                            </div>
                            
                            <?php if ($intern['school'] || $intern['department'] || $intern['phone']): ?>
                            <div style="margin-bottom: 15px;">
                                <?php if ($intern['school']): ?>
                                    <span style="background: #e3f2fd; padding: 5px 12px; border-radius: 15px; margin-right: 10px; font-size: 0.85rem;">
                                        <i class="fas fa-university"></i> <?php echo htmlspecialchars($intern['school']); ?>
                                    </span>
                                <?php endif; ?>
                                <?php if ($intern['department']): ?>
                                    <span style="background: #e8f5e9; padding: 5px 12px; border-radius: 15px; margin-right: 10px; font-size: 0.85rem;">
                                        <i class="fas fa-building"></i> <?php echo htmlspecialchars($intern['department']); ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                            <?php endif; ?>
                            
                            <!-- Metrics -->
                            <div class="metrics-grid">
                                <div class="metric-item">
                                    <div class="metric-value"><?php echo $intern_stats['days_present']; ?>/<?php echo $intern_stats['total_days']; ?></div>
                                    <div class="metric-label"><i class="fas fa-calendar-check"></i> Attendance</div>
                                </div>
                                <div class="metric-item">
                                    <div class="metric-value"><?php echo $intern_stats['total_reports']; ?></div>
                                    <div class="metric-label"><i class="fas fa-file-alt"></i> Reports</div>
                                </div>
                                <div class="metric-item">
                                    <div class="metric-value"><?php echo $intern_stats['completed_tasks']; ?>/<?php echo $intern_stats['total_tasks']; ?></div>
                                    <div class="metric-label"><i class="fas fa-tasks"></i> Tasks</div>
                                </div>
                                <?php if ($intern_stats['avg_performance']): ?>
                                <div class="metric-item">
                                    <div class="metric-value"><?php echo number_format($intern_stats['avg_performance'], 1); ?>/5</div>
                                    <div class="metric-label"><i class="fas fa-star"></i> Performance</div>
                                </div>
                                <?php endif; ?>
                            </div>
                            
                            <!-- Action Buttons -->
                            <div style="margin-top: 15px;">
                                <a href="intern_profile_view.php?id=<?php echo $intern['id']; ?>" class="btn-action btn-view">
                                    <i class="fas fa-eye"></i> View Profile
                                </a>
                                <a href="supervisor_reports.php?intern_id=<?php echo $intern['id']; ?>" class="btn-action btn-view">
                                    <i class="fas fa-file-alt"></i> View Reports
                                </a>
                                <a href="attendance_view.php?intern_id=<?php echo $intern['id']; ?>" class="btn-action btn-view">
                                    <i class="fas fa-calendar-check"></i> Attendance
                                </a>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-users-slash"></i>
                        <h4>No Interns Assigned</h4>
                        <p>You don't have any interns assigned to you yet.</p>
                        <p>Contact the admin to get interns assigned to your supervision.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Back Button -->
        <div class="text-center mb-4">
            <a href="supervisordashboard.php" class="btn btn-secondary btn-lg">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php $conn->close(); ?>