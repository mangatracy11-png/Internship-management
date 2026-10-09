<?php
// ============================================
//  BACKEND LOGIC – UNCHANGED, ROBUST
// ============================================
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/db_connect.php';

$userId = $_SESSION['user_id'];
$profileExists = false;

$sql = "SELECT 1 FROM intern_profiles WHERE user_id = ? LIMIT 1";
$stmt = $conn->prepare($sql);

if ($stmt) {
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $stmt->store_result();
    $profileExists = $stmt->num_rows > 0;
    $stmt->close();
} else {
    error_log('Prepare failed: ' . $conn->error);
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.5">
    <title>Intern Dashboard · Profile</title>
    
    <!-- Font Awesome 6 (free) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    
    <!-- Google Font: Inter – clean, modern, highly readable -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,400;14..32,500;14..32,600&display=swap" rel="stylesheet">
    
    <style>
        /* ---------- GLOBAL PAGE STYLES – PROFESSIONAL BACKGROUND ---------- */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background-color: #fafbfc;   /* Soft, premium off-white */
            color: #1e293b;
            line-height: 1.5;
            padding: 2rem 1.5rem;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* Subtle texture – adds depth without distraction */
        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image: radial-gradient(circle at 20% 30%, rgba(0,0,0,0.01) 0%, transparent 30%);
            pointer-events: none;
            z-index: -1;
        }

        /* Main content container – centered, card-like */
        .dashboard-container {
            max-width: 1200px;
            width: 100%;
            margin: 0 auto;
            background: rgba(255,255,255,0.7); /* Frosted glass effect */
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
            border-radius: 32px;
            padding: 2rem;
            box-shadow: 0 10px 25px rgba(0,0,0,0.02), 0 2px 5px rgba(0,0,0,0.01);
            border: 1px solid rgba(255,255,255,0.5);
        }

        /* Header area with greeting */
        .page-header {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 2rem;
        }

        .page-header h1 {
            font-weight: 600;
            font-size: 1.75rem;
            letter-spacing: -0.01em;
            color: #0f172a;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .page-header h1 i {
            color: #3b82f6;
            font-size: 1.9rem;
        }

        /* ---------- PROFILE NAVIGATION – ELEVATED, PILL BACKGROUND ---------- */
        .profile-nav-container {
            background: white;
            border-radius: 60px;
            padding: 0.4rem 0.6rem;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.02), 0 0 0 1px rgba(0,0,0,0.01);
            display: inline-flex;
            width: auto;
        }

        .profile-nav {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 0.2rem;
            list-style: none;
            margin: 0;
            padding: 0;
        }

        .profile-nav .nav-link {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.6rem 1.3rem;
            background: transparent;
            color: #334155;
            font-weight: 500;
            font-size: 0.95rem;
            text-decoration: none;
            border-radius: 40px;
            transition: all 0.18s ease;
            border: 1px solid transparent;
            white-space: nowrap;
        }

        .profile-nav .nav-link i {
            font-size: 1.1em;
            color: #64748b;
            transition: color 0.18s;
        }

        .profile-nav .nav-link:hover {
            background-color: #f1f5f9;
            color: #2563eb;
            border-color: #e2e8f0;
        }

        .profile-nav .nav-link:hover i {
            color: #2563eb;
        }

        /* Primary action – Create Profile */
        .profile-nav .btn-create {
            background: linear-gradient(145deg, #2563eb, #1d4ed8);
            color: white;
            border: none;
            box-shadow: 0 2px 6px rgba(37, 99, 235, 0.2);
        }

        .profile-nav .btn-create i {
            color: white;
        }

        .profile-nav .btn-create:hover {
            background: linear-gradient(145deg, #1d4ed8, #1e40af);
            color: white;
            box-shadow: 0 8px 14px rgba(37, 99, 235, 0.25);
            transform: translateY(-1px);
        }

        /* Active page indicator */
        .profile-nav .nav-link[aria-current="page"] {
            background-color: #eff6ff;
            color: #1e40af;
            font-weight: 600;
            border-left: 4px solid #2563eb;
            border-radius: 40px 4px 4px 40px;
        }

        .profile-nav .nav-link[aria-current="page"] i {
            color: #1e40af;
        }

        /* ---------- PLACEHOLDER CONTENT – MAKES PAGE FEEL REAL ---------- */
        .welcome-card {
            background: white;
            border-radius: 24px;
            padding: 1.8rem 2rem;
            margin-top: 2rem;
            box-shadow: 0 5px 15px rgba(0,0,0,0.01);
            border: 1px solid #f1f5f9;
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 1.5rem;
        }

        .welcome-card p {
            font-size: 1.1rem;
            color: #334155;
            flex: 1;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
            gap: 1rem;
            margin-top: 1.8rem;
        }

        .stat-card {
            background: white;
            border-radius: 20px;
            padding: 1.2rem 1rem;
            border: 1px solid #f1f5f9;
            display: flex;
            align-items: center;
            gap: 0.8rem;
            transition: all 0.2s;
        }

        .stat-card:hover {
            border-color: #cbd5e1;
            background: #ffffff;
        }

        .stat-icon {
            background: #f1f5f9;
            width: 44px;
            height: 44px;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #2563eb;
            font-size: 1.2rem;
        }

        .stat-info h4 {
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.03em;
            color: #64748b;
            margin-bottom: 0.2rem;
        }

        .stat-info span {
            font-size: 1.4rem;
            font-weight: 600;
            color: #0f172a;
        }

        /* Mobile refinement */
        @media (max-width: 640px) {
            body { padding: 1.5rem 1rem; }
            .dashboard-container { padding: 1.5rem; }
            .profile-nav-container { width: 100%; }
            .profile-nav { flex-wrap: wrap; width: 100%; }
            .profile-nav .nav-link { 
                flex: 1 1 auto; 
                justify-content: center; 
                white-space: normal;
                padding: 0.6rem 0.8rem;
            }
            .page-header { flex-direction: column; align-items: flex-start; gap: 1rem; }
        }
    </style>
</head>
<body>

    <div class="dashboard-container">
        <!-- HEADER WITH GREETING -->
        <div class="page-header">
            <h1>
                <i class="fas fa-id-card"></i> 
                <?= $profileExists ? 'My Profile' : 'Intern Profile' ?>
            </h1>
            
            <!-- ========== PROFILE‑AWARE NAVIGATION ========== -->
            <div class="profile-nav-container">
                <ul class="profile-nav">
                    <?php if ($profileExists): ?>
                        <li>
                            <a href="internsprofile.php" class="nav-link"
                               <?= basename($_SERVER['PHP_SELF']) == 'internsprofile.php' ? 'aria-current="page"' : '' ?>>
                                <i class="fas fa-user-circle"></i> View
                            </a>
                        </li>
                        <li>
                            <a href="editprofile.php" class="nav-link"
                               <?= basename($_SERVER['PHP_SELF']) == 'editprofile.php' ? 'aria-current="page"' : '' ?>>
                                <i class="fas fa-edit"></i> Edit
                            </a>
                        </li>
                    <?php else: ?>
                        <li>
                            <a href="createprofile.php" class="nav-link btn-create"
                               <?= basename($_SERVER['PHP_SELF']) == 'createprofile.php' ? 'aria-current="page"' : '' ?>>
                                <i class="fas fa-plus-circle"></i> Create Profile
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>

        <!-- WELCOME / STATUS CARD – DYNAMIC CONTENT BASED ON PROFILE -->
        <?php if ($profileExists): ?>
            <div class="welcome-card">
                <i class="fas fa-check-circle" style="font-size: 2rem; color: #22c55e;"></i>
                <p><strong>Your intern profile is complete.</strong> You can view or edit it anytime.</p>
            </div>
        <?php else: ?>
            <div class="welcome-card">
                <i class="fas fa-sparkles" style="font-size: 2rem; color: #3b82f6;"></i>
                <p><strong>Welcome! You haven’t created your intern profile yet.</strong> Get started now  it only takes few minute.</p>
            </div>
        <?php endif; ?>

        <!-- EXAMPLE STATISTICS – ILLUSTRATES PROFESSIONAL DASHBOARD LAYOUT -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-file-alt"></i></div>
                <div class="stat-info">
                    <h4>Profile</h4>
                    <span><?= $profileExists ? 'Active' : 'Draft' ?></span>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-calendar"></i></div>
                <div class="stat-info">
                    <h4>Internship</h4>
                    <span>2026</span>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-tasks"></i></div>
                <div class="stat-info">
                    <h4>Tasks</h4>
                    <span>—</span>
                </div>
            </div>
        </div>
    </div>

    <!-- SIMPLE FOOTER (optional) -->
    <footer style="max-width: 1200px; margin: 2rem auto 0; text-align: center; color: #94a3b8; font-size: 0.8rem; border-top: 1px solid #e2e8f0; padding-top: 1.5rem;">
        <p>© <?= date('Y') ?> Internship Portal. Professional experience starts here.</p>
    </footer>

</body>
</html>