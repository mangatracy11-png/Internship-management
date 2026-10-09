
<?php
session_start();
require_once 'config.php';

// Admin attendance approval
if (isset($_POST['admin_action'])) {
    $attendance_id = $_POST['attendance_id'];
    $action = $_POST['admin_action'];
    $status = $action == 'approve' ? 'approved' : 'rejected';
    
    $stmt = $pdo->prepare("UPDATE attendance SET supervisor_status = ?, admin_approved = 1, admin_approved_at = NOW() WHERE id = ?");
    $stmt->execute([$status, $attendance_id]);
    $success_message = "Attendance " . ucfirst($status) . " successfully!";
    header("Location: AdminDashboard.php?success=" . $status);
    exit;
}

// Get pending attendance for admin
$stmt = $pdo->prepare("
    SELECT a.*, i.Name as intern_name, i.department, s.name as supervisor_name 
    FROM attendance a
    JOIN intern i ON a.intern_id = i.intern_id
    LEFT JOIN assignments assign ON a.intern_id = assign.intern_id
    LEFT JOIN supervisors s ON assign.supervisor_id = s.id
    WHERE (a.supervisor_status IS NULL OR a.supervisor_status = 'pending') 
    ORDER BY a.date DESC, a.check_in DESC 
    LIMIT 50
");
$stmt->execute();
$pending_attendance = $stmt->fetchAll(PDO::FETCH_ASSOC);


// Get stats with error handling
try {
    $total_interns = $pdo->query("SELECT COUNT(*) FROM intern")->fetchColumn() ?: 0;
    $total_supervisors = $pdo->query("SELECT COUNT(*) FROM supervisors")->fetchColumn() ?: 0;
    $total_assignments = $pdo->query("SELECT COUNT(*) FROM assignments WHERE status = 'active'")->fetchColumn() ?: 0;
    $total_reports = $pdo->query("SELECT COUNT(*) FROM intern_reports")->fetchColumn() ?: 0;
    
    // Recent interns with row number starting from 1 (auto-increment display)
    $recent_interns = $pdo->query("SELECT intern_id, Name, school, department FROM intern ORDER BY intern_id ASC LIMIT 10")->fetchAll(PDO::FETCH_ASSOC);
    
    // Logs with try-catch (table may not exist)
    $logs = [];
    try {
        $logs = $pdo->query("SELECT action, table_name, new_value, created_at FROM audit_logs ORDER BY id DESC LIMIT 10")->fetchAll();
    } catch (PDOException $e) {
        // audit_logs table doesn't exist yet
        $logs = [];
    }
    
    // Accurate counts from intern/applications
    $applied = $pdo->query("SELECT COUNT(*) FROM intern")->fetchColumn() ?: 0;
    $approved = $pdo->query("SELECT COUNT(DISTINCT a.intern_id) FROM applications a WHERE a.status = 'approved'")->fetchColumn() ?: 0;
    $pending = $pdo->query("SELECT COUNT(*) FROM intern i LEFT JOIN applications a ON i.intern_id = a.intern_id WHERE a.status != 'approved' OR a.intern_id IS NULL")->fetchColumn() ?: 0;

} catch (PDOException $e) {
    // Fallback static values if DB error
    $total_interns = $total_supervisors = $total_assignments = $total_reports = 0;
    $recent_interns = $logs = [];
    $applied = $approved = $pending = 0;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Dashboard — CENADI</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <style>
    :root {
        --sw: 260px; --p: #2563eb; --sb: #0f172a; --sh: #1e293b;
        --g: #10b981; --a: #f59e0b; --r: #ef4444; --v: #8b5cf6;
        --bg: #f1f5f9; --card: #fff; --tm: #1e293b; --mu: #64748b; --bd: #e2e8f0;
    }
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body { font-family: 'Inter','Segoe UI',sans-serif; background: var(--bg); color: var(--tm); min-height: 100vh; }

    /* ── SIDEBAR ── */
    .sidebar { position: fixed; top: 0; left: 0; height: 100vh; width: var(--sw); background: var(--sb); display: flex; flex-direction: column; z-index: 100; overflow-y: auto; }
    .sb-brand { padding: 22px 24px 18px; border-bottom: 1px solid #1e293b; display: flex; align-items: center; gap: 12px; }
    .b-icon { width: 36px; height: 36px; background: var(--p); border-radius: 8px; display: flex; align-items: center; justify-content: center; color: #fff; font-size: 1rem; flex-shrink: 0; }
    .b-text { color: #f8fafc; font-size: .9rem; font-weight: 700; line-height: 1.2; }
    .b-text span { display: block; font-size: .68rem; font-weight: 400; color: #94a3b8; letter-spacing: .6px; text-transform: uppercase; }
    .sb-lbl { padding: 20px 24px 6px; font-size: .65rem; font-weight: 600; letter-spacing: 1.2px; text-transform: uppercase; color: #475569; }
    .nli { display: flex; align-items: center; gap: 12px; padding: 10px 20px; margin: 2px 12px; border-radius: 8px; color: #94a3b8; text-decoration: none; font-size: .875rem; font-weight: 500; transition: background .2s, color .2s; }
    .nli i { font-size: 1rem; width: 20px; text-align: center; flex-shrink: 0; }
    .nli:hover { background: var(--sh); color: #f8fafc; }
    .nli.active { background: var(--p); color: #fff; }
    .sb-foot { margin-top: auto; padding: 16px 12px; border-top: 1px solid #1e293b; }

    /* ── TOPBAR ── */
    .topbar { position: fixed; top: 0; left: var(--sw); right: 0; height: 64px; background: #fff; border-bottom: 1px solid var(--bd); display: flex; align-items: center; justify-content: space-between; padding: 0 28px; z-index: 90; box-shadow: 0 1px 3px rgba(0,0,0,.06); }
    .tb-title { font-size: 1rem; font-weight: 600; }
    .tb-title span { color: var(--mu); font-weight: 400; font-size: .875rem; }
    .tb-actions { display: flex; align-items: center; gap: 12px; }
    .tb-btn { width: 36px; height: 36px; border-radius: 8px; border: 1px solid var(--bd); background: #fff; display: flex; align-items: center; justify-content: center; color: var(--mu); cursor: pointer; position: relative; transition: background .2s; }
    .tb-btn:hover { background: var(--bg); }
    .bdot { position: absolute; top: 5px; right: 5px; width: 8px; height: 8px; background: var(--r); border-radius: 50%; border: 2px solid #fff; }
    .ava { width: 36px; height: 36px; border-radius: 50%; background: linear-gradient(135deg,#2563eb,#8b5cf6); display: flex; align-items: center; justify-content: center; color: #fff; font-size: .8rem; font-weight: 700; cursor: pointer; }
    .uname { font-size: .85rem; font-weight: 600; text-align: right; }
    .urole { font-size: .72rem; color: var(--mu); text-align: right; }

    /* ── MAIN ── */
    .main { margin-left: var(--sw); padding: 88px 28px 40px; min-height: 100vh; }
    .ph { font-size: 1.4rem; font-weight: 700; }
    .ps { font-size: .875rem; color: var(--mu); margin-top: 2px; }

    /* ── STAT CARDS ── */
    .stat-card { background: var(--card); border-radius: 12px; padding: 22px; border: 1px solid var(--bd); display: flex; align-items: center; gap: 18px; box-shadow: 0 1px 3px rgba(0,0,0,.05); transition: transform .2s, box-shadow .2s; height: 100%; }
    .stat-card:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(0,0,0,.08); }
    .si { width: 52px; height: 52px; border-radius: 14px; display: flex; align-items: center; justify-content: center; font-size: 1.4rem; flex-shrink: 0; }
    .sv { font-size: 2rem; font-weight: 800; line-height: 1; }
    .sl { font-size: .8rem; color: var(--mu); margin-top: 4px; }
    .sch { font-size: .75rem; font-weight: 500; margin-top: 5px; }
    .sch.up { color: var(--g); } .sch.nt { color: var(--mu); }

    /* ── SECTION CARD ── */
    .scard { background: var(--card); border-radius: 12px; border: 1px solid var(--bd); box-shadow: 0 1px 3px rgba(0,0,0,.05); overflow: hidden; margin-bottom: 24px; }
    .scard-head { padding: 16px 20px; border-bottom: 1px solid var(--bd); display: flex; align-items: center; justify-content: space-between; }
    .scard-head h6 { font-size: .9rem; font-weight: 700; margin: 0; display: flex; align-items: center; gap: 8px; }
    .scard-head h6 i { font-size: 1rem; }
    .va { font-size: .78rem; color: var(--p); text-decoration: none; font-weight: 500; }

    /* ── REGISTRATION ROW ── */
    .reg-item { display: flex; align-items: center; gap: 14px; padding: 13px 20px; border-bottom: 1px solid var(--bd); transition: background .15s; }
    .reg-item:last-child { border-bottom: none; }
    .reg-item:hover { background: #f8fafc; }
    .reg-av { width: 38px; height: 38px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: .82rem; font-weight: 700; color: #fff; flex-shrink: 0; }
    .reg-name { font-size: .85rem; font-weight: 600; }
    .reg-role { font-size: .75rem; color: var(--mu); margin-top: 1px; }
    .reg-time { font-size: .72rem; color: var(--mu); white-space: nowrap; margin-left: auto; background: var(--bg); padding: 3px 10px; border-radius: 20px; border: 1px solid var(--bd); }

    /* ── LOG ITEM ── */
    .log-item { display: flex; align-items: flex-start; gap: 14px; padding: 14px 20px; border-bottom: 1px solid var(--bd); transition: background .15s; }
    .log-item:last-child { border-bottom: none; }
    .log-item:hover { background: #f8fafc; }
    .log-dot { width: 34px; height: 34px; border-radius: 9px; display: flex; align-items: center; justify-content: center; font-size: .85rem; flex-shrink: 0; }
    .log-title { font-size: .83rem; font-weight: 600; }
    .log-body { font-size: .78rem; color: var(--mu); margin-top: 2px; }
    .log-time { font-size: .7rem; color: var(--mu); white-space: nowrap; margin-top: 2px; }

    /* ── QUICK ACTIONS ── */
    .qa-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(160px, 1fr)); gap: 14px; padding: 20px; }
    .qa-btn { display: flex; flex-direction: column; align-items: center; gap: 10px; padding: 20px 16px; border-radius: 12px; border: 1px solid var(--bd); background: #f8fafc; text-decoration: none; color: var(--tm); font-size: .82rem; font-weight: 600; transition: transform .2s, box-shadow .2s, background .2s; cursor: pointer; }
    .qa-btn:hover { transform: translateY(-2px); box-shadow: 0 4px 14px rgba(0,0,0,.08); background: #fff; color: var(--tm); }
    .qa-icon { width: 44px; height: 44px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; }

    /* ── COLOR HELPERS ── */
    .b-blue   { background: #eff6ff; color: #2563eb; }
    .b-green  { background: #ecfdf5; color: #10b981; }
    .b-amb    { background: #fffbeb; color: #f59e0b; }
    .b-red    { background: #fef2f2; color: #ef4444; }
    .b-vio    { background: #f5f3ff; color: #8b5cf6; }
    .b-slate  { background: #f8fafc; color: #475569; }

    ::-webkit-scrollbar { width: 6px; }
    ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 3px; }
  </style>
</head>
<body>

<!-- ══════════ SIDEBAR ══════════ -->
<aside class="sidebar">
  <div class="sb-brand">
    <div class="b-icon"><i class="bi bi-grid-fill"></i></div>
    <div class="b-text">CENADI<span>ADMIN</span></div>
  </div>

  <div class="sb-lbl">Main</div>
  <a href="#" class="nli active"><i class="bi bi-house-door-fill"></i> Dashboard</a>
  <a href="list_of_interns.php" class="nli"><i class="bi bi-people-fill"></i> Manage Interns</a>
  <a href="allsupervisors.php" class="nli"><i class="bi bi-person-badge-fill"></i> Manage Supervisors</a>
  <a href="reports and logs.php" class="nli"><i class="bi bi-file-earmark-text-fill"></i> Reports &amp; Logs</a>
  <a href="chat.php" class="nli"><i class="bi bi-chat-dots-fill"></i> Notifications</a>

  <div class="sb-lbl">System</div>
  <a href="settings.php" class="nli"><i class="bi bi-gear-fill"></i> Settings</a>

  <div class="sb-foot">
    <a href="login.php" onclick="return confirmLogout(event)" class="nli" style="color:#ef4444;">
      <i class="bi bi-box-arrow-right"></i> Log Out
    </a>
  </div>
</aside>

<!-- ══════════ TOPBAR ══════════ -->
<div class="topbar">
  <div class="tb-title">
    Admin Dashboard
    <span>&nbsp;— Welcome back, <strong>Admin</strong></span>
  </div>
  <div class="tb-actions">
    <div class="tb-btn" title="Notifications">
      <i class="bi bi-bell" style="font-size:.95rem;"></i>
      <span class="bdot"></span>
    </div>
    <div class="tb-btn" title="Messages">
      <i class="bi bi-chat-dots" style="font-size:.95rem;"></i>
    </div>
    <div>
      <div class="uname">Administrator</div>
      <div class="urole">System Admin</div>
    </div>
    <div class="ava">AD</div>
  </div>
</div>

<!-- ══════════ MAIN CONTENT ══════════ -->
<main class="main">

  <!-- Page Heading -->
  <div class="d-flex align-items-center justify-content-between mb-4">
    <div>
      <div class="ph">Overview</div>
      <div class="ps" id="dateLabel"></div>
    </div>
    <a href="list_of_interns.php"
       style="border-radius:8px;padding:9px 18px;font-weight:600;font-size:.82rem;background:#2563eb;color:#fff;text-decoration:none;display:inline-flex;align-items:center;gap:6px;">
      <i class="bi bi-plus-lg"></i> Add User
    </a>
  </div>

  <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="si b-blue"><i class="bi bi-people-fill"></i></div>
                <div>
                    <div class="sv"><?php echo number_format($total_interns); ?></div>
                    <div class="sl">Total Interns</div>
                    <div class="sch up"><i class="bi bi-arrow-up-short"></i> All time</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="si b-green"><i class="bi bi-person-badge-fill"></i></div>
                <div>
                    <div class="sv"><?php echo number_format($total_supervisors); ?></div>
                    <div class="sl">Total Supervisors</div>
                    <div class="sch up"><i class="bi bi-arrow-up-short"></i> All time</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="si b-amb"><i class="bi bi-file-earmark-check-fill"></i></div>
                <div>
                    <div class="sv"><?php echo number_format($total_reports); ?></div>
                    <div class="sl">Total Reports</div>
                    <div class="sch nt"><i class="bi bi-dash"></i>All submitted</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="si b-vio"><i class="bi bi-diagram-3-fill"></i></div>
                <div>
                    <div class="sv"><?php echo number_format($total_assignments); ?></div>
                    <div class="sl">Active Assignments</div>
                    <div class="sch up"><i class="bi bi-arrow-up-short"></i>Current</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Applied/Approved/Pending Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="stat-card">
                <div class="si b-blue"><i class="bi bi-clock"></i></div>
                <div>
                    <div class="sv"><?php echo number_format($applied); ?></div>
                    <div class="sl">Applications (Applied)</div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card">
                <div class="si b-green"><i class="bi bi-check-circle"></i></div>
                <div>
                    <div class="sv"><?php echo number_format($approved); ?></div>
                    <div class="sl">Approved</div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card">
                <div class="si b-amb"><i class="bi bi-hourglass-split"></i></div>
                <div>
                    <div class="sv"><?php echo number_format($pending); ?></div>
                    <div class="sl">Pending Approval</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Intern Status Tables - Side by Side -->
    <div class="row g-4 mb-5">
        <!-- Applied -->
        <div class="col-md-4">
            <div class="scard">
                <div class="scard-head">
                    <h6><i class="bi bi-people-fill text-primary"></i> Applied Interns <span class="badge bg-primary ms-2"><?php echo $total_interns; ?></span></h6>
                    <a href="list_of_interns.php" class="va">View All →</a>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover table-sm mb-0">
                        <thead class="table-light">
                            <tr><th>#</th><th>Name</th><th>Department</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent_interns as $intern): ?>
                            <tr>
                                <td><?php static $row_num = 0; echo ++$row_num; ?></td>
                                <td><?php echo htmlspecialchars($intern['Name']); ?></td>
                                <td><?php echo htmlspecialchars($intern['department']); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <!-- Approved -->
        <div class="col-md-4">
            <div class="scard">
                <div class="scard-head">
                    <h6><i class="bi bi-check-circle-fill text-success"></i> Approved <span class="badge bg-success ms-2"><?php echo $total_assignments; ?></span></h6>
                    <a href="" class="va">View All →</a>
                </div>
                <?php
                $approved_list = $pdo->query("SELECT i.Name, i.department, a.status FROM intern i JOIN applications a ON i.intern_id = a.intern_id WHERE a.status = 'approved' LIMIT 5")->fetchAll();
                ?>
                <div class="table-responsive">
                    <table class="table table-hover table-sm mb-0">
                        <thead class="table-light">
                            <tr><th>Name</th><th>Status</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ($approved_list as $app): ?>
                            <tr><td><?php echo htmlspecialchars($app['Name']); ?></td><td><span class="badge bg-success"><?php echo ucfirst($app['status']); ?></span></td></tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <!-- Pending -->
        <div class="col-md-4">
            <div class="scard">
                <div class="scard-head">
                    <h6><i class="bi bi-hourglass-split text-warning"></i> Pending <span class="badge bg-warning ms-2"><?php echo $pending; ?></span></h6>
                    <a href="list_of_interns.php?status=pending" class="va">View All →</a>
                </div>
                <?php
                $pending_list = $pdo->query("SELECT i.Name, i.department FROM intern i LEFT JOIN applications a ON i.intern_id = a.intern_id WHERE a.status = 'pending' OR a.status IS NULL LIMIT 5")->fetchAll();
                ?>
                <div class="table-responsive">
                    <table class="table table-hover table-sm mb-0">
                        <thead class="table-light">
                            <tr><th>Name</th><th>Action</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pending_list as $pend): ?>
                            <tr><td><?php echo htmlspecialchars($pend['Name']); ?></td><td><a href="assign_supervisor.php" class="btn btn-sm btn-warning">Approve</a></td></tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Supervisors with Interns Table -->
    <div class="row g-3">
        <div class="col-12">
            <div class="scard">
                <div class="scard-head">
                    <h6><i class="bi bi-diagram-3-fill text-info"></i> Supervisors & Their Interns</h6>
                </div>
                <?php
                $sup_interns = $pdo->query("SELECT s.name as supervisor, COUNT(a.id) as intern_count, GROUP_CONCAT(i.Name SEPARATOR ', ') as interns 
                                            FROM supervisors s 
                                            LEFT JOIN assignments a ON s.id = a.supervisor_id 
                                            LEFT JOIN intern i ON a.intern_id = i.intern_id 
                                            WHERE a.status = 'active' 
                                            GROUP BY s.id, s.name ORDER BY intern_count DESC LIMIT 10")->fetchAll();
                ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr><th>Supervisor</th><th>Intern Count</th><th>Interns</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ($sup_interns as $sup): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($sup['supervisor']); ?></strong></td>
                                <td><span class="badge bg-info"><?php echo $sup['intern_count']; ?></span></td>
                                <td><?php echo htmlspecialchars($sup['interns'] ?: 'None'); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php if (empty($sup_interns)): ?>
                <div class="text-center py-4 text-muted">No supervisor assignments yet</div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Create New Account Section -->
    <div class="row g-3">
        <div class="col-12">
            <div class="scard shadow-lg border-0" style="background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);">
                <div class="scard-head d-flex justify-content-between align-items-center">
<h6><i class="bi bi-person-plus-fill text-success"></i> Create Supervisor Account</h6>
                </div>
                <div class="card-body p-4">
<form action="register_supervisor.php" method="POST" class="row g-3">
                        <div class="col-md-5">
                            <label class="form-label fw-bold text-success">Full Name</label>
                            <input type="text" name="name" class="form-control border-success" required>
                        </div>
                        <div class="col-md-5">
                            <label class="form-label fw-bold text-success">Email</label>
                            <input type="email" name="email" class="form-control border-success" required>
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-success w-100 fw-bold">
                                <i class="bi bi-plus-circle"></i> Create Supervisor
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            </div>
        </div>
    </div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
const d = new Date();
document.getElementById('dateLabel').textContent = d.toLocaleDateString('en-US', {weekday:'long', year:'numeric', month:'long', day:'numeric'});
</script>
</body>
</html>

</body>
</html>