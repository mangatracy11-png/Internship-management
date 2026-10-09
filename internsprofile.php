<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

include 'db_connect.php'; // connect to the database

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];

// Fetch profile data
$sql = "SELECT * FROM intern_profiles WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    // Profile doesn't exist, redirect to create profile
    header("Location: profile.php");
    exit();
}

$profile = $result->fetch_assoc();
$stmt->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - <?php echo htmlspecialchars($profile['full_name']); ?></title>
    <!-- AdminLTE CSS -->
    <link rel="stylesheet" href="AdminLTE-master/dist/css/adminlte.min.css">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background-color: #f4f6f9;
        }
        .profile-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            border-radius: 10px 10px 0 0;
            position: relative;
        }
        .profile-picture {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            border: 5px solid white;
            object-fit: cover;
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }
        .profile-picture-placeholder {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            border: 5px solid white;
            background-color: #ccc;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 60px;
            color: white;
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }
        .card {
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .info-label {
            font-weight: 600;
            color: #495057;
            margin-bottom: 5px;
        }
        .info-value {
            color: #212529;
            margin-bottom: 15px;
        }
        .section-title {
            color: #667eea;
            font-weight: 600;
            font-size: 18px;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #667eea;
        }
        .btn-edit {
            position: absolute;
            top: 20px;
            right: 20px;
        }
        .stats-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .stats-card .icon {
            font-size: 40px;
            color: #667eea;
            margin-bottom: 10px;
        }
        .stats-card .value {
            font-size: 24px;
            font-weight: bold;
            color: #212529;
        }
        .stats-card .label {
            color: #6c757d;
            font-size: 14px;
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
                <li class="nav-item d-none d-sm-inline-block">
                    <span class="nav-link">Welcome, <?php echo htmlspecialchars($username); ?>!</span>
                </li>
            </ul>
        </nav>

        <!-- Main Sidebar Container -->
        <aside class="main-sidebar sidebar-dark-primary elevation-4">
            <a href="#" class="brand-link">
                <img src="cenadi.jpeg" alt="CENADI Logo" class="brand-image img-circle elevation-3" style="opacity: .8">
                <span class="brand-text font-weight-light">CENADI</span>
            </a>
            <div class="sidebar">
                <nav class="mt-2">
                    <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu">
                        <li class="nav-item">
                            <a href="interndashboard.php" class="nav-link">
                                <i class="nav-icon fas fa-home"></i>
                                <p>Home</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="internsprofile.php" class="nav-link active">
                                <i class="nav-icon fas fa-user"></i>
                                <p>My Profile</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="logout.php" class="nav-link">
                                <i class="nav-icon fas fa-sign-out-alt"></i>
                                <p>Logout</p>
                            </a>
                        </li>
                    </ul>
                </nav>
            </div>
        </aside>

        <!-- Content Wrapper -->
        <div class="content-wrapper">
            <section class="content">
                <div class="container-fluid py-4">
                    
                    <!-- Profile Header -->
                    <div class="card">
                        <div class="profile-header">
                            <a href="editprofile.php" class="btn btn-light btn-edit">
                                <i class="fas fa-edit"></i> Edit Profile
                            </a>
                            <div class="row align-items-center">
                                <div class="col-md-2 text-center">
                                    <?php if (!empty($profile['profile_picture']) && file_exists($profile['profile_picture'])): ?>
                                        <img src="<?php echo htmlspecialchars($profile['profile_picture']); ?>" alt="Profile Picture" class="profile-picture">
                                    <?php else: ?>
                                        <div class="profile-picture-placeholder">
                                            <i class="fas fa-user"></i>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="col-md-10">
                                    <h2 class="mb-2"><?php echo htmlspecialchars($profile['full_name']); ?></h2>
                                    <p class="mb-1"><i class="fas fa-briefcase"></i> <?php echo htmlspecialchars($profile['internship_type']); ?> Intern</p>
                                    <p class="mb-0"><i class="fas fa-calendar"></i> <?php echo date('M d, Y', strtotime($profile['internship_start_date'])); ?> - <?php echo date('M d, Y', strtotime($profile['internship_end_date'])); ?></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Stats Row -->
                    <div class="row mt-4">
                        <div class="col-md-3">
                            <div class="stats-card">
                                <div class="icon"><i class="fas fa-calendar-check"></i></div>
                                <div class="value">
                                    <?php 
                                    $start = new DateTime($profile['internship_start_date']);
                                    $today = new DateTime();
                                    $days = $today->diff($start)->days;
                                    echo $days; 
                                    ?>
                                </div>
                                <div class="label">Days Active</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stats-card">
                                <div class="icon"><i class="fas fa-clock"></i></div>
                                <div class="value">
                                    <?php 
                                    $end = new DateTime($profile['internship_end_date']);
                                    $remaining = $today->diff($end)->days;
                                    echo $remaining; 
                                    ?>
                                </div>
                                <div class="label">Days Remaining</div>
                            </div>
                                    </div>
                        <div class="col-md-3">
                            <div class="stats-card">
                                <div class="icon"><i class="fas fa-graduation-cap"></i></div>
                                <div class="value" style="font-size: 16px;">
                                    <?php echo htmlspecialchars($profile['school']); ?>
                                </div>
                                <div class="label">Institution</div>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-4">
                        <!-- Personal Information -->
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="section-title"><i class="fas fa-user"></i> Personal Information</h5>
                                    
                                    <div class="info-label">Full Name</div>
                                    <div class="info-value"><?php echo htmlspecialchars($profile['full_name']); ?></div>
                                    
                                    <div class="info-label">Phone</div>
                                    <div class="info-value"><?php echo htmlspecialchars($profile['phone']); ?></div>
                                    
                                    <div class="info-label">Date of Birth</div>
                                    <div class="info-value"><?php echo date('F d, Y', strtotime($profile['date_of_birth'])); ?></div>
                                    
                                    <div class="info-label">Gender</div>
                                    <div class="info-value"><?php echo htmlspecialchars($profile['gender']); ?></div>
                                    
                                    <div class="info-label">Address</div>
                                    <div class="info-value"><?php echo htmlspecialchars($profile['address']); ?></div>
                                    
                                    <div class="info-label">Country</div>
                                    <div class="info-value"><?php echo htmlspecialchars($profile['country']); ?></div>
                                </div>
                            </div>
                        </div>

                        <!-- Academic Information -->
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="section-title"><i class="fas fa-graduation-cap"></i> Academic Information</h5>
                                    
                                    <div class="info-label">School/University</div>
                                    <div class="info-value"><?php echo htmlspecialchars($profile['school']); ?></div>
                                    
                                </div>
                            </div>

                            <!-- Internship Details -->
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="section-title"><i class="fas fa-briefcase"></i> Internship Details</h5>
                                    
                                    <div class="info-label">Start Date</div>
                                    <div class="info-value"><?php echo date('F d, Y', strtotime($profile['internship_start_date'])); ?></div>
                                    
                                    <div class="info-label">End Date</div>
                                    <div class="info-value"><?php echo date('F d, Y', strtotime($profile['internship_end_date'])); ?></div>
                                    
                                    <div class="info-label">Internship Type</div>
                                    <div class="info-value"><?php echo htmlspecialchars($profile['internship_type']); ?></div>
                                
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Skills and Experience -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="section-title"><i class="fas fa-tools"></i> Skills</h5>
                                    <?php if (!empty($profile['skills'])): ?>
                                        <p><?php echo nl2br(htmlspecialchars($profile['skills'])); ?></p>
                                    <?php else: ?>
                                        <p class="text-muted">No skills listed yet.</p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="section-title"><i class="fas fa-history"></i> Previous Experience</h5>
                                    <?php if (!empty($profile['previous_experience'])): ?>
                                        <p><?php echo nl2br(htmlspecialchars($profile['previous_experience'])); ?></p>
                                    <?php else: ?>
                                        <p class="text-muted">No previous experience listed.</p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="row">
                        <div class="col-md-12 text-center">
                            <a href="editprofile.php" class="btn btn-primary btn-lg">
                                <i class="fas fa-edit"></i> Edit Profile
                            </a>
                            <a href="interndashboard.php" class="btn btn-secondary btn-lg">
                                <i class="fas fa-arrow-left"></i> Back to Dashboard
                            </a>
                        </div>
                    </div>

                </div>
            </section>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- AdminLTE JS -->
    <script src="AdminLTE-master/dist/js/adminlte.min.js"></script>
</body>
</html>