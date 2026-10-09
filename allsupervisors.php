<?php
include 'db_connect.php'; // DB connection

// Handle delete action
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $delete_sql = "DELETE FROM supervisors WHERE id = ?";
    $stmt = $conn->prepare($delete_sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    header("Location: supervisordashboard.php");
    exit();
}

$sql = "SELECT * FROM supervisors";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>List of supervisors - CENADI</title>
    <!-- AdminLTE CSS -->
    <link rel="stylesheet" href="AdminLTE-master/dist/css/adminlte.min.css">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        .main-sidebar {
           background: linear-gradient(180deg, #766f81ff 0%, #495057 100%);
        }
        .brand-link {
            background: #007bff !important;
        }
        .content-wrapper {
            background-color: black;
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
                <img src="cenadi.jpeg" alt="CENADI Logo" width="350" height="100">
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
                                <h3 class="card-title"><i class="bi bi-people-fill"></i> List of Supervisors</h3>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-striped table-hover table-bordered">
                                        <thead class="table-dark">
                                            <tr>
                                                <th>ID</th>
                                                <th>Name</th>
                                                <th>Email</th>
                                                <th>Interns Assigned</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if ($result->num_rows > 0): ?>
                                                <?php while ($row = $result->fetch_assoc()): ?>
                                                    <?php
                                                    // Get intern count for this supervisor
                                                    $count_sql = "SELECT COUNT(*) as count FROM assignments WHERE supervisor_id = ?";
                                                    $count_stmt = $conn->prepare($count_sql);
                                                    $count_stmt->bind_param("i", $row['id']);
                                                    $count_stmt->execute();
                                                    $count_result = $count_stmt->get_result();
                                                    $count_row = $count_result->fetch_assoc();
                                                    $intern_count = $count_row['count'] ?? 0;
                                                    $count_stmt->close();
                                                    ?>
                                                    <tr>
                                                        <td><?php echo $row['id']; ?></td>
                                                        <td><strong><?php echo htmlspecialchars($row['name'] ?? $row['Name'] ?? 'N/A'); ?></strong></td>
                                                        <td><?php echo htmlspecialchars($row['email'] ?? 'N/A'); ?></td>
                                                        <td><span class="badge bg-primary"><?php echo $intern_count; ?></span></td>
                                                        <td>
                                                            <a href="view_interns_assigned.php?supervisor_id=<?php echo $row['id']; ?>&view=admin" class="btn btn-sm btn-primary">
                                                                <i class="bi bi-eye"></i> View Interns
                                                            </a>
                                                            <a href="?delete=<?php echo $row['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this supervisor?')">
                                                                <i class="bi bi-trash"></i>
                                                            </a>
                                                        </td>
                                                    </tr>
                                                <?php endwhile; ?>
                                            <?php else: ?>
                                                <tr>
                                                    <td colspan="5" class="text-center text-muted p-4">No supervisors found</td>
                                                </tr>
                                            <?php endif; ?>
                                        </tbody>


                                           
    <footer class="main-footer">
        <strong>&copy; 2026 CENADI</strong>
    </footer>
</div>

<!-- AdminLTE JS -->
<script src="AdminLTE-master/dist/js/adminlte.min.js"></script>
</body>
</html>
