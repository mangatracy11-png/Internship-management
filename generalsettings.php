<?php
header("Location: settings.php");
exit();
?>
</xai:function_call

<xai:function_call name="edit_file">
<parameter name="path">c:/xampp/htdocs/StepIn/TODO_settings.md

$message = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    foreach ($_POST as $key => $value) {
        $value = trim($value);
        $stmt = $conn->prepare("INSERT INTO system_settings (setting_key, setting_value) 
                               VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = ?");
        $stmt->bind_param("sss", $key, $value, $value);
        $stmt->execute();
        $stmt->close();
    }
    
    // Log the action
    $stmt = $conn->prepare("INSERT INTO audit_logs (user_id, action, table_name) 
                           VALUES (?, 'update', 'system_settings')");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $stmt->close();
    
    $message = "<div class='alert success'>Settings saved successfully!</div>";
}

// Get all settings
$settings = [];
$result = $conn->query("SELECT setting_key, setting_value, category FROM system_settings ORDER BY category, setting_key");
while ($row = $result->fetch_assoc()) {
    $settings[$row['category']][$row['setting_key']] = $row['setting_value'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>General Settings - Admin</title>
    <style>
        /* Add to existing CSS */
        .settings-container {
            background: white;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.05);
            padding: 40px;
            margin-top: 30px;
        }
        
        .settings-tabs {
            display: flex;
            border-bottom: 2px solid #e2e8f0;
            margin-bottom: 30px;
            overflow-x: auto;
        }
        
        .tab {
            padding: 15px 25px;
            background: none;
            border: none;
            color: #64748b;
            font-weight: 600;
            font-size: 15px;
            cursor: pointer;
            transition: all 0.3s ease;
            border-bottom: 3px solid transparent;
            white-space: nowrap;
        }
        
        .tab:hover {
            color: #1e40af;
        }
        
        .tab.active {
            color: #1e40af;
            border-bottom-color: #1e40af;
        }
        
        .tab-content {
            display: none;
        }
        
        .tab-content.active {
            display: block;
        }
        
        .settings-group {
            margin-bottom: 40px;
        }
        
        .group-title {
            font-size: 18px;
            color: #1e293b;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #f1f5f9;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .settings-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 25px;
        }
        
        .setting-item {
            display: flex;
            flex-direction: column;
        }
        
        .setting-label {
            color: #475569;
            font-weight: 600;
            margin-bottom: 10px;
            font-size: 14px;
        }
        
        .setting-description {
            color: #94a3b8;
            font-size: 13px;
            margin-top: 5px;
            line-height: 1.5;
        }
        
        .alert {
            padding: 15px 20px;
            border-radius: 10px;
            margin-bottom: 25px;
            font-weight: 500;
        }
        
        .alert.success {
            background: #dcfce7;
            color: #166534;
            border: 1px solid #bbf7d0;
        }
        
        .alert.error {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <main class="admin-content">
            <div class="admin-header">
                <h1>General Settings</h1>
                <div class="user-profile">
                    <div class="user-avatar">A</div>
                    <div>
                        <div style="font-weight: 600; color: #1e293b;"><?php echo htmlspecialchars($_SESSION['Name']); ?></div>
                        <div style="color: #64748b; font-size: 14px;">Administrator</div>
                    </div>
                </div>
            </div>
            
            <?php if ($message): ?>
                <?php echo $message; ?>
            <?php endif; ?>
            
            <div class="settings-container">
                <div class="settings-tabs">
                    <button class="tab active" onclick="openTab('site')">🌐 Site Settings</button>
                    <button class="tab" onclick="openTab('email')">📧 Email Settings</button>
                    <button class="tab" onclick="openTab('dates')">📅 Date & Time</button>
                    <button class="tab" onclick="openTab('display')">🎨 Display</button>
                    <button class="tab" onclick="openTab('system')">⚙️ System</button>
                </div>
                
                <form method="POST" action="">
                    <!-- Site Settings -->
                    <div