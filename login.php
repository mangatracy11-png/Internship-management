<?php
session_start();
include 'db_connect.php';

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name     = trim($_POST['name']);
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT id, name, role, password FROM users WHERE name = ?");
    $stmt->bind_param("s", $name);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id']  = $user['id'];
        $_SESSION['username'] = $user['name'];
        $_SESSION['role']     = $user['role'];

        if ($user['role'] === 'admin') {
            header("Location: AdminDashboard.php");
            exit;
        } elseif ($user['role'] === 'supervisor') {
            header("Location: supervisordashboard.php");
            exit;
        } elseif ($user['role'] === 'intern') {
            header("Location: rules&permissions.php");
            exit;
        } else {
            $message = "Invalid user role!";
        }
    } else {
        $message = $user ? "Incorrect password!" : "User not found!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login · Internship Portal</title>
    <link rel="stylesheet" href="AdminLTE-master/dist/css/adminlte.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            /* Background image featuring Black professionals working on computers */
            background-image: url('https://images.unsplash.com/photo-1531482615713-2afd69097998?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=2070&q=80');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            background-attachment: fixed;
            position: relative;
            margin: 0;
            padding: 0;
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
            max-width: 420px;
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
        }
        
        .login-logo a {
            font-size: 2rem;
            font-weight: 700;
            color: white;
            text-decoration: none;
            text-shadow: 2px 2px 8px rgba(0,0,0,0.6);
        }
        
        .login-logo .subtitle {
            display: block;
            font-size: 0.78rem;
            color: rgba(255,255,255,0.88);
            font-weight: 400;
            margin-top: 6px;
            text-transform: uppercase;
            letter-spacing: 1.2px;
            text-shadow: 1px 1px 4px rgba(0,0,0,0.5);
        }
        
        .btn-primary {
            background: #2563eb;
            border: none;
            border-radius: 30px;
            padding: 10px;
            font-weight: 600;
            transition: all 0.2s;
        }
        
        .btn-primary:hover {
            background: #1e40af;
            transform: translateY(-1px);
            box-shadow: 0 6px 14px rgba(37,99,235,0.45);
        }
        
        .form-control {
            border-radius: 30px;
            padding: 10px 15px;
            border: 1px solid #e2e8f0;
        }
        
        .form-control:focus {
            box-shadow: 0 0 0 3px rgba(37,99,235,0.2);
            border-color: #2563eb;
        }
        
        .input-group-text {
            border-radius: 0 30px 30px 0;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
        }
        
        .input-group .form-control {
            border-radius: 30px 0 0 30px;
        }
        
        .message {
            color: #dc2626;
            text-align: center;
            margin-bottom: 15px;
            padding: 10px;
            background: rgba(220, 38, 38, 0.1);
            border-radius: 10px;
        }
        
        .forgot-link {
            text-align: right;
            margin: 8px 0 20px;
        }
        
        .forgot-link a {
            color: #2563eb;
            text-decoration: none;
            font-size: 0.9rem;
        }
        
        .forgot-link a:hover {
            text-decoration: underline;
        }
        
        .register-link {
            text-align: center;
            margin-top: 20px;
        }
        
        .register-link a {
            color: #2563eb;
            font-weight: 600;
            text-decoration: none;
        }
        
        .register-link a:hover {
            text-decoration: underline;
        }
        
        .text-muted {
            color: #64748b !important;
        }
        
        /* Responsive adjustments */
        @media (max-width: 576px) {
            .login-logo a {
                font-size: 1.5rem;
            }
            
            .login-logo .subtitle {
                font-size: 0.7rem;
            }
            
            .card-body {
                padding: 1.5rem !important;
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
    <div class="login-box">
        <div class="login-logo text-center mb-4">
            <a href="#">INTERNSHIP PORTAL</a>
            <span class="subtitle">DESIGN AND IMPLEMENTATION OF A WEB BASE APPLICATION FOR INTERNSHIP</span>
        </div>
        
        <div class="card">
            <div class="card-body p-4">
                <p class="text-center text-muted mb-4">Sign in to your account</p>

                <?php if ($message): ?>
                    <div class="message"><?= htmlspecialchars($message) ?></div>
                <?php endif; ?>

                <form method="POST" action="">
                    <div class="input-group mb-3">
                        <input type="text" name="name" class="form-control" placeholder="Username" required>
                        <div class="input-group-text">
                            <span class="bi bi-person"></span>
                        </div>
                    </div>
                    
                    <div class="input-group mb-2">
                        <input type="password" name="password" class="form-control" placeholder="Password" required>
                        <div class="input-group-text">
                            <span class="bi bi-lock-fill"></span>
                        </div>
                    </div>
                    
                    <div class="forgot-link">
                        <a href="forgot_password.php">Forgot password?</a>
                    </div>

                    <button type="submit" class="btn btn-primary w-100">Login</button>

                    <div class="register-link">
                        <a href="register.php">Don't have an account? Register</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Hidden image preloader for faster loading -->
    <div style="display: none;">
        <img src="https://images.unsplash.com/photo-1531482615713-2afd69097998?ixlib=rb-4.0.3&auto=format&fit=crop&w=2070&q=80" alt="Background Preloader">
    </div>
    
    <script src="AdminLTE-master/dist/js/adminlte.min.js"></script>
</body>
</html>