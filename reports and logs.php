
<?php
session_start();
include 'db_connect.php';
date_default_timezone_set('Africa/Douala');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

$user_name = $_SESSION['Name'] ?? 'Admin';
$active_page = 'reports';

// Handle POST actions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['create_admin'])) {
        $name = trim($_POST['admin_name']);
        $email = trim($_POST['admin_email']);
        $password = password_hash(trim($_POST['admin_password']), PASSWORD_DEFAULT);
        
        $stmt = $conn->prepare("INSERT INTO users (Name, email, password, role, status) VALUES (?, ?, ?, 'admin', 'active')");
        $stmt->bind_param("sss", $name, $email, $password);
        if ($stmt->execute()) {
            $message = 'New admin created successfully!';
        }
        $stmt->close();
    } elseif (isset($_POST['update_dept'])) {
        $id = $_POST['dept_id'];
        $name = trim($_POST['dept_name']);
        $desc = trim($_POST['dept_desc']);
        $stmt = $conn->prepare("UPDATE department SET name = ?, description = ? WHERE id = ?");
        $stmt->bind_param("ssi", $name, $desc, $id);
        $stmt->execute();
        $stmt->close();
        $message = 'Department updated!';
    } elseif (isset($_POST['delete_dept'])) {
        $id = $_POST['dept_id'];
        $conn->query("DELETE FROM department WHERE id = $id");
        $message = 'Department deleted!';
    } elseif (isset($_POST['update_user'])) {
        $id = $_POST['user_id'];
        $name = trim($_POST['user_name']);
        $email = trim($_POST['user_email']);
        $role = $_POST['user_role'];
        $status = $_POST['user_status'];
        $stmt = $conn->prepare("UPDATE users SET Name = ?, email = ?, role = ?, status = ? WHERE id = ?");
        $stmt->bind_param("sssii", $name, $email, $role, $status, $id);
        $stmt->execute();
        $stmt->close();
        $message = 'User updated!';
    } elseif (isset($_POST['delete_user'])) {
        $id = $_POST['user_id'];
        $conn->query("DELETE FROM users WHERE id = $id");
        $message = 'User deleted!';
    } elseif (isset($_POST['approve_app'])) {
        $id = $_POST['app_id'];
        $stmt = $conn->prepare("UPDATE applications SET status = 'approved' WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $message = 'Application approved!';
        $stmt->close();
    } elseif (isset($_POST['reject_app'])) {
        $id = $_POST['app_id'];
        $stmt = $conn->prepare("UPDATE applications SET status = 'rejected' WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $message = 'Application rejected!';
        $stmt->close();
    }
}

// Stats
$total_reports = $conn->query("SELECT COUNT(*) FROM intern_reports")->fetch_row()[0] ?? 0;
$pending_reports = $conn->query("SELECT COUNT(*) FROM intern_reports WHERE status = 'pending'")->fetch_row()[0] ?? 0;
$total_logs = $conn->query("SELECT COUNT(*) FROM audit_logs")->fetch_row()[0] ?? 0;
$total_depts = $conn->query("SELECT COUNT(*) FROM department")->fetch_row()[0] ?? 0;
$total_users = $conn->query("SELECT COUNT(*) FROM users WHERE role != 'intern'")->fetch_row()[0] ?? 0;
$pending_apps = $conn->query("SELECT COUNT(*) FROM applications WHERE status = 'pending'")->fetch_row()[0] ?? 0;
$approved_apps = $conn->query("SELECT COUNT(*) FROM applications WHERE status = 'approved'")->fetch_row()[0] ?? 0;

// Data queries
$depts = $conn->query("SELECT * FROM department ORDER BY name")->fetch_all(MYSQLI_ASSOC);
$users = $conn->query("SELECT id, Name, email, role, status, created_at FROM users WHERE role != 'intern' ORDER BY created_at DESC")->fetch_all(MYSQLI_ASSOC);
$apps = $conn->query("SELECT a.*, i.Name as intern_name FROM applications a LEFT JOIN intern i ON a.intern_id = i.intern_id ORDER BY a.application_date DESC")->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Management Dashboard | CENADI</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <style>
        :root {
            --green: #10b981; --green-light: #d1fae5; --white: #ffffff; --gray-light: #f8fafc; 
            --gray: #64748b; --shadow: 0 10px 30px rgba(16,185,129,0.15);
        }
        body { font-family: 'Inter', sans-serif; background: var(--gray-light); color: #1e293b; }
        .card { border: none; border-radius: 20px; box-shadow: var(--shadow); background: var(--white); }
        .btn-success { background: var(--green); border-color: var(--green); }
        .btn-success:hover { background: #059669; }
        .modal-header { border-bottom: 1px solid var(--gray-light); }
        .stat-card { text-align: center; padding: 2rem; transition: transform 0.3s; }
        .stat-card:hover { transform: translateY(-5px); }
        .nav-tabs .nav-link.active { color: var(--green); border-bottom-color: var(--green); }
    </style>
</head>
<body>
<?php include 'includes/header.php'; include 'includes/sidebar.php'; ?>

<main class="main p-4" style="margin-left: 260px; padding-top: 80px;">
    <div class="container-fluid">
        <?php if (isset($message)): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php echo $message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Stats Row -->
        <div class="row mb-5 g-4">
            <div class="col-md-2">
                <div class="card stat-card">
                    <i class="bi bi-file-text fs-1 text-success mb-3"></i>
                    <div class="h4 fw-bold text-success"><?php echo $total_reports; ?></div>
                    <div class="text-muted">Reports</div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card stat-card">
                    <i class="bi bi-building fs-1 text-success mb-3"></i>
                    <div class="h4 fw-bold text-success"><?php echo $total_depts; ?></div>
                    <div class="text-muted">Departments</div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card stat-card">
                    <i class="bi bi-people fs-1 text-success mb-3"></i>
                    <div class="h4 fw-bold text-success"><?php echo $total_users; ?></div>
                    <div class="text-muted">Users</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stat-card">
                    <div class="row text-center">
                        <div class="col">
                            <div class="h5 fw-bold text-warning"><?php echo $pending_apps; ?></div>
                            <small>Pending Apps</small>
                        </div>
                        <div class="col">
                            <div class="h5 fw-bold text-success"><?php echo $approved_apps; ?></div>
                            <small>Approved</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabs -->
        <ul class="nav nav-tabs mb-4" role="tablist">
            <li class="nav-item"><a class="nav-link active" href="#reports" data-bs-toggle="tab">📊 Intern Reports</a></li>
            <li class="nav-item"><a class="nav-link" href="#logs" data-bs-toggle="tab">📋 Audit Logs</a></li>
            <li class="nav-item"><a class="nav-link" href="#departments" data-bs-toggle="tab">🏢 Departments</a></li>
            <li class="nav-item"><a class="nav-link" href="#users" data-bs-toggle="tab">👥 Users & Admins</a></li>
            <li class="nav-item"><a class="nav-link" href="#applications" data-bs-toggle="tab">📋 Applications</a></li>
        </ul>

        <div class="tab-content">
            <!-- Departments Tab -->
            <div class="tab-pane fade show active" id="departments">
                <div class="card">
                    <div class="card-header d-flex justify-content-between">
                        <h5><i class="bi bi-building me-2"></i>Departments</h5>
                        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#deptModal">
                            <i class="bi bi-plus"></i> Add Department
                        </button>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>Name</th>
                                        <th>Description</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($depts as $dept): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($dept['name']); ?></td>
                                        <td><?php echo htmlspecialchars($dept['description']); ?></td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-primary edit-dept" data-id="<?php echo $dept['id']; ?>" data-name="<?php echo htmlspecialchars($dept['name']); ?>" data-desc="<?php echo htmlspecialchars($dept['description']); ?>">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-danger delete-dept" data-id="<?php echo $dept['id']; ?>">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Users Tab -->
            <div class="tab-pane fade" id="users">
                <div class="card">
                    <div class="card-header d-flex justify-content-between">
                        <h5><i class="bi bi-people me-2"></i>Users & Admins</h5>
                        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#adminModal">
                            <i class="bi bi-person-plus"></i> Create Admin
                        </button>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Role</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($users as $user): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($user['Name']); ?></td>
                                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                                    <td><span class="badge bg-<?php echo $user['role'] == 'admin' ? 'dark' : 'info'; ?>"><?php echo ucfirst($user['role']); ?></span></td>
                                    <td><span class="badge bg-<?php echo $user['status'] == 'active' ? 'success' : 'warning'; ?>"><?php echo ucfirst($user['status']); ?></span></td>
                                    <td><?php echo date('M j', strtotime($user['created_at'])); ?></td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-primary edit-user" data-id="<?php echo $user['id']; ?>" data-name="<?php echo htmlspecialchars($user['Name']); ?>" data-email="<?php echo htmlspecialchars($user['email']); ?>" data-role="<?php echo $user['role']; ?>" data-status="<?php echo $user['status']; ?>">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-danger delete-user" data-id="<?php echo $user['id']; ?>">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Applications Tab -->
            <div class="tab-pane fade" id="applications">
                <div class="card">
                    <div class="card-header">
                        <h5><i class="bi bi-file-earmark-check me-2"></i>Applications Management</h5>
                        <div class="row text-center mt-3">
                            <div class="col-md-3">
                                <div class="h4 text-warning"><?php echo $pending_apps; ?></div>
                                <small>Pending</small>
                            </div>
                            <div class="col-md-3">
                                <div class="h4 text-success"><?php echo $approved_apps; ?></div>
                                <small>Approved</small>
                            </div>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Intern</th>
                                    <th>Position</th>
                                    <th>Department</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($apps as $app): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($app['intern_name']); ?></td>
                                    <td><?php echo htmlspecialchars($app['position']); ?></td>
                                    <td><?php echo htmlspecialchars($app['department']); ?></td>
                                    <td><span class="badge bg-<?php echo $app['status'] == 'approved' ? 'success' : 'warning'; ?>">
                                        <?php echo ucfirst($app['status']); ?>
                                    </span></td>
                                    <td><?php echo isset($app['application_date']) ? date('M j, Y', strtotime($app['application_date'])) : 'N/A'; ?></td>
                                    <td>
                                        <?php if ($app['status'] == 'pending'): ?>
                                            <form method="POST" style="display:inline;">
                                                <input type="hidden" name="app_id" value="<?php echo $app['id']; ?>">
                                                <button type="submit" name="approve_app" class="btn btn-sm btn-success me-1">
                                                    <i class="bi bi-check-lg"></i> Approve
                                                </button>
                                                <button type="submit" name="reject_app" class="btn btn-sm btn-danger">
                                                    <i class="bi bi-x-lg"></i> Reject
                                                </button>
                                            </form>
                                        <?php else: ?>
                                            <span class="text-muted">Handled</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<!-- Modals -->
<!-- Create Admin Modal -->
<div class="modal fade" id="adminModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Create New Admin</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Full Name</label>
                        <input type="text" name="admin_name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="admin_email" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input type="password" name="admin_password" class="form-control" required minlength="6">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="create_admin" class="btn btn-success">Create Admin</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Department Modal -->
<div class="modal fade" id="deptModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Department</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" id="dept_id" name="dept_id">
                    <div class="mb-3">
                        <label class="form-label">Name</label>
                        <input type="text" id="dept_name" name="dept_name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea id="dept_desc" name="dept_desc" class="form-control" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="update_dept" class="btn btn-success">Save</button>
                    <button type="submit" name="delete_dept" class="btn btn-danger" onclick="return confirm('Delete?')">Delete</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- User Modal -->
<div class="modal fade" id="userModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" id="user_id" name="user_id">
                    <div class="mb-3">
                        <label class="form-label">Name</label>
                        <input type="text" id="user_name" name="user_name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" id="user_email" name="user_email" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Role</label>
                        <select id="user_role" name="user_role" class="form-select">
                            <option value="admin">Admin</option>
                            <option value="supervisor">Supervisor</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select id="user_status" name="user_status" class="form-select">
                            <option value="1">Active</option>
                            <option value="0">Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="update_user" class="btn btn-success">Update</button>
                    <button type="submit" name="delete_user" class="btn btn-danger" onclick="return confirm('Delete user?')">Delete</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
<script>
$('.edit-dept').click(function() {
    $('#deptModal').modal('show');
    $('#dept_id').val($(this).data('id'));
    $('#dept_name').val($(this).data('name'));
    $('#dept_desc').val($(this).data('desc'));
});
$('.edit-user').click(function() {
    $('#userModal').modal('show');
    $('#user_id').val($(this).data('id'));
    $('#user_name').val($(this).data('name'));
    $('#user_email').val($(this).data('email'));
    $('#user_role').val($(this).data('role'));
    $('#user_status').val($(this).data('status'));
});
$('.delete-dept, .delete-user').click(function(e) {
    e.preventDefault();
    if (confirm('Are you sure?')) {
        $(this).closest('form').submit();
    }
});
</script>
</body>
</html>

