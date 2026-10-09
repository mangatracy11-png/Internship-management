<?php
include 'db_connect.php';

// Fix 1: Add supervisor column if missing
$conn->query("ALTER TABLE users ADD COLUMN IF NOT EXISTS supervisor INT NULL");

// Fix 2: Link unassigned interns to first supervisor
$first_sup = $conn->query("SELECT id FROM users WHERE role='supervisor' LIMIT 1")->fetch_assoc()['id'] ?? 25;
$conn->query("UPDATE users SET supervisor = $first_sup WHERE role='intern' AND status='approved' AND (supervisor IS NULL OR supervisor = 0)");

// Fix 3: Update intern_reports supervisor_id from users.supervisor
$conn->query("UPDATE intern_reports ir JOIN users u ON ir.intern_id = u.id SET ir.supervisor_id = u.supervisor WHERE u.supervisor IS NOT NULL");

// Verify
$count = $conn->query("SELECT COUNT(*) c FROM intern_reports WHERE supervisor_id IS NOT NULL")->fetch_assoc()['c'];
echo "<h1>✅ FIXED! $count reports now assigned to supervisors</h1>";
echo "<p>Run this once. Reports now visible in view_report.php</p>";
echo "<a href='view_report.php'>Test Supervisor Dashboard</a>";
$conn->close();
?>

