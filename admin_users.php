<?php
// admin_users.php
session_start();
include 'db_connect.php';

// Check if user is admin
/*if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}*/

$admin_id = $_SESSION['user_id'];
$message = "";

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Add new user
    if (isset($_POST['add_user'])) {
        $name = trim($_POST['name']);
        $email = trim($_POST['email']);
        $password = $_POST['password'];
        $role = $_POST['role'];
        $status = $_POST['status'];
        
        // Validate inputs
        if (empty($name) || empty($email) || empty($password) || empty($role)) {
            $message = "<div class='alert error'>✗ All fields are required</div>";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $message = "<div class='alert error'>✗ Invalid email format</div>";
        } else {
            // Check if email already exists
            $check_sql = "SELECT id FROM users WHERE email = ?";
            $check_stmt = $conn->prepare($check_sql);
            $check_stmt->bind_param("s", $email);
            $check_stmt->execute();
            $check_result = $check_stmt->get_result();
            
            if ($check_result->num_rows > 0) {
                $message = "<div class='alert error'>✗ Email already exists</div>";
            } else {
                // Hash password
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                
                // Insert new user
                $insert_sql = "INSERT INTO users (name, email, password, role, status, created_at) 
                              VALUES (?, ?, ?, ?, ?, NOW())";
                $insert_stmt = $conn->prepare($insert_sql);
                $insert_stmt->bind_param("sssss", $name, $email, $hashed_password, $role, $status);
                
                if ($insert_stmt->execute()) {
                    $new_user_id = $insert_stmt->insert_id;
                    
                    // Log the action
                    $log_sql = "INSERT INTO audit_logs (user_id, action, table_name, record_id, new_value) 
                               VALUES (?, 'insert', 'users', ?, ?)";
                    $log_stmt = $conn->prepare($log_sql);
                    $log_message = "Created new {$role} user: {$name} ({$email})";
                    $log_stmt->bind_param("iis", $admin_id, $new_user_id, $log_message);
                    $log_stmt->execute();
                    $log_stmt->close();
                    
                    $message = "<div class='alert success'>✓ User created successfully</div>";
                } else {
                    $message = "<div class='alert error'>✗ Error: " . $conn->error . "</div>";
                }
                $insert_stmt->close();
            }
            $check_stmt->close();
        }
    }
    
    // Update user
    if (isset($_POST['update_user'])) {
        $user_id = $_POST['user_id'];
        $name = trim($_POST['edit_name']);
        $email = trim($_POST['edit_email']);
        $role = $_POST['edit_role'];
        $status = $_POST['edit_status'];
        
        // Get old values for logging
        $old_sql = "SELECT name, email, role, status FROM users WHERE id = ?";
        $old_stmt = $conn->prepare($old_sql);
        $old_stmt->bind_param("i", $user_id);
        $old_stmt->execute();
        $old_result = $old_stmt->get_result();
        $old_user = $old_result->fetch_assoc();
        $old_stmt->close();
        
        // Update user
        $update_sql = "UPDATE users SET name = ?, email = ?, role = ?, status = ?, updated_at = NOW() WHERE id = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("ssssi", $name, $email, $role, $status, $user_id);
        
        if ($update_stmt->execute()) {
            // Prepare change log
            $changes = [];
            if ($old_user['name'] != $name) $changes[] = "Name: {$old_user['name']} → {$name}";
            if ($old_user['email'] != $email) $changes[] = "Email: {$old_user['email']} → {$email}";
            if ($old_user['role'] != $role) $changes[] = "Role: {$old_user['role']} → {$role}";
            if ($old_user['status'] != $status) $changes[] = "Status: {$old_user['status']} → {$status}";
            
            if (!empty($changes)) {
                $log_sql = "INSERT INTO audit_logs (user_id, action, table_name, record_id, old_value, new_value) 
                           VALUES (?, 'update', 'users', ?, ?, ?)";
                $log_stmt = $conn->prepare($log_sql);
                $log_old = implode(", ", array_map(function($change) {
                    return explode(" → ", $change)[0];
                }, $changes));
                $log_new = implode(", ", $changes);
                $log_stmt->bind_param("iiss", $admin_id, $user_id, $log_old, $log_new);
                $log_stmt->execute();
                $log_stmt->close();
            }
            
            $message = "<div class='alert success'>✓ User updated successfully</div>";
        } else {
            $message = "<div class='alert error'>✗ Error: " . $conn->error . "</div>";
        }
        $update_stmt->close();
    }
    
    // Change password
    if (isset($_POST['change_password'])) {
        $user_id = $_POST['password_user_id'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];
        
        if (empty($new_password)) {
            $message = "<div class='alert error'>✗ Password cannot be empty</div>";
        } elseif ($new_password !== $confirm_password) {
            $message = "<div class='alert error'>✗ Passwords do not match</div>";
        } else {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            
            $update_sql = "UPDATE users SET password = ?, updated_at = NOW() WHERE id = ?";
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bind_param("si", $hashed_password, $user_id);
            
            if ($update_stmt->execute()) {
                $log_sql = "INSERT INTO audit_logs (user_id, action, table_name, record_id, new_value) 
                           VALUES (?, 'update', 'users', ?, ?)";
                $log_stmt = $conn->prepare($log_sql);
                $log_message = "Password changed by admin";
                $log_stmt->bind_param("iis", $admin_id, $user_id, $log_message);
                $log_stmt->execute();
                $log_stmt->close();
                
                $message = "<div class='alert success'>✓ Password changed successfully</div>";
            } else {
                $message = "<div class='alert error'>✗ Error: " . $conn->error . "</div>";
            }
            $update_stmt->close();
        }
    }
    
    // Delete user
    if (isset($_POST['delete_user'])) {
        $user_id = $_POST['delete_user_id'];
        
        // Get user info for logging
        $user_sql = "SELECT name, email FROM users WHERE id = ?";
        $user_stmt = $conn->prepare($user_sql);
        $user_stmt->bind_param("i", $user_id);
        $user_stmt->execute();
        $user_result = $user_stmt->get_result();
        $user = $user_result->fetch_assoc();
        $user_stmt->close();
        
        // Soft delete (update status to deleted)
        $delete_sql = "UPDATE users SET status = 'deleted', deleted_at = NOW() WHERE id = ?";
        $delete_stmt = $conn->prepare($delete_sql);
        $delete_stmt->bind_param("i", $user_id);
        
        if ($delete_stmt->execute()) {
            $log_sql = "INSERT INTO audit_logs (user_id, action, table_name, record_id, old_value) 
                       VALUES (?, 'delete', 'users', ?, ?)";
            $log_stmt = $conn->prepare($log_sql);
            $log_message = "Deleted user: {$user['name']} ({$user['email']})";
            $log_stmt->bind_param("iis", $admin_id, $user_id, $log_message);
            $log_stmt->execute();
            $log_stmt->close();
            
            $message = "<div class='alert success'>✓ User deleted successfully</div>";
        } else {
            $message = "<div class='alert error'>✗ Error: " . $conn->error . "</div>";
        }
        $delete_stmt->close();
    }
    
    // Bulk actions
    if (isset($_POST['bulk_action'])) {
        $user_ids = $_POST['user_ids'] ?? [];
        $action = $_POST['bulk_action_type'];
        
        if (empty($user_ids)) {
            $message = "<div class='alert error'>✗ No users selected</div>";
        } else {
            $success_count = 0;
            $error_count = 0;
            $ids = implode(",", $user_ids);
            
            switch ($action) {
                case 'activate':
                    $update_sql = "UPDATE users SET status = 'active', updated_at = NOW() WHERE id IN ($ids)";
                    break;
                case 'deactivate':
                    $update_sql = "UPDATE users SET status = 'inactive', updated_at = NOW() WHERE id IN ($ids)";
                    break;
                case 'delete':
                    $update_sql = "UPDATE users SET status = 'deleted', deleted_at = NOW() WHERE id IN ($ids)";
                    break;
                default:
                    $update_sql = "";
            }
            
            if ($update_sql && $conn->query($update_sql)) {
                $affected = $conn->affected_rows;
                $message = "<div class='alert success'>✓ {$action}d {$affected} user(s) successfully</div>";
                
                // Log bulk action
                $log_sql = "INSERT INTO audit_logs (user_id, action, table_name, new_value) 
                           VALUES (?, 'bulk_update', 'users', ?)";
                $log_stmt = $conn->prepare($log_sql);
                $log_message = "Bulk {$action} for user IDs: {$ids}";
                $log_stmt->bind_param("is", $admin_id, $log_message);
                $log_stmt->execute();
                $log_stmt->close();
            } else {
                $message = "<div class='alert error'>✗ Error processing bulk action</div>";
            }
        }
    }
}

