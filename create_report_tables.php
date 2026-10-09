<?php
include 'db_connect.php';

$sql1 = "CREATE TABLE IF NOT EXISTS `intern_reports` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

$sql2 = "CREATE TABLE IF NOT EXISTS `report_comments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `report_id` int(11) NOT NULL,
  `supervisor_id` int(11) NOT NULL,
  `comment` text NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_report` (`report_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

if ($conn->query($sql1) === TRUE && $conn->query($sql2) === TRUE) {
    echo "<h1 style='color:green;text-align:center;'>✅ Tables created successfully!</h1>";
    echo "<p><a href='submit_report.php'>Test Submit Report</a> | <a href='my_reports.php'>Test My Reports</a> | <a href='view_report.php'>Test View Reports</a></p>";
} else {
    echo "Error: " . $conn->error;
}

$conn->close();
?>

