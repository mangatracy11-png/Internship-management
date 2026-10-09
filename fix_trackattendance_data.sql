-- FIX TRACKATTENDANCE - CREATE TEST DATA
USE defenseproject;

-- Ensure test intern exists
INSERT IGNORE INTO intern (intern_id, Name, department, email) VALUES 
(1, 'Test Intern One', 'DIRE', 'test1@example.com'),
(2, 'Test Intern Two', 'DEL', 'test2@example.com');

-- Create test supervisor (ID=25)
INSERT IGNORE INTO users (id, name, role) VALUES (25, 'Test Supervisor', 'supervisor');

-- Assign interns to supervisor
INSERT IGNORE INTO assignments (intern_id, supervisor_id, status, assigned_date) VALUES 
(1, 25, 'active', CURDATE()),
(2, 25, 'active', CURDATE());

-- Create pending attendance records
INSERT INTO attendance (intern_id, date, check_in, status, supervisor_status, created_at) VALUES
(1, CURDATE(), NOW(), 'present', 'pending', NOW()),
(2, CURDATE(), '09:00:00', 'present', NULL, NOW())
ON DUPLICATE KEY UPDATE check_in=VALUES(check_in), status='present', supervisor_status='pending';

SELECT 'TEST DATA CREATED - Check trackattendance.php as supervisor ID=25' as result;
SELECT * FROM attendance WHERE supervisor_status IS NULL OR supervisor_status = 'pending';

