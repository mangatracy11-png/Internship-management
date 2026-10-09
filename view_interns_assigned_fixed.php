<?php
session_start();
include 'db_connect.php';

// Admin or supervisor access
if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['admin', 'supervisor'])) {
    header("Location: login.php");
    exit();
}

// Get supervisor_id from GET param for admin, or session
$supervisor_id = null;
$supervisor_name = 'Supervisor';

if (isset($_GET['supervisor_id'])) {
    // Admin viewing specific supervisor's interns
    $stmt = $conn->prepare("SELECT id, name FROM supervisors WHERE id = ?");
    $stmt->bind_param("i", $_GET['supervisor_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $supervisor = $result->fetch_assoc();
    if ($supervisor) {
        $supervisor_id = $supervisor['id'];
        $supervisor_name = $supervisor['name'];
    } else {
        die("Supervisor not found");
    }
} else {
    // Supervisor viewing own interns
    $possible_session_keys = ['supervisor_id', 'user_id', 'id'];
    foreach ($possible_session_keys as $key) {
        if (isset($_SESSION[$key])) {
            $supervisor_id = $_SESSION[$key];
            $supervisor_name = $_SESSION['name'] ?? $_SESSION['username'] ?? 'Supervisor';
            break;
        }
    }
}

if (!$supervisor_id) {
    die("No supervisor ID found");
}

// Get interns for this supervisor
$error = '';
$stmt = $conn->prepare("
    SELECT i.*, a.assigned_date, a.status as assignment_status
    FROM interns i
    LEFT JOIN assignments a ON i.id = a.intern_id AND a.supervisor_id = ?
    WHERE a.supervisor_id = ? OR (a.supervisor_id IS NULL AND i.status = 'active')
    ORDER BY a.assigned_date DESC, i.created_at DESC
");
$stmt->bind_param("ii", $supervisor_id, $supervisor_id);
$stmt->execute();
$result = $stmt->get_result();
$interns = [];
while ($row = $result->fetch_assoc()) {
    $interns[] = $row;
}
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Interns Assigned to <?php echo htmlspecialchars($supervisor_name); ?> - CENADI</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root {
            --primary: #2563eb; --success: #10b981; --bg: #f8fafc; --card: #fff;
            --text: #1e293b; --mute: #64748b; --border: #e2e8f0;
        }
        body { font-family: 'Inter', sans-serif; background: var(--bg); color: var(--text); }
        .container { max-width: 1200px; margin: 0 auto; padding: 20px; }
        .header {
            background: var(--card); padding: 25px; border-radius: 12px; margin-bottom: 30px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08); display: flex; justify-content: space-between; align-items: center;
        }
        .header h1 { font-size: 1.6rem; font-weight: 700; color: var(--text); margin: 0; }
        .back-btn { background: var(--mute); color: white; padding: 10px 20px; border-radius: 8px; text-decoration: none; transition: all 0.3s; }
        .back-btn:hover { background: #475569; transform: translateY(-1px); }
        .stats-card { background: var(--card); border-radius: 12px; padding: 25px; margin-bottom: 30px; box-shadow: 0 4px 12px rgba(0,0,0,0.08); text-align: center; }
        .stats-number { font-size: 2.5rem; font-weight: 800; color: var(--primary); line-height: 1; }
        .stats-label { color: var(--mute); font-size: 0.95rem; font-weight: 500; text-transform: uppercase; letter-spacing: 0.5px; }
        .search-box { background: var(--card); padding: 25px; border-radius: 12px; margin-bottom: 30px; box-shadow: 0 4px 12px rgba(0,0,0,0.08); }
        .search-input { border: 2px solid var(--border); border-radius: 10px; padding: 15px 20px; font-size: 1rem; transition: all 0.3s; }
        .search-input:focus { border-color: var(--primary); box-shadow: 0 0 0 0.2rem rgba(37,99,235,0.1); outline: none; }
        .interns-table { background: var(--card); border-radius: 12px; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,0.08); }
        .table { margin: 0; }
        .table th { background: var(--primary); color: white; font-weight: 600; border: none; padding: 18px 15px; }
        .table td { padding: 18px 15px; border-color: var(--border); vertical-align: middle; }
        .table tbody tr:hover { background: #f8faff; }
        .status-active { background: #d1fae5; color: #065f46; }
        .status-pending { background: #fef3c7; color: #92400e; }
        .badge { padding: 6px 12px; border-radius: 20px; font-weight: 600; font-size: 0.85rem; }
        .no-data { text-align: center; padding: 60px 20px; color: var(--mute); }
        .no-data i { font-size: 4rem; opacity: 0.5; margin-bottom: 20px; display: block; }
        @media (max-width: 768px) { .header { flex-direction: column; gap: 15px; text-align: center; } }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <div>
                <h1><?php echo (isset($_GET['supervisor_id']) ? 'Interns Assigned to' : 'My Assigned Interns'); ?> 
                    <span class="text-primary"><?php echo htmlspecialchars($supervisor_name); ?></span>
                </h1>
            </div>
            <a href="javascript:history.back()" class="back-btn">
                <i class="bi bi-arrow-left"></i> Back
            </a>
        </div>

        <?php if (isset($error) && $error): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <!-- Stats -->
        <div class="stats-card">
            <div class="stats-number"><?php echo count($interns); ?></div>
            <div class="stats-label">Total Interns Assigned</div>
        </div>

        <!-- Search -->
        <div class="search-box">
            <input type="text" id="searchInput" class="search-input" placeholder="🔍 Search interns by name, email, school...">
        </div>

        <!-- Table -->
        <?php if (count($interns) > 0): ?>
            <div class="interns-table">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Photo</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>School</th>
                            <th>Department</th>
                            <th>Start Date</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="internsTable">
                        <?php foreach ($interns as $intern): ?>
                            <tr>
                                <td><?php echo $intern['id'] ?? 'N/A'; ?></td>
                                <td>
                                    <img src="uploads/profile_<?php echo $intern['id']; ?>_*.jpg" onerror="this.src='https://via.placeholder.com/40x40/007bff/ffffff?text=I'" class="rounded-circle" width="40" height="40" alt="Photo">
                                </td>
                                <td>
                                    <strong><?php echo htmlspecialchars($intern['name'] ?? $intern['Name'] ?? 'N/A'); ?></strong>
                                </td>
                                <td><?php echo htmlspecialchars($intern['email'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($intern['phone'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($intern['school'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($intern['department'] ?? 'N/A'); ?></td>
                                <td><?php echo date('M d', strtotime($intern['start_date'] ?? 'N/A')); ?></td>
                                <td>
                                    <span class="badge status-<?php echo $intern['status'] ?? 'active'; ?>">
                                        <?php echo ucfirst($intern['status'] ?? 'Active'); ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="internsprofile.php?id=<?php echo $intern['id']; ?>" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-eye"></i> View
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="no-data">
                <i class="bi bi-inbox"></i>
                <h4>No interns assigned yet</h4>
                <p>This supervisor has no active intern assignments.</p>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Search
        document.getElementById('searchInput')?.addEventListener('keyup', function() {
            const searchText = this.value.toLowerCase();
            const rows = document.querySelectorAll('#internsTable tr');
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(searchText) ? '' : 'none';
            });
        });
    </script>
</body>
</html>  