// Get filter parameters
$role_filter = $_GET['role'] ?? '';
$status_filter = $_GET['status'] ?? '';
$search = $_GET['search'] ?? '';

// Build query with filters
$sql = "SELECT * FROM users WHERE status != 'deleted'";

$params = [];
$types = "";

if (!empty($role_filter)) {
    $sql .= " AND role = ?";
    $params[] = $role_filter;
    $types .= "s";
}

if (!empty($status_filter)) {
    $sql .= " AND status = ?";
    $params[] = $status_filter;
    $types .= "s";
}

if (!empty($search)) {
    $sql .= " AND (name LIKE ? OR email LIKE ?)";
    $search_term = "%$search%";
    $params[] = $search_term;
    $params[] = $search_term;
    $types .= "ss";
}

$sql .= " ORDER BY created_at DESC";

// Prepare and execute query
$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f5f7fa; min-height: 100vh; }
        .container { max-width: 1400px; margin: 0 auto; padding: 20px; }
        header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; border-radius: 15px; margin-bottom: 30px; box-shadow: 0 10px 30px rgba(0,0,0,0.2); }
        h1 { font-size: 2.5em; margin-bottom: 10px; }
        .subtitle { font-size: 1.2em; opacity: 0.9; }
        
        /* Navigation */
        .nav { display: flex; gap: 10px; margin-bottom: 20px; }
        .nav-btn { padding: 10px 20px; background: #e9ecef; border: none; border-radius: 8px; cursor: pointer; transition: all 0.3s; text-decoration: none; color: #495057; font-weight: 500; }
        .nav-btn.active { background: #4e54c8; color: white; }
        .nav-btn:hover { background: #dee2e6; transform: translateY(-2px); }
        
        /* Stats */
        .stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px; }
        .stat-card { background: white; padding: 25px; border-radius: 12px; text-align: center; box-shadow: 0 5px 15px rgba(0,0,0,0.08); border: 1px solid #e9ecef; transition: transform 0.3s ease; }
        .stat-card:hover { transform: translateY(-5px); }
        .stat-value { font-size: 2.8em; font-weight: bold; color: #4e54c8; margin: 15px 0; }
        .stat-label { color: #6c757d; font-size: 1em; font-weight: 500; }
        
        /* Alerts */
        .alert { padding: 15px 20px; border-radius: 10px; margin-bottom: 25px; font-weight: 500; display: flex; align-items: center; gap: 10px; }
        .success { background: linear-gradient(135deg, #d4edda, #c3e6cb); color: #155724; border-left: 5px solid #28a745; }
        .error { background: linear-gradient(135deg, #f8d7da, #f5c6cb); color: #721c24; border-left: 5px solid #dc3545; }
        .warning { background: linear-gradient(135deg, #fff3cd, #ffeaa7); color: #856404; border-left: 5px solid #ffc107; }
        
        /* Filters */
        .filters { background: white; padding: 25px; border-radius: 12px; margin-bottom: 25px; box-shadow: 0 5px 15px rgba(0,0,0,0.05); }
        .filter-form { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; }
        .form-group { margin-bottom: 0; }
        label { display: block; margin-bottom: 8px; color: #495057; font-weight: 600; }
        select, input { width: 100%; padding: 12px 15px; border: 2px solid #e9ecef; border-radius: 8px; font-size: 15px; transition: all 0.3s; background: white; }
        select:focus, input:focus { outline: none; border-color: #4e54c8; box-shadow: 0 0 0 3px rgba(78, 84, 200, 0.1); }
        
        /* Buttons */
        .btn { padding: 12px 25px; border: none; border-radius: 8px; font-size: 16px; font-weight: 600; cursor: pointer; transition: all 0.3s; display: inline-flex; align-items: center; justify-content: center; gap: 8px; }
        .btn-primary { background: linear-gradient(135deg, #4e54c8, #8f94fb); color: white; }
        .btn-primary:hover { background: linear-gradient(135deg, #3a3f9c, #6b6fbb); transform: translateY(-2px); box-shadow: 0 7px 20px rgba(78, 84, 200, 0.3); }
        .btn-secondary { background: #6c757d; color: white; }
        .btn-secondary:hover { background: #545b62; }
        .btn-danger { background: linear-gradient(135deg, #dc3545, #e35d6a); color: white; }
        .btn-danger:hover { background: linear-gradient(135deg, #c82333, #d64552); }
        .btn-success { background: linear-gradient(135deg, #28a745, #5cb85c); color: white; }
        .btn-success:hover { background: linear-gradient(135deg, #218838, #4cae4c); }
        
        /* Tables */
        .table-container { background: white; border-radius: 12px; overflow: hidden; box-shadow: 0 5px 25px rgba(0,0,0,0.1); }
        table { width: 100%; border-collapse: collapse; }
        thead { background: linear-gradient(135deg, #4e54c8, #8f94fb); color: white; }
        th { padding: 18px 15px; text-align: left; font-weight: 600; font-size: 15px; }
        tbody tr { border-bottom: 1px solid #e9ecef; transition: background 0.3s; }
        tbody tr:hover { background: #f8f9fa; }
        td { padding: 16px 15px; color: #495057; }
        
        /* Badges */
        .badge { padding: 6px 12px; border-radius: 20px; font-size: 12px; font-weight: 600; display: inline-block; }
        .badge-admin { background: #4e54c8; color: white; }
        .badge-supervisor { background: #17a2b8; color: white; }
        .badge-intern { background: #28a745; color: white; }
        .badge-active { background: #d4edda; color: #155724; }
        .badge-inactive { background: #fff3cd; color: #856404; }
        .badge-pending { background: #cce5ff; color: #004085; }
        .badge-deleted { background: #f8d7da; color: #721c24; }
        
        /* Modal */
        .modal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center; }
        .modal-content { background: white; border-radius: 15px; width: 90%; max-width: 500px; max-height: 90vh; overflow-y: auto; box-shadow: 0 25px 50px rgba(0,0,0,0.3); }
        .modal-header { background: linear-gradient(135deg, #4e54c8, #8f94fb); color: white; padding: 25px; border-radius: 15px 15px 0 0; display: flex; justify-content: space-between; align-items: center; }
        .modal-header h3 { margin: 0; font-size: 1.5em; }
        .close-modal { background: none; border: none; color: white; font-size: 24px; cursor: pointer; }
        .modal-body { padding: 25px; }
        .modal-footer { padding: 20px 25px; background: #f8f9fa; border-radius: 0 0 15px 15px; display: flex; justify-content: flex-end; gap: 10px; }
        
        /* Cards */
        .card { background: white; border-radius: 12px; padding: 25px; box-shadow: 0 5px 15px rgba(0,0,0,0.05); margin-bottom: 25px; }
        .card h3 { color: #4e54c8; margin-bottom: 20px; padding-bottom: 15px; border-bottom: 2px solid #e9ecef; }
        
        /* Utilities */
        .text-center { text-align: center; }
        .mb-3 { margin-bottom: 15px; }
        .mb-4 { margin-bottom: 20px; }
        .mt-4 { margin-top: 20px; }
        .flex { display: flex; }
        .gap-2 { gap: 10px; }
        .gap-3 { gap: 15px; }
        .justify-between { justify-content: space-between; }
        .align-center { align-items: center; }
        .w-100 { width: 100%; }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1><i class="fas fa-users-cog"></i> User Management</h1>
            <p class="subtitle">Manage all users, roles, and permissions</p>
        </header>
        
        <!-- Navigation -->
        <div class="nav">
            <a href="dashboard.php" class="nav-btn"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
            <a href="admin_users.php" class="nav-btn active"><i class="fas fa-users-cog"></i> User Management</a>
            <a href="Adminassignment.php" class="nav-btn"><i class="fas fa-user-graduate"></i> Intern Assignment</a>
            <a href="logout.php" class="nav-btn" style="margin-left: auto;"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
        
        <?php echo $message; ?>
        
        <!-- Statistics -->
        <div class="stats">
            <?php
            // Get user statistics
            $stats_sql = "SELECT 
                COUNT(*) as total_users,
                SUM(role = 'admin') as admins,
                SUM(role = 'supervisor') as supervisors,
                SUM(role = 'intern') as interns,
                SUM(status = 'active') as active_users,
                SUM(status = 'pending') as pending_users,
                SUM(status = 'inactive') as inactive_users
                FROM users WHERE status != 'deleted'";
            $stats_result = $conn->query($stats_sql);
            $stats = $stats_result->fetch_assoc();
            ?>
            <div class="stat-card">
                <div class="stat-label">Total Users</div>
                <div class="stat-value"><?php echo $stats['total_users']; ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Admins</div>
                <div class="stat-value"><?php echo $stats['admins']; ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Supervisors</div>
                <div class="stat-value"><?php echo $stats['supervisors']; ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Interns</div>
                <div class="stat-value"><?php echo $stats['interns']; ?></div>
            </div>
        </div>
        
        <!-- Action Buttons -->
        <div class="card">
            <div class="flex justify-between align-center">
                <h3>User Management</h3>
                <div class="flex gap-2">
                    <button class="btn btn-primary" onclick="openAddUserModal()">
                        <i class="fas fa-user-plus"></i> Add New User
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Filters -->
        <div class="filters">
            <form method="GET" class="filter-form">
                <div class="form-group">
                    <label>Role</label>
                    <select name="role">
                        <option value="">All Roles</option>
                        <option value="admin" <?php echo $role_filter == 'admin' ? 'selected' : ''; ?>>Admin</option>
                        <option value="supervisor" <?php echo $role_filter == 'supervisor' ? 'selected' : ''; ?>>Supervisor</option>
                        <option value="intern" <?php echo $role_filter == 'intern' ? 'selected' : ''; ?>>Intern</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Status</label>
                    <select name="status">
                        <option value="">All Status</option>
                        <option value="active" <?php echo $status_filter == 'active' ? 'selected' : ''; ?>>Active</option>
                        <option value="inactive" <?php echo $status_filter == 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                        <option value="pending" <?php echo $status_filter == 'pending' ? 'selected' : ''; ?>>Pending</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Search</label>
                    <input type="text" name="search" placeholder="Search by name or email..." value="<?php echo htmlspecialchars($search); ?>">
                </div>
                <div class="form-group" style="align-self: flex-end;">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-filter"></i> Apply Filters
                    </button>
                </div>
            </form>
        </div>
        
        <!-- Bulk Actions -->
        <form method="POST" id="bulkForm">
            <div class="flex gap-3 mb-4">
                <select name="bulk_action_type" style="width: 200px; padding: 12px; border-radius: 8px; border: 2px solid #e9ecef;">
                    <option value="">Bulk Actions</option>
                    <option value="activate">Activate Selected</option>
                    <option value="deactivate">Deactivate Selected</option>
                    <option value="delete">Delete Selected</option>
                </select>
                <button type="submit" name="bulk_action" class="btn btn-secondary">
                    <i class="fas fa-play"></i> Apply
                </button>
                <span id="selectedCount" style="align-self: center; margin-left: auto; color: #6c757d;">
                    0 users selected
                </span>
            </div>
        
            <!-- Users Table -->
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th width="50"><input type="checkbox" id="selectAll"></th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Created</th>
                            <th>Last Login</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result->num_rows > 0): ?>
                            <?php while($user = $result->fetch_assoc()): ?>
                                <tr>
                                    <td><input type="checkbox" name="user_ids[]" value="<?php echo $user['id']; ?>" class="user-checkbox"></td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($user['Name']); ?></strong>
                                        <?php if ($user['id'] == $admin_id): ?>
                                            <span class="badge" style="background: #ffc107; margin-left: 5px;">You</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                                    <td>
                                        <?php
                                        // Determine badge class based on role
                                        $badge_class = 'badge-';
                                        switch($user['role']) {
                                            case 'admin': $badge_class .= 'admin'; break;
                                            case 'supervisor': $badge_class .= 'supervisor'; break;
                                            case 'intern': $badge_class .= 'intern'; break;
                                            default: $badge_class .= 'intern';
                                        }
                                        ?>
                                        <span class="badge <?php echo $badge_class; ?>">
                                            <?php echo ucfirst($user['role']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php
                                        // Determine status badge class
                                        $status_class = 'badge-';
                                        switch($user['STATUS']) {
                                            case 'active': $status_class .= 'active'; break;
                                            case 'inactive': $status_class .= 'inactive'; break;
                                            case 'pending': $status_class .= 'pending'; break;
                                            case 'deleted': $status_class .= 'deleted'; break;
                                            default: $status_class .= 'active';
                                        }
                                        ?>
                                        <span class="badge <?php echo $status_class; ?>">
                                            <?php echo ucfirst($user['STATUS']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                                    <td>
                                        <?php if (!empty($user['last_login'])): ?>
                                            <?php echo date('M d, Y H:i', strtotime($user['last_login'])); ?>
                                        <?php else: ?>
                                            Never
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="flex gap-2">
                                            <button type="button" class="btn btn-secondary" style="padding: 6px 12px; font-size: 12px;" onclick="openEditModal(<?php echo $user['id']; ?>, '<?php echo addslashes($user['name']); ?>', '<?php echo addslashes($user['email']); ?>', '<?php echo $user['role']; ?>', '<?php echo $user['STATUS']; ?>')">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button type="button" class="btn btn-success" style="padding: 6px 12px; font-size: 12px;" onclick="openPasswordModal(<?php echo $user['id']; ?>)">
                                                <i class="fas fa-key"></i>
                                            </button>
                                            <?php if ($user['id'] != $admin_id): ?>
                                                <button type="button" class="btn btn-danger" style="padding: 6px 12px; font-size: 12px;" onclick="confirmDelete(<?php echo $user['id']; ?>)">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" class="text-center" style="padding: 40px; color: #6c757d;">
                                    <i class="fas fa-users fa-3x mb-3" style="color: #dee2e6;"></i>
                                    <p>No users found</p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </form>
    </div>
    
    <!-- Add User Modal -->
    <div id="addUserModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3><i class="fas fa-user-plus"></i> Add New User</h3>
                <button class="close-modal" onclick="closeAddUserModal()">&times;</button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <div class="form-group mb-3">
                        <label>Full Name *</label>
                        <input type="text" name="name" required>
                    </div>
                    <div class="form-group mb-3">
                        <label>Email Address *</label>
                        <input type="email" name="email" required>
                    </div>
                    <div class="form-group mb-3">
                        <label>Password *</label>
                        <input type="password" name="password" required>
                    </div>
                    <div class="form-group mb-3">
                        <label>Role *</label>
                        <select name="role" required>
                            <option value="admin">Admin</option>
                            <option value="supervisor">Supervisor</option>
                            <option value="intern" selected>Intern</option>
                        </select>
                    </div>
                    <div class="form-group mb-3">
                        <label>Status *</label>
                        <select name="status" required>
                            <option value="active" selected>Active</option>
                            <option value="inactive">Inactive</option>
                            <option value="pending">Pending</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeAddUserModal()">Cancel</button>
                    <button type="submit" name="add_user" class="btn btn-primary">Create User</button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Edit User Modal -->
    <div id="editUserModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3><i class="fas fa-user-edit"></i> Edit User</h3>
                <button class="close-modal" onclick="closeEditModal()">&times;</button>
            </div>
            <form method="POST">
                <input type="hidden" name="user_id" id="edit_user_id">
                <div class="modal-body">
                    <div class="form-group mb-3">
                        <label>Full Name *</label>
                        <input type="text" name="edit_name" id="edit_name" required>
                    </div>
                    <div class="form-group mb-3">
                        <label>Email Address *</label>
                        <input type="email" name="edit_email" id="edit_email" required>
                    </div>
                    <div class="form-group mb-3">
                        <label>Role *</label>
                        <select name="edit_role" id="edit_role" required>
                            <option value="admin">Admin</option>
                            <option value="supervisor">Supervisor</option>
                            <option value="intern">Intern</option>
                        </select>
                    </div>
                    <div class="form-group mb-3">
                        <label>Status *</label>
                        <select name="edit_status" id="edit_status" required>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                            <option value="pending">Pending</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeEditModal()">Cancel</button>
                    <button type="submit" name="update_user" class="btn btn-primary">Update User</button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Change Password Modal -->
    <div id="passwordModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3><i class="fas fa-key"></i> Change Password</h3>
                <button class="close-modal" onclick="closePasswordModal()">&times;</button>
            </div>
            <form method="POST">
                <input type="hidden" name="password_user_id" id="password_user_id">
                <div class="modal-body">
                    <div class="form-group mb-3">
                        <label>New Password *</label>
                        <input type="password" name="new_password" id="new_password" required>
                    </div>
                    <div class="form-group mb-3">
                        <label>Confirm Password *</label>
                        <input type="password" name="confirm_password" id="confirm_password" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closePasswordModal()">Cancel</button>
                    <button type="submit" name="change_password" class="btn btn-primary">Change Password</button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Delete Confirmation Modal -->
    <div id="deleteModal" class="modal">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, #dc3545, #e35d6a);">
                <h3><i class="fas fa-exclamation-triangle"></i> Confirm Delete</h3>
                <button class="close-modal" onclick="closeDeleteModal()">&times;</button>
            </div>
            <form method="POST">
                <input type="hidden" name="delete_user_id" id="delete_user_id">
                <div class="modal-body">
                    <p style="font-size: 16px; line-height: 1.6;">Are you sure you want to delete this user? This action cannot be undone.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeDeleteModal()">Cancel</button>
                    <button type="submit" name="delete_user" class="btn btn-danger">Delete User</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Modal Functions
        function openAddUserModal() {
            document.getElementById('addUserModal').style.display = 'flex';
        }
        
        function closeAddUserModal() {
            document.getElementById('addUserModal').style.display = 'none';
        }
        
        function openEditModal(userId, name, email, role, status) {
            document.getElementById('edit_user_id').value = userId;
            document.getElementById('edit_name').value = name;
            document.getElementById('edit_email').value = email;
            document.getElementById('edit_role').value = role;
            document.getElementById('edit_status').value = status;
            document.getElementById('editUserModal').style.display = 'flex';
        }
        
        function closeEditModal() {
            document.getElementById('editUserModal').style.display = 'none';
        }
        
        function openPasswordModal(userId) {
            document.getElementById('password_user_id').value = userId;
            document.getElementById('passwordModal').style.display = 'flex';
        }
        
        function closePasswordModal() {
            document.getElementById('passwordModal').style.display = 'none';
        }
        
        function confirmDelete(userId) {
            document.getElementById('delete_user_id').value = userId;
            document.getElementById('deleteModal').style.display = 'flex';
        }
        
        function closeDeleteModal() {
            document.getElementById('deleteModal').style.display = 'none';
        }
        
        // Close modals when clicking outside
        window.onclick = function(event) {
            if (event.target.classList.contains('modal')) {
                event.target.style.display = 'none';
            }
        }
        
        // Bulk selection
        document.getElementById('selectAll').addEventListener('change', function() {
            var checkboxes = document.querySelectorAll('.user-checkbox');
            for (var i = 0; i < checkboxes.length; i++) {
                checkboxes[i].checked = this.checked;
            }
            updateSelectedCount();
        });
        
        // Update selected count
        function updateSelectedCount() {
            var checkboxes = document.querySelectorAll('.user-checkbox');
            var selected = 0;
            for (var i = 0; i < checkboxes.length; i++) {
                if (checkboxes[i].checked) selected++;
            }
            document.getElementById('selectedCount').textContent = selected + ' users selected';
        }
        
        // Add event listeners to checkboxes
        document.addEventListener('DOMContentLoaded', function() {
            var checkboxes = document.querySelectorAll('.user-checkbox');
            for (var i = 0; i < checkboxes.length; i++) {
                checkboxes[i].addEventListener('change', updateSelectedCount);
            }
            updateSelectedCount();
        });
        
        // Export functionality
        function exportUsers() {
            alert('Export feature would be implemented here. This would typically generate a CSV or Excel file.');
        }
    </script>
</body>
</html>