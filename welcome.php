<?php
// Optional: If you want to redirect logged-in users to their dashboard,
// you can uncomment the lines below and start the session.
// session_start();
// if (isset($_SESSION['user_id'])) {
//     if ($_SESSION['role'] === 'admin') header("Location: AdminDashboard.php");
//     elseif ($_SESSION['role'] === 'supervisor') header("Location: supervisordashboard.php");
//     elseif ($_SESSION['role'] === 'intern') header("Location: rules&permissions.php");
//     exit;
// }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome · CENADI Internship Portal</title>
    <!-- Bootstrap 5 & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <!-- Google Fonts Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Inter', sans-serif;
            background: #f8fafc;
            color: #1e293b;
        }
        /* Navbar */
        .navbar {
            background: white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            padding: 1rem 2rem;
        }
        .navbar-brand {
            font-weight: 700;
            color: #2563eb;
            font-size: 1.5rem;
            letter-spacing: -0.5px;
        }
        .navbar-brand span {
            color: #475569;
            font-weight: 400;
            font-size: 1rem;
        }
        .nav-link {
            color: #475569;
            font-weight: 500;
            margin-left: 1.5rem;
        }
        .nav-link:hover {
            color: #2563eb;
        }
        .btn-outline-primary {
            border-color: #2563eb;
            color: #2563eb;
            border-radius: 30px;
            padding: 0.5rem 1.5rem;
            font-weight: 500;
        }
        .btn-outline-primary:hover {
            background: #2563eb;
            color: white;
        }
        .btn-primary {
            background: #2563eb;
            border: none;
            border-radius: 30px;
            padding: 0.5rem 1.8rem;
            font-weight: 500;
        }
        .btn-primary:hover {
            background: #1e40af;
        }

        /* Hero Section */
        .hero {
            padding: 5rem 2rem;
            background: linear-gradient(135deg, #ffffff 0%, #f1f5f9 100%);
        }
        .hero h1 {
            font-size: 3rem;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 1.5rem;
        }
        .hero .highlight {
            color: #2563eb;
        }
        .hero p {
            font-size: 1.1rem;
            color: #475569;
            margin-bottom: 2rem;
        }
        .hero-image {
            max-width: 100%;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
        }

        /* Features Section */
        .features {
            padding: 5rem 2rem;
            background: white;
        }
        .section-title {
            text-align: center;
            margin-bottom: 3rem;
        }
        .section-title h2 {
            font-size: 2.2rem;
            font-weight: 700;
            color: #0f172a;
        }
        .section-title p {
            color: #64748b;
            font-size: 1rem;
        }
        .feature-card {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 24px;
            padding: 2rem 1.5rem;
            text-align: center;
            transition: transform 0.2s, box-shadow 0.2s;
            height: 100%;
        }
        .feature-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(0,0,0,0.1);
        }
        .feature-icon {
            width: 70px;
            height: 70px;
            background: #eff6ff;
            color: #2563eb;
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            margin: 0 auto 1.5rem;
        }
        .feature-card h4 {
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 0.8rem;
        }
        .feature-card p {
            color: #64748b;
            font-size: 0.9rem;
            margin-bottom: 0;
        }

        /* About CENADI */
        .about {
            padding: 5rem 2rem;
            background: #f1f5f9;
        }
        .about h2 {
            font-size: 2rem;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 1.5rem;
        }
        .about p {
            color: #334155;
            font-size: 1rem;
            line-height: 1.7;
        }
        .about img {
            max-width: 100%;
            border-radius: 20px;
            box-shadow: 0 15px 30px rgba(0,0,0,0.1);
        }

        /* Footer */
        footer {
            background: white;
            padding: 2rem;
            text-align: center;
            color: #64748b;
            border-top: 1px solid #e2e8f0;
        }

        @media (max-width: 768px) {
            .hero h1 { font-size: 2.2rem; }
            .hero { text-align: center; }
            .navbar-nav { margin-top: 1rem; }
        }
    </style>
</head>
<body>

    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg">
        <div class="container">
            <a class="navbar-brand" href="#">CENADI <span>| Internship Portal</span></a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item"><a class="nav-link" href="#features">Features</a></li>
                    <li class="nav-item"><a class="nav-link" href="#about">About</a></li>
                    <li class="nav-item"><a class="btn btn-outline-primary ms-lg-3 mt-2 mt-lg-0" href="login.php">Login</a></li>
                    <li class="nav-item"><a class="btn btn-primary ms-lg-2 mt-2 mt-lg-0" href="register.php">Register</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <h1>Welcome to the <span class="highlight">CENADI Internship</span> Portal</h1>
                    <p>A centralized platform to manage internship applications, track progress, and facilitate communication between interns, supervisors, and administrators.</p>
                    <div class="d-flex gap-3 flex-wrap">
                        <a href="register.php" class="btn btn-primary btn-lg px-4">Get Started</a>
                        <a href="login.php" class="btn btn-outline-primary btn-lg px-4">Login</a>
                    </div>
                </div>
                <div class="col-lg-6 mt-5 mt-lg-0">
                    <!-- Replace with your own relevant image or use a free illustration -->
                    <img src="https://images.unsplash.com/photo-1522071820081-009f0129c71c?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80" alt="Interns collaborating" class="hero-image">
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features" id="features">
        <div class="container">
            <div class="section-title">
                <h2>Why Use This Portal?</h2>
                <p>Designed to streamline the entire internship lifecycle</p>
            </div>
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="feature-card">
                        <div class="feature-icon"><i class="bi bi-file-earmark-text-fill"></i></div>
                        <h4>Application Management</h4>
                        <p>Interns can submit applications, upload documents, and track their status. Admins can review and approve with ease.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-card">
                        <div class="feature-icon"><i class="bi bi-bar-chart-fill"></i></div>
                        <h4>Progress Tracking</h4>
                        <p>Supervisors can monitor intern performance, log feedback, and generate reports – all in one place.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-card">
                        <div class="feature-icon"><i class="bi bi-chat-dots-fill"></i></div>
                        <h4>Integrated Messaging</h4>
                        <p>Seamless communication between interns, supervisors, and admins to resolve queries and share updates.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- About CENADI -->
    <section class="about" id="about">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <h2>About CENADI</h2>
                    <p><strong>Centre National de Développement de l'Informatique</strong> (CENADI) is a government IT agency based in Yaoundé, Cameroon. We support public institutions by managing data centers, developing software, securing digital services, and promoting digital transformation in the public sector. This internship portal is part of our commitment to nurturing young talent and providing them with real‑world experience in IT.</p>
                    <p>Our mission is to deliver high‑quality IT services and infrastructure, ensuring data security, software development, and digital innovation for Cameroon's public sector.</p>
                </div>
                <div class="col-lg-6 mt-4 mt-lg-0">
                    <img src="cenadi.jpeg" alt="CENADI building or team" class="about-image" style="width:100%; border-radius:20px;">
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer>
        <div class="container">
            <p>&copy; <?= date('Y') ?> CENADI – Ministry of Finance, Cameroon. All rights reserved.</p>
            <p class="small">Designed for the internship management system.</p>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>