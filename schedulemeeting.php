<?php 
session_start();
include 'db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'supervisor') {
    header("location: login.php");
    exit();
}

$message = "";
$success_message = "";
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $intern_id = $_POST['intern_id'];        
    $title = trim($_POST['title']);                
    $meeting_date = $_POST['meeting_date'];  
    $start_time = $_POST['start_time'];      
    $end_time = $_POST['end_time'];
    $meeting_type = $_POST['meeting_type'];
    $location = trim($_POST['location']);
    $description = trim($_POST['description']);
    
    // Validation
    $errors = [];
    
    if (empty($intern_id)) {
        $errors[] = "Please select an intern";
    }
    
    if (empty($title)) {
        $errors[] = "Meeting title is required";
    }
    
    if (empty($meeting_date)) {
        $errors[] = "Meeting date is required";
    }
    
    if (empty($start_time) || empty($end_time)) {
        $errors[] = "Both start and end times are required";
    }
    
    if (strtotime($end_time) <= strtotime($start_time)) {
        $errors[] = "End time must be after start time";
    }
    
    if (empty($location)) {
        $errors[] = "Location/Meeting link is required";
    }
    
    // Check if date is not in past
    if ($meeting_date < date('Y-m-d')) {
        $errors[] = "Meeting date cannot be in the past";
    }
    
    if (empty($errors)) {
        // Check for time conflicts
        $conflict_sql = "SELECT id FROM meetings WHERE intern_id = ? 
                        AND meeting_date = ? 
                        AND ((start_time <= ? AND end_time > ?) 
                        OR (start_time < ? AND end_time >= ?))";
        $conflict_stmt = $conn->prepare($conflict_sql);
        $conflict_stmt->bind_param("isssss", $intern_id, $meeting_date, 
                                  $end_time, $start_time, $end_time, $start_time);
        $conflict_stmt->execute();
        $conflict_result = $conflict_stmt->get_result();
        
        if ($conflict_result->num_rows > 0) {
            $message = "<div class='alert alert-error'>This intern already has a meeting scheduled during this time.</div>";
        } else {
            // Insert meeting
            $supervisor_id = $_SESSION['user_id'];
            $sql = "INSERT INTO meetings (supervisor_id, intern_id, title, description, 
                    meeting_date, start_time, end_time, meeting_type, location, status) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'scheduled')";
            
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("iisssssss", $supervisor_id, $intern_id, $title, $description,
                              $meeting_date, $start_time, $end_time, $meeting_type, $location);
            
            if ($stmt->execute()) {
                $meeting_id = $conn->insert_id;
                
                // Send notification to intern
                $notif_sql = "INSERT INTO notifications (user_id, type, title, message, meeting_id, `read`, created_at) 
                              VALUES (?, 'meeting_scheduled', ?, ?, ?, 0, NOW())";
                $notif_stmt = $conn->prepare($notif_sql);
                $notif_title = "New Meeting Scheduled: " . $title;
                $notif_message = "Your supervisor has scheduled a meeting for " . date('l, F j', strtotime($meeting_date)) . " at " . date('g:i A', strtotime($start_time)) . ". Location/Link: " . $location;
                $notif_stmt->bind_param("issi", $intern_id, $notif_title, $notif_message, $meeting_id);
                $notif_stmt->execute();
                $notif_stmt->close();
                
                $success_message = "<div class='alert alert-success'>✓ Meeting scheduled successfully! Notification sent to intern 📅</div>";
                $_POST = array();
            } else {
                $message = "<div class='alert alert-error'>✗ Error scheduling meeting: " . $conn->error . "</div>";
            }
            $stmt->close();
        }
        $conflict_stmt->close();
    } else {
        $message = "<div class='alert alert-error'>";
        foreach ($errors as $error) {
            $message .= "✗ " . $error . "<br>";
        }
        $message .= "</div>";
    }
}

$interns_sql = "SELECT id, name, email FROM users WHERE role = 'intern' AND status = 'approved' ORDER BY name";
$interns_result = $conn->query($interns_sql);

