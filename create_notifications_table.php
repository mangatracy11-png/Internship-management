<?php
include 'db_connect.php';

$sql = "CREATE TABLE IF NOT EXISTS notifications (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  type VARCHAR(50) NOT NULL,
  title VARCHAR(255) NOT NULL,
  message TEXT,
  meeting_id INT NULL,
  `read` TINYINT DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_user (user_id),
  INDEX idx_read (`read`),
  INDEX idx_type (type)
)";

if ($conn->query($sql)) {
    echo "<h1>✅ Notifications table created!</h1>";
} else {
    echo "<h1>❌ Error: " . $conn->error . "</h1>";
}
?>

