<?php
$servername = "localhost";      // usually "localhost"
$username = "root";             // default XAMPP username
$password = "";                 // leave empty unless you set one
$dbname = "defenseproject";    // your database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

// Initialize variables for intern fields
$name  = $department = $Start_date = $end_date = $Date_of_birth = $email = $school = $file = "";

// Check if 'id' is set in GET parameters
if (isset($_GET['id'])) {
    $intern_id = intval($_GET['id']);

    // Prepare and execute query to fetch intern info
    $stmt = $conn->prepare("SELECT Name,  Department, Start_date, end_date, Date_of_birth, email, school, file_name FROM intern WHERE intern_id = ?");
    $stmt->bind_param("i", $intern_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $name = $row['Name'];
        $department = $row['Department'];
        $Start_date = $row['Start_date'];
        $end = $row['end_date'];
        $Date_of_birth = $row['Date_of_birth'];
        $email = $row['email'];
        $school = $row['school'];
        $file = $row['file'];
    } else {
        echo "Intern not found.";
        exit;
    }
} else {
    echo "No intern ID specified.";
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Internship Dashboard - CENADI</title>
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
                <div class="row justify-content-center">
                    <div class="col-md-8">
                        <div class="card">
                            <div class="card-header bg-primary text-white">
                                <h3 class="card-title mb-0"><i class="bi bi-file-earmark-plus"></i> Internship Application Form</h3>
                            </div>
                            <div class="card-body">
                                <form action="" method="post" enctype="multipart/form-data">
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label for="Name" class="form-label"><i class="bi bi-person-fill"></i> Full Name</label>
                                            <input type="text" class="form-control" id="Name" name="Name" placeholder="Enter your full name" value="<?php echo htmlspecialchars($name); ?>" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="school" class="form-label"><i class="bi bi-building"></i> School</label>
                                            <input type="text" class="form-control" id="school" name="school" placeholder="Enter your school name" value="<?php echo htmlspecialchars($school); ?>" required>
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label for="department" class="form-label"><i class="bi bi-diagram-3"></i> Department</label>
                                            <input type="text" class="form-select" id="department" name="department" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="contact" class="form-label"><i class="bi bi-envelope-fill"></i> Email</label>
                                            <input type="email" class="form-control" id="contact" name="Contact" placeholder="Enter email address" value="<?php echo htmlspecialchars($email); ?>" required>
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label for="start_date" class="form-label"><i class="bi bi-calendar-event"></i> Start Date</label>
                                            <input type="date" class="form-control" id="start_date" name="start_date" value="<?php echo htmlspecialchars($Start_date); ?>" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="end_date" class="form-label"><i class="bi bi-calendar-check"></i> End Date</label>
                                            <input type="date" class="form-control" id="end_date" name="end_date" value="<?php echo htmlspecialchars($end); ?>" required>
                                        </div>
                                        <br>
                                        <br>
                                        <div class="col-md-6">
                                            <label for="Date_of_birth" class="form-label"><i class="bi bi-calendar-check"></i> Date of birth</label>
                                            <input type="date" class="form-control" id="Date_of_birth" name="Date_of_birth" value="<?php echo htmlspecialchars($Date_of_birth); ?>" required>
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label for="internship_type" class="form-label"><i class="bi bi-briefcase"></i> Internship Type</label>
                                            <select class="form-select" id="internship_type" name="internship_type" required>
                                                <option value="">Select Type</option>
                                                <option value="academic" <?php echo (isset($internship_type) && $internship_type == 'academic') ? 'selected' : ''; ?>>Academic</option>
                                                <option value="professional" <?php echo (isset($internship_type) && $internship_type == 'professional') ? 'selected' : ''; ?>>Professional</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label for="upload_file" class="form-label"> <a href="upload.php"></a><i class="bi bi-upload"></i> View file</label>
                                        <div class="form-text">Accepted formats: PDF, DOC, DOCX, JPG, PNG (Max 5MB)</div>
                                        <a href="assign_supervisor.php" class="btn btn-secondary">Assign supervior</a>
                                    </div>
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