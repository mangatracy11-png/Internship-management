<?php
session_start();
require_once 'config.php';

// Check if supervisor logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'supervisor') {
    header('Location: login.php');
    exit;
}

$supervisor_id = $_SESSION['user_id'];

// Handle approval/rejection - FIXED to update status field
if (isset($_POST['action'])) {
    $attendance_id = intval($_POST['attendance_id']);
    $action = $_POST['action'];
    
    if ($action == 'approve') {
        // APPROVE: Set supervisor_status='approved' AND status='present'
        $stmt = $pdo->prepare("
            UPDATE attendance 
            SET supervisor_status = 'approved',
                status = 'present',
                supervisor_id = ?,
                supervisor_approved_at = NOW()
            WHERE id = ?
        ");
        $stmt->execute([$supervisor_id, $attendance_id]);
        $_SESSION['success_message'] = 'Attendance approved! Intern is now marked PRESENT.';
        
    } else {
        // REJECT: Set supervisor_status='rejected' AND status='absent'
        $stmt = $pdo->prepare("
            UPDATE attendance 
            SET supervisor_status = 'rejected',
                status = 'absent',
                supervisor_id = ?,
                supervisor_approved_at = NOW()
            WHERE id = ?
        ");
        $stmt->execute([$supervisor_id, $attendance_id]);
        $_SESSION['success_message'] = 'Attendance rejected. Intern marked as ABSENT.';
    }
    
    header("Location: trackattendance.php");
    exit;
}

// Fetch pending attendance requests
$stmt = $pdo->prepare("
    SELECT a.*, i.Name as intern_name, i.department 
    FROM attendance a
    JOIN intern i ON a.intern_id = i.intern_id
    WHERE (a.supervisor_status IS NULL OR a.supervisor_status = 'pending')
    AND EXISTS (
        SELECT 1 FROM assignments assign 
        WHERE assign.intern_id = a.intern_id 
        AND assign.supervisor_id = ? 
        AND assign.status = 'active'
    )
    ORDER BY a.created_at DESC, a.date DESC
");
$stmt->execute([$supervisor_id]);
$pending_attendance = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Monthly stats
$stmt = $pdo->prepare("
    SELECT 
        COUNT(*) as total_requests,
        SUM(CASE WHEN supervisor_status = 'approved' THEN 1 ELSE 0 END) as approved,
        SUM(CASE WHEN supervisor_status = 'rejected' THEN 1 ELSE 0 END) as rejected,
        SUM(CASE WHEN supervisor_status IS NULL OR supervisor_status = 'pending' THEN 1 ELSE 0 END) as pending
    FROM attendance a
    WHERE MONTH(a.date) = MONTH(CURDATE()) AND YEAR(a.date) = YEAR(CURDATE())
    AND EXISTS (
        SELECT 1 FROM assignments assign 
        WHERE assign.intern_id = a.intern_id 
        AND assign.supervisor_id = ? 
        AND assign.status = 'active'
    )
");
$stmt->execute([$supervisor_id]);
$stats = $stmt->fetch(PDO::FETCH_ASSOC);

// Summary table (supervisor scoped)
$stmt = $pdo->prepare("
    SELECT 
        i.Name,
        i.intern_id,
        COUNT(DISTINCT a.id) as total_days,
        SUM(CASE WHEN a.supervisor_status = 'approved' AND a.status = 'present' THEN 1 ELSE 0 END) as days_present,
        SUM(CASE WHEN a.supervisor_status = 'rejected' OR a.status = 'absent' THEN 1 ELSE 0 END) as days_absent,
        SUM(CASE WHEN a.supervisor_status = 'pending' OR a.supervisor_status IS NULL THEN 1 ELSE 0 END) as days_pending,
        COALESCE(SUM(a.total_hours), 0) as total_hours
    FROM intern i 
    JOIN assignments assign ON i.intern_id = assign.intern_id AND assign.supervisor_id = ? AND assign.status = 'active'
    LEFT JOIN attendance a ON i.intern_id = a.intern_id
    GROUP BY i.intern_id, i.Name
    ORDER BY days_present DESC
    LIMIT 20
");
$stmt->execute([$supervisor_id]);
$summary = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Track Attendance - CENADI</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
  <style>
    body { background-color: #ffffff; }
    .avatar-xs { width: 40px; height: 40px; }
    .avatar-title { width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; font-weight: 700; background-color: #198754; color: #ffffff; }
    .custom-header { background-color: #198754 !important; color: #ffffff !important; }
    .custom-badge { background-color: #198754 !important; color: #ffffff !important; }
    .text-theme { color: #198754 !important; }
    .btn-reject { background-color: #ffffff; border: 1px solid #198754; color: #198754; }
    .btn-reject:hover { background-color: #e8f5e9; }
    .table-theme thead { background-color: #e9f7ee; color: #198754; }
  </style>
</head>
<body> 
  <div class="container-fluid py-4">
    
    <!-- Success Message -->
    <?php if (isset($_SESSION['success_message'])): ?>
      <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
        <i class="bi bi-check-circle-fill me-2"></i>
        <strong><?php echo $_SESSION['success_message']; ?></strong>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
      <?php unset($_SESSION['success_message']); ?>
    <?php endif; ?>

    <div class="row">
      <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
          <h1 class="h3 mb-0">
            <i class="bi bi-calendar-check2 text-theme me-2"></i>
            Track Attendance - Approve/Reject Requests
          </h1>
          <a href="supervisordashboard.php" class="btn btn-outline-success">
            <i class="bi bi-arrow-left me-2"></i> Back to Dashboard
          </a>
        </div>
      </div>
    </div>

    <!-- Stats Cards -->
    <div class="row g-3 mb-4">
      <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
          <div class="card-body text-center">
            <i class="bi bi-check-circle-fill fs-1 text-success mb-2"></i>
            <h3 class="mb-1"><?php echo $stats['approved'] ?? 0; ?></h3>
            <small class="text-success fw-semibold">Approved</small>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
          <div class="card-body text-center">
            <i class="bi bi-x-circle-fill fs-1 text-danger mb-2"></i>
            <h3 class="mb-1"><?php echo $stats['rejected'] ?? 0; ?></h3>
            <small class="text-danger fw-semibold">Rejected</small>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
          <div class="card-body text-center">
            <i class="bi bi-hourglass-split fs-1 text-warning mb-2"></i>
            <h3 class="mb-1"><?php echo $stats['pending'] ?? 0; ?></h3>
            <small class="text-warning fw-semibold">Pending</small>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
          <div class="card-body text-center">
            <i class="bi bi-people-fill fs-1 text-primary mb-2"></i>
            <h3 class="mb-1"><?php echo ($stats['total_requests'] ?? 0); ?></h3>
            <small class="text-primary fw-semibold">Total Requests</small>
          </div>
        </div>
      </div>
    </div>

    <!-- Pending Requests Table -->
    <div class="card shadow-lg border-0 mb-4">
      <div class="card-header custom-header">
        <h5 class="mb-0">
          <i class="bi bi-hourglass me-2"></i>Pending Attendance Requests - Awaiting Your Decision
          <?php if (($stats['pending'] ?? 0) > 0): ?>
            <span class="badge bg-danger ms-2"><?php echo $stats['pending']; ?> NEW</span>
          <?php endif; ?>
        </h5>
      </div>
      <div class="card-body p-0">
        <?php if (empty($pending_attendance)): ?>
          <div class="text-center py-5">
            <i class="bi bi-check-all display-1 text-success opacity-75 mb-3"></i>
            <h4 class="text-muted mb-2">No Pending Requests</h4>
            <p class="text-muted lead">All attendance requests have been reviewed</p>
            <small class="text-muted d-block mt-2">When interns submit attendance requests, they will appear here for your approval.</small>
          </div>
        <?php else: ?>
          <div class="alert alert-info m-3">
            <i class="bi bi-info-circle me-2"></i>
            <strong>Important:</strong> Interns are NOT marked present until you approve their request. Click "Approve" to mark them present, or "Reject" to mark them absent.
          </div>
          <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
              <thead class="table-dark">
                <tr>
                  <th width="25%">Intern</th>
                  <th width="15%">Date</th>
                  <th width="15%">Request Time</th>
                  <th width="15%">Current Status</th>
                  <th width="30%">Your Decision</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($pending_attendance as $row): ?>
                <tr>
                  <td>
                    <div class="d-flex align-items-center">
                      <div class="flex-shrink-0 me-3">
                        <div class="avatar-xs">
                          <div class="avatar-title bg-light text-primary rounded-circle fs-6">
                            <?php echo strtoupper(substr($row['intern_name'], 0, 1)); ?>
                          </div>
                        </div>
                      </div>
                      <div class="flex-grow-1">
                        <h6 class="mb-1"><?php echo htmlspecialchars($row['intern_name']); ?></h6>
                        <small class="text-muted"><?php echo htmlspecialchars($row['department'] ?? 'N/A'); ?></small>
                      </div>
                    </div>
                  </td>
                  <td>
                    <span class="fw-semibold"><?php echo date('M d, Y', strtotime($row['date'])); ?></span><br>
                    <small class="text-muted"><?php echo date('l', strtotime($row['date'])); ?></small>
                  </td>
                  <td>
                    <span class="badge bg-info text-white px-2 py-1">
                      <?php echo date('h:i A', strtotime($row['check_in'])); ?>
                    </span><br>
                    <small class="text-muted"><?php echo date('g:i A', strtotime($row['created_at'] ?? $row['check_in'])); ?></small>
                  </td>
                  <td>
                    <span class="badge custom-badge px-3 py-2">
                      <i class="bi bi-hourglass-split me-1"></i>
                      AWAITING APPROVAL
                    </span>
                  </td>
                  <td>
                    <div class="btn-group" role="group">
                      <form method="POST" class="d-inline me-1">
                        <input type="hidden" name="attendance_id" value="<?php echo $row['id']; ?>">
                        <button type="submit" name="action" value="approve" class="btn btn-sm btn-success" 
                                onclick="return confirm('✓ APPROVE this attendance?\n\nThis will mark the intern as PRESENT for <?php echo date('M d, Y', strtotime($row['date'])); ?>.')">
                          <i class="bi bi-check-lg me-1"></i> Approve (Mark Present)
                        </button>
                      </form>
                      <form method="POST" class="d-inline">
                        <input type="hidden" name="attendance_id" value="<?php echo $row['id']; ?>">
                        <button type="submit" name="action" value="reject" class="btn btn-sm btn-danger" 
                                onclick="return confirm('✗ REJECT this attendance?\n\nThis will mark the intern as ABSENT for <?php echo date('M d, Y', strtotime($row['date'])); ?>.')">
                          <i class="bi bi-x-lg me-1"></i> Reject (Mark Absent)
                        </button>
                      </form>
                    </div>
                  </td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php endif; ?>
      </div>
    </div>

    <!-- Summary Table -->
    <div class="card shadow-lg border-0">
      <div class="card-header custom-header">
        <h5 class="mb-0"><i class="bi bi-bar-chart me-2"></i>Attendance Summary</h5>
      </div>
      <div class="card-body p-0">
        <?php if (empty($summary)): ?>
          <div class="text-center py-5">
            <i class="bi bi-people display-1 text-muted opacity-50 mb-3"></i>
            <h5 class="text-muted">No interns assigned yet</h5>
            <p class="text-muted">Assign interns to start tracking their attendance</p>
          </div>
        <?php else: ?>
          <div class="table-responsive">
            <table class="table table-hover mb-0">
              <thead class="table-light">
                <tr>
                  <th>Intern</th>
                  <th>Total Days</th>
                  <th>Approved (Present)</th>
                  <th>Rejected (Absent)</th>
                  <th>Pending</th>
                  <th>Total Hours</th>
                  <th>Attendance %</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($summary as $row): ?>
                <tr>
                  <td><strong><?php echo htmlspecialchars($row['Name']); ?></strong></td>
                  <td><span class="badge bg-secondary"><?php echo $row['total_days']; ?></span></td>
                  <td><span class="badge bg-success"><?php echo $row['days_present']; ?></span></td>
                  <td><span class="badge bg-danger"><?php echo $row['days_absent']; ?></span></td>
                  <td><span class="badge bg-warning text-dark"><?php echo $row['days_pending'] ?? 0; ?></span></td>
                  <td><strong><?php echo number_format($row['total_hours'], 1); ?>h</strong></td>
                  <td>
                    <div class="progress" style="height: 20px;">
                      <?php 
                      $percentage = $row['total_days'] > 0 ? round(($row['days_present'] / $row['total_days']) * 100) : 0;
                      ?>
                      <div class="progress-bar bg-success" style="width: <?php echo $percentage; ?>%">
                        <?php echo $percentage; ?>%
                      </div>
                    </div>
                  </td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    // Auto-dismiss alerts
    setTimeout(() => {
      document.querySelectorAll('.alert-success').forEach(alert => {
        bootstrap.Alert.getOrCreateInstance(alert).close();
      });
    }, 5000);
    
    // Auto-refresh every 60 seconds to check for new requests
    setTimeout(() => location.reload(), 60000);
  </script>
</body>
</html>