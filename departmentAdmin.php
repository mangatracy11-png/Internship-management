<?php
// Department selection form processing
$message = '';
$selected_department = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $department = $_POST['department'] ?? '';

    if (empty($name) || empty($email) || empty($department)) {
        $message = '<div class="alert alert-danger">Please fill in all fields and select a department.</div>';
    } else {
        // Here you would typically save to database or redirect
        $message = '<div class="alert alert-success">Application submitted for ' . htmlspecialchars($department) . ' department!</div>';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
z<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Department Selection - CENADI</title>
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
        .department-card {
            transition: transform 0.3s, box-shadow 0.3s;
            border-radius: 10px;
            overflow: hidden;
        }
        .department-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }
        .dep-card { background: linear-gradient(135deg, #007bff 0%, #0056b3 100%); color: white; }
        .del-card { background: linear-gradient(135deg, #17a2b8 0%, #138496 100%); color: white; }
        .dtp-card { background: linear-gradient(135deg, #6f42c1 0%, #5a32a3 100%); color: white; }
        .dire-card { background: linear-gradient(135deg, #28a745 0%, #1e7e34 100%); color: white; }
        .daaf-card { background: linear-gradient(135deg, #fd7e14 0%, #e8680d 100%); color: white; }
        .apply-btn {
            background: rgba(255,255,255,0.2);
            border: 2px solid white;
            color: white;
            padding: 10px 20px;
            border-radius: 25px;
            transition: all 0.3s;
        }
        .apply-btn:hover {
            background: white;
            color: #333;
        }
        .general-apply {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 1000;
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
                <a class="nav-link" data-widget="pushmenu" href="" role="button"><i class="fas fa-bars"></i></a>
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
                            <div class="card-header bg-primary text-white">
                                <h3 class="card-title mb-0"><i class="bi bi-clipboard-check"></i> Apply for Internship at CENADI</h3>
                            </div>
                            <div class="card-body">
                                <?php echo $message; ?>


                <div class="row">
                    <div class="col-md-4 mb-4">
                        <div class="card department-card dep-card h-100">
                            <div class="card-body text-center">
                                <h5 class="card-title">Departments of Studies and Projects (DEP)</h5>
                                <p class="card-text">Focuses on research, development, and implementation of innovative IT projects for public sector enhancement.</p>
                                <button class="apply-btn" onclick="selectDepartment('DEP')" href="">Apply</button>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-4">
                        <div class="card department-card del-card h-100">
                            <div class="card-body text-center">
                                <h5 class="card-title">Department of Exploitation and Software (DEL)</h5>
                                <p class="card-text">Manages software exploitation, maintenance, and development of custom applications for government use.</p>
                                <button class="apply-btn" onclick="selectDepartment('DEL')" href="">Apply</button>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-4">
                        <div class="card department-card dtp-card h-100">
                            <div class="card-body text-center">
                                <h5 class="card-title">Department of Telecomputing and Office Automation (DTP)</h5>
                                <p class="card-text">Handles telecomputing infrastructure, network management, and office automation solutions.</p>
                                <button class="apply-btn" onclick="selectDepartment('DTP')" href="">Apply</button>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mb-4">
                        <div class="card department-card dire-card h-100">
                            <div class="card-body text-center">
                                <h5 class="card-title">Department of Applied Computing to Research and Teaching (DIRE)</h5>
                                <p class="card-text">Supports research institutions and educational facilities with advanced computing solutions and digital tools.</p>
                                <button class="apply-btn" onclick="selectDepartment('DIRE')" href="">Apply</button>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mb-4">
                        <div class="card department-card daaf-card h-100">
                            <div class="card-body text-center">
                                <h5 class="card-title">Department of Administrative and Finance Affairs (DAAF)</h5>
                                <p class="card-text">Manages administrative processes, financial systems, and resource allocation for IT projects.</p>
                                <button class="apply-btn" onclick="selectDepartment('DAAF')"href=""> Apply</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
s

    <!-- Footer -->
    <footer class="main-footer">
        <strong>&copy; 2026 CENADI</strong>
    </footer>
</div>

<!-- AdminLTE JS -->
<script src="AdminLTE-master/dist/js/adminlte.min.js"></script>
<script>
function selectDepartment(dept) {
    // Redirect to applyforinternship.php with selected department
    window.location.href = 'applyforinternship.php?department=' + encodeURIComponent(dept);
}
</script>
</body>
</html>

