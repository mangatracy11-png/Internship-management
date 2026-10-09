<?php
include 'db_connect.php'; // DB connection

// Handle delete action
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $delete_sql = "DELETE FROM users WHERE id = ? AND role = 'intern'";
    $stmt = $conn->prepare($delete_sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    header("Location: view_interns_assigned.php");
    exit();
}

// Fetch interns - FIXED: Added id to SELECT
$sql = "SELECT u.id, u.name, u.email 
        FROM users u 
        WHERE u.role = 'intern' 
        ORDER BY u.id DESC";
$result = $conn->query($sql);

if (!$result) {
    die("Database query failed: " . $conn->error);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>List of Interns - CENADI</title>
    <!-- AdminLTE CSS -->
    <link rel="stylesheet" href="AdminLTE-master/dist/css/adminlte.min.css">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        .main-sidebar {
            background: linear-gradient(180deg, #343a40 0%, #495057 100%);
        }
        .brand-link {
            background: #007bff !important;
        }
        .content-wrapper {
            background-color: #f4f6f9;
        }
        footer {
            background-color: #343a40;
            color: white;
            text-align: center;
            padding: 15px;
            position: fixed;
            bottom: 0;
            width: 100%;
        }
        .table th {
            background-color: #007bff;
            color: white;
            border: none;
        }
        .table td {
            vertical-align: middle;
        }
        .btn-action {
            margin-right: 5px;
        }
        .card {
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            margin-bottom: 80px;
        }
        .card-header {
            background: linear-gradient(90deg, #007bff 0%, #0056b3 100%);
            color: white;
            border-radius: 10px 10px 0 0;
        }
    </style>
</head>
<body class="hold-transition sidebar-mini">
<div class="wrapper">
    <!-- Navbar -->
    <nav class="main-header navbar navbar-expand navbar-white navbar-light">
        <ul class="navbar-nav">
            <li class="nav-item">
                <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
            </li>
        </ul>
        <ul class="navbar-nav ml-auto">
            <li class="nav-item">
                <img src="cenadi.jpeg" alt="CENADI Logo" width="250" height="60">
            </li>
        </ul>
    </nav>

    <!-- Content Wrapper -->
    <div class="content-wrapper">
        <section class="content">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title"><i class="bi bi-people-fill"></i> List of Interns that applied for internship</h3>
                            </div>
                            <div class="card-body">
                                <?php if ($result->num_rows > 0): ?>
                                    <div class="table-responsive">
                                        <table class="table table-striped table-hover table-bordered">
                                            <thead class="table-dark">
                                                <tr>
                                                    <th><i class="bi bi-person"></i> Name</th>
                                                    <th><i class="bi bi-envelope"></i> Email</th>
                                                    <th><i class="bi bi-gear"></i> Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php while ($row = $result->fetch_assoc()): ?>
                                                    <tr>
                                                        <td><?php echo htmlspecialchars($row['name']); ?></td>
                                                        <td><?php echo htmlspecialchars($row['email']); ?></td>
                                                        <td>
                                                            <a href="assign_supervisor.php?id=<?php echo $row['id']; ?>" 
                                                               class="btn btn-sm btn-primary btn-action">
                                                                <i class="bi bi-person-plus"></i> Assign supervisor
                                                            </a>
                                                            <a href="?delete=<?php echo $row['id']; ?>" 
                                                               class="btn btn-sm btn-danger" 
                                                               onclick="return confirm('Are you sure you want to delete this intern?')">
                                                                <i class="bi bi-trash"></i> Delete
                                                            </a>
                                                        </td>
                                                    </tr>
                                                <?php endwhile; ?>
                                            </tbody>
                                        </table> 
                                    </div>
                                <?php else: ?>
                                    <div class="alert alert-info">
                                        <strong>No interns found!</strong> There are no registered interns yet.
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <!-- Footer -->
    <footer class="main-footer">
        <strong>&copy; 2026 CENADI</strong>
    </footer>
</div>

<!-- AdminLTE JS -->
<script src="AdminLTE-master/dist/js/adminlte.min.js"></script>
</body>
</html>
<?php $conn->close(); ?>