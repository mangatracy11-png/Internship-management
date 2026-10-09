<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'supervisor') {
    header('Location: login.php');
    exit;
}
$name = $_SESSION['username'] ?? $_SESSION['name'] ?? 'Supervisor';

require_once 'config.php';

$supervisor_id = $_SESSION['user_id'];

// Get real data with error handling
$total_interns = 0;
$total_reports = 0;
$avg_attendance = 'N/A';
$upcoming_meetings = 0;

try {
    $total_interns = $pdo->query("SELECT COUNT(*) FROM assignments WHERE supervisor_id = $supervisor_id AND status = 'active'")->fetchColumn() ?: 0;
    $total_reports = $pdo->query("SELECT COUNT(*) FROM intern_reports ir JOIN assignments a ON ir.intern_id = a.intern_id WHERE a.supervisor_id = $supervisor_id")->fetchColumn() ?: 0;
    
    // Mock avg attendance since no attendance table visible
    $avg_attendance = '95%'; 
    
    $upcoming_meetings = $pdo->query("SELECT COUNT(*) FROM meetings m JOIN assignments a ON m.intern_id = a.intern_id WHERE a.supervisor_id = $supervisor_id AND m.date > CURDATE()")->fetchColumn() ?: 0;
} catch (PDOException $e) {
    // Fallback if tables don't exist
}
$assigned_interns = []; // Get recent 5 interns
try {
    $stmt = $pdo->prepare("SELECT i.Name, i.department as project, a.status FROM intern i JOIN assignments a ON i.intern_id = a.intern_id WHERE a.supervisor_id = ? AND a.status = 'active' ORDER BY a.assigned_date DESC LIMIT 5");
    $stmt->execute([$supervisor_id]);
    $assigned_interns = $stmt->fetchAll();
} catch (PDOException $e) {
    $assigned_interns = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Supervisor Dashboard — CENADI</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<style>
:root{--sw:260px;--p:#2563eb;--sb:#0f172a;--sh:#1e293b;--g:#10b981;--a:#f59e0b;--r:#ef4444;--v:#8b5cf6;--bg:#f1f5f9;--card:#fff;--tm:#1e293b;--mu:#64748b;--bd:#e2e8f0}
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:'Inter','Segoe UI',sans-serif;background:var(--bg);color:var(--tm);min-height:100vh}
.sidebar{position:fixed;top:0;left:0;height:100vh;width:var(--sw);background:var(--sb);display:flex;flex-direction:column;z-index:100;overflow-y:auto}
.sb-brand{padding:22px 24px 18px;border-bottom:1px solid #1e293b;display:flex;align-items:center;gap:12px}
.b-icon{width:36px;height:36px;background:var(--p);border-radius:8px;display:flex;align-items:center;justify-content:center;color:#fff;font-size:1rem;flex-shrink:0}
.b-text{color:#f8fafc;font-size:.9rem;font-weight:700;line-height:1.2}
.b-text span{display:block;font-size:.68rem;font-weight:400;color:#94a3b8;letter-spacing:.6px;text-transform:uppercase}
.sb-lbl{padding:20px 24px 6px;font-size:.65rem;font-weight:600;letter-spacing:1.2px;text-transform:uppercase;color:#475569}
.nli{display:flex;align-items:center;gap:12px;padding:10px 20px;margin:2px 12px;border-radius:8px;color:#94a3b8;text-decoration:none;font-size:.875rem;font-weight:500;transition:background .2s,color .2s}
.nli i{font-size:1rem;width:20px;text-align:center;flex-shrink:0}
.nli:hover{background:var(--sh);color:#f8fafc}
.nli.active{background:var(--p);color:#fff}
.sb-foot{margin-top:auto;padding:16px 12px;border-top:1px solid #1e293b}
.topbar{position:fixed;top:0;left:var(--sw);right:0;height:64px;background:#fff;border-bottom:1px solid var(--bd);display:flex;align-items:center;justify-content:space-between;padding:0 28px;z-index:90;box-shadow:0 1px 3px rgba(0,0,0,.06)}
.tb-title{font-size:1rem;font-weight:600}
.tb-title span{color:var(--mu);font-weight:400;font-size:.875rem}
.tb-actions{display:flex;align-items:center;gap:12px}
.tb-btn{width:36px;height:36px;border-radius:8px;border:1px solid var(--bd);background:#fff;display:flex;align-items:center;justify-content:center;color:var(--mu);cursor:pointer;position:relative;transition:background .2s}
.tb-btn:hover{background:var(--bg)}
.bdot{position:absolute;top:5px;right:5px;width:8px;height:8px;background:var(--r);border-radius:50%;border:2px solid #fff}
.ava{width:36px;height:36px;border-radius:50%;background:linear-gradient(135deg,#2563eb,#8b5cf6);display:flex;align-items:center;justify-content:center;color:#fff;font-size:.8rem;font-weight:700;cursor:pointer}
.uname{font-size:.85rem;font-weight:600;text-align:right}
.urole{font-size:.72rem;color:var(--mu);text-align:right}
.main{margin-left:var(--sw);padding:88px 28px 40px;min-height:100vh}
.ph{font-size:1.4rem;font-weight:700}
.ps{font-size:.875rem;color:var(--mu);margin-top:2px}
.sc{background:var(--card);border-radius:12px;padding:20px;border:1px solid var(--bd);display:flex;align-items:center;gap:16px;box-shadow:0 1px 3px rgba(0,0,0,.05);transition:transform .2s,box-shadow .2s;height:100%}
.sc:hover{transform:translateY(-2px);box-shadow:0 6px 16px rgba(0,0,0,.08)}
.si{width:48px;height:48px;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:1.3rem;flex-shrink:0}
.sv{font-size:1.6rem;font-weight:700;line-height:1}
.sl{font-size:.78rem;color:var(--mu);margin-top:3px}
.sch{font-size:.75rem;font-weight:500;margin-top:4px}
.sch.up{color:var(--g)}.sch.nt{color:var(--mu)}
.card2{background:var(--card);border-radius:12px;border:1px solid var(--bd);box-shadow:0 1px 3px rgba(0,0,0,.05);overflow:hidden}
.ch{padding:16px 20px;border-bottom:1px solid var(--bd);display:flex;align-items:center;justify-content:space-between}
.ch h6{font-size:.9rem;font-weight:600;margin:0}
.va{font-size:.78rem;color:var(--p);text-decoration:none;font-weight:500}
.ic{background:var(--card);border-radius:12px;padding:18px;border:1px solid var(--bd);box-shadow:0 1px 3px rgba(0,0,0,.05);transition:transform .2s,box-shadow .2s;height:100%}
.ic:hover{transform:translateY(-2px);box-shadow:0 6px 16px rgba(0,0,0,.08)}
.iav{width:44px;height:44px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:.95rem;font-weight:700;color:#fff;margin-bottom:12px}
.in{font-size:.9rem;font-weight:600}
.ir{font-size:.75rem;color:var(--mu);margin-top:1px}
.pl{display:flex;justify-content:space-between;font-size:.75rem;color:var(--mu);margin:10px 0 5px}
.progress{height:6px;border-radius:99px;background:#f1f5f9}
.progress-bar{border-radius:99px}
.ni{display:flex;align-items:flex-start;gap:12px;padding:14px 20px;border-bottom:1px solid var(--bd);transition:background .15s}
.ni:last-child{border-bottom:none}.ni:hover{background:#f8fafc}
.nd{width:36px;height:36px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:.9rem;flex-shrink:0}
.nt2{font-size:.83rem;font-weight:600}
.ns{font-size:.77rem;color:var(--mu);margin-top:2px}
.ntime{font-size:.7rem;color:var(--mu);white-space:nowrap}
.mi{display:flex;align-items:center;gap:12px;padding:13px 20px;border-bottom:1px solid var(--bd);transition:background .15s}
.mi:last-child{border-bottom:none}.mi:hover{background:#f8fafc}
.mav{width:36px;height:36px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:.78rem;font-weight:700;color:#fff;flex-shrink:0}
.mn{font-size:.83rem;font-weight:600}
.mt{font-size:.77rem;color:var(--mu);margin-top:1px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:200px}
.ti{display:flex;align-items:center;justify-content:space-between;padding:14px 20px;border-bottom:1px solid var(--bd);transition:background .15s}
.ti:last-child{border-bottom:none}.ti:hover{background:#f8fafc}
.tt{font-size:.85rem;font-weight:500}
.td{font-size:.75rem;color:var(--mu);margin-top:2px}
.tb2{font-size:.7rem;font-weight:600;padding:4px 10px;border-radius:20px;white-space:nowrap;flex-shrink:0}
.b-blue{background:#eff6ff;color:#2563eb}.b-green{background:#ecfdf5;color:#10b981}
.b-amb{background:#fffbeb;color:#f59e0b}.b-vio{background:#f5f3ff;color:#8b5cf6}
.tag-td{background:#eff6ff;color:#2563eb}.tag-tm{background:#fffbeb;color:#b45309}
.tag-3d{background:#fef2f2;color:#b91c1c}.tag-5d{background:#ecfdf5;color:#065f46}
::-webkit-scrollbarremove {width:6px}::-webkit-scrollbar-thumb{background:#cbd5e1;border-radius:3px}
</style>
</head>
<body>

<aside class="sidebar">
  <div class="sb-brand">
    <div class="b-icon"><i class="bi bi-grid-fill"></i></div>
    <div class="b-text">CENADI <span>Internship Portal</span></div>
  </div>
  <div class="sb-lbl">Main Menu</div>
  <a href="#" class="nli active"><i class="bi bi-house-door-fill"></i> Dashboard</a>
  <a href="view_interns_assigned.php" class="nli"><i class="bi bi-people-fill"></i> My Interns</a>
  <a href="chat.php" class="nli">
        <i class="bi bi-chat-dots-fill"></i> Messages
    </a>
<a href="view_report.php" class="nli"><i class="bi bi-file-earmark-texts-fill"></i> Review Reports</a>
<a href="trackattendance.php" class="nli"><i class="bi bi-file-earmark-texts-fill"></i>Track attendance</a>
  <a href="schedulemeeting.php" class="nli"><i class="bi bi-calendar-event-fill"></i> Schedule Meeting</a>
  <div class="sb-lbl">System</div>
  <a href="supervisor_setting.php" class="nli"><i class="bi bi-gear-fill"></i> Settings</a>
  <div class="sb-foot">
    <a href="login.php" onclick="return confirmLogout(event)" class="nli" style="color:#ef4444;">
      <i class="bi bi-box-arrow-right"></i> Log Out
    </a>
  </div>
</aside>

<div class="topbar">
  <div class="tb-title">
    Supervisor Dashboard
    <span>&nbsp;— Welcome back, <strong><?php echo htmlspecialchars($name); ?></strong></span>
  </div>
  <div class="tb-actions">
    <div class="tb-btn" title="Notifications">
      <i class="bi bi-bell" style="font-size:.95rem;"></i><span class="bdot"></span>
    </div>
    <div class="tb-btn" title="Messages">
      <i class="bi bi-chat-dots" style="font-size:.95rem;"></i>
    </div>
    <div>
      <div class="uname"><?php echo htmlspecialchars($name); ?></div>
      <div class="urole">Internship Supervisor</div>
    </div>
    <div class="ava">SV</div>
  </div>
</div>

<main class="main">

  <!-- Stats Overview -->
  <div class="row g-4 mb-5">
    <div class="col-lg-3 col-md-6">
      <div class="sc">
        <div class="si" style="background:linear-gradient(135deg,#10b981,#059669)"><i class="bi bi-people-fill"></i></div>
        <div>
  <div class="sv" style="color:#10b981"><?php echo $total_interns; ?></div>
          <div class="sl">Total Interns</div>
          <div class="sch up"><i class="bi bi-arrow-up me-1"></i> +2 this month</div>
        </div>
      </div>
    </div>
    <div class="col-lg-3 col-md-6">
      <div class="sc">
        <div class="si" style="background:linear-gradient(135deg,#f59e0b,#d97706)"><i class="bi bi-clock-history"></i></div>
        <div>
          <div class="sv" style="color:#f59e0b"><?php echo $total_reports; ?></div>
          <div class="sl">Total Reports</div>
          <div class="sch nt"><?php echo $total_reports; ?> submitted</div>
        </div>
      </div>
    </div>
    <div class="col-lg-3 col-md-6">
      <div class="sc">
        <div class="si" style="background:linear-gradient(135deg,#3b82f6,#1d4ed8)"><i class="bi bi-graph-up"></i></div>
        <div>
  <div class="sv" style="color:#3b82f6"><?php echo $avg_attendance; ?></div>
          <div class="sl">Avg Attendance</div>
          <div class="sch up"><i class="bi bi-arrow-up me-1"></i> +5% WoW</div>
        </div>
      </div>
    </div>
    <div class="col-lg-3 col-md-6">
      <div class="sc">
        <div class="si" style="background:linear-gradient(135deg,#f59e0b,#d97706)"><i class="bi bi-calendar-event"></i></div>
        <div>
  <div class="sv" style="color:#f59e0b"><?php echo $upcoming_meetings; ?></div>
          <div class="sl">Upcoming Meetings</div>
          <div class="sch up"><i class="bi bi-arrow-up me-1"></i> Next: Tomorrow</div>
        </div>
      </div>
    </div>
  </div>



  <div class="row g-4">
    <!-- Quick Actions -->

    <div class="col-lg-4 col-md-6">
      <div class="ic h-100">
        <div class="iav" style="background:linear-gradient(135deg,#10b981,#059669)">R</div>
        <div class="in">Review Reports</div>
        <div class="ir">Check submissions</div>
        <div class="pl">
          <span>3 pending</span>
          <a href="view_report.php" class="text-decoration-none" style="color:#10b981">Review →</a>
        </div>
      </div>
    </div>
    <div class="col-lg-4 col-md-6">
      <div class="ic h-100">
        <div class="iav" style="background:linear-gradient(135deg,#f59e0b,#d97706)">A</div>
        <div class="in">Track Attendance</div>
        <div class="ir">Monitor daily logs</div>
        <div class="pl">
          <span>Avg 95%</span>
          <a href="trackattendance.php" class="text-decoration-none" style="color:#f59e0b">Track →</a>
        </div>
      </div>
    </div>

    <!-- Pending Reports & Chart -->
        <div class="col-lg-8">
      <div class="card2">
        <div class="ch">
          <h6><i class="bi bi-diagram-3 me-2" style="color:#10b981;"></i>Recent Activity</h6>
        </div>
        <div class="p-3">
          <div class="row g-3">
            <div class="col-6">
              <div class="d-flex align-items-center gap-3 p-3 border rounded-3">
                <div class="flex-shrink-0"><div class="nd b-blue" style="width:40px;height:40px"><i class="bi bi-people-fill"></i></div></div>
                <div>
                  <div class="fw-bold"><?php echo $total_interns; ?> Interns</div>
                  <div>Active assignments</div>
                </div>
              </div>
            </div>
            <div class="col-6">
              <div class="d-flex align-items-center gap-3 p-3 border rounded-3">
                <div class="flex-shrink-0"><div class="nd b-green" style="width:40px;height:40px"><i class="bi bi-file-earmark-text"></i></div></div>
                <div>
                  <div class="fw-bold"><?php echo $total_reports; ?> Reports</div>
                  <div>Submitted reports</div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  <!-- Updated Notifications & Messages -->
  <div class="row g-4 mt-4">
    <div class="col-md-6">
      <div class="card2">
        <div class="ch">
          <h6><i class="bi bi-bell-fill me-2" style="color:#f59e0b;"></i>Notifications</h6>
          <a href="#" class="va">Mark All Read</a>
        </div>
        <div class="ni">
          <div class="nd" style="background:#eff6ff;color:#2563eb"><i class="bi bi-chat-fill"></i></div>
          <div><div class="nt2">New Message from John</div><div class="ns">Regarding project update</div></div>
          <div class="ntime">10 min ago</div>
        </div>
        <div class="ni">
          <div class="nd" style="background:#ecfdf5;color:#10b981"><i class="bi bi-check-lg"></i></div>
          <div><div class="nt2">Report Approved</div><div class="ns">Jane's weekly report approved</div></div>
          <div class="ntime">2 hrs ago</div>
        </div>
        <div class="ni">
          <div class="nd" style="background:#fef2f2;color:#ef4444"><i class="bi bi-exclamation"></i></div>
          <div><div class="nt2">Attendance Issue</div><div class="ns">Alex missed 2 days</div></div>
          <div class="ntime">1 day ago</div>
        </div>
      </div>
    </div>
    <div class="col-md-6">
      <div class="card2">
        <div class="ch">
          <h6><i class="bi bi-chat-dots-fill me-2" style="color:#2563eb;"></i>Recent Messages</h6>
          <a href="chat.php" class="va">Open Chat</a>
        </div>
        <div class="mi">
          <div class="mav" style="background:#3b82f6">JD</div>
          <div><div class="mn">John Doe</div><div class="mt">Need clarification on task 3...</div></div>
          <div class="ntime">Now</div>
        </div>
        <div class="mi">
          <div class="mav" style="background:#10b981">JS</div>
          <div><div class="mn">Jane Smith</div><div class="mt">Report submitted ✅</div></div>
          <div class="ntime">1h</div>
        </div>
        <div class="mi">
          <div class="mav" style="background:#ef4444">AJ</div>
          <div><div class="mn">Alex Johnson</div><div class="mt">Request meeting next week</div></div>
          <div class="ntime">4h</div>
        </div>
      </div>
    </div>
  </div>

</main>
<style>
.b-r {background:#fee2e2 !important;color:#ef4444 !important}
.table .badge {font-size:.75rem;padding:.25em .5em}
.nd {border-radius:8px !important}
</style>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
  // Attendance chart
  const ctx = document.getElementById('attendanceChart');
  if (ctx) {
    new Chart(ctx, {
      type: 'line',
      data: {
        labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri'],
        datasets: [{
          data: [95, 98, 92, 97, 96],
          borderColor: '#10b981',
          backgroundColor: 'rgba(16,185,129,0.1)',
          tension: 0.4,
          fill: true,
          borderWidth: 3
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {legend: {display: false}},
        scales: {
          x: {display: false},
          y: {display: false}
        }
      }
    });
  }
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
  const d = new Date();
  document.getElementById('dateLabel').textContent =
    d.toLocaleDateString('en-US',{weekday:'long',year:'numeric',month:'long',day:'numeric'}) + ' · 4 active interns';
  function confirmLogout(e){
    e.preventDefault();
    if(confirm('Are you sure you want to log out?')) window.location.href='login.php';
    return false;
  }
</script>
</body>
</html>