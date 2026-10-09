<?php
require_once 'config.php';

// Handle delete intern
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    try {
        // First delete related assignments
        $stmt = $pdo->prepare("DELETE FROM assignments WHERE intern_id = ?");
        $stmt->execute([$id]);
        
        // Then delete intern
        $stmt = $pdo->prepare("DELETE FROM intern WHERE intern_id = ?");
        $stmt->execute([$id]);
        
        header('Location: assign_supervisor.php');
        exit;
    } catch(PDOException $e) {
        $error = "Error deleting intern: " . $e->getMessage();
    }
}

// Handle assign supervisor
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['assign_intern'])) {
    $intern_id = $_POST['intern_id'];
    $supervisor_id = $_POST['supervisor_id'];
    $assigned_date = date('Y-m-d');
    
    try {
        // Check if already assigned
        $check = $pdo->prepare("SELECT id FROM assignments WHERE intern_id = ? AND status = 'active'");
        $check->execute([$intern_id]);
        
        if ($check->rowCount() > 0) {
            $error = "This intern is already assigned to a supervisor!";
        } else {
            // Check if supervisor has capacity
            $capacity = $pdo->prepare("SELECT COUNT(*) as count FROM assignments WHERE supervisor_id = ? AND status = 'active'");
            $capacity->execute([$supervisor_id]);
            $capacity_result = $capacity->fetch();
            $current = ($capacity_result && isset($capacity_result['count'])) ? $capacity_result['count'] : 0;
            
            // Get supervisor's max interns
            $max = $pdo->prepare("SELECT max_interns FROM supervisors WHERE id = ?");
            $max->execute([$supervisor_id]);
            $max_result = $max->fetch();
            $max_interns = ($max_result && isset($max_result['max_interns'])) ? $max_result['max_interns'] : 5;
            
            if ($current >= $max_interns) {
                $error = "This supervisor has reached maximum capacity!";
            } else {
                // Assign intern
                $stmt = $pdo->prepare("INSERT INTO assignments (intern_id, supervisor_id, assigned_date, status) VALUES (?, ?, ?, 'active')");
                if ($stmt->execute([$intern_id, $supervisor_id, $assigned_date])) {
                    $success = "Intern assigned successfully!";
                } else {
                    $error = "Error assigning intern!";
                }
            }
        }
    } catch(PDOException $e) {
        $error = "Database error: " . $e->getMessage();
    }
}

// Get all interns with their assignment status
$interns_query = $pdo->query("
    SELECT i.*, 
           a.id as assignment_id, 
           a.supervisor_id as assigned_supervisor_id,
           s.name as supervisor_name
    FROM intern i
    LEFT JOIN assignments a ON i.intern_id = a.intern_id AND a.status = 'active'
    LEFT JOIN supervisors s ON a.supervisor_id = s.id
    ORDER BY i.intern_id DESC
");

$interns = $interns_query ? $interns_query->fetchAll() : [];

// Get all supervisors for dropdown
$supervisors_query = $pdo->query("
    SELECT id, name, department, max_interns,
           (SELECT COUNT(*) FROM assignments WHERE supervisor_id = supervisors.id AND status = 'active') as current_assignments
    FROM supervisors 
    ORDER BY name
");

$supervisors = $supervisors_query ? $supervisors_query->fetchAll() : [];

// Get current assignments count for each supervisor
$assignments_count = [];
$count_query = $pdo->query("SELECT supervisor_id, COUNT(*) as count FROM assignments WHERE status = 'active' GROUP BY supervisor_id");


if ($count_query) {
    while($row = $count_query->fetch()) {
        if ($row && isset($row['supervisor_id'])) {
            $assignments_count[$row['supervisor_id']] = $row['count'];
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin - Intern Assignment System</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        .container {
            max-width: 1400px;
            margin: 0 auto;
            background: white;
            border-radius: 15px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            overflow: hidden;
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .header h1 { font-size: 32px; margin-bottom: 10px; }
        .header p { opacity: 0.9; font-size: 18px; }
        .content { padding: 30px; }
        .alert {
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-weight: 500;
        }
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .stat-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .stat-card h3 { font-size: 16px; margin-bottom: 10px; opacity: 0.9; }
        .stat-card .number { font-size: 36px; font-weight: 700; }
        .search-section {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
        }
        .search-section input {
            width: 100%;
            padding: 12px;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            font-size: 16px;
        }
        .search-section input:focus {
            outline: none;
            border-color: #667eea;
        }
        .table-container {
            overflow-x: auto;
            border-radius: 10px;
            border: 1px solid #e9ecef;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th {
            background: #f8f9fa;
            padding: 15px;
            text-align: left;
            font-weight: 600;
            color: #495057;
            border-bottom: 2px solid #dee2e6;
        }
        td {
            padding: 15px;
            border-bottom: 1px solid #e9ecef;
            color: #212529;
        }
        tr:hover { background: #f8f9fa; }
        .status-badge {
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
            display: inline-block;
        }
        .status-active {
            background: #d4edda;
            color: #155724;
        }
        .status-pending {
            background: #fff3cd;
            color: #856404;
        }
        .btn {
            padding: 8px 16px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 13px;
            font-weight: 500;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s;
        }
        .btn-success { background: #28a745; color: white; }
        .btn-success:hover { background: #218838; }
        .btn-danger { background: #dc3545; color: white; }
        .btn-danger:hover { background: #c82333; }
        .btn-primary { background: #007bff; color: white; }
        .btn-primary:hover { background: #0056b3; }
        
        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            animation: fadeIn 0.3s;
        }
        @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
        .modal-content {
            background: white;
            margin: 100px auto;
            padding: 0;
            width: 90%;
            max-width: 500px;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.3);
            animation: slideIn 0.3s;
        }
        @keyframes slideIn {
            from { transform: translateY(-50px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
        .modal-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
        }
        .modal-header h2 { margin: 0; font-size: 20px; }
        .modal-header .close {
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
            color: white;
            opacity: 0.8;
        }
        .modal-header .close:hover { opacity: 1; }
        .modal-body { padding: 20px; }
        .modal-footer {
            padding: 15px 20px;
            background: #f8f9fa;
            border-top: 1px solid #dee2e6;
            text-align: right;
        }
        .form-group { margin-bottom: 20px; }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #495057;
        }
        .form-group select {
            width: 100%;
            padding: 12px;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            font-size: 16px;
            background: white;
            cursor: pointer;
        }
        .form-group select:focus {
            outline: none;
            border-color: #667eea;
        }
        .supervisor-info {
            background: #f8f9fa;
            padding: 10px;
            border-radius: 8px;
            margin-top: 10px;
            font-size: 14px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>ASSIGN SUPERVISORS TO INTERNS </h1>
            <p>Admin Dashboard</p>
        </div>

        <div class="content">
            <?php if (isset($success)): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>

            <?php if (isset($error)): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>

            <!-- Statistics -->
            <div class="stats">
                <div class="stat-card">
                    <h3>Total Interns</h3>
                    <div class="number"><?php echo count($interns); ?></div>
                </div>
                <div class="stat-card">
                    <h3>Assigned</h3>
                    <div class="number">
                        <?php 
                        $assigned = array_filter($interns, function($i) { 
                            return isset($i['assignment_id']); 
                        });
                        echo count($assigned);
                        ?>
                    </div>
                </div>
                <div class="stat-card">
                    <h3>Unassigned</h3>
                    <div class="number">
                        <?php echo count($interns) - count($assigned); ?>
                    </div>
                </div>
                <div class="stat-card">
                    <h3>Supervisors</h3>
                    <div class="number"><?php echo count($supervisors); ?></div>
                </div>
            </div>

            <!-- Search -->
            <div class="search-section">
                <input type="text" id="searchInput" placeholder="🔍 Search interns by name, school, or department...">
            </div>

            <!-- Interns Table -->
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>School</th>
                            <th>Department</th>
                            <th>Status</th>
                            <th>Supervisor</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="internsTable">
                        <?php foreach ($interns as $intern): ?>
                        <tr>
                            <td>#<?php echo $intern['intern_id']; ?></td>
                            <td><strong><?php echo htmlspecialchars($intern['Name'] ?? 'Unknown'); ?></strong></td>
                            <td><?php echo htmlspecialchars($intern['email'] ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars($intern['school'] ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars($intern['department'] ?? 'N/A'); ?></td>
                            <td>
                                <?php if (isset($intern['assignment_id'])): ?>
                                    <span class="status-badge status-active">Assigned</span>
                                <?php else: ?>
                                    <span class="status-badge status-pending">Unassigned</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (isset($intern['supervisor_name'])): ?>
                                    <?php echo htmlspecialchars($intern['supervisor_name']); ?>
                                <?php else: ?>
                                    <span style="color: #999;">Not assigned</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (!isset($intern['assignment_id'])): ?>
                                    <button onclick="openAssignModal(<?php echo $intern['intern_id']; ?>, '<?php echo htmlspecialchars(addslashes($intern['Name'] ?? 'Unknown')); ?>')" 
                                            class="btn btn-success">Assign</button>
                                <?php endif; ?>
                                <a href="?delete=<?php echo $intern['intern_id']; ?>" 
                                   class="btn btn-danger" 
                                   onclick="return confirm('Are you sure you want to delete this intern?')">Delete</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Assign Modal with Dropdown -->
    <div id="assignModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <span class="close" onclick="closeModal()">&times;</span>
                <h2>Assign Supervisor</h2>
            </div>
            <form method="POST" action="">
                <div class="modal-body">
                    <p><strong>Intern:</strong> <span id="internNameDisplay"></span></p>
                    
                    <input type="hidden" name="assign_intern" value="1">
                    <input type="hidden" name="intern_id" id="modalInternId">
                    
                    <div class="form-group">
                        <label for="supervisorSelect">Select Supervisor:</label>
                        <select name="supervisor_id" id="supervisorSelect" required onchange="updateSupervisorInfo()">
                            <option value="">-- Choose a supervisor --</option>
                            <?php foreach ($supervisors as $supervisor): ?>
                                <?php 
                                $current = $assignments_count[$supervisor['id']] ?? 0;
                                $available = ($supervisor['max_intern'] ?? 5) - $current;
                                $disabled = $available <= 0 ? 'disabled' : '';
                                ?>

                                <option value="<?php echo $supervisor['id']; ?>" <?php echo $disabled; ?>>
                                    <?php echo htmlspecialchars($supervisor['name']); ?> 
                                    (<?php echo $supervisor['department']; ?>) - 
                                    <?php echo $available; ?>/<?php echo $supervisor['max_intern'] ?? 5; ?> slots
                                </option>

                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div id="supervisorDetails" class="supervisor-info">
                        Select a supervisor to see details
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" onclick="closeModal()">Cancel</button>
                    <button type="submit" class="btn btn-success" id="assignBtn">Assign Supervisor</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        var modal = document.getElementById('assignModal');
        
        function openAssignModal(internId, internName) {
            document.getElementById('modalInternId').value = internId;
            document.getElementById('internNameDisplay').textContent = internName;
            document.getElementById('supervisorSelect').value = '';
            document.getElementById('supervisorDetails').innerHTML = 'Select a supervisor to see details';
            modal.style.display = 'block';
        }
        
        function closeModal() {
            modal.style.display = 'none';
        }
        
        function updateSupervisorInfo() {
            var select = document.getElementById('supervisorSelect');
            var selectedOption = select.options[select.selectedIndex];
            
            if (selectedOption.value) {
                var text = selectedOption.text;
                document.getElementById('supervisorDetails').innerHTML = 
                    'Selected: <strong>' + text + '</strong>';
            } else {
                document.getElementById('supervisorDetails').innerHTML = 
                    'Select a supervisor to see details';
            }
        }
        
        document.getElementById('searchInput').addEventListener('keyup', function() {
            var searchText = this.value.toLowerCase();
            var table = document.getElementById('internsTable');
            var rows = table.getElementsByTagName('tr');
            
            for (var i = 0; i < rows.length; i++) {
                var name = rows[i].getElementsByTagName('td')[1]?.textContent.toLowerCase() || '';
                var school = rows[i].getElementsByTagName('td')[3]?.textContent.toLowerCase() || '';
                var dept = rows[i].getElementsByTagName('td')[4]?.textContent.toLowerCase() || '';
                
                if (name.includes(searchText) || school.includes(searchText) || dept.includes(searchText)) {
                    rows[i].style.display = '';
                } else {
                    rows[i].style.display = 'none';
                }
            }
        });
        
        window.onclick = function(event) {
            if (event.target == modal) {
                closeModal();
            }
        }
    </script>
</body>
</html>