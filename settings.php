<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

$message = '';
$error = '';

$upload_dir = 'uploads/settings/';
// Create upload dir if not exists
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $valid = true;
    $upload_key = '';

    // Handle logo upload
    if (isset($_FILES['site_logo']) && $_FILES['site_logo']['error'] == 0) {
        $file = $_FILES['site_logo'];
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        if (in_array(strtolower($ext), ['jpg','jpeg','png','gif','svg']) && $file['size'] < 2*1024*1024) {
            $filename = 'logo.' . $ext;
            $target = $upload_dir . $filename;
            if (move_uploaded_file($file['tmp_name'], $target)) {
                $upload_key = 'site_logo_url';
                $_POST[$upload_key] = '/' . $upload_dir . $filename;
            } else {
                $valid = false;
                $error = 'Logo upload failed.';
            }
        } else {
            $valid = false;
            $error = 'Invalid logo file (JPG/PNG/SVG, <2MB).';
        }
    }

    if ($valid) {
        foreach ($_POST as $key => $value) {
            if (in_array($key, ['site_logo_url'])) continue; // Handled above
            $value = trim($value);
            if (empty($value) && !in_array($key, ['smtp_pass'])) continue; // Allow empty password
            
            // Basic validation
            if (preg_match('/^(max_|session_timeout|report_due)/', $key) && (!is_numeric($value) || $value < 0)) {
                $valid = false;
                $error = ucwords(str_replace('_', ' ', $key)) . ' must be positive number.';
                break;
            }
            
            $stmt = $conn->prepare("INSERT INTO system_settings (setting_key, setting_value, category) 
                                   VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE setting_value = ?");
$cat = $_POST['category'][$key] ?? 'general';
$stmt->bind_param("ssss", $key, $value, $cat, $value);
            $stmt->execute();
            $stmt->close();
        }
        
        // Save upload if any
        if ($upload_key && isset($_POST[$upload_key])) {
            $stmt = $conn->prepare("INSERT INTO system_settings (setting_key, setting_value, category) 
                                   VALUES (?, ?, 'site') ON DUPLICATE KEY UPDATE setting_value = ?");
            $stmt->bind_param("sss", $upload_key, $_POST[$upload_key], $_POST[$upload_key]);
            $stmt->execute();
            $stmt->close();
        }
        
        // Audit log
        $stmt = $conn->prepare("INSERT INTO audit_logs (user_id, action, table_name) VALUES (?, 'update', 'system_settings')");
        $stmt->bind_param("i", $_SESSION['user_id']);
        $stmt->execute();
        
        $message = '<div class="alert alert-success alert-dismissible fade show" role="alert">
                      Settings saved successfully! <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>';
    }
}

// Fetch all settings
$settings = [];
$categories = [];
$result = $conn->query("SELECT setting_key, setting_value, category FROM system_settings ORDER BY category, setting_key");
while ($row = $result->fetch_assoc()) {
    $settings[$row['setting_key']] = $row['setting_value'];
    $categories[$row['category']][] = $row['setting_key'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Settings - CENADI Internship Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root {
            --primary: #2563eb; --success: #10b981; --bg: #f8fafc; --card: #fff; 
            --text: #1e293b; --mute: #64748b; --border: #e2e8f0;
        }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: var(--bg); color: var(--text); }
        .settings-container { background: var(--card); border-radius: 20px; box-shadow: 0 10px 40px rgba(0,0,0,0.1); padding: 40px; max-width: 1200px; margin: 40px auto; }
        .tab-nav { border-bottom: 3px solid var(--border); margin-bottom: 30px; }
        .nav-link { color: var(--mute) !important; font-weight: 600; padding: 15px 30px; border: none; border-bottom: 3px solid transparent; }
        .nav-link.active { color: var(--primary) !important; background: transparent; border-bottom-color: var(--primary); }
        .settings-group { margin-bottom: 40px; padding: 25px; background: #f9fcff; border-radius: 15px; border-left: 4px solid var(--primary); }
        .group-title { font-size: 1.4rem; font-weight: 700; margin-bottom: 20px; display: flex; align-items: center; gap: 12px; color: var(--text); }
        .form-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(350px, 1fr)); gap: 25px; }
        .setting-item { background: var(--card); padding: 20px; border-radius: 12px; border: 1px solid var(--border); }
        .setting-label { font-weight: 600; color: var(--text); margin-bottom: 8px; display: block; }
        .setting-desc { color: var(--mute); font-size: 0.9rem; margin-bottom: 12px; }
        .preview { text-align: center; margin-top: 15px; }
        .logo-preview { max-height: 80px; max-width: 200px; border-radius: 8px; border: 2px dashed var(--border); padding: 20px; background: #f8f9fa; }
        .color-preview { width: 50px; height: 50px; border-radius: 50%; border: 3px solid white; box-shadow: 0 4px 12px rgba(0,0,0,0.2); display: inline-block; margin: 0 5px; }
        #settingsSearch { border-radius: 25px; border: 2px solid var(--border); padding: 12px 20px; font-size: 1rem; }
        #settingsSearch:focus { border-color: var(--primary); box-shadow: 0 0 0 0.2rem rgba(37,99,235,0.15); }
        .form-switch { font-size: 1.1rem; }
        @media (max-width: 768px) { .form-grid { grid-template-columns: 1fr; } .settings-container { margin: 20px; padding: 20px; } }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="settings-container">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1 class="h2 mb-0"><i class="bi bi-gear-fill text-primary me-3"></i>Admin Settings</h1>
                    <button class="btn btn-outline-success" onclick="location.reload()"><i class="bi bi-arrow-clockwise"></i> Refresh</button>
                </div>

                <?php echo $message ?? ''; ?>
                <?php if ($error): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?php echo htmlspecialchars($error); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <input type="text" id="settingsSearch" class="form-control mb-4 w-50" placeholder="Search settings... (e.g., email, report)">

                <div class="mb-4">
                    <h3 class="mb-3"><i class="bi bi-gear-fill me-2"></i>Admin Settings</h3>
                </div>

                <form method="POST" enctype="multipart/form-data" id="settingsForm">

                <!-- ONLY 4 SETTINGS - Single Clean Form -->
                <div class="row g-4 mb-5">
                   <!-- 1. User Registration Toggle -->
                    <div class="col-lg-6">
                        <div class="card h-100 border-0 shadow-sm">
                            <div class="card-body text-center p-4">
                                <div class="mb-3">
                                    <i class="bi bi-people-fill" style="font-size: 3rem; color: #10b981;"></i>
                                </div>
                                <h5 class="card-title mb-4">User Registration</h5>
                <div class="form-check form-switch form-switch-lg d-flex justify-content-center">
                                    <input class="form-check-input" type="checkbox" name="allow_self_reg" id="allow_reg" <?php echo ($settings['allow_self_reg'] ?? 'no') == 'yes' ? 'checked' : ''; ?> value="yes" onchange="updateRegStatus(this.checked)">
                                    <label class="form-check-label" for="allow_reg" style="font-size: 1.2rem; font-weight: 600;" id="regLabel">
                                        Allow users to register
                                    </label>
                                </div>
                                <div id="regStatus" class="mt-2 p-2 bg-light rounded text-center fw-bold" style="font-size: 1rem;"></div>

                            </div>
                        </div>
                    </div>
                    
                    <!-- 2. Language -->
                    <div class="col-lg-6">
                        <div class="card h-100 border-0 shadow-sm">
                            <div class="card-body text-center p-4">
                                <div class="mb-3">
                                    <i class="bi bi-translate" style="font-size: 3rem; color: #8b5cf6;"></i>
                                </div>
                                <h5 class="card-title mb-4">Language</h5>
                                <select name="language" class="form-select form-select-lg mb-0" style="font-size: 1.2rem;">
                                    <option value="en" <?php echo ($settings['language'] ?? 'en') == 'en' ? 'selected' : ''; ?>>🇺🇸 English</option>
                                    <option value="fr" <?php echo ($settings['language'] ?? '') == 'fr' ? 'selected' : ''; ?>>🇫🇷 Français</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="row g-4 mb-5">
                    <!-- 3. App Color -->
                    <div class="col-lg-6">
                        <div class="card h-100 border-0 shadow-sm">
                            <div class="card-body text-center p-4">
                                <div class="mb-3">
                                    <i class="bi bi-palette-fill" style="font-size: 3rem; color: #f59e0b;"></i>
                                </div>
                                <h5 class="card-title mb-4">Application Color</h5>
                                <div class="mb-3">
                                    <input type="color" name="theme_color_primary" class="form-control form-control-color w-100 mx-auto d-block" id="colorPicker" style="height: 80px; width: 120px; border-radius: 20px; border: 4px solid white; box-shadow: 0 8px 25px rgba(0,0,0,0.15);" value="<?php echo htmlspecialchars($settings['theme_color_primary'] ?? '#2563eb'); ?>">
                                </div>
                                <div id="colorPreview" class="color-preview mx-auto mb-2" style="background-color: <?php echo htmlspecialchars($settings['theme_color_primary'] ?? '#2563eb'); ?>; width: 80px; height: 80px; border-radius: 50%; border: 4px solid white; box-shadow: 0 4px 20px rgba(0,0,0,0.2);"></div>
                                <small class="text-muted">Live preview - applies site-wide</small>
                            </div>
                        </div>
                    </div>
                    
                    <!-- 4. Max Logins -->
                    <div class="col-lg-6">
                        <div class="card h-100 border-0 shadow-sm">
                            <div class="card-body p-4">
                                <div class="mb-3">
                                    <i class="bi bi-shield-lock-fill" style="font-size: 3rem; color: #ef4444;"></i>
                                </div>
                                <h5 class="card-title mb-4">Maximum Login Attempts</h5>
                                <div class="input-group input-group-lg mb-3">
                                    <span class="input-group-text">Max</span>
                                    <input type="number" name="max_login_attempts" class="form-control text-center" style="font-size: 2rem; font-weight: 700; height: 80px;" min="3" max="10" value="<?php echo htmlspecialchars($settings['max_login_attempts'] ?? '5'); ?>">
                                    <span class="input-group-text">attempts</span>
                                </div>
                                <small class="text-muted">Before account lockout (3-10)</small>
                            </div>
                        </div>
                    </div>
                </div>

                        <!-- Site Settings -->




                        <!-- Internship Settings -->
                        <div class="tab-pane fade" id="internship">
                            <div class="settings-group">
                                <div class="group-title"><i class="bi bi-mortarboard"></i> Internship Rules</div>
                                <div class="form-grid">
                                    <div class="setting-item">
                                        <label class="setting-label">Report Due Days</label>
                                        <input type="number" name="report_due_days" class="form-control" value="<?php echo htmlspecialchars($settings['report_due_days'] ?? ''); ?>" min="1" max="30">
                                        <small class="setting-desc">Days after assignment for weekly report</small>
                                    </div>
                                    <div class="setting-item">
                                        <label class="setting-label">Max Reports/Month</label>
                                        <input type="number" name="max_reports_per_month" class="form-control" value="<?php echo htmlspecialchars($settings['max_reports_per_month'] ?? ''); ?>" min="1" max="20">
                                    </div>
                                    <div class="setting-item">
                                        <label class="setting-label">Final Eval Weight (%)</label>
                                        <input type="number" name="evaluation_weight_final" class="form-control" value="<?php echo htmlspecialchars($settings['evaluation_weight_final'] ?? ''); ?>" min="0" max="100">
                                    </div>
                                    <div class="setting-item">
                                        <label class="setting-label">Max Interns/Supervisor</label>
                                        <input type="number" name="max_interns_per_supervisor" class="form-control" value="<?php echo htmlspecialchars($settings['max_interns_per_supervisor'] ?? ''); ?>" min="1" max="50">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Users -->
                        <div class="tab-pane fade" id="users">
                            <div class="settings-group">
                                <div class="group-title"><i class="bi bi-people"></i> User Management</div>
                                <div class="form-grid">
                                    <div class="setting-item">
                                        <label class="form-switch">
                                            <input type="checkbox" name="allow_self_reg" class="form-check-input" <?php echo ($settings['allow_self_reg'] ?? '0') == '1' ? 'checked' : ''; ?> value="1">
                                            <span class="form-check-label">Allow Self Registration</span>
                                        </label>
                                    </div>
                                    <div class="setting-item">
                                        <label class="form-switch">
                                            <input type="checkbox" name="require_intern_approval" class="form-check-input" <?php echo ($settings['require_intern_approval'] ?? '0') == '1' ? 'checked' : ''; ?> value="1">
                                            <span class="form-check-label">Require Intern Approval</span>
                                        </label>
                                    </div>
                                    <div class="setting-item">
                                        <label class="setting-label">Password Expiry (days)</label>
                                        <input type="number" name="password_expiry_days" class="form-control" value="<?php echo htmlspecialchars($settings['password_expiry_days'] ?? ''); ?>" min="0">
                                    </div>
                                    <div class="setting-item">
                                        <label class="setting-label">Default Internship Duration (weeks)</label>
                                        <input type="number" name="default_intern_duration_weeks" class="form-control" value="<?php echo htmlspecialchars($settings['default_intern_duration_weeks'] ?? ''); ?>" min="1">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Display -->
                        <div class="tab-pane fade" id="display">
                            <div class="settings-group">
                                <div class="group-title"><i class="bi bi-palette"></i> Display & Theme</div>
                                <div class="form-grid">
                                    <div class="setting-item">
                                        <label class="setting-label">Primary Color</label>
                                        <input type="color" name="theme_color_primary" class="form-control form-control-color" value="<?php echo htmlspecialchars($settings['theme_color_primary'] ?? '#2563eb'); ?>" onchange="updatePreview(this.value)">
                                        <div class="preview mt-2">
                                            <span class="color-preview" id="colorPreview" style="background-color: <?php echo htmlspecialchars($settings['theme_color_primary'] ?? '#2563eb'); ?>;"></span>
                                            <small class="text-muted">Live preview</small>
                                        </div>
                                    </div>
                                    <div class="setting-item">
                                        <label class="setting-label">Date Format</label>
                                        <select name="date_format" class="form-select">
                                            <option value="Y-m-d" <?php echo ($settings['date_format'] ?? '') == 'Y-m-d' ? 'selected' : ''; ?>>2024-09-15</option>
                                            <option value="d/m/Y" <?php echo ($settings['date_format'] ?? '') == 'd/m/Y' ? 'selected' : ''; ?>>15/09/2024</option>
                                            <option value="F j, Y" <?php echo ($settings['date_format'] ?? '') == 'F j, Y' ? 'selected' : ''; ?>>September 15, 2024</option>
                                        </select>
                                    </div>
                                    <div class="setting-item">
                                        <label class="setting-label">Timezone</label>
                                        <select name="timezone" class="form-select">
                                            <option value="Africa/Douala" <?php echo ($settings['timezone'] ?? '') == 'Africa/Douala' ? 'selected' : ''; ?>>Africa/Douala (GMT+1)</option>
                                            <option value="UTC">UTC</option>
                                            <option value="Europe/Paris">Europe/Paris (GMT+2)</option>
                                        </select>
                                    </div>
                                    <div class="setting-item">
                                        <label class="setting-label">Language</label>
                                        <select name="language" class="form-select">
                                            <option value="en" <?php echo ($settings['language'] ?? 'en') == 'en' ? 'selected' : ''; ?>>English</option>
                                            <option value="fr">Français</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>



                        <!-- Security -->
                        <div class="tab-pane fade" id="security">
                            <div class="settings-group">
                                <div class="group-title"><i class="bi bi-shield-lock"></i> Security</div>
                                <div class="form-grid">
                                    <div class="setting-item">
                                        <label class="setting-label">Max Login Attempts</label>
                                        <input type="number" name="max_login_attempts" class="form-control" value="<?php echo htmlspecialchars($settings['max_login_attempts'] ?? ''); ?>" min="3" max="10">
                                    </div>
                                    <div class="setting-item">
                                        <label class="setting-label">Session Timeout (minutes)</label>
                                        <input type="number" name="session_timeout_minutes" class="form-control" value="<?php echo htmlspecialchars($settings['session_timeout_minutes'] ?? ''); ?>" min="15" max="1440">
                                    </div>
                                    <div class="setting-item">
                                        <label class="form-switch">
                                            <input type="checkbox" name="require_2fa_admins" class="form-check-input" <?php echo ($settings['require_2fa_admins'] ?? '0') == '1' ? 'checked' : ''; ?> value="1">
                                            <span class="form-check-label">Require 2FA for Admins</span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>


                                <small class="text-muted">Before account lockout (3-10)</small>
                            </div>
                            
                            </div>
                            <div class="card-footer bg-transparent border-0 pt-0 mt-4">
                                <div class="text-center">
                                    <button type="submit" class="btn btn-success btn-lg px-6 py-3 shadow-lg" style="font-size: 1.25rem; font-weight: 700; min-width: 300px;">
                                        <i class="bi bi-check-circle-fill me-2"></i>💾 Save All Settings
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // Live color preview - applies site-wide instantly
        const colorPicker = document.getElementById('colorPicker');
        const colorPreview = document.getElementById('colorPreview');
        colorPicker.addEventListener('input', function(e) {
            const color = e.target.value;
            document.documentElement.style.setProperty('--primary', color);
            colorPreview.style.backgroundColor = color;
            // Persist color change across tabs
            localStorage.setItem('appColor', color);
        });

        // Apply saved color on load
        const savedColor = localStorage.getItem('appColor') || document.documentElement.style.getPropertyValue('--primary');
        colorPicker.value = savedColor;
        colorPreview.style.backgroundColor = savedColor;

// FULL DYNAMIC LANGUAGE i18n
const i18n = {
  en: {
    regLabel: 'Allow users to register',
    regTitle: 'User Registration',
    colorTitle: 'Application Color',
    loginTitle: 'Maximum Login Attempts',
    saveBtn: '💾 Save Settings Now',
    regStatus: 'Registration: ON | OFF'
  },
  fr: {
    regLabel: 'Autoriser l\'enregistrement des utilisateurs',
    regTitle: 'Inscription utilisateur', 
    colorTitle: 'Couleur de l\'application',
    loginTitle: 'Tentatives de connexion maximum',
    saveBtn: '💾 Sauvegarder maintenant',
    regStatus: 'Inscription: ACTIVÉE | DÉSACTIVÉE'
  }
};

let currentLang = localStorage.getItem('appLanguage') || 'en';

function updateLanguage(lang) {
  currentLang = lang;
  localStorage.setItem('appLanguage', lang);
  document.querySelector('[name="language"]').value = lang;
  
  document.querySelector('label[for="allow_reg"]').textContent = i18n[lang].regLabel;
  document.querySelectorAll('.card-title').forEach((el, i) => {
    if (i === 0) el.textContent = i18n[lang].regTitle;
    if (i === 2) el.textContent = i18n[lang].colorTitle;
    if (i === 3) el.textContent = i18n[lang].loginTitle;
  });
  
  document.querySelector('button[type="submit"]').innerHTML = '<i class="bi bi-check-circle-fill me-2"></i>' + i18n[lang].saveBtn;
  document.getElementById('regStatus').textContent = i18n[lang].regStatus;
}

const langSelect = document.querySelector('select[name="language"]');
langSelect.addEventListener('change', function(e) {
  updateLanguage(e.target.value);
});


        // Load saved language
        const savedLang = localStorage.getItem('appLanguage') || 'en';
        langSelect.value = savedLang;
        if (savedLang === 'fr') {
            const saveBtn = document.querySelector('button[type="submit"]');
            saveBtn.innerHTML = '<i class="bi bi-check-circle-fill me-2"></i>💾 Sauvegarder';
        }

// DYNAMIC Max login counter
const maxLoginInput = document.querySelector('input[name="max_login_attempts"]');
const loginCounter = document.createElement('div');
loginCounter.className = 'mt-2 p-2 bg-info text-white rounded fw-bold text-center';
loginCounter.id = 'loginCounter';
maxLoginInput.parentNode.parentNode.appendChild(loginCounter);

maxLoginInput.addEventListener('input', function(e) {
  const value = parseInt(e.target.value) || 0;
  if (value >= 3 && value <= 10) {
    e.target.classList.remove('is-invalid');
    e.target.classList.add('is-valid');
    loginCounter.innerHTML = `🔒 Max: <strong>${value}</strong> attempts`;
    loginCounter.className = 'mt-2 p-2 bg-success text-white rounded fw-bold text-center';
  } else {
    e.target.classList.add('is-invalid');
    e.target.classList.remove('is-valid');
    loginCounter.innerHTML = `⚠️ Invalid (3-10)`;
    loginCounter.className = 'mt-2 p-2 bg-danger text-white rounded fw-bold text-center';
  }
  localStorage.setItem('maxLoginAttempts', value);
});

// Init
maxLoginInput.dispatchEvent(new Event('input'));


        // Form validation + success feedback

        document.getElementById('settingsForm').addEventListener('submit', function(e) {
            const btn = e.target.querySelector('button[type="submit"]');
            const originalText = btn.innerHTML;
            btn.innerHTML = '<i class="bi bi-hourglass-split spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></i>Saving...';
            btn.disabled = true;
            
            // Real validation
            let valid = true;
            const fields = ['allow_self_reg', 'language', 'theme_color_primary', 'max_login_attempts'];
            fields.forEach(name => {
                const field = e.target.querySelector('[name="' + name + '"]');
                if (!field.value.trim()) {
                    field.classList.add('is-invalid');
                    valid = false;
                } else {
                    field.classList.remove('is-invalid');
                }
            });
            
            if (!valid) {
                btn.innerHTML = originalText;
                btn.disabled = false;
                e.preventDefault();
                return;
            }
            
            // Submit after animation
            setTimeout(() => {
                e.target.submit();
            }, 1000);
        });

    </script>

</body>
</html>
