<?php
session_start();
include 'db_connect.php';

// Check if user is logged in and is admin
/*if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}*/

//$admin_id = $_SESSION['user_id'];//
$message = "";

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['assign_supervisor'])) {
        // Manual assignment
        $intern_ids = $_POST['intern_ids'] ?? [];
        $supervisor_id = $_POST['supervisor_id'];
        $assignment_date = date('Y-m-d');
        
        if (empty($intern_ids)) {
            $message = "<div class='alert error'>✗ Please select at least one intern</div>";
        } elseif (empty($supervisor_id)) {
            $message = "<div class='alert error'>✗ Please select a supervisor</div>";
        } else {
            $success_count = 0;
            $error_count = 0;
            
            foreach ($intern_ids as $intern_id) {
                // Check if intern already has a supervisor
                // Assuming your table has a 'supervisor' column instead of 'supervisor_id'
                $check_sql = "SELECT supervisor FROM users WHERE id = ?";
                $check_stmt = $conn->prepare($check_sql);
                $check_stmt->bind_param("i", $intern_id);
                $check_stmt->execute();
                $check_result = $check_stmt->get_result();
                $intern = $check_result->fetch_assoc();
                
                if (!empty($intern['supervisor'])) {
                    $error_count++;
                } else {
                    // Assign supervisor
                    $update_sql = "UPDATE users SET supervisor = ? WHERE id = ?";
                    $update_stmt = $conn->prepare($update_sql);
                    $update_stmt->bind_param("ii", $supervisor_id, $intern_id);
                    
                    if ($update_stmt->execute()) {
                        // Log assignment
                        $log_sql = "INSERT INTO audit_logs (user_id, action, table_name, record_id, new_value) 
                                   VALUES (?, 'update', 'users', ?, ?)";
                        $log_stmt = $conn->prepare($log_sql);
                        $log_message = "Assigned to supervisor ID: $supervisor_id";
                        $log_stmt->bind_param("iis", $admin_id, $intern_id, $log_message);
                        $log_stmt->execute();
                        $log_stmt->close();
                        
                        $success_count++;
                    } else {
                        $error_count++;
                    }
                    $update_stmt->close();
                }
                $check_stmt->close();
            }
            
            if ($success_count > 0) {
                $message = "<div class='alert success'>✓ Successfully assigned $success_count intern(s)</div>";
                if ($error_count > 0) {
                    $message .= "<div class='alert warning'>⚠ $error_count intern(s) already had supervisors</div>";
                }
            } else {
                $message = "<div class='alert error'>✗ No interns were assigned</div>";
            }
        }
    }
    
    if (isset($_POST['reassign_supervisor'])) {
        // Reassignment
        $intern_id = $_POST['intern_id'];
        $new_supervisor_id = $_POST['new_supervisor_id'];
        $reason = trim($_POST['reason']);
        
        // Get old supervisor
        $old_sql = "SELECT supervisor FROM users WHERE id = ?";
        $old_stmt = $conn->prepare($old_sql);
        $old_stmt->bind_param("i", $intern_id);
        $old_stmt->execute();
        $old_result = $old_stmt->get_result();
        $intern = $old_result->fetch_assoc();
        $old_supervisor_id = $intern['supervisor'];
        $old_stmt->close();
        
        // Update supervisor
        $update_sql = "UPDATE users SET supervisor = ? WHERE id = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("ii", $new_supervisor_id, $intern_id);
        
        if ($update_stmt->execute()) {
            // Log reassignment
            $log_sql = "INSERT INTO audit_logs (user_id, action, table_name, record_id, old_value, new_value) 
                       VALUES (?, 'update', 'users', ?, ?, ?)";
            $log_stmt = $conn->prepare($log_sql);
            $log_old = "Supervisor ID: $old_supervisor_id";
            $log_new = "Reassigned to Supervisor ID: $new_supervisor_id. Reason: $reason";
            $log_stmt->bind_param("iiss", $admin_id, $intern_id, $log_old, $log_new);
            $log_stmt->execute();
            $log_stmt->close();
            
            $message = "<div class='alert success'>✓ Intern reassigned successfully</div>";
        } else {
            $message = "<div class='alert error'>✗ Error: " . $conn->error . "</div>";
        }
        $update_stmt->close();
    }
    
    if (isset($_POST['auto_assign'])) {
        // Auto-assignment based on rules
        $assignment_rule = $_POST['assignment_rule'];
        $department_id = $_POST['department_id'] ?? null;
        
        // Get unassigned interns - FIXED: using 'supervisor' column instead of 'supervisor_id'
        $intern_sql = "SELECT id, department_id FROM users WHERE role = 'intern' AND status = 'approved' AND (supervisor IS NULL OR supervisor = '')";
        if ($department_id) {
            $intern_sql .= " AND department_id = ?";
        }
        $intern_stmt = $conn->prepare($intern_sql);
        if ($department_id) {
            $intern_stmt->bind_param("i", $department_id);
        }
        $intern_stmt->execute();
        $interns_result = $intern_stmt->get_result();
        $unassigned_interns = [];
        while($intern = $interns_result->fetch_assoc()) {
            $unassigned_interns[] = $intern;
        }
        $intern_stmt->close();
        
        // Get available supervisors - FIXED: using 'supervisor' column in subquery
        $supervisor_sql = "SELECT id, name, department_id, 
                          (SELECT COUNT(*) FROM users WHERE supervisor = users.id) as current_load 
                          FROM users WHERE role = 'supervisor' AND status = 'active'";
        if ($department_id) {
            $supervisor_sql .= " AND department_id = ?";
        }
        $supervisor_sql .= " ORDER BY current_load ASC";
        
        $supervisor_stmt = $conn->prepare($supervisor_sql);
        if ($department_id) {
            $supervisor_stmt->bind_param("i", $department_id);
        }
        $supervisor_stmt->execute();
        $supervisors_result = $supervisor_stmt->get_result();
        $supervisors = [];
        while($supervisor = $supervisors_result->fetch_assoc()) {
            $supervisors[] = $supervisor;
        }
        $supervisor_stmt->close();
        
        if (empty($unassigned_interns)) {
            $message = "<div class='alert warning'>⚠ No unassigned interns found</div>";
        } elseif (empty($supervisors)) {
            $message = "<div class='alert error'>✗ No available supervisors found</div>";
        } else {
            $assignments_made = 0;
            
            foreach ($unassigned_interns as $intern) {
                $assigned = false;
                
                if ($assignment_rule == 'load_balance') {
                    // Load balancing: assign to supervisor with least interns
                    usort($supervisors, function($a, $b) {
                        return $a['current_load'] <=> $b['current_load'];
                    });
                    
                    foreach ($supervisors as &$supervisor) {
                        if ($supervisor['current_load'] < 10) { // Max 10 interns per supervisor
                            // Assign - FIXED: using 'supervisor' column
                            $update_sql = "UPDATE users SET supervisor = ? WHERE id = ?";
                            $update_stmt = $conn->prepare($update_sql);
                            $update_stmt->bind_param("ii", $supervisor['id'], $intern['id']);
                            
                            if ($update_stmt->execute()) {
                                $supervisor['current_load']++;
                                $assignments_made++;
                                $assigned = true;
                            
                                // Log
                                $log_sql = "INSERT INTO audit_logs (user_id, action, table_name, record_id, new_value) 
                                           VALUES (?, 'update', 'users', ?, ?)";
                                $log_stmt = $conn->prepare($log_sql);
                                $log_msg = "Auto-assigned to Supervisor ID: {$supervisor['id']} (Load Balancing)";
                                $log_stmt->bind_param("iis", $admin_id, $intern['id'], $log_msg);
                                $log_stmt->execute();
                                $log_stmt->close();
                                $update_stmt->close();
                                break;
                            }
                        }
                    } // End of foreach supervisors
                } // End of if load_balance
                
                if (!$assigned && $assignment_rule == 'department_match') {
                    // Department matching logic
                    foreach ($supervisors as &$supervisor) {
                        if ($supervisor['department_id'] == $intern['department_id'] && $supervisor['current_load'] < 10) {
                            $update_sql = "UPDATE users SET supervisor = ? WHERE id = ?";
                            $update_stmt = $conn->prepare($update_sql);
                            $update_stmt->bind_param("ii", $supervisor['id'], $intern['id']);
                            
                            if ($update_stmt->execute()) {
                                $supervisor['current_load']++;
                                $assignments_made++;
                                $assigned = true;
                                
                                // Log
                                $log_sql = "INSERT INTO audit_logs (user_id, action, table_name, record_id, new_value) 
                                           VALUES (?, 'update', 'users', ?, ?)";
                                $log_stmt = $conn->prepare($log_sql);
                                $log_msg = "Auto-assigned to Supervisor ID: {$supervisor['id']} (Department Match)";
                                $log_stmt->bind_param("iis", $admin_id, $intern['id'], $log_msg);
                                $log_stmt->execute();
                                $log_stmt->close();
                                $update_stmt->close();
                                break;
                            }
                        }
                    } // End of foreach supervisors for department_match
                } // End of if department_match
            } // End of foreach unassigned_interns
            
            if ($assignments_made > 0) {
                $message = "<div class='alert success'>✓ Auto-assigned $assignments_made intern(s) successfully</div>";
            } else {
                $message = "<div class='alert warning'>⚠ No assignments were made with the selected criteria</div>";
            }
        } // End of else (when both interns and supervisors exist)
    } // End of if auto_assign
} // End of if POST method

