<?php
session_start();
if (!isset($_SESSION['user_id'])) exit('Unauthorized');

include 'db_connect.php';

$id = (int)$_POST['id'];
$stmt = $conn->prepare("UPDATE notifications SET `read` = 1 WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $id, $_SESSION['user_id']);
$stmt->execute();
echo 'OK';
$conn->close();
?>

