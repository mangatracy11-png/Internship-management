<?php
include 'db_connect.php';

$tables = [
    "CREATE TABLE IF NOT EXISTS `intern_reports` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `intern_id` int(11) NOT NULL,
      `supervisor_id` int(11) DEFAULT NULL,
      `title` varchar(255) DEFAULT NULL,
      `content` text,
      `file_path` varchar(500) DEFAULT NULL,
      `week_number` int(11) DEFAULT NULL,
      `submission_date` datetime DEFAULT CURRENT_TIMESTAMP,
      `status` enum('pending','reviewed','feedback_sent') DEFAULT 'pending',
      PRIMARY KEY (`id`),
      KEY `idx_intern` (`intern_id`),
      KEY `idx_supervisor` (`supervisor_id`),
      KEY `idx_status` (`status`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

    "CREATE TABLE IF NOT EXISTS `report_comments` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `report_id` int(11) NOT NULL,
      `supervisor_id` int(11) NOT NULL,
      `comment` text NOT NULL,
      `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
      PRIMARY KEY (`id`),
      KEY `idx_report` (`report_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;"
];

$success_count = 0;
foreach ($tables as $sql) {
    if ($conn->query($sql) === TRUE) {
        $success_count++;
    } else {
        echo "Error creating table: " . $conn->error . "<br>";
    }
}

if ($success_count == 2) {
    echo "<h1 style='color: green; text-align: center;'>✅ ALL TABLES CREATED SUCCESSFULLY!</h1>";
    echo "<p style='text-align: center; font-size: 18px;'>";
    echo "<a href='submit_report.php' style='background: #10b981; color: white; padding: 12px 24px; text-decoration: none; border-radius: 8px; margin: 0 10px;'>Test Submit Report</a>";
    echo "<a href='my_reports.php' style='background: #2563eb; color: white; padding: 12px 24px; text-decoration: none; border-radius: 8px; margin: 0 10px;'>Test My Reports</a>";
    echo "<a href='view_report.php' style='background: #ef4444; color: white; padding: 12px 24px; text-decoration: none; border-radius: 8px;'>Test View Reports</a>";
    echo "</p>";
} else {
    echo "<h1 style='color: red; text-align: center;'>❌ Table Creation Failed</h1>";
}

$conn->close();
?>

