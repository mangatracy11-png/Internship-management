<?php
// Start session to access form data
session_start();

// Database connection
include 'db_connect.php';

// Initialize variables
$name = $school = $department = $start_date = $end_date = $date_of_birth = "";
$internship_type = $contact = $email = $file_path = "";
$success = false;
$error = "";

// Check if form was submitted from applyforinternship.php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get all form data
    $name = $_POST['Name'] ?? '';
    $school = $_POST['school'] ?? '';
    $department = $_POST['department'] ?? '';
    $start_date = $_POST['start_date'] ?? '';
    $end_date = $_POST['end_date'] ?? '';
    $date_of_birth = $_POST['date_of_birth'] ?? '';
    $internship_type = $_POST['internship_type'] ?? '';
    $contact = $_POST['Contact'] ?? '';
    $email = $_POST['email'] ?? '';

    // Store in session in case user goes back
    $_SESSION['intern_data'] = $_POST;
    
    // Handle file upload
    $file_path = '';
    if (isset($_FILES['file_path']) && $_FILES['file_path']['error'] == 0) {
        $upload_dir = 'uploads/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        
        $original_name = $_FILES['file_path']['name'];
        $file_extension = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));
        $new_filename = uniqid() . '_' . preg_replace('/[^a-zA-Z0-9]/', '_', $name) . '.' . $file_extension;
        $file_path = $upload_dir . $new_filename;
        
        $allowed_types = ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png'];
        
        if (in_array($file_extension, $allowed_types) && $_FILES['file_path']['size'] < 5000000) {
            if (move_uploaded_file($_FILES['file_path']['tmp_name'], $file_path)) {
                // File uploaded successfully
            } else {
                $error = "File upload failed. Please try again.";
            }
        } else {
            $error = "Invalid file type or size. Please upload PDF, DOC, DOCX, JPG, or PNG files under 5MB.";
        }
    }
    
    // Check if user confirmed submission
    if (isset($_POST['confirm_submit']) && $_POST['confirm_submit'] == 'yes') {
        // Insert into database
        // 1. First insert into 'intern' table
        $sql1 = "INSERT INTO intern (Name, school, department, start_date, end_date, date_of_birth, internship_type, Contact, email, file_path) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                
        $stmt1 = $conn->prepare($sql1);
        $stmt1->bind_param("ssssssssss", 
            $name, $school, $department, $start_date, $end_date,
            $date_of_birth, $internship_type, $contact, $email, $file_path
        );

        if ($stmt1->execute()) {
            // Get the auto-generated intern_id
            $intern_id = $stmt1->insert_id;
            
            // 2. Then insert into 'applications' table
            $sql2 = "INSERT INTO applications (intern_id) VALUES (?)";
            $stmt2 = $conn->prepare($sql2);
            $stmt2->bind_param("i", $intern_id);
            
            if ($stmt2->execute()) {
                $success = true;
                $application_id = $stmt2->insert_id;
                
                // Clear session data
                unset($_SESSION['intern_data']);
                
                // Store success message
                $_SESSION['success_message'] = "Application submitted successfully! Your application ID is: #" . $application_id;
            } else {
                $error = "Error inserting into applications: " . $stmt2->error;
            }
            
            $stmt2->close();
        } else {
            $error = "Error inserting into intern table: " . $stmt1->error;
        }
        
        $stmt1->close();
    }
} else {
    // If not POST request, check if there's session data
    if (isset($_SESSION['intern_data'])) {
        $name = $_SESSION['intern_data']['Name'] ?? '';
        $school = $_SESSION['intern_data']['school'] ?? '';
        $department = $_SESSION['intern_data']['department'] ?? '';
        $start_date = $_SESSION['intern_data']['start_date'] ?? '';
        $end_date = $_SESSION['intern_data']['end_date'] ?? '';
        $date_of_birth = $_SESSION['intern_data']['date_of_birth'] ?? '';
        $internship_type = $_SESSION['intern_data']['internship_type'] ?? '';
        $contact = $_SESSION['intern_data']['Contact'] ?? '';
        $email = $_SESSION['intern_data']['email'] ?? '';
    } else {
        // No data, redirect to form
        header("Location: applyforinternship.php");
        exit();
    }
}

