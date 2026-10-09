<?php
include 'db_connect.php';

$sql = "ALTER TABLE users ADD COLUMN supervisor_id INT DEFAULT NULL AFTER role";
$conn->query($sql);

$sql = "CREATE INDEX idx_supervisor_id ON users(supervisor_id)";
$conn->query($sql);

echo "Added supervisor_id column to users table";
?>