// Get upcoming meetings for the sidebar
$upcoming_sql = "SELECT m.*, u.name as intern_name 
                FROM meetings m 
                JOIN users u ON m.intern_id = u.id 
                WHERE m.supervisor_id = ? 
                AND m.meeting_date >= CURDATE() 
                AND m.status = 'scheduled'
                ORDER BY m.meeting_date ASC, m.start_time ASC 
                LIMIT 20";
$upcoming_stmt = $conn->prepare($upcoming_sql);
$upcoming_stmt->bind_param("i", $_SESSION['user_id']);
$upcoming_stmt->execute();
$upcoming_result = $upcoming_stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Schedule Meeting | Supervisor Portal</title>
    <style>
        /* ===== PROFESSIONAL SCHEDULING SYSTEM ===== */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
            overflow: hidden;
        }
        
        /* Header */
        .header {
            background: linear-gradient(90deg, #4a6cf7 0%, #2e4b8f 100%);
            color: white;
            padding: 25px 40px;
            position: relative;
        }
        
        .header:after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #ff6b6b, #4a6cf7, #1dd1a1);
        }
        
        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .header h1 {
            font-size: 28px;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .header-icon {
            background: rgba(255, 255, 255, 0.2);
            width: 55px;
            height: 55px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            backdrop-filter: blur(10px);
        }
        
        .user-profile {
            display: flex;
            align-items: center;
            gap: 15px;
            background: rgba(255, 255, 255, 0.15);
            padding: 12px 25px;
            border-radius: 30px;
            backdrop-filter: blur(10px);
        }
        
        .user-avatar {
            width: 45px;
            height: 45px;
            background: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #4a6cf7;
            font-weight: bold;
            font-size: 18px;
        }
        
        /* Navigation */
        .navbar {
            background: #f8fafc;
            padding: 0;
            border-bottom: 1px solid #e2e8f0;
        }
        
        .nav-container {
            display: flex;
            padding: 0 40px;
        }
        
        .nav-link {
            padding: 20px 25px;
            color: #64748b;
            text-decoration: none;
            font-weight: 600;
            font-size: 15px;
            transition: all 0.3s ease;
            position: relative;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .nav-link:hover {
            color: #4a6cf7;
            background: #f1f5f9;
        }
        
        .nav-link.active {
            color: #4a6cf7;
            background: linear-gradient(to bottom, rgba(74, 108, 247, 0.05), transparent);
        }
        
        .nav-link.active:after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: #4a6cf7;
            border-radius: 3px 3px 0 0;
        }
        
        /* Messages */
        .alert {
            padding: 18px 25px;
            border-radius: 12px;
            margin: 25px 40px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 12px;
            animation: slideIn 0.5s ease;
        }
        
        .alert-success {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            box-shadow: 0 5px 15px rgba(16, 185, 129, 0.3);
        }
        
        .alert-error {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            color: white;
            box-shadow: 0 5px 15px rgba(239, 68, 68, 0.3);
        }
        
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        /* Main Content */
        .content-wrapper {
            display: flex;
            min-height: 600px;
            padding: 0;
        }
        
        /* Form Panel */
        .form-panel {
            flex: 3;
            padding: 40px;
            background: white;
        }
        
        .panel-title {
            font-size: 24px;
            color: #1e293b;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #f1f5f9;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .panel-title:before {
            content: '📋';
            font-size: 28px;
        }
        
        /* Form Styles */
        .form-group {
            margin-bottom: 25px;
        }
        
        .form-label {
            display: block;
            margin-bottom: 10px;
            color: #475569;
            font-weight: 600;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .form-control {
            width: 100%;
            padding: 16px 20px;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            font-size: 15px;
            transition: all 0.3s ease;
            background: #f8fafc;
            color: #334155;
        }
        
        .form-control:focus {
            outline: none;
            border-color: #4a6cf7;
            box-shadow: 0 0 0 3px rgba(74, 108, 247, 0.15);
            background: white;
            transform: translateY(-2px);
        }
        
        .form-control::placeholder {
            color: #94a3b8;
        }
        
        select.form-control {
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='%234a6cf7' viewBox='0 0 16 16'%3E%3Cpath d='M7.247 11.14L2.451 5.658C1.885 5.013 2.345 4 3.204 4h9.592a1 1 0 0 1 .753 1.659l-4.796 5.48a1 1 0 0 1-1.506 0z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 20px center;
            padding-right: 50px;
        }
        
        textarea.form-control {
            min-height: 120px;
            resize: vertical;
            line-height: 1.6;
        }
        
        /* Time Group */
        .time-group {
            display: flex;
            gap: 20px;
        }
        
        .time-group .form-group {
            flex: 1;
        }
        
        /* Buttons */
        .form-actions {
            display: flex;
            gap: 15px;
            margin-top: 40px;
            padding-top: 30px;
            border-top: 2px solid #f1f5f9;
        }
        
        .btn {
            padding: 16px 35px;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            text-decoration: none;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #4a6cf7 0%, #3b5bdb 100%);
            color: white;
            box-shadow: 0 5px 20px rgba(74, 108, 247, 0.4);
        }
        
        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(74, 108, 247, 0.5);
        }
        
        .btn-secondary {
            background: #f1f5f9;
            color: #475569;
            border: 2px solid #e2e8f0;
        }
        
        .btn-secondary:hover {
            background: #e2e8f0;
            transform: translateY(-3px);
        }
        
        /* Upcoming Meetings Panel */
        .upcoming-panel {
            flex: 2;
            background: #f8fafc;
            padding: 40px;
            border-left: 1px solid #e2e8f0;
        }
        
        .upcoming-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }
        
        .upcoming-title {
            font-size: 20px;
            color: #1e293b;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .upcoming-title:before {
            content: '📅';
        }
        
        .upcoming-count {
            background: linear-gradient(135deg, #4a6cf7 0%, #3b5bdb 100%);
            color: white;
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 600;
        }
        
        /* Meeting Cards */
        .meeting-card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 15px;
            border: 1px solid #e2e8f0;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .meeting-card:before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            width: 4px;
            background: #4a6cf7;
        }
        
        .meeting-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            border-color: #cbd5e1;
        }
        
        .meeting-card.virtual:before {
            background: #10b981;
        }
        
        .meeting-card.in-person:before {
            background: #f59e0b;
        }
        
        .meeting-title {
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 8px;
            font-size: 16px;
        }
        
        .meeting-details {
            color: #64748b;
            font-size: 14px;
            margin-bottom: 5px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .meeting-time {
            background: linear-gradient(135deg, #4a6cf7 0%, #3b5bdb 100%);
            color: white;
            padding: 8px 15px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 600;
            display: inline-block;
            margin-top: 10px;
            box-shadow: 0 3px 10px rgba(74, 108, 247, 0.3);
        }
        
        .meeting-type {
            display: inline-block;
            padding: 5px 12px;
            border-radius: 15px;
            font-size: 12px;
            font-weight: 600;
            margin-left: 10px;
        }
        
        .type-virtual {
            background: #d1fae5;
            color: #065f46;
        }
        
        .type-in-person {
            background: #fef3c7;
            color: #92400e;
        }
        
        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 50px 20px;
        }
        
        .empty-icon {
            font-size: 60px;
            margin-bottom: 20px;
            opacity: 0.5;
        }
        
        .empty-state h3 {
            color: #94a3b8;
            margin-bottom: 10px;
            font-weight: 600;
        }
        
        .empty-state p {
            color: #cbd5e1;
            font-size: 14px;
        }
        
        /* View All Button */
        .view-all-btn {
            display: block;
            text-align: center;
            margin-top: 25px;
            padding: 12px;
            background: #f1f5f9;
            color: #475569;
            text-decoration: none;
            border-radius: 10px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .view-all-btn:hover {
            background: #e2e8f0;
            transform: translateY(-2px);
        }
        
        /* Responsive Design */
        @media (max-width: 1024px) {
            .content-wrapper {
                flex-direction: column;
            }
            
            .upcoming-panel {
                border-left: none;
                border-top: 1px solid #e2e8f0;
            }
        }
        
        @media (max-width: 768px) {
            .header-content {
                flex-direction: column;
                gap: 15px;
                text-align: center;
            }
            
            .nav-container {
                flex-direction: column;
                padding: 0;
            }
            
            .nav-link {
                justify-content: center;
                border-bottom: 1px solid #e2e8f0;
            }
            
            .time-group {
                flex-direction: column;
                gap: 15px;
            }
            
            .form-actions {
                flex-direction: column;
            }
            
            .btn {
                width: 100%;
            }
            
            .container {
                margin: 10px;
                border-radius: 15px;
            }
            
            .header, .form-panel, .upcoming-panel {
                padding: 25px;
            }
        }
        
        /* Stats */
        .stats-box {
            background: linear-gradient(135deg, #4a6cf7 0%, #3b5bdb 100%);
            color: white;
            padding: 25px;
            border-radius: 15px;
            margin-bottom: 30px;
            text-align: center;
        }
        
        .stats-title {
            font-size: 14px;
            opacity: 0.9;
            margin-bottom: 10px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .stats-value {
            font-size: 36px;
            font-weight: 700;
            margin-bottom: 5px;
        }
        
        .stats-change {
            font-size: 12px;
            opacity: 0.8;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <div class="header-content">
                <h1>
                    <span class="header-icon">📅</span>
                    Schedule Meeting
                </h1>
                <a href="supervisordashboard.php">
                <div class="user-profile">
                    <div class="user-avatar">S</div>
                    <span>Supervisor Dashboard</span>
                </div>
                </a>
            </div>
        </div>
        
        <!-- Navigation -->
        <nav class="navbar">
            <div class="nav-container">
                <a href="supervisordashboard.php" class="nav-link">🏠 Dashboard</a>
                <a href="schedule_meeting.php" class="nav-link active">📋 Schedule Meeting</a>
                <a href="view_meetings.php" class="nav-link">📅 View Meetings</a>
                <a href="view_interns_assigned.php" class="nav-link">👥 My Interns</a>
                <a href="logout.php" class="nav-link">🚪 Logout</a>
            </div>
        </nav>
        
        <!-- Messages -->
        <?php 
        if ($success_message) echo $success_message;
        if ($message) echo $message;
        ?>
        
        <!-- Main Content -->
        <div class="content-wrapper">
            <!-- Left Panel - Form -->
            <div class="form-panel">
                <h2 class="panel-title">Schedule New Meeting</h2>
                
                <form method="POST" action="">
                    <!-- Intern Selection -->
                    <div class="form-group">
                        <label class="form-label">Select Intern *</label>
                        <select name="intern_id" class="form-control" required>
                            <option value="">-- Choose Intern --</option>
                            <?php if ($interns_result && $interns_result->num_rows > 0): ?>
                                <?php while($intern = $interns_result->fetch_assoc()): ?>
                                    <option value="<?php echo $intern['id']; ?>" 
                                        <?php echo (isset($_POST['intern_id']) && $_POST['intern_id'] == $intern['id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($intern['name']); ?> 
                                        (<?php echo htmlspecialchars($intern['email']); ?>)
                                    </option>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <option value="">No interns available</option>
                            <?php endif; ?>
                        </select>
                    </div>
                    
                    <!-- Meeting Title -->
                    <div class="form-group">
                        <label class="form-label">Meeting Title *</label>
                        <input type="text" name="title" class="form-control" 
                               value="<?php echo htmlspecialchars($_POST['title'] ?? ''); ?>"
                               placeholder="Weekly Check-in, Progress Review, etc." required>
                    </div>
                    
                    <!-- Description -->
                    <div class="form-group">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" 
                                  placeholder="Agenda, topics to discuss, preparation needed..."><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
                    </div>
                    
                    <div class="time-group">
                        <!-- Date -->
                        <div class="form-group">
                            <label class="form-label">Meeting Date *</label>
                            <input type="date" name="meeting_date" class="form-control" 
                                   value="<?php echo $_POST['meeting_date'] ?? ''; ?>" 
                                   min="<?php echo date('Y-m-d'); ?>" required>
                        </div>
                        
                        <!-- Meeting Type -->
                        <div class="form-group">
                            <label class="form-label">Meeting Type *</label>
                            <select name="meeting_type" class="form-control" required>
                                <option value="virtual" <?php echo ($_POST['meeting_type'] ?? 'virtual') == 'virtual' ? 'selected' : ''; ?>>Virtual</option>
                                <option value="in-person" <?php echo ($_POST['meeting_type'] ?? '') == 'in-person' ? 'selected' : ''; ?>>In-Person</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="time-group">
                        <!-- Start Time -->
                        <div class="form-group">
                            <label class="form-label">Start Time *</label>
                            <input type="time" name="start_time" class="form-control" 
                                   value="<?php echo $_POST['start_time'] ?? '09:00'; ?>" required>
                        </div>
                        
                        <!-- End Time -->
                        <div class="form-group">
                            <label class="form-label">End Time *</label>
                            <input type="time" name="end_time" class="form-control" 
                                   value="<?php echo $_POST['end_time'] ?? '10:00'; ?>" required>
                        </div>
                    </div>
                    
                    <!-- Location/Link -->
                    <div class="form-group">
                        <label class="form-label">Location / Meeting Link *</label>
                        <input type="text" name="location" class="form-control" 
                               value="<?php echo htmlspecialchars($_POST['location'] ?? ''); ?>"
                               placeholder="Zoom link or physical location" required>
                    </div>
                    
                    <!-- Buttons -->
                    <div class="form-actions">
                        <button type="reset" class="btn btn-secondary">Clear Form</button>
                        <button type="submit" class="btn btn-primary">Schedule Meeting</button>
                    </div>
                </form>
            </div>
            
            <!-- Right Panel - Upcoming Meetings -->
            <div class="upcoming-panel">
                <div class="upcoming-header">
                    <h3 class="upcoming-title">Upcoming Meetings</h3>
                    <span class="upcoming-count">
                        <?php echo $upcoming_result ? $upcoming_result->num_rows : 0; ?> meetings
                    </span>
                </div>
                
                <?php if ($upcoming_result && $upcoming_result->num_rows > 0): ?>
                    <?php while($meeting = $upcoming_result->fetch_assoc()): ?>
                        <div class="meeting-card <?php echo $meeting['meeting_type']; ?>">
                            <div class="meeting-title">
                                <?php echo htmlspecialchars($meeting['title']); ?>
                                <span class="meeting-type type-<?php echo $meeting['meeting_type']; ?>">
                                    <?php echo $meeting['meeting_type']; ?>
                                </span>
                            </div>
                            <div class="meeting-details">
                                👤 With: <?php echo htmlspecialchars($meeting['intern_name']); ?>
                            </div>
                            <div class="meeting-details">
                                📅 Date: <?php echo date('F j, Y', strtotime($meeting['meeting_date'])); ?>
                            </div>
                            <div class="meeting-time">
                                🕒 <?php echo date('g:i A', strtotime($meeting['start_time'])); ?> - 
                                <?php echo date('g:i A', strtotime($meeting['end_time'])); ?>
                            </div>
                        </div>
                    <?php endwhile; ?>
                    
                    <a href="view_meetings.php" class="view-all-btn">View All Meetings →</a>
                    
                    <!-- Stats Box -->
                    <div class="stats-box">
                        <div class="stats-title">Interns Available</div>
                        <div class="stats-value"><?php echo $interns_result ? $interns_result->num_rows : 0; ?></div>
                        <div class="stats-change">Ready for meetings</div>
                    </div>
                    
                <?php else: ?>
                    <div class="empty-state">
                        <div class="empty-icon">📅</div>
                        <h3>No Upcoming Meetings</h3>
                        <p>Schedule your first meeting using the form</p>
                    </div>
                    
                    <!-- Stats Box -->
                    <div class="stats-box">
                        <div class="stats-title">Interns Available</div>
                        <div class="stats-value"><?php echo $interns_result ? $interns_result->num_rows : 0; ?></div>
                        <div class="stats-change">Ready for meetings</div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <?php
    // Close database connection
    if (isset($conn)) {
        $conn->close();
    }
    ?>
</body>
</html>