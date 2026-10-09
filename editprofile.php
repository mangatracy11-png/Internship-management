<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

include 'db_connect.php';

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];
$message = '';

// Fetch existing profile data
$sql = "SELECT * FROM intern_profiles WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    // No profile exists, redirect to create
    header("Location: profile.php");
    exit();
}

$profile = $result->fetch_assoc();
$stmt->close();

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $full_name = trim($_POST['full_name']);
    $phone = trim($_POST['phone']);
    $date_of_birth = $_POST['date_of_birth'];
    $gender = $_POST['gender'];
    $address = trim($_POST['address']);
    $city = trim($_POST['city']);
    $country = trim($_POST['country']);
    $school = trim($_POST['school']);
    $department = trim($_POST['department']);
    $level_of_study = trim($_POST['level_of_study']);
    $internship_start_date = $_POST['internship_start_date'];
    $internship_end_date = $_POST['internship_end_date'];
    $internship_type = $_POST['internship_type'];
    $assigned_department = trim($_POST['assigned_department']);
    $supervisor_id = $_POST['supervisor_id'];
    $skills = trim($_POST['skills']);
    $previous_experience = trim($_POST['previous_experience']);

    // Handle file upload
    $profile_picture = $profile['profile_picture']; // Keep existing picture by default
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] == 0) {
        $target_dir = "uploads/";
        
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        
        $file_extension = strtolower(pathinfo($_FILES["profile_picture"]["name"], PATHINFO_EXTENSION));
        $new_filename = "profile_" . $user_id . "_" . time() . "." . $file_extension;
        $target_file = $target_dir . $new_filename;
        
        $check = getimagesize($_FILES["profile_picture"]["tmp_name"]);
        if ($check !== false) {
            if ($_FILES["profile_picture"]["size"] <= 5000000) {
                $allowed_types = array("jpg", "jpeg", "png", "gif");
                if (in_array($file_extension, $allowed_types)) {
                    if (move_uploaded_file($_FILES["profile_picture"]["tmp_name"], $target_file)) {
                        // Delete old profile picture if exists
                        if (!empty($profile['profile_picture']) && file_exists($profile['profile_picture'])) {
                            unlink($profile['profile_picture']);
                        }
                        $profile_picture = $target_file;
                    } else {
                        $message = '<div class="alert alert-danger">Sorry, there was an error uploading your file.</div>';
                    }
                } else {
                    $message = '<div class="alert alert-danger">Sorry, only JPG, JPEG, PNG & GIF files are allowed.</div>';
                }
            } else {
                $message = '<div class="alert alert-danger">Sorry, your file is too large. Maximum size is 5MB.</div>';
            }
        } else {
            $message = '<div class="alert alert-danger">File is not an image.</div>';
        }
    }

    if (empty($message)) {
        // Update profile
        $sql = "UPDATE intern_profiles SET 
                full_name = ?, phone = ?, date_of_birth = ?, gender = ?, 
                address = ?, city = ?, country = ?, school = ?, 
                department = ?, level_of_study = ?, internship_start_date = ?, 
                internship_end_date = ?, internship_type = ?, assigned_department = ?, 
                supervisor_id = ?, skills = ?, previous_experience = ?, profile_picture = ?
                WHERE user_id = ?";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssssssssssssisssi", 
            $full_name, $phone, $date_of_birth, $gender, 
            $address, $city, $country, $school, 
            $department, $level_of_study, $internship_start_date, 
            $internship_end_date, $internship_type, $assigned_department, 
            $supervisor_id, $skills, $previous_experience, $profile_picture, 
            $user_id);

        if ($stmt->execute()) {
            $message = '<div class="alert alert-success">✅ Profile updated successfully!</div>';
            // Refresh profile data
            $profile['full_name'] = $full_name;
            $profile['phone'] = $phone;
            $profile['date_of_birth'] = $date_of_birth;
            $profile['gender'] = $gender;
            $profile['address'] = $address;
            $profile['city'] = $city;
            $profile['country'] = $country;
            $profile['school'] = $school;
            $profile['department'] = $department;
            $profile['level_of_study'] = $level_of_study;
            $profile['internship_start_date'] = $internship_start_date;
            $profile['internship_end_date'] = $internship_end_date;
            $profile['internship_type'] = $internship_type;
            $profile['assigned_department'] = $assigned_department;
            $profile['supervisor_id'] = $supervisor_id;
            $profile['skills'] = $skills;
            $profile['previous_experience'] = $previous_experience;
            $profile['profile_picture'] = $profile_picture;
            
            header("refresh:2;url=internsprofile.php");
        } else {
            $message = '<div class="alert alert-danger">❌ Error: ' . $stmt->error . '</div>';
        }
        $stmt->close();
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile</title>
    <link rel="stylesheet" href="AdminLTE-master/dist/css/adminlte.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background-color: #f4f6f9;
        }
        .card {
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .required-field::after {
            content: " *";
            color: red;
        }
    </style>
</head>
<body class="hold-transition sidebar-mini">
    <div class="wrapper">
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

        <div class="content-wrapper">
            <section class="content-header">
                <div class="container-fluid">
                    <div class="row mb-2">
                        <div class="col-sm-6">
                            <h1>Edit Profile</h1>
                        </div>
                    </div>
                </div>
            </section>

            <section class="content">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="card card-primary">
                                <div class="card-header">
                                    <h3 class="card-title">Update Your Information</h3>
                                </div>
                                <div class="card-body">
                                    <?php if (!empty($message)) echo $message; ?>
                                    
                                    <form method="POST" action="" enctype="multipart/form-data">
                                        <h5 class="text-primary">Personal Information</h5>
                                        <hr>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="full_name" class="required-field">Full Name</label>
                                                    <input type="text" id="full_name" name="full_name" class="form-control" value="<?php echo htmlspecialchars($profile['full_name']); ?>" required>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="phone" class="required-field">Phone</label>
                                                    <input type="tel" id="phone" name="phone" class="form-control" value="<?php echo htmlspecialchars($profile['phone']); ?>" required>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="date_of_birth" class="required-field">Date of Birth</label>
                                                    <input type="date" id="date_of_birth" name="date_of_birth" class="form-control" value="<?php echo htmlspecialchars($profile['date_of_birth']); ?>" required>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="gender" class="required-field">Gender</label>
                                                    <select id="gender" name="gender" class="form-control" required>
                                                        <option value="Male" <?php echo ($profile['gender'] == 'Male') ? 'selected' : ''; ?>>Male</option>
                                                        <option value="Female" <?php echo ($profile['gender'] == 'Female') ? 'selected' : ''; ?>>Female</option>
                                                        <option value="Other" <?php echo ($profile['gender'] == 'Other') ? 'selected' : ''; ?>>Other</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <h5 class="text-primary mt-4">Address Information</h5>
                                        <hr>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="address" class="required-field">Address</label>
                                                    <input type="text" id="address" name="address" class="form-control" value="<?php echo htmlspecialchars($profile['address']); ?>" required>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="city" class="required-field">City</label>
                                                    <input type="text" id="city" name="city" class="form-control" value="<?php echo htmlspecialchars($profile['city']); ?>" required>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="country" class="required-field">Country</label>
                                                    <input type="text" id="country" name="country" class="form-control" value="<?php echo htmlspecialchars($profile['country']); ?>" required>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <h5 class="text-primary mt-4">Academic Information</h5>
                                        <hr>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="school" class="required-field">School/University</label>
                                                    <input type="text" id="school" name="school" class="form-control" value="<?php echo htmlspecialchars($profile['school']); ?>" required>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="department" class="required-field">Department/Field of Study</label>
                                                    <input type="text" id="department" name="department" class="form-control" value="<?php echo htmlspecialchars($profile['department']); ?>" required>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="level_of_study" class="required-field">Level of Study</label>
                                                    <input type="text" id="level_of_study" name="level_of_study" class="form-control" value="<?php echo htmlspecialchars($profile['level_of_study']); ?>" required>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <h5 class="text-primary mt-4">Internship Details</h5>
                                        <hr>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="internship_start_date" class="required-field">Start Date</label>
                                                    <input type="date" id="internship_start_date" name="internship_start_date" class="form-control" value="<?php echo htmlspecialchars($profile['internship_start_date']); ?>" required>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="internship_end_date" class="required-field">End Date</label>
                                                    <input type="date" id="internship_end_date" name="internship_end_date" class="form-control" value="<?php echo htmlspecialchars($profile['internship_end_date']); ?>" required>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="internship_type" class="required-field">Internship Type</label>
                                                    <select id="internship_type" name="internship_type" class="form-control" required>
                                                        <option value="Academic" <?php echo ($profile['internship_type'] == 'Academic') ? 'selected' : ''; ?>>Academic</option>
                                                        <option value="Professional" <?php echo ($profile['internship_type'] == 'Professional') ? 'selected' : ''; ?>>Professional</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="assigned_department" class="required-field">Assigned Department</label>
                                                    <input type="text" id="assigned_department" name="assigned_department" class="form-control" value="<?php echo htmlspecialchars($profile['assigned_department']); ?>" required>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="supervisor_id" class="required-field">Supervisor ID</label>
                                                    <input type="number" id="supervisor_id" name="supervisor_id" class="form-control" value="<?php echo htmlspecialchars($profile['supervisor_id']); ?>" required>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="profile_picture">Profile Picture</label>
                                                    <input type="file" id="profile_picture" name="profile_picture" class="form-control" accept="image/*">
                                                    <?php if (!empty($profile['profile_picture'])): ?>
                                                        <small class="text-muted">Current: <img src="<?php echo htmlspecialchars($profile['profile_picture']); ?>" style="max-width: 50px; max-height: 50px; border-radius: 5px;"></small>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <h5 class="text-primary mt-4">Additional Information</h5>
                                        <hr>
                                        <div class="form-group">
                                            <label for="skills">Skills</label>
                                            <textarea id="skills" name="skills" class="form-control" rows="3"><?php echo htmlspecialchars($profile['skills']); ?></textarea>
                                        </div>
                                        <div class="form-group">
                                            <label for="previous_experience">Previous Experience</label>
                                            <textarea id="previous_experience" name="previous_experience" class="form-control" rows="3"><?php echo htmlspecialchars($profile['previous_experience']); ?></textarea>
                                        </div>
                                        
                                        <div class="form-group mt-4">
                                            <button type="submit" class="btn btn-primary btn-lg">
                                                <i class="fas fa-save"></i> Update Profile
                                            </button>
                                            <a href="internsprofile.php" class="btn btn-secondary btn-lg">
                                                <i class="fas fa-times"></i> Cancel
                                            </a>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>

    <script src="AdminLTE-master/dist/js/adminlte.min.js"></script>
</body>
</html>