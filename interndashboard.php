<?php
session_start();

/*if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'intern') {
    header("Location: login.php");
    exit();
}*/

include 'db_connect.php';

$user_id = $_SESSION['user_id'];
$app_sql = "SELECT status FROM applications WHERE intern_id = ? ORDER BY application_date DESC LIMIT 1";
$app_stmt = $conn->prepare($app_sql);
$app_stmt->bind_param("i", $user_id);
$app_stmt->execute();
$app_result = $app_stmt->get_result();

$app_status = 'pending';
$is_approved = false;

if ($app_result->num_rows > 0) {
    $app_data = $app_result->fetch_assoc();
    $app_status = $app_data['status'];
    $is_approved = ($app_status == 'approved');
}
$app_stmt->close();

// NOTIFICATION COUNT
$notif_count_sql = "SELECT COUNT(*) as unread FROM notifications WHERE user_id = ? AND `read` = 0";
$notif_stmt = $conn->prepare($notif_count_sql);
$notif_stmt->bind_param("i", $user_id);
$notif_stmt->execute();
$notif_result = $notif_stmt->get_result();
$notif_count = $notif_result->fetch_assoc()['unread'];
$notif_stmt->close();

$username = $_SESSION['username'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Intern Dashboard — CENADI</title>
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

/* NOTIFICATION STYLES */
.notification-badge {
    position: absolute;
    top: -5px;
    right: -5px;
    background: #ef4444;
    color: white;
    border-radius: 50%;
    width: 20px;
    height: 20px;
    font-size: 11px;
    font-weight: 700;
    display: flex;
    align-items: center;
    justify-content: center;
    animation: pulse 2s infinite;
}
@keyframes pulse {
    0% { box-shadow: 0 0 0 0 rgba(239,68,68,0.7); }
    70% { box-shadow: 0 0 0 8px rgba(239,68,68,0); }
    100% { box-shadow: 0 0 0 0 rgba(239,68,68,0); }
}
.notification-dropdown {
    position: absolute;
    top: 45px;
    right: 0;
    background: white;
    border-radius: 12px;
    box-shadow: 0 20px 40px rgba(0,0,0,0.15);
    border: 1px solid #e2e8f0;
    min-width: 320px;
    max-height: 400px;
    overflow-y: auto;
    z-index: 1000;
    display: none;
}
.notification-item {
    padding: 16px 20px;
    border-bottom: 1px solid #f1f5f9;
    cursor: pointer;
    transition: background 0.2s;
}
.notification-item:hover {
    background: #f8fafc;
}
.notification-item.unread {
    border-left: 4px solid #2563eb;
    background: #eff6ff;
}
.notif-header {
    font-size: 14px;
    font-weight: 600;
    color: #1e293b;
    margin-bottom: 4px;
}
.notif-body {
    font-size: 13px;
    color: #64748b;
    line-height: 1.4;
}
.notif-time {
    font-size: 12px;
    color: #94a3b8;
    margin-top: 8px;
}

.sidebar { position: fixed; top: 0; left: 0; height: 100vh; width: var(--sw); background: var(--sb); display: flex; flex-direction: column; z-index: 100; overflow-y: auto; }
.sb-brand { padding: 22px 24px 18px; border-bottom: 1px solid #1e293b; display: flex; align-items: center; gap: 12px; }
.b-icon { width: 36px; height: 36px; background: var(--p); border-radius: 8px; display: flex; align-items: center; justify-content: center; color: #fff; font-size: 1rem; flex-shrink: 0; }
.b-text { color: #f8fafc; font-size: .9rem; font-weight: 700; line-height: 1.2; }
.b-text span { display: block; font-size: .68rem; font-weight: 400; color: #94a3b8; letter-spacing: .6px; text-transform: uppercase; }
.sb-lbl { padding: 20px 24px 6px; font-size: .65rem; font-weight: 600; letter-spacing: 1.2px; text-transform: uppercase; color: #475569; }
.nli { display: flex; align-items: center; gap: 12px; padding: 10px 20px; margin: 2px 12px; border-radius: 8px; color: #94a3b8; text-decoration: none; font-size: .875rem; font-weight: 500; transition: background .2s, color .2s; position: relative; }
.nli i { font-size: 1rem; width: 20px; text-align: center; flex-shrink: 0; }
.nli:hover:not(.locked) { background: var(--sh); color: #f8fafc; }
.nli.active { background: var(--p); color: #fff; }
.nli.locked { opacity: .4; cursor: not-allowed; pointer-events: none; }
.nli.locked .lk { margin-left: auto; font-size: .7rem; }
.sb-foot { margin-top: auto; padding: 16px 12px; border-top: 1px solid #1e293b; }

.topbar { position: fixed; top: 0; left: var(--sw); right: 0; height: 64px; background: #fff; border-bottom: 1px solid var(--bd); display: flex; align-items: center; justify-content: space-between; padding: 0 28px; z-index: 90; box-shadow: 0 1px 3px rgba(0,0,0,.06); }
.tb-title { font-size: 1rem; font-weight: 600; }
.tb-title span { color: var(--mu); font-weight: 400; font-size: .875rem; }
.tb-actions { display: flex; align-items: center; gap: 12px; position: relative; }
.tb-btn { width: 36px; height: 36px; border-radius: 8px; border: 1px solid var(--bd); background: #fff; display: flex; align-items: center; justify-content: center; color: var(--mu); cursor: pointer; transition: background .2s; position: relative; }
.tb-btn:hover { background: var(--bg); }
.ava { width: 36px; height: 36px; border-radius: 50%; background: linear-gradient(135deg,#2563eb,#8b5cf6); display: flex; align-items: center; justify-content: center; color: #fff; font-size: .8rem; font-weight: 700; cursor: pointer; }
.uname { font-size: .85rem; font-weight: 600; text-align: right; }

.notification-container { position: relative; }
.notification-toggle { position: relative; }
.notification-badge {
    position: absolute;
    top: -4px;
    right: -4px;
    background: #ef4444;
    color: white;
    border-radius: 50%;
    width: 18px;
    height: 18px;
    font-size: 10px;
    font-weight: 700;
    display: flex;
    align-items: center;
    justify-content: center;
}
.notification-dropdown {
    position: absolute;
    top: 50px;
    right: 0;
    background: white;
    min-width: 320px;
    max-height: 400px;
    overflow-y: auto;
    box-shadow: 0 10px 30px rgba(0,0,0,0.15);
    border-radius: 12px;
    border: 1px solid #e2e8f0;
    z-index: 1001;
    display: none;
}
.notification-item {
    padding: 16px 20px;
    border-bottom: 1px solid #f1f5f9;
    cursor: pointer;
    transition: background 0.2s;
}
.notification-item:hover {
    background: #f8fafc;
}
.notification-item.unread {
    background: #eff6ff;
    border-left: 4px solid #2563eb;
}
.notif-title {
    font-weight: 600;
    color: #1e293b;
    font-size: 14px;
    margin-bottom: 4px;
}
.notif-body {
    color: #64748b;
    font-size: 13px;
}
.notif-time {
    font-size: 12px;
    color: #94a3b8;
    margin-top: 6px;
}

.status-pill { display: inline-flex; align-items: center; gap: 5px; padding: 3px 10px; border-radius: 20px; font-size: .7rem; font-weight: 600; text-transform: uppercase; letter-spacing: .4px; }
.status-pending  { background: #fffbeb; color: #92400e; border: 1px solid #fde68a; }
.status-approved { background: #ecfdf5; color: #065f46; border: 1px solid #6ee7b7; }
.status-rejected { background: #fef2f2; color: #991b1b; border: 1px solid #fca5a5; }

.main { margin-left: var(--sw); padding: 88px 28px 40px; min-height: 100vh; }
.ph { font-size: 1.4rem; font-weight: 700; }
.ps { font-size: .875rem; color: var(--mu); margin-top: 2px; }

.notice-banner { border-radius: 12px; padding: 18px 20px; margin-bottom: 24px; display: flex; align-items: flex-start; gap: 14px; border: 1px solid; }
.notice-banner.warn { background: #fffbeb; border-color: #fde68a; }
.notice-banner.err  { background: #fef2f2; border-color: #fca5a5; }
.notice-banner.ok   { background: #ecfdf5; border-color: #6ee7b7; }
.n-icon { width: 40px; height: 40px; border-radius: 10px; flex-shrink: 0; display: flex; align-items: center; justify-content: center; font-size: 1.1rem; }
.warn .n-icon { background: #fef3c7; color: #d97706; }
.err  .n-icon { background: #fee2e2; color: #ef4444; }
.ok   .n-icon { background: #d1fae5; color: #10b981; }
.n-title { font-size: .9rem; font-weight: 700; margin-bottom: 4px; }
.warn .n-title { color: #92400e; } .err .n-title { color: #991b1b; } .ok .n-title { color: #065f46; }
.n-body { font-size: .83rem; }
.warn .n-body { color: #78350f; } .err .n-body { color: #7f1d1d; } .ok .n-body { color: #064e3b; }

.icard { background: var(--card); border-radius: 12px; border: 1px solid var(--bd); box-shadow: 0 1px 3px rgba(0,0,0,.05); overflow: hidden; margin-bottom: 24px; }
.icard-head { padding: 16px 24px; border-bottom: 1px solid var(--bd); display: flex; align-items: center; gap: 12px; }
.hi { width: 38px; height: 38px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1rem; flex-shrink: 0; }
.icard-head h5 { font-size: 1rem; font-weight: 700; margin: 0; }
.icard-head p { font-size: .78rem; color: var(--mu); margin: 0; }
.icard-body { padding: 24px; }
.icard-body p { font-size: .875rem; line-height: 1.75; color: var(--mu); margin-bottom: 16px; }
.icard-body h6 { font-size: .9rem; font-weight: 700; color: var(--tm); margin-bottom: 10px; }

.svc-list { list-style: none; padding: 0; margin: 0 0 20px; display: flex; flex-wrap: wrap; gap: 10px; }
.svc-list li { background: #f8fafc; border: 1px solid var(--bd); border-radius: 8px; padding: 8px 14px; font-size: .8rem; font-weight: 500; display: supervisor flex; align-items: center; gap: 8px; }
.svc-list li i { color: var(--p); }

.fg { display: grid; grid-template-columns: repeat(auto-fill, minmax(190px, 1fr)); gap: 14px; }
.fi { background: #f8fafc; border: 1px solid var(--bd); border-radius: 10px; padding: 16px; display: flex; align-items: flex-start; gap: 12px; text-decoration: none; transition: transform .2s, box-shadow .2s; }
.fi:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(0,0,0,.06); }
.f-ic { width: 36px; height: 36px; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: .9rem; flex-shrink: 0; }
.f-nm { font-size: .83rem; font-weight: 600; color: var(--tm); }
.f-ds { font-size: .75rem; color: var(--mu); margin-top: 2px; }

.logo-wrap { display: flex; justify-content: center; padding: 16px 0 4px; }
.logo-wrap img { border-radius: 10px; max-width: 100%; height: auto; box-shadow: 0 2px 8px rgba(0,0,0,.08); }

::-webkit-scrollbar { width: 6px; }
::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 3px; }
</style>
</head>
<body>

<!-- SIDEBAR -->
<aside class="sidebar">
    <div class="sb-brand">
        <div class="b-icon"><i class="bi bi-grid-fill"></i></div>
        <div class="b-text">CENADI <span>Internship Portal</span></div>
    </div>
    <div class="sb-lbl">Navigation</div>
    <a href="interndashboard.php" class="nli active"><i class="bi bi-house-door-fill"></i> Home</a>
    <a href="departmentboard.php" class="nli"><i class="bi bi-send-fill"></i> Apply</a>
    <a href="<?php echo $is_approved ? 'chat.php' : '#'; ?>" class="nli <?php echo !$is_approved ? 'locked' : ''; ?>">
        <i class="bi bi-chat-dots-fill"></i> Messages
        <?php if (!$is_approved): ?><i class="bi bi-lock-fill lk"></i><?php endif; ?>
    </a>
    <a href="<?php echo $is_approved ? 'attendance_of_intern.php' : '#'; ?>" class="nli <?php echo !$is_approved ? 'locked' : ''; ?>">
        <i class="bi bi-calendar-check-fill"></i> Attendance
        <?php if (!$is_approved): ?><i class="bi bi-lock-fill lk"></i><?php endif; ?>
    </a>
<a href="<?php echo $is_approved ? 'submit_report.php' : 'my_reports.php'; ?>" class="nli <?php echo !$is_approved ? 'locked' : ''; ?>">
        <i class="bi bi-file-earmark-text-fill"></i> Submit Report
        <?php if (!$is_approved): ?><i class="bi bi-lock-fill lk"></i><?php endif; ?>
    </a>
    <a href="<?php echo $is_approved ? 'my_reports.php' : '#'; ?>" class="nli <?php echo !$is_approved ? 'locked' : ''; ?>">
        <i class="bi bi-file-earmark-texts-fill"></i> My Reports
        <?php if (!$is_approved): ?><i class="bi bi-lock-fill lk"></i><?php endif; ?>
    </a>
    <a href="<?php echo $is_approved ? 'profile.php' : '#'; ?>" class="nli <?php echo !$is_approved ? 'locked' : ''; ?>">
        <i class="bi bi-person-fill"></i> Profile
        <?php if (!$is_approved): ?><i class="bi bi-lock-fill lk"></i><?php endif; ?>
    </a>
    <div class="sb-foot">
        <a href="login.php" onclick="return confirmLogout(event)" class="nli" style="color:#ef4444;">
            <i class="bi bi-box-arrow-right"></i> Log Out
        </a>
    </div>
</aside>

<!-- TOPBAR -->
<div class="topbar">
    <div class="tb-title">
        Intern Dashboard
        <span>&nbsp;— Welcome back, <strong><?php echo htmlspecialchars($username); ?></strong></span>
    </div>
    <div class="tb-actions">
        <!-- Notification Bell -->
        <div class="notification-container">
            <div class="tb-btn notification-toggle" onclick="toggleNotifications()" title="Notifications">
                <i class="bi bi-bell"></i>
                <?php if ($notif_count > 0): ?>
                    <span class="notification-badge"><?php echo min($notif_count, 99); ?></span>
                <?php endif; ?>
            </div>
            <div class="notification-dropdown" id="notificationDropdown">
                <?php if ($notif_count > 0): ?>
                    <?php 
                    $notifs_sql = "SELECT * FROM notifications WHERE user_id = ? AND `read` = 0 ORDER BY created_at DESC LIMIT 5";
                    $notifs_stmt = $conn->prepare($notifs_sql);
                    $notifs_stmt->bind_param("i", $user_id);
                    $notifs_stmt->execute();
                    $notifs_result = $notifs_stmt->get_result();
                    while ($notif = $notifs_result->fetch_assoc()): 
                    ?>
                    <div class="notification-item unread" onclick="markRead(<?php echo $notif['id']; ?>)">
                        <div class="notif-title"><?php echo htmlspecialchars($notif['title']); ?></div>
                        <div class="notif-body"><?php echo htmlspecialchars($notif['message']); ?></div>
                        <div class="notif-time"><?php echo date('M j, H:i', strtotime($notif['created_at'])); ?></div>
                    </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="notification-item" style="text-align:center;color:#94a3b8;padding:30px 20px;">
                        <i class="bi bi-bell-slash" style="font-size:2rem;margin-bottom:10px;"></i>
                        <div>No new notifications</div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <div>
            <div class="uname"><?php echo htmlspecialchars($username); ?></div>
            <div style="text-align:right;margin-top:2px;">
                <span class="status-pill status-<?php echo $app_status; ?>">
                    <?php if ($app_status=='approved'): ?><i class="bi bi-check-circle-fill"></i>
                    <?php elseif ($app_status=='rejected'): ?><i class="bi bi-x-circle-fill"></i>
                    <?php else: ?><i class="bi bi-clock-fill"></i><?php endif; ?>
                    <?php echo ucfirst($app_status); ?>
                </span>
            </div>
        </div>
        <div class="ava"><?php echo strtoupper(substr($username, 0, 2)); ?></div>
    </div>
</div>

<!-- MAIN -->
<main class="main">
    <!-- ... rest of the dashboard content unchanged ... -->
    
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <div class="ph">Dashboard</div>
            <div class="ps" id="dateLabel"></div>
        </div>
        <a href="departmentboard.php" style="border-radius:8px;padding:9px 18px;font-weight:600;font-size:.82rem;background:#2563eb;color:#fff;text-decoration:none;display:inline-flex;align-items:center;gap:6px;">
            <i class="bi bi-plus-lg"></i> Apply Now
        </a>
    </div>

    <!-- Status Banner -->
    <?php if ($app_status == 'pending'): ?>
    <div class="notice-banner warn">
        <div class="n-icon"><i class="bi bi-hourglass-split"></i></div>
        <div>
            <div class="n-title">Application Under Review</div>
            <div class="n-body">Your application is being reviewed by our team. Full access will be granted once it is approved. Please check back soon.</div>
        </div>
    </div>
    <?php elseif ($app_status == 'rejected'): ?>
    <div class="notice-banner err">
        <div class="n-icon"><i class="bi bi-x-circle"></i></div>
        <div>
            <div class="n-title">Application Not Approved</div>
            <div class="n-body">Unfortunately your application was not approved. Some features are restricted. Please contact your administrator for further assistance.</div>
        </div>
    </div>
    <?php elseif ($is_approved): ?>
    <div class="notice-banner ok">
        <div class="n-icon"><i class="bi bi-check-circle"></i></div>
        <div>
            <div class="n-title">Application Approved — Full Access Granted</div>
            <div class="n-body">Congratulations! You now have access to all intern features including Messages, Attendance, Reports, and your Profile.</div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Feature Grid (approved only) -->
    <?php if ($is_approved): ?>
    <div class="icard">
        <div class="icard-head">
            <div class="hi" style="background:#ecfdf5;color:#10b981;"><i class="bi bi-rocket-takeoff-fill"></i></div>
            <div><h5>You're All Set!</h5><p>Here's what you can do with your account</p></div>
        </div>
        <div class="icard-body">
            <div class="fg">
                <a href="departmentboard.php" class="fi">
                    <div class="f-ic" style="background:#eff6ff;color:#2563eb;"><i class="bi bi-send-fill"></i></div>
                    <div><div class="f-nm">Apply</div><div class="f-ds">Submit department applications</div></div>
                </a>
                <a href="chat.php" class="fi">
                    <div class="f-ic" style="background:#f5f3ff;color:#8b5cf6;"><i class="bi bi-chat-dots-fill"></i></div>
                    <div><div class="f-nm">Messages</div><div class="f-ds">Chat with your supervisor</div></div>
                </a>
                <a href="attendance_of_intern.php" class="fi">
                    <div class="f-ic" style="background:#fffbeb;color:#f59e0b;"><i class="bi bi-calendar-check-fill"></i></div>
                    <div><div class="f-nm">Attendance</div><div class="f-ds">Track your daily attendance</div></div>
                </a>
                <a href="submit_report.php" class="fi">
                    <div class="f-ic" style="background:#ecfdf5;color:#10b981;"><i class="bi bi-file-earmark-text-fill"></i></div>
                    <div><div class="f-nm">Reports</div><div class="f-ds">Submit weekly progress reports</div></div>
                </a>
                <a href="profile.php" class="fi">
                    <div class="f-ic" style="background:#fef2f2;color:#ef4444;"><i class="bi bi-person-fill"></i></div>
                    <div><div class="f-nm">Profile</div><div class="f-ds">Manage your personal info</div></div>
                </a>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- About CENADI -->
    <div class="icard">
        <div class="icard-head">
            <div class="hi" style="background:#eff6ff;color:#2563eb;"><i class="bi bi-building-fill"></i></div>
            <div><h5>About CENADI</h5><p>Centre National de Développement de l'Informatique</p></div>
        </div>
        <div class="icard-body">
            <p>CENADI is a government IT agency located in Yaoundé, Cameroon. It plays a pivotal role in supporting public institutions by managing data centers, developing software, securing digital services, and promoting digital transformation in the public sector. CENADI operates under the Ministry of Finance and is dedicated to enhancing the efficiency and security of government operations through innovative IT solutions.</p>
            <h6><i class="bi bi-bullseye me-2" style="color:#2563eb;"></i>Mission</h6>
            <p>To provide high-quality IT services and infrastructure to public administrations, ensuring data security, software development, and digital innovation for the benefit of Cameroon's public sector.</p>
            <h6><i class="bi bi-grid-3x3-gap-fill me-2" style="color:#2563eb;"></i>Services</h6>
            <ul class="svc-list">
                <li><i class="bi bi-server"></i> Data Center Management</li>
                <li><i class="bi bi-code-slash"></i> Software Development & Maintenance</li>
                <li><i class="bi bi-shield-lock-fill"></i> Cybersecurity Solutions</li>
                <li><i class="bi bi-lightbulb-fill"></i> Digital Transformation Consulting</li>
                <li><i class="bi bi-mortarboard-fill"></i> IT Training & Capacity Building</li>
            </ul>
            <div class="logo-wrap">
                <img src="cenadi.jpeg" alt="CENADI" width="420" height="190">
            </div>
        </div>
    </div>

</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    const d = new Date();
    document.getElementById('dateLabel').textContent =
        d.toLocaleDateString('en-US', {weekday:'long',year:'numeric',month:'long',day:'numeric'});
    function confirmLogout(e) {
        e.preventDefault();
        if (confirm('Are you sure you want to log out?')) window.location.href = 'logout.php';
        return false;
    }
    
    // NOTIFICATION FUNCTIONALITY
    let dropdownVisible = false;
    function toggleNotifications() {
        const dropdown = document.getElementById('notificationDropdown');
        dropdown.style.display = dropdown.style.display === 'block' ? 'none' : 'block';
        dropdownVisible = !dropdownVisible;
    }
    
    function markRead(notifId) {
        // AJAX mark as read
        fetch('mark_notification_read.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: 'id=' + notifId
        }).then(() => {
            location.reload(); // Reload to update badge
        });
    }
    
    // Close dropdown when clicking outside
    document.addEventListener('click', (e) => {
        const container = document.querySelector('.notification-container');
        if (!container.contains(e.target)) {
            document.getElementById('notificationDropdown').style.display = 'none';
        }
    });
</script>
</body>
</html>
