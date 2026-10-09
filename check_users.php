<?php
include 'db_connect.php';

$result = $conn->query("SELECT COUNT(*) as count FROM users");
$row = $result->fetch_assoc();
echo "Users table has " . $row['count'] . " records.\n";

if ($row['count'] > 0) {
    $result = $conn->query("SELECT id, email FROM users LIMIT 5");
    while ($row = $result->fetch_assoc()) {
        echo "ID: " . $row['id'] . ", Email: " . $row['email'] . "\n";
    }
}

$conn->close();
?>
