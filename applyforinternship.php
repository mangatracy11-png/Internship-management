<?php
// Start session to pass data between pages
session_start();

// Database connection (if needed for other purposes)
include 'db_connect.php';

// Clear any previous session data
unset($_SESSION['intern_data']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Internship Application - CENADI</title>
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
        .form-label {
            font-weight: 600;
            color: #495057;
        }
        .required:after {
            content: " *";
            color: red;
        }
        .card {
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            border: none;
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
                    <div class="col-md-10">
                        <div class="card">
                            <div class="card-header bg-primary text-white">
                                <h3 class="card-title mb-0"><i class="bi bi-file-earmark-plus"></i> Internship Application Form</h3>
                            </div>
                            <div class="card-body">
                                <form action="internsconfirmation.php" method="POST" enctype="multipart/form-data">
                                    <h5 class="mb-4" style="color: #666;">Please fill in all required fields</h5>
                                    
                                    <!-- Personal Information -->
                                    <div class="row mb-4">
                                        <div class="col-md-6">
                                            <label for="Name" class="form-label required"><i class="bi bi-person-fill"></i> Full Name</label>
                                            <input type="text" class="form-control" id="Name" name="Name" 
                                                   placeholder="Enter your full name" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="school" class="form-label required"><i class="bi bi-building"></i> School/University</label>
                                            <input type="text" class="form-control" id="school" name="school" 
                                                   placeholder="Enter your school/university name" required>
                                        </div>
                                    </div>
                                    
                                    <!-- Contact Information -->
                                    <div class="row mb-4">
                                        <div class="col-md-6">
                                            <label for="email" class="form-label required"><i class="bi bi-envelope-fill"></i> Email Address</label>
                                            <input type="email" class="form-control" id="email" name="email" 
                                                   placeholder="Enter your email address" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="Contact" class="form-label required"><i class="bi bi-telephone-fill"></i> Contact Number</label>
                                            <input type="tel" class="form-control" id="Contact" name="Contact" 
                                                   placeholder="Enter your phone number" required>
                                        </div>
                                    </div>
                                    
                                    <!-- Dates -->
                                    <div class="row mb-4">
                                        <div class="col-md-4">
                                            <label for="date_of_birth" class="form-label required"><i class="bi bi-calendar-heart"></i> Date of Birth</label>
                                            <input type="date" class="form-control" id="date_of_birth" name="date_of_birth" required>
                                        </div>
                                        <div class="col-md-4">
                                            <label for="start_date" class="form-label required"><i class="bi bi-calendar-event"></i> Internship Start Date</label>
                                            <input type="date" class="form-control" id="start_date" name="start_date" required>
                                        </div>
                                        <div class="col-md-4">
                                            <label for="end_date" class="form-label required"><i class="bi bi-calendar-check"></i> Internship End Date</label>
                                            <input type="date" class="form-control" id="end_date" name="end_date" required>
                                        </div>
                                    </div>
                                    
                                    <!-- Department & Internship Type -->
                                    <div class="row mb-4">
                                        <div class="col-md-6">
                                            <label for="department" class="form-label required"><i class="bi bi-diagram-3"></i> Preferred Department</label>
                                            <select class="form-select" id="department" name="department" required>
                                                <option value="">Select Department</option>
                                                <option value="DEP">Departments of Studies and Projects (DEP)</option>
                                                <option value="DEL">Department of Exploitation and Software (DEL)</option>
                                                <option value="DTP">Department of Telecomputing and Office Automation (DTP)</option>
                                                <option value="DIRE">Department of Applied Computing to Research and Teaching (DIRE)</option>
                                                <option value="DAAF">Department of Administrative and Finance Affairs (DAAF)</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="internship_type" class="form-label required"><i class="bi bi-briefcase"></i> Internship Type</label>
                                            <select class="form-select" id="internship_type" name="internship_type" required>
                                                <option value="">Select Type</option>
                                                <option value="academic">Academic</option>
                                                <option value="professional">Professional</option>
                                            </select>
                                        </div>
                                    </div>
                                    
                                    <!-- File Upload -->
                                    <div class="mb-4">
                                        <label for="file_path" class="form-label"><i class="bi bi-upload"></i> Upload Supporting Documents</label>
                                        <input type="file" class="form-control" id="file_path" name="file_path" accept=".pdf,.doc,.docx,.jpg,.png">
                                        <div class="form-text">Upload your CV, recommendation letter, or other documents (PDF, DOC, DOCX, JPG, PNG - Max 5MB)</div>
                                    </div>
                                    
                                    <!-- Submit Button -->
                                    <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-4">
                                        <button type="reset" class="btn btn-secondary me-md-2">
                                            <i class="bi bi-arrow-clockwise"></i> Clear Form
                                        </button>
                                        <button type="submit" class="btn btn-primary btn-lg">
                                            <i class="bi bi-send-check"></i> Review Application
                                        </button>
                                    </div>
                                    
                                    <div class="mt-3 text-muted">
                                        <small><i class="bi bi-info-circle"></i> You will have a chance to review your information before final submission.</small>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <!-- Footer -->
    <footer class="main-footer">
        <strong>&copy; <?php echo date('Y'); ?> CENADI - National Centre for IT Development</strong>
    </footer>
</div>

<!-- AdminLTE JS -->
<script src="AdminLTE-master/dist/js/adminlte.min.js"></script>
<!-- Font Awesome -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/js/all.min.js"></script>
</body>
</html>