<?php
session_start();
require_once 'config.php';

// Check if intern logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'intern') {
    header('Location: login.php');
    exit;
}

$intern_id = $_SESSION['user_id'];
$today = date('Y-m-d');

// Handle Mark Present - Creates PENDING request, NOT automatically present
if (isset($_POST['mark_present'])) {
    try {
        // Check if already submitted today
        $check = $pdo->prepare("SELECT id, supervisor_status FROM attendance WHERE intern_id = ? AND date = ?");
        $check->execute([$intern_id, $today]);
        $existing = $check->fetch(PDO::FETCH_ASSOC);
        
        if ($existing) {
            $error = 'You have already submitted attendance for today. Status: ' . ($existing['supervisor_status'] ?? 'Pending approval');
        } else {
            $check_in_time = date('H:i:s');
            
            // Insert with status = NULL (not present yet) and supervisor_status = 'pending'
            $stmt = $pdo->prepare("
                INSERT INTO attendance (
                    intern_id, 
                    date, 
                    check_in, 
                    status,
                    supervisor_status,
                    created_at
                ) VALUES (?, ?, ?, NULL, 'pending', NOW())
            ");
            $stmt->execute([$intern_id, $today, $check_in_time]);
            
            // Send notification to supervisor
            $supervisor_stmt = $pdo->prepare("
                SELECT supervisor_id FROM assignments 
                WHERE intern_id = ? AND status = 'active' 
                LIMIT 1
            ");
            $supervisor_stmt->execute([$intern_id]);
            $supervisor_id = $supervisor_stmt->fetchColumn();

            if ($supervisor_id) {
                try {
                    $title = "New Attendance Request";
                    $message = "Intern has requested attendance approval for " . date('M d, Y');
                    $notif_stmt = $pdo->prepare("
                        INSERT INTO notifications (user_id, title, message, `read`, created_at) 
                        VALUES (?, ?, ?, 0, NOW())
                    ");
                    $notif_stmt->execute([$supervisor_id, $title, $message]);
                } catch (PDOException $e) {
                    // Notifications table might not exist, continue anyway
                }
            }

            $success = 'Attendance request submitted! Waiting for supervisor approval. Notification sent to supervisor.';
        }
    } catch (PDOException $e) {
        $error = 'Error submitting attendance: ' . $e->getMessage();
    }
}

// Handle Check Out - Only allowed if supervisor approved
if (isset($_POST['check_out'])) {
    $stmt = $pdo->prepare("SELECT id, check_in, supervisor_status FROM attendance WHERE intern_id = ? AND date = ?");
    $stmt->execute([$intern_id, $today]);
    $attendance = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$attendance) {
        $error = 'No attendance record found for today!';
    } elseif ($attendance['supervisor_status'] !== 'approved') {
        $error = 'Cannot check out until supervisor approves your attendance!';
    } else {
        $check_out_time = date('H:i:s');
        $check_in_time = $attendance['check_in'];
        $total_hours = round((strtotime($check_out_time) - strtotime($check_in_time)) / 3600, 2);
        
        $stmt = $pdo->prepare("UPDATE attendance SET check_out = ?, total_hours = ? WHERE id = ?");
        $stmt->execute([$check_out_time, $total_hours, $attendance['id']]);
        $success = 'Checked out successfully. Total: ' . $total_hours . ' hours.';
    }
}

// Get today's attendance
$stmt = $pdo->prepare("SELECT * FROM attendance WHERE intern_id = ? AND date = ?");
$stmt->execute([$intern_id, $today]);
$today_attendance = $stmt->fetch(PDO::FETCH_ASSOC);

// Monthly stats - Only count APPROVED days as present
$current_month = date('Y-m');
$stmt = $pdo->prepare("
    SELECT 
        COUNT(*) as total_days,
        SUM(CASE WHEN supervisor_status = 'approved' THEN 1 ELSE 0 END) as days_present,
        SUM(CASE WHEN supervisor_status = 'rejected' THEN 1 ELSE 0 END) as days_absent,
        SUM(CASE WHEN supervisor_status = 'pending' OR supervisor_status IS NULL THEN 1 ELSE 0 END) as days_pending
    FROM attendance 
    WHERE intern_id = ? AND DATE_FORMAT(date, '%Y-%m') = ?
");
$stmt->execute([$intern_id, $current_month]);
$stats = $stmt->fetch(PDO::FETCH_ASSOC);

// Recent attendance
$stmt = $pdo->prepare("SELECT * FROM attendance WHERE intern_id = ? ORDER BY date DESC LIMIT 10");
$stmt->execute([$intern_id]);
$recent = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Attendance - CENADI</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --blue-primary: #1e3a8a; 
            --blue-light: #3b82f6;
            --green-success: #059669;
            --white: #ffffff;
            --gray-light: #f8fafc;
            --gray-medium: #64748b;
            --shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }
        * { box-sizing: border-box; }
        body { 
            font-family: 'Inter', sans-serif; 
            background: linear-gradient(135deg, var(--gray-light) 0%, #e2e8f0 100%);
            min-height: 100vh;
            color: #1e293b;
        }
        .main-container { max-width: 1200px; margin: 0 auto; padding: 2rem 1rem; }
        .page-header { 
            background: var(--white); 
            border-radius: 16px; 
            padding: 2rem; 
            box-shadow: var(--shadow); 
            margin-bottom: 2rem; 
            text-align: center;
        }
        .page-title { font-size: clamp(1.5rem, 4vw, 2.5rem); font-weight: 700; color: var(--blue-primary); margin-bottom: 0.5rem; }
        .status-card { 
            background: var(--white); 
            border-radius: 16px; 
            padding: 1.5rem; 
            box-shadow: var(--shadow); 
            border-left: 4px solid var(--blue-light); 
            transition: all 0.3s ease; 
        }
        .status-card:hover { transform: translateY(-4px); box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1); }
        .btn-present { 
            background: linear-gradient(135deg, var(--green-success), #10b981); 
            color: white; 
            border: none; 
            padding: 1rem 3rem; 
            font-size: 1.2rem; 
            font-weight: 600; 
            border-radius: 50px; 
            box-shadow: 0 8px 20px rgba(16,185,129,0.3);
        }
        .btn-present:hover { transform: translateY(-3px); box-shadow: 0 12px 30px rgba(16,185,129,0.4); color: white; }
        .btn-checkout { background: linear-gradient(135deg, var(--blue-light), #2563eb); color: white; border: none; padding: 0.8rem 2rem; font-weight: 600; border-radius: 50px; }
        .stat-card { background: #f8fafc; border-radius: 15px; padding: 1.5rem; text-align: center; border: 2px solid #e2e8f0; }
        .stat-number { font-size: 2.5rem; font-weight: 800; color: var(--blue-primary); }
        .stat-label { color: #64748b; font-weight: 500; }
    </style>
</head>
<body>
    <div class="main-container">
        <!-- Success/Error Message -->
        <?php if (isset($success)): ?>
            <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i>
                <?php echo $success; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        <?php if (isset($error)): ?>
            <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                <?php echo $error; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Header -->
        <header class="page-header shadow-lg">
            <i class="bi bi-calendar3-week-fill fs-1 text-primary mb-3 d-block"></i>
            <h1 class="page-title">Daily Attendance</h1>
            <p class="lead">Mark your presence and track attendance history</p>
            <div class="mt-4">
                <div class="h3 fw-bold text-primary mb-1"><?php echo date('l, F jS, Y'); ?></div>
                <div class="h2 fw-semibold" id="current-time"></div>
            </div>
        </header>

        <!-- Today's Action -->
        <div class="row g-4 mb-5">
            <?php if (!$today_attendance): ?>
                <!-- No request submitted yet -->
                <div class="col-12">
                    <div class="status-card text-center p-5">
                        <i class="bi bi-person-check fs-1 text-primary mb-4"></i>
                        <h3 class="mb-3 text-primary">Request Attendance Approval</h3>
                        <p class="lead text-muted mb-4">Submit your attendance request for supervisor approval</p>
                        <form method="POST">
                            <button type="submit" name="mark_present" class="btn-present">
                                <i class="bi bi-check-lg me-2"></i>
                                I am Present Today
                            </button>
                        </form>
                        <p class="text-muted mt-3 small">
                            <i class="bi bi-info-circle me-1"></i>
                            Your supervisor will approve or reject this request
                        </p>
                    </div>
                </div>
            <?php else: ?>
                <!-- Request submitted -->
                <div class="col-lg-8">
                    <div class="status-card">
                        <div class="row g-4">
                            <div class="col-md-4 text-center">
                                <i class="bi bi-clock-fill fs-1 text-primary mb-2"></i>
                                <h6 class="text-muted mb-1">Request Time</h6>
                                <h4><?php echo date('h:i A', strtotime($today_attendance['check_in'])); ?></h4>
                            </div>
                            <div class="col-md-4 text-center">
                                <i class="bi bi-clipboard-check-fill fs-1 mb-2 
                                    <?php echo $today_attendance['supervisor_status'] == 'approved' ? 'text-success' : ($today_attendance['supervisor_status'] == 'rejected' ? 'text-danger' : 'text-warning'); ?>"></i>
                                <h6 class="text-muted mb-1">Approval Status</h6>
                                <h5>
                                    <?php if ($today_attendance['supervisor_status'] == 'approved'): ?>
                                        <span class="badge bg-success px-3 py-2">
                                            <i class="bi bi-check-lg me-1"></i>APPROVED
                                        </span>
                                    <?php elseif ($today_attendance['supervisor_status'] == 'rejected'): ?>
                                        <span class="badge bg-danger px-3 py-2">
                                            <i class="bi bi-x-lg me-1"></i>REJECTED
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-warning text-dark px-3 py-2">
                                            <i class="bi bi-hourglass-split me-1"></i>PENDING
                                        </span>
                                    <?php endif; ?>
                                </h5>
                            </div>
                            <div class="col-md-4 text-center">
                                <?php if ($today_attendance['supervisor_status'] == 'approved'): ?>
                                    <?php if ($today_attendance['check_out']): ?>
                                        <i class="bi bi-check-circle-fill fs-1 text-success mb-2"></i>
                                        <h6 class="text-muted mb-1">Checked Out</h6>
                                        <h4><?php echo date('h:i A', strtotime($today_attendance['check_out'])); ?></h4>
                                        <small class="text-muted">Hours: <?php echo number_format($today_attendance['total_hours'], 2); ?>h</small>
                                    <?php else: ?>
                                        <i class="bi bi-box-arrow-right fs-1 text-primary mb-2"></i>
                                        <h6 class="text-muted mb-2">Check Out</h6>
                                        <form method="POST">
                                            <button type="submit" name="check_out" class="btn btn-checkout">
                                                <i class="bi bi-box-arrow-right me-1"></i>Check Out
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <i class="bi bi-lock-fill fs-1 text-muted mb-2"></i>
                                    <h6 class="text-muted mb-1">Check Out</h6>
                                    <small class="text-muted">Locked until approved</small>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="status-card h-100">
                        <h5 class="mb-4"><i class="bi bi-info-circle me-2"></i>Status Info</h5>
                        <?php if ($today_attendance['supervisor_status'] == 'pending' || !$today_attendance['supervisor_status']): ?>
                            <div class="alert alert-warning mb-0">
                                <i class="bi bi-hourglass-split me-2"></i>
                                <strong>Waiting for Approval</strong>
                                <p class="mb-0 mt-2 small">Your supervisor has been notified and will review your request soon.</p>
                            </div>
                        <?php elseif ($today_attendance['supervisor_status'] == 'approved'): ?>
                            <div class="alert alert-success mb-0">
                                <i class="bi bi-check-circle-fill me-2"></i>
                                <strong>Attendance Approved!</strong>
                                <p class="mb-0 mt-2 small">You are marked present for today. Don't forget to check out!</p>
                                <?php if ($today_attendance['notes']): ?>
                                    <hr>
                                    <small><strong>Note:</strong> <?php echo htmlspecialchars($today_attendance['notes']); ?></small>
                                <?php endif; ?>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-danger mb-0">
                                <i class="bi bi-x-circle-fill me-2"></i>
                                <strong>Request Rejected</strong>
                                <p class="mb-0 mt-2 small">Your attendance request was rejected. Please contact your supervisor.</p>
                                <?php if ($today_attendance['notes']): ?>
                                    <hr>
                                    <small><strong>Reason:</strong> <?php echo htmlspecialchars($today_attendance['notes']); ?></small>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Monthly Stats - Only count APPROVED as present -->
        <div class="status-card mb-4">
            <h5 class="mb-4"><i class="bi bi-calendar-month me-2 text-primary"></i>Monthly Overview (<?php echo date('F Y'); ?>)</h5>
            <div class="row g-3">
                <div class="col-md-3">
                    <div class="stat-card">
                        <div class="stat-number text-success"><?php echo $stats['days_present'] ?? 0; ?></div>
                        <div class="stat-label">Days Approved</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card">
                        <div class="stat-number text-warning"><?php echo $stats['days_pending'] ?? 0; ?></div>
                        <div class="stat-label">Pending Approval</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card">
                        <div class="stat-number text-danger"><?php echo $stats['days_absent'] ?? 0; ?></div>
                        <div class="stat-label">Rejected</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card">
                        <div class="stat-number text-primary"><?php echo round((($stats['days_present'] ?? 0) / max(1, $stats['total_days'] ?? 1)) * 100, 0); ?>%</div>
                        <div class="stat-label">Approval Rate</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent History -->
        <div class="status-card">
            <h5 class="mb-4"><i class="bi bi-clock-history me-2 text-primary"></i>Recent Activity (Last 10 Days)</h5>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th>Date</th>
                            <th>Request Time</th>
                            <th>Check Out</th>
                            <th>Hours</th>
                            <th>Supervisor Decision</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recent as $row): ?>
                        <tr>
                            <td>
                                <strong><?php echo date('M d, Y', strtotime($row['date'])); ?></strong><br>
                                <small class="text-muted"><?php echo date('l', strtotime($row['date'])); ?></small>
                            </td>
                            <td><?php echo $row['check_in'] ? date('h:i A', strtotime($row['check_in'])) : '-'; ?></td>
                            <td><?php echo $row['check_out'] ? date('h:i A', strtotime($row['check_out'])) : '-'; ?></td>
                            <td><strong><?php echo $row['total_hours'] ? number_format($row['total_hours'], 2) . 'h' : '0h'; ?></strong></td>
                            <td>
                                <?php if ($row['supervisor_status'] == 'approved'): ?>
                                    <span class="badge bg-success px-3 py-1">
                                        <i class="bi bi-check-lg me-1"></i>Approved
                                    </span>
                                <?php elseif ($row['supervisor_status'] == 'rejected'): ?>
                                    <span class="badge bg-danger px-3 py-1">
                                        <i class="bi bi-x-lg me-1"></i>Rejected
                                    </span>
                                <?php else: ?>
                                    <span class="badge bg-warning text-dark px-3 py-1">
                                        <i class="bi bi-hourglass-split me-1"></i>Pending
                                    </span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($recent)): ?>
                        <tr>
                            <td colspan="5" class="text-center py-5">
                                <i class="bi bi-calendar-x fs-1 text-muted mb-3 d-block"></i>
                                <div class="text-muted">No attendance history yet</div>
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function updateClock() {
            const now = new Date();
            document.getElementById('current-time').textContent = now.toLocaleTimeString('en-US', { 
                hour12: true, hour: 'numeric', minute: '2-digit', second: '2-digit'
            });
        }
        updateClock();
        setInterval(updateClock, 1000);

        setTimeout(() => {
            document.querySelectorAll('.alert').forEach(alert => {
                new bootstrap.Alert(alert).close();
            });
        }, 5000);
    </script>
</body>
</html>