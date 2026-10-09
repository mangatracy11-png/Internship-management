<?php
include 'db_connect.php';
$result = $conn->query("SHOW COLUMNS FROM intern_reports LIKE 'viewed_at'");
if ($result->num_rows == 0) {
    $conn->query("ALTER TABLE intern_reports ADD COLUMN viewed_at TIMESTAMP NULL DEFAULT NULL");
    echo "Added viewed_at column.";
} else {
    echo "Column already exists.";
}
$conn->close();
?>
