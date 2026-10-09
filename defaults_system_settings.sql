-- Default system settings for Admin Settings page
-- Run: mysql defenseproject < defaults_system_settings.sql

USE defenseproject;

-- Clear existing if needed (backup first!)
-- DELETE FROM system_settings;

-- Site Settings
INSERT INTO system_settings (setting_key, setting_value, category) VALUES
('site_name', 'CENADI Internship Portal', 'site'),
('site_logo_url', '', 'site'),
('contact_email', 'admin@cenadi.edu', 'site'),
('contact_phone', '+237 6XX XXX XXX', 'site'),
('site_url', 'http://localhost/StepIn', 'site'),

-- Email Settings
('smtp_host', 'smtp.gmail.com', 'email'),
('smtp_port', '587', 'email'),
('smtp_user', 'your-email@gmail.com', 'email'),
('smtp_pass', '', 'email'),
('email_from', 'no-reply@cenadi.edu', 'email'),
('email_from_name', 'CENADI Portal', 'email'),

-- Internship Settings
('report_due_days', '7', 'internship'),
('max_reports_per_month', '4', 'internship'),
('evaluation_weight_final', '40', 'internship'),
('max_interns_per_supervisor', '10', 'internship'),

-- User Management
('allow_self_reg', '1', 'users'),
('require_intern_approval', '1', 'users'),
('password_expiry_days', '90', 'users'),
('default_intern_duration_weeks', '12', 'users'),

-- Display
('theme_color_primary', '#2563eb', 'display'),
('date_format', 'Y-m-d', 'display'),
('timezone', 'Africa/Douala', 'display'),
('language', 'en', 'display'),

-- System
('maintenance_mode', '0', 'system'),
('backup_email', 'backup@cenadi.edu', 'system'),
('allow_demo_mode', '0', 'system'),

-- Security
('max_login_attempts', '5', 'security'),
('session_timeout_minutes', '60', 'security'),
('require_2fa_admins', '0', 'security')

ON DUPLICATE KEY UPDATE 
setting_value = VALUES(setting_value);

SELECT 'Default settings populated successfully!' as status;