// Get statistics
$stats = [];

// Total interns
$sql = "SELECT COUNT(*) as total FROM users WHERE role = 'intern' AND status = 'approved'";
$result = $conn->query($sql);
if ($result) {
    $stats['total_interns'] = $result->fetch_assoc()['total'];
} else {
    $stats['total_interns'] = 0;
}

// Unassigned interns - FIXED: using 'supervisor' column
$sql = "SELECT COUNT(*) as total FROM users WHERE role = 'intern' AND status = 'approved' ";
$result = $conn->query($sql);
if ($result) {
    $stats['unassigned'] = $result->fetch_assoc()['total'];
} else {
    $stats['unassigned'] = 0;
}

// Total supervisors
$sql = "SELECT COUNT(*) as total FROM users WHERE role = 'supervisor' AND status = 'active'";
$result = $conn->query($sql);
if ($result) {
    $stats['total_supervisors'] = $result->fetch_assoc()['total'];
} else {
    $stats['total_supervisors'] = 0;
}

// Average load per supervisor - FIXED: using 'supervisor' column
$sql = "SELECT AVG(load_count) as avg_load FROM (
        SELECT COUNT(*) as load_count FROM users WHERE role = 'intern' 
    ) as loads";
$result = $conn->query($sql);
if ($result) {
    $avg = $result->fetch_assoc()['avg_load'];
    $stats['avg_load'] = round($avg ?? 0, 1);
} else {
    $stats['avg_load'] = 0;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Intern Supervisor Assignment</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; padding: 20px; }
        .container { max-width: 1200px; margin: 0 auto; background: white; border-radius: 15px; box-shadow: 0 20px 60px rgba(0,0,0,0.3); overflow: hidden; }
        header { background: linear-gradient(to right, #4e54c8, #8f94fb); color: white; padding: 30px; text-align: center; }
        h1 { font-size: 2.5em; margin-bottom: 10px; }
        .subtitle { font-size: 1.2em; opacity: 0.9; }
        .content { padding: 30px; display: grid; grid-template-columns: repeat(auto-fit, minmax(350px, 1fr)); gap: 30px; }
        .card { background: #f8f9fa; border-radius: 10px; padding: 25px; box-shadow: 0 5px 15px rgba(0,0,0,0.08); border: 1px solid #e9ecef; transition: transform 0.3s ease; }
        .card:hover { transform: translateY(-5px); }
        .card h2 { color: #4e54c8; margin-bottom: 20px; padding-bottom: 10px; border-bottom: 2px solid #4e54c8; font-size: 1.5em; }
        .form-group { margin-bottom: 20px; }
        label { display: block; margin-bottom: 8px; color: #495057; font-weight: 600; }
        select, input, textarea { width: 100%; padding: 12px 15px; border: 2px solid #dee2e6; border-radius: 8px; font-size: 16px; transition: border-color 0.3s; }
        select:focus, input:focus, textarea:focus { outline: none; border-color: #4e54c8; }
        select[multiple] { height: 150px; }
        .checkbox-group { max-height: 200px; overflow-y: auto; border: 1px solid #dee2e6; padding: 15px; border-radius: 8px; }
        .checkbox-item { margin-bottom: 10px; display: flex; align-items: center; }
        .checkbox-item input { width: auto; margin-right: 10px; }
        .btn { background: linear-gradient(to right, #4e54c8, #8f94fb); color: white; border: none; padding: 14px 25px; border-radius: 8px; font-size: 16px; font-weight: 600; cursor: pointer; transition: all 0.3s; width: 100%; }
        .btn:hover { background: linear-gradient(to right, #3a3f9c, #6b6fbb); transform: translateY(-2px); box-shadow: 0 7px 14px rgba(74, 84, 200, 0.4); }
        .alert { padding: 15px; border-radius: 8px; margin-bottom: 20px; font-weight: 500; }
        .success { background: #d4edda; color: #155724; border-left: 5px solid #28a745; }
        .error { background: #f8d7da; color: #721c24; border-left: 5px solid #dc3545; }
        .warning { background: #fff3cd; color: #856404; border-left: 5px solid #ffc107; }
        .stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px; }
        .stat-card { background: white; padding: 20px; border-radius: 10px; text-align: center; box-shadow: 0 3px 10px rgba(0,0,0,0.1); border: 1px solid #e9ecef; }
        .stat-value { font-size: 2.5em; font-weight: bold; color: #4e54c8; margin: 10px 0; }
        .stat-label { color: #6c757d; font-size: 0.9em; }
        .nav { display: flex; gap: 10px; margin-bottom: 20px; }
        .nav-btn { padding: 10px 20px; background: #e9ecef; border: none; border-radius: 5px; cursor: pointer; transition: background 0.3s; }
        .nav-btn.active { background: #4e54c8; color: white; }
        .nav-btn:hover { background: #dee2e6; }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1>Supervisor Assignment System</h1>
            <p class="subtitle">Manage intern-supervisor assignments efficiently</p>
        </header>
        
        <div class="content">
            <?php echo $message; ?>
            
            <div class="stats">
                <div class="stat-card">
                    <div class="stat-label">Total Interns</div>
                    <div class="stat-value"><?php echo $stats['total_interns']; ?></div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">Unassigned</div>
                    <div class="stat-value"><?php echo $stats['unassigned']; ?></div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">Supervisors</div>
                    <div class="stat-value"><?php echo $stats['total_supervisors']; ?></div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">Avg Load</div>
                    <div class="stat-value"><?php echo $stats['avg_load']; ?></div>
                </div>
            </div>
            
            <!-- Manual Assignment Card -->
            <div class="card">
                <h2>Manual Assignment</h2>
                <form method="POST" action="">
                    <div class="form-group">
                        <label for="intern_ids">Select Interns (Hold Ctrl/Cmd to select multiple):</label>
                        <select name="intern_ids[]" id="intern_ids" multiple required>
                            <?php
                            $sql = "SELECT id, name, email FROM users WHERE role = 'intern' AND status = 'approved' AND (supervisor IS NULL OR supervisor = '') ORDER BY name";
                            $result = $conn->query($sql);
                            if ($result && $result->num_rows > 0) {
                                while($row = $result->fetch_assoc()) {
                                    echo "<option value='{$row['id']}'>{$row['name']} ({$row['email']})</option>";
                                }
                            } else {
                                echo "<option value='' disabled>No unassigned interns available</option>";
                            }
                            ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="supervisor_id">Select Supervisor:</label>
                        <select name="supervisor_id" id="supervisor_id" required>
                            <option value="">-- Choose Supervisor --</option>
                            <?php
                            $sql = "SELECT id, name, email, department_id FROM users WHERE role = 'supervisor' AND status = 'active' ORDER BY name";
                            $result = $conn->query($sql);
                            if ($result && $result->num_rows > 0) {
                                while($row = $result->fetch_assoc()) {
                                    echo "<option value='{$row['id']}'>{$row['name']} ({$row['email']})</option>";
                                }
                            } else {
                                echo "<option value='' disabled>No supervisors available</option>";
                            }
                            ?>
                        </select>
                    </div>
                    
                    <button type="submit" name="assign_supervisor" class="btn">Assign Selected Interns</button>
                </form>
            </div>
            
            <!-- Auto Assignment Card -->
            <div class="card">
                <h2>Auto Assignment</h2>
                <form method="POST" action="">
                    <div class="form-group">
                        <label for="assignment_rule">Assignment Rule:</label>
                        <select name="assignment_rule" id="assignment_rule" required>
                            <option value="load_balance">Load Balancing (Even Distribution)</option>
                            <option value="department_match">Department Matching</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="department_id">Filter by Department (Optional):</label>
                        <select name="department_id" id="department_id">
                            <option value="">All Departments</option>
                            <?php
                            $sql = "SELECT id, name FROM departments ORDER BY name";
                            $result = $conn->query($sql);
                            if ($result && $result->num_rows > 0) {
                                while($row = $result->fetch_assoc()) {
                                    echo "<option value='{$row['id']}'>{$row['name']}</option>";
                                }
                            }
                            ?>
                        </select>
                    </div>
                    
                    <button type="submit" name="auto_assign" class="btn">Run Auto Assignment</button>
                </form>
            </div>
            
            <!-- Reassignment Card -->
            <div class="card">
                <h2>Reassignment</h2>
                <form method="POST" action="">
                    <div class="form-group">
                        <label for="intern_id">Select Intern to Reassign:</label>
                        <select name="intern_id" id="intern_id" required>
                            <option value="">-- Choose Intern --</option>
                            <?php
                            $sql = "SELECT u.id, u.name, u.email, s.name as supervisor_name 
                                    FROM users u 
                                    LEFT JOIN users s ON u.supervisor = s.id 
                                    WHERE u.role = 'intern' AND u.status = 'approved' AND u.supervisor IS NOT NULL AND u.supervisor != ''
                                    ORDER BY u.name";
                            $result = $conn->query($sql);
                            if ($result && $result->num_rows > 0) {
                                while($row = $result->fetch_assoc()) {
                                    $current = $row['supervisor_name'] ? " (Current: {$row['supervisor_name']})" : "";
                                    echo "<option value='{$row['id']}'>{$row['name']}{$current}</option>";
                                }
                            } else {
                                echo "<option value='' disabled>No assigned interns available</option>";
                            }
                            ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="new_supervisor_id">New Supervisor:</label>
                        <select name="new_supervisor_id" id="new_supervisor_id" required>
                            <option value="">-- Choose New Supervisor --</option>
                            <?php
                            $sql = "SELECT id, name, email FROM users WHERE role = 'supervisor' AND status = 'active' ORDER BY name";
                            $result = $conn->query($sql);
                            if ($result && $result->num_rows > 0) {
                                while($row = $result->fetch_assoc()) {
                                    echo "<option value='{$row['id']}'>{$row['name']} ({$row['email']})</option>";
                                }
                            } else {
                                echo "<option value='' disabled>No supervisors available</option>";
                            }
                            ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="reason">Reason for Reassignment:</label>
                        <textarea name="reason" id="reason" rows="3" placeholder="Briefly explain why this reassignment is necessary..." required></textarea>
                    </div>
                    
                    <button type="submit" name="reassign_supervisor" class="btn">Submit Reassignment</button>
                </form>
            </div>
            
            <!-- Assignment Overview Card -->
            <div class="card">
                <h2>Current Assignments Overview</h2>
                <div style="max-height: 400px; overflow-y: auto;">
                    <table style="width: 100%; border-collapse: collapse;">
                        <thead style="background: #4e54c8; color: white; position: sticky; top: 0;">
                            <tr>
                                <th style="padding: 12px; text-align: left;">Intern</th>
                                <th style="padding: 12px; text-align: left;">Supervisor</th>
                                <th style="padding: 12px; text-align: left;">Department</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $sql = "SELECT u.id, u.name as intern_name, u.email, d.name as dept_name, 
                                   s.name as supervisor_name, s.email as supervisor_email
                                   FROM users u
                                   LEFT JOIN users s ON u.supervisor = s.id
                                   LEFT JOIN departments d ON u.department_id = d.id
                                   WHERE u.role = 'intern' AND u.status = 'approved' AND u.supervisor IS NOT NULL AND u.supervisor != ''
                                   ORDER BY s.name, u.name";
                            $result = $conn->query($sql);
                            $count = 0;
                            if ($result && $result->num_rows > 0) {
                                while($row = $result->fetch_assoc()) {
                                    $bg_color = $count % 2 == 0 ? '#f8f9fa' : '#ffffff';
                                    echo "<tr style='background: {$bg_color};'>
                                            <td style='padding: 10px; border-bottom: 1px solid #dee2e6;'>{$row['intern_name']}<br><small>{$row['email']}</small></td>
                                            <td style='padding: 10px; border-bottom: 1px solid #dee2e6;'>{$row['supervisor_name']}<br><small>{$row['supervisor_email']}</small></td>
                                            <td style='padding: 10px; border-bottom: 1px solid #dee2e6;'>{$row['dept_name']}</td>
                                          </tr>";
                                    $count++;
                                }
                            } else {
                                echo "<tr><td colspan='3' style='padding: 20px; text-align: center; color: #6c757d;'>No assignments found</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</body>
</html>