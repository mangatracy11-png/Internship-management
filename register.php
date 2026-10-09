<?php
include 'db_connect.php'; // connect to the database

$message = '';
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    // Encrypt password (good practice)
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Insert data into users table with role as 'intern'
    $sql = "INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'intern')";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $name, $email, $hashedPassword);

    if ($stmt->execute()) {
        // Get the newly created user's ID
        $new_user_id = $stmt->insert_id;
        
        // Automatically create an application entry for the new intern
        $app_sql = "INSERT INTO applications (intern_id, position, department, status) VALUES (?, 'Intern Position', 'General Department', 'pending')";
        $app_stmt = $conn->prepare($app_sql);
        $app_stmt->bind_param("i", $new_user_id);
        
        if ($app_stmt->execute()) {
            $message = '<div class="alert alert-success">✅ Registration successful! Your application is pending approval. Welcome, ' . htmlspecialchars($name) . '</div>';
            $app_stmt->close();
            
            // Redirect to login after successful registration
            header("Location: login.php");
            exit();
        } else {
            $message = '<div class="alert alert-warning">⚠️ User created but application entry failed: ' . $app_stmt->error . '</div>';
            $app_stmt->close();
        }
    } else {
        $message = '<div class="alert alert-danger">❌ Error: ' . $stmt->error . '</div>';
    }

    $stmt->close();
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register · Internship Portal</title>
    <!-- AdminLTE CSS -->
    <link rel="stylesheet" href="AdminLTE-master/dist/css/adminlte.min.css">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            /* Same background image as login page - Black professionals working on computers */
            background-image: url('https://images.unsplash.com/photo-1531482615713-2afd69097998?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=2070&q=80');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            background-attachment: fixed;
            position: relative;
            margin: 0;
            padding: 0;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
        }
        
        /* Very light overlay - people and computers stay clearly visible */
        body::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(10, 30, 80, 0.22);
            z-index: 0;
            pointer-events: none;
        }
        
        .login-box {
            width: 100%;
            max-width: 450px;
            position: relative;
            z-index: 1;
            margin: 20px;
        }
        
        .card {
            border: none;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.4);
            background: rgba(255,255,255,0.92);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255,255,255,0.2);
        }
        
        .login-logo {
            text-align: center;
            margin-bottom: 1.5rem;
        }
        
        .login-logo a {
            font-size: 2rem;
            font-weight: 700;
            color: white;
            text-decoration: none;
            text-shadow: 2px 2px 8px rgba(0,0,0,0.6);
            letter-spacing: 1px;
        }
        
        .login-logo .subtitle {
            display: block;
            font-size: 0.75rem;
            color: rgba(255,255,255,0.88);
            font-weight: 400;
            margin-top: 8px;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            text-shadow: 1px 1px 4px rgba(0,0,0,0.5);
        }
        
        .login-card-body {
            padding: 2rem !important;
        }
        
        .login-box-msg {
            font-size: 1.1rem;
            color: #1e293b;
            font-weight: 500;
            margin-bottom: 1.5rem;
            text-align: center;
        }
        
        .btn-primary {
            background: #2563eb;
            border: none;
            border-radius: 30px;
            padding: 12px;
            font-weight: 600;
            font-size: 1rem;
            transition: all 0.3s ease;
            width: 100%;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .btn-primary:hover {
            background: #1e40af;
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(37,99,235,0.4);
        }
        
        .form-control {
            border-radius: 30px;
            padding: 12px 18px;
            border: 1px solid #e2e8f0;
            font-size: 0.95rem;
            transition: all 0.3s;
            height: auto;
        }
        
        .form-control:focus {
            box-shadow: 0 0 0 3px rgba(37,99,235,0.2);
            border-color: #2563eb;
            outline: none;
        }
        
        .input-group-text {
            border-radius: 0 30px 30px 0;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-left: none;
            padding: 0 18px;
        }
        
        .input-group .form-control {
            border-radius: 30px 0 0 30px;
            border-right: none;
        }
        
        .input-group:focus-within .input-group-text {
            border-color: #2563eb;
            background: white;
        }
        
        .input-group:focus-within .form-control {
            border-color: #2563eb;
        }
        
        .alert {
            border-radius: 15px;
            padding: 12px 15px;
            margin-bottom: 20px;
            border: none;
            font-size: 0.95rem;
        }
        
        .alert-success {
            background: rgba(16, 185, 129, 0.15);
            color: #065f46;
            border-left: 4px solid #10b981;
        }
        
        .alert-danger {
            background: rgba(239, 68, 68, 0.15);
            color: #991b1b;
            border-left: 4px solid #ef4444;
        }
        
        .alert-warning {
            background: rgba(245, 158, 11, 0.15);
            color: #92400e;
            border-left: 4px solid #f59e0b;
        }
        
        .form-check-label {
            color: #334155;
            font-size: 0.9rem;
        }
        
        .form-check-input {
            border-radius: 4px;
            border: 1px solid #cbd5e1;
        }
        
        .form-check-input:checked {
            background-color: #2563eb;
            border-color: #2563eb;
        }
        
        .mb-1 a {
            color: #2563eb;
            text-decoration: none;
            font-weight: 500;
            font-size: 0.95rem;
            transition: color 0.3s;
            display: inline-block;
            margin-top: 15px;
        }
        
        .mb-1 a:hover {
            color: #1e40af;
            text-decoration: underline;
        }
        
        .mb-1 i {
            margin-right: 5px;
        }
        
        .row {
            margin-top: 20px;
        }
        
        /* Responsive adjustments */
        @media (max-width: 576px) {
            .login-logo a {
                font-size: 1.5rem;
            }
            
            .login-logo .subtitle {
                font-size: 0.65rem;
                letter-spacing: 1px;
            }
            
            .login-card-body {
                padding: 1.5rem !important;
            }
            
            .login-box {
                margin: 15px;
            }
            
            .btn-primary {
                padding: 10px;
            }
            
            .form-control {
                padding: 10px 15px;
            }
        }
        
        /* High-resolution screens */
        @media (-webkit-min-device-pixel-ratio: 2), (min-resolution: 192dpi) {
            body {
                /* Higher quality image for retina displays */
                background-image: url('https://images.unsplash.com/photo-1531482615713-2afd69097998?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=2070&q=90');
            }
        }
    </style>
</head>
<body>
    <!-- Hidden image preloader for faster loading -->
    <div style="display: none;">
        <img src="https://images.unsplash.com/photo-1531482615713-2afd69097998?ixlib=rb-4.0.3&auto=format&fit=crop&w=2070&q=80" alt="Background Preloader">
    </div>
    
    <div class="login-box">
        <div class="login-logo">
            <a href="index.php"><b>INTERNSHIP PORTAL</b></a>
            <span class="subtitle">DESIGN AND IMPLEMENTATION OF A WEB BASE APPLICATION FOR INTERNSHIP</span>
        </div>
        <!-- /.login-logo -->
        <div class="card">
            <div class="card-body login-card-body">
                <p class="login-box-msg">
                    <i class="bi bi-person-plus-fill me-2"></i>
                    Create your account
                </p>
                
                <?php echo $message; ?>
                
                <form method="POST" action="">
                    <div class="input-group mb-3">
                        <input type="text" id="username" name="username" class="form-control" placeholder="Username" required>
                        <div class="input-group-text">
                            <span class="bi bi-person"></span>
                        </div>
                    </div>
                    
                    <div class="input-group mb-3">
                        <input type="email" id="email" name="email" class="form-control" placeholder="Email address" required>
                        <div class="input-group-text">
                            <span class="bi bi-envelope"></span> 
                        </div>
                    </div>
                    
                    <div class="input-group mb-3">
                        <input type="password" id="password" name="password" class="form-control" placeholder="Password" required>
                        <div class="input-group-text">
                            <span class="bi bi-lock-fill"></span>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-7">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" value="" id="flexCheckDefault">
                                <label class="form-check-label" for="flexCheckDefault">
                                    I agree to the <a href="#" style="color: #2563eb;">terms</a>
                                </label>
                            </div>
                        </div>
                        <!-- /.col -->
                        <div class="col-5">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle me-2"></i>Register
                            </button>
                        </div>
                        <!-- /.col -->
                    </div>
                    <!--end::Row-->
                    
                    <p class="mb-1 text-center">
                        <a href="login.php">
                            <i class="bi bi-box-arrow-in-right me-1"></i>
                            Already have an account? Sign in
                        </a>
                    </p>
                </form>
            </div>
            <!-- /.login-card-body -->
        </div>
    </div>
    <!-- /.login-box -->

    <!-- AdminLTE JS -->
    <script src="AdminLTE-master/dist/js/adminlte.min.js"></script>
</body>
</html>