// Check for success message from previous submission
if (isset($_SESSION['success_message'])) {
    $success_message = $_SESSION['success_message'];
    unset($_SESSION['success_message']);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirm Application - CENADI</title>
    <!-- AdminLTE CSS -->
    <link rel="stylesheet" href="AdminLTE-master/dist/css/adminlte.min.css">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        body {
            background-color: #f4f6f9;
        }
        .confirmation-card {
            max-width: 800px;
            margin: 40px auto;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            border: none;
            border-radius: 10px;
        }
        .info-item {
            padding: 12px 0;
            border-bottom: 1px solid #eee;
        }
        .info-label {
            font-weight: 600;
            color: #495057;
            min-width: 200px;
            display: inline-block;
        }
        .info-value {
            color: #6c757d;
        }
        .success-box {
            background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
            border-left: 5px solid #28a745;
        }
        .error-box {
            background: linear-gradient(135deg, #f8d7da 0%, #f5c6cb 100%);
            border-left: 5px solid #dc3545;
        }
    </style>
</head>
<body class="hold-transition">
<div class="wrapper">
    <!-- Navbar -->
    <nav class="main-header navbar navbar-expand navbar-white navbar-light">
        <ul class="navbar-nav">
            <li class="nav-item">
                <a class="nav-link" href="applyforinternship.php">
                    <i class="fas fa-arrow-left"></i> Back to Form
                </a>
            </li>
        </ul>
        <ul class="navbar-nav ml-auto">
            <li class="nav-item">
                <img src="cenadi.jpeg" alt="CENADI Logo" width="300" height="80">
            </li>
        </ul>
    </nav>

    <div class="content-wrapper">
        <div class="container">
            <?php if (isset($success_message)): ?>
                <div class="alert alert-success success-box mt-4">
                    <h4><i class="bi bi-check-circle-fill"></i> Success!</h4>
                    <p><?php echo $success_message; ?></p>
                    <div class="mt-3">
                        <a href="interndashboard.php" class="btn btn-outline-secondary">Return to Dashboard</a>
                    </div>
                </div>
            <?php elseif ($success): ?>
                <div class="alert alert-success success-box mt-4">
                    <h4><i class="bi bi-check-circle-fill"></i> Application Submitted Successfully!</h4>
                    <p>Your internship application has been received. We will contact you shortly.</p>
                    <div class="mt-3">
                        <a href="applyforinternship.php" class="btn btn-primary">Submit Another Application</a>
                        <a href="index.php" class="btn btn-outline-secondary">Return to Dashboard</a>
                    </div>
                </div>
            <?php else: ?>
                <div class="card confirmation-card">
                    <div class="card-header bg-info text-white">
                        <h3 class="card-title mb-0"><i class="bi bi-clipboard-check"></i> Review Your Application</h3>
                        <p class="mb-0">Please review all information before final submission</p>
                    </div>
                    
                    <div class="card-body">
                        <?php if (!empty($error)): ?>
                            <div class="alert alert-danger error-box">
                                <i class="bi bi-exclamation-triangle"></i> <?php echo $error; ?>
                            </div>
                        <?php endif; ?>
                        
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle"></i> If any information is incorrect, please go back and edit your application.
                        </div>
                        
                        <h5 class="mb-3" style="color: #495057;">Application Details</h5>
                        
                        <div class="info-item">
                            <span class="info-label">Full Name:</span>
                            <span class="info-value"><?php echo htmlspecialchars($name); ?></span>
                        </div>
                        
                        <div class="info-item">
                            <span class="info-label">School/University:</span>
                            <span class="info-value"><?php echo htmlspecialchars($school); ?></span>
                        </div>
                        
                        <div class="info-item">
                            <span class="info-label">Department:</span>
                            <span class="info-value">
                                <?php 
                                $dept_names = [
                                    'DEP' => 'Departments of Studies and Projects (DEP)',
                                    'DEL' => 'Department of Exploitation and Software (DEL)',
                                    'DTP' => 'Department of Telecomputing and Office Automation (DTP)',
                                    'DIRE' => 'Department of Applied Computing to Research and Teaching (DIRE)',
                                    'DAAF' => 'Department of Administrative and Finance Affairs (DAAF)'
                                ];
                                echo $dept_names[$department] ?? $department;
                                ?>
                            </span>
                        </div>
                        
                        <div class="info-item">
                            <span class="info-label">Email:</span>
                            <span class="info-value"><?php echo htmlspecialchars($email); ?></span>
                        </div>
                        
                        <div class="info-item">
                            <span class="info-label">Contact Number:</span>
                            <span class="info-value"><?php echo htmlspecialchars($contact); ?></span>
                        </div>
                        
                        <div class="info-item">
                            <span class="info-label">Date of Birth:</span>
                            <span class="info-value"><?php echo htmlspecialchars($date_of_birth); ?></span>
                        </div>
                        
                        <div class="info-item">
                            <span class="info-label">Internship Period:</span>
                            <span class="info-value">
                                <?php echo htmlspecialchars($start_date); ?> to <?php echo htmlspecialchars($end_date); ?>
                            </span>
                        </div>
                        
                        <div class="info-item">
                            <span class="info-label">Internship Type:</span>
                            <span class="info-value">
                                <?php echo ucfirst(htmlspecialchars($internship_type)); ?>
                            </span>
                        </div>
                        
                        <div class="info-item">
                            <span class="info-label">Uploaded File:</span>
                            <span class="info-value">
                                <?php 
                                if (!empty($file_path)) {
                                    echo '<a href="' . htmlspecialchars($file_path) . '" target="_blank" class="text-primary">
                                            <i class="bi bi-file-earmark"></i> View Uploaded File
                                          </a>';
                                } else {
                                    echo '<span class="text-muted">No file uploaded</span>';
                                }
                                ?>
                            </span>
                        </div>
                        
                        <!-- Hidden form to submit confirmed data -->
                        <form method="POST" enctype="multipart/form-data" class="mt-4">
                            <input type="hidden" name="Name" value="<?php echo htmlspecialchars($name); ?>">
                            <input type="hidden" name="school" value="<?php echo htmlspecialchars($school); ?>">
                            <input type="hidden" name="department" value="<?php echo htmlspecialchars($department); ?>">
                            <input type="hidden" name="start_date" value="<?php echo htmlspecialchars($start_date); ?>">
                            <input type="hidden" name="end_date" value="<?php echo htmlspecialchars($end_date); ?>">
                            <input type="hidden" name="date_of_birth" value="<?php echo htmlspecialchars($date_of_birth); ?>">
                            <input type="hidden" name="internship_type" value="<?php echo htmlspecialchars($internship_type); ?>">
                            <input type="hidden" name="Contact" value="<?php echo htmlspecialchars($contact); ?>">
                            <input type="hidden" name="email" value="<?php echo htmlspecialchars($email); ?>">
                            <input type="hidden" name="file_path" value="<?php echo htmlspecialchars($file_path); ?>">
                            <input type="hidden" name="confirm_submit" value="yes">
                            
                            <div class="d-flex justify-content-between mt-4">
                                <a href="applyforinternship.php" class="btn btn-outline-secondary">
                                    <i class="bi bi-arrow-left"></i> Back to Edit
                                </a>
                                
                                <div>
                                    <button type="submit" name="submit" class="btn btn-success btn-lg">
                                        <i class="bi bi-check-circle"></i> Confirm & Submit Application
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Footer -->
    <footer class="main-footer text-center py-3">
        <strong>&copy; <?php echo date('Y'); ?> CENADI - National Centre for IT Development</strong>
    </footer>
</div>

<!-- AdminLTE JS -->
<script src="AdminLTE-master/dist/js/adminlte.min.js"></script>
<!-- Font Awesome -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/js/all.min.js"></script>
</body>
</html>