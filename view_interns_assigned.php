<?php
session_start();
include 'db_connect.php';

// Admin or supervisor access
if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['admin', 'supervisor'])) {
    header("Location: login.php");
    exit();
}

$error = '';
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
        $error = "Supervisor not found.";
        $supervisor_id = null;
    }
    $stmt->close();
} else {
    // Supervisor viewing own intern (direct access)
    if (isset($_SESSION['user_id'])) {
        $supervisor_id = $_SESSION['user_id'];
        $supervisor_name = $_SESSION['username'] ?? $_SESSION['name'] ?? 'Supervisor';
    } else {
        $error = "Supervisor session not found. Please log in again.";
    }
}

if (!$supervisor_id) {
    // Fall back to error handling, no die/hardcoded page
}



$interns = [];
if ($supervisor_id) {
    // Get interns for this supervisor
    $stmt = $conn->prepare("
        SELECT i.*, a.assigned_date, a.status as assignment_status
        FROM intern i
        LEFT JOIN assignments a ON i.intern_id = a.intern_id AND a.supervisor_id = ?
        WHERE a.supervisor_id = ? OR (a.supervisor_id IS NULL AND i.status = 'active')
        ORDER BY a.assigned_date DESC, i.intern_id DESC
    ");

    $stmt->bind_param("ii", $supervisor_id, $supervisor_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $interns[] = $row;
    }
    $stmt->close();
} else if (empty($error)) {
    $error = "Unable to load supervisor data.";
}



?>


<!DOCTYPE html>
<html>
<head>
    <title>My Assigned Interns</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f4f6f9;
            padding: 20px;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .header {
            background: white;
            padding: 20px 30px;
            border-radius: 10px;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .header h1 {
            color: #333;
            font-size: 24px;
        }

        .header h1 span {
            color: #007bff;
        }

        .back-btn {
            background: #6c757d;
            color: white;
            padding: 8px 16px;
            border-radius: 5px;
            text-decoration: none;
            transition: background 0.3s;
        }

        .back-btn:hover {
            background: #5a6268;
        }

        .stats-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            display: inline-block;
            min-width: 200px;
        }

        .stats-card h3 {
            color: #666;
            font-size: 14px;
            margin-bottom: 10px;
            text-transform: uppercase;
        }

        .stats-card .number {
            font-size: 32px;
            font-weight: bold;
            color: #007bff;
        }

        .search-box {
            background: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .search-box input {
            width: 100%;
            padding: 12px 20px;
            border: 2px solid #e9ecef;
            border-radius: 5px;
            font-size: 16px;
            transition: border 0.3s;
        }

        .search-box input:focus {
            outline: none;
            border-color: #007bff;
        }

        .interns-table {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            background: #007bff;
            color: white;
            padding: 15px;
            text-align: left;
            font-weight: 600;
        }

        td {
            padding: 15px;
            border-bottom: 1px solid #e9ecef;
            color: #333;
        }

        tr:hover {
            background: #f8f9fa;
        }

        .status-badge {
            background: #28a745;
            color: white;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 12px;
            display: inline-block;
        }

        .no-data {
            text-align: center;
            padding: 50px;
            color: #666;
            background: white;
            border-radius: 10px;
        }

        .no-data p {
            font-size: 18px;
            margin-bottom: 10px;
        }

        .no-data small {
            color: #999;
        }

        .error-message {
            background: #f8d7da;
            color: #721c24;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            border: 1px solid #f5c6cb;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <div>
<h1><?php echo (isset($_GET['supervisor_id']) ? 'Interns Assigned to' : 'My Assigned Interns'); ?> - <span><?php echo htmlspecialchars($supervisor_name); ?></span></h1>

            </div>
            <a href="javascript:history.back()" class="back-btn">← Back</a>
        </div>

        <?php if (isset($error)): ?>
            <div class="error-message"><?php echo $error; ?></div>
        <?php endif; ?>

        <!-- Stats -->
        <div class="stats-card">
            <h3>Total Interns Assigned</h3>
            <div class="number"><?php echo count($interns); ?></div>
        </div>


        <!-- Search Box -->
        <div class="search-box">
            <input type="text" id="searchInput" placeholder="🔍 Search by name, email, school, or department...">
        </div>

        <!-- Interns Table -->
        <?php if (count($interns) > 0): ?>
            <div class="interns-table">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>School</th>
                            <th>Department</th>
                            <th>Start Date</th>
                            <th>End Date</th>
                            <th>Type</th>
                            <th>Assigned Date</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody id="internsTable">
                        <?php foreach ($interns as $intern): ?>
                        <tr>
                            <td>#<?php echo $intern['intern_id']; ?></td>
                            <td><strong><?php echo htmlspecialchars($intern['Name'] ?? 'N/A'); ?></strong></td>
                            <td><?php echo htmlspecialchars($intern['email'] ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars($intern['school'] ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars($intern['department'] ?? 'N/A'); ?></td>
                            <td><?php echo $intern['start_date'] ?? 'N/A'; ?></td>
                            <td><?php echo $intern['end_date'] ?? 'N/A'; ?></td>
                            <td><?php echo htmlspecialchars($intern['internship_type'] ?? 'N/A'); ?></td>
                            <td><?php echo $intern['assigned_date']; ?></td>
                            <td><span class="status-badge">Active</span></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="no-data">
                <p>📋 No interns assigned to you yet</p>
                <small>Please check back later or contact the administrator</small>
            </div>
        <?php endif; ?>

    </div>

    <script>
        // Search functionality
        document.getElementById('searchInput').addEventListener('keyup', function() {
            let searchText = this.value.toLowerCase();
            let table = document.getElementById('internsTable');
            let rows = table.getElementsByTagName('tr');
            
            for (let i = 0; i < rows.length; i++) {
                let name = rows[i].getElementsByTagName('td')[1]?.textContent.toLowerCase() || '';
                let email = rows[i].getElementsByTagName('td')[2]?.textContent.toLowerCase() || '';
                let school = rows[i].getElementsByTagName('td')[3]?.textContent.toLowerCase() || '';
                let dept = rows[i].getElementsByTagName('td')[4]?.textContent.toLowerCase() || '';
                
                if (name.includes(searchText) || email.includes(searchText) || school.includes(searchText) || dept.includes(searchText)) {
                    rows[i].style.display = '';
                } else {
                    rows[i].style.display = 'none';
                }
            }
        });
    </script>
</body>
</html>