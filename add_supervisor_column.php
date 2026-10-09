<?php
include 'db_connect.php';

$sql = "ALTER TABLE users ADD COLUMN IF NOT EXISTS supervisor INT NULL COMMENT 'Supervisor user ID for interns'";
if ($conn->query($sql) === TRUE) {
    echo "<h1 style='color:green;'>✅ Supervisor column added/verified successfully!</h1>";
    echo "<p>Now you can restore full query functionality in view_report.php.</p>";
    echo "<a href='view_report.php' class='btn btn-success'>Test View Reports</a>";
} else {
    echo "<h1 style='color:red;'>❌ Error: " . $conn->error . "</h1>";
}
$conn->close();
?>
<!DOCTYPE html>
<html><head><title>Schema Update</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head><body class="p-5">
<div class="container">
<?php echo ob_get_clean(); ?>
</div>
</body></html>
