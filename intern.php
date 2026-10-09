<?php
// Database connection
$servername = "localhost";      // usually "localhost"
$username = "root";             // default XAMPP username
$password = "";                 // leave empty unless you set one
$dbname = "defense_project";    // your database name

// Create the connection
$conn = mysqli_connect($servername, $username, $password, $dbname);

// Check the connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $school = $_POST['school'];
    $department = $_POST['department'];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $internship_type = $_POST['internship_type'];
    $cv = $_POST['cv'];
    $email = $_POST['email'];

    // Sanitize input to prevent SQL injection
    $school = mysqli_real_escape_string($conn, $school);
    $department = mysqli_real_escape_string($conn, $department);
    $start_date = mysqli_real_escape_string($conn, $start_date);
    $end_date = mysqli_real_escape_string($conn, $end_date);
    $internship_type = mysqli_real_escape_string($conn, $internship_type);
    $cv = mysqli_real_escape_string($conn, $cv);
    $email = mysqli_real_escape_string($conn, $email);

    // Insert data into "intern" table
    $sql = "INSERT INTO intern (school, department, start_date, end_date, internship_type, cv, email)
            VALUES ('$school', '$department', '$start_date', '$end_date', '$internship_type', '$cv', '$email')";

    if (mysqli_query($conn, $sql)) {
        echo "<p style='color: green;'>New intern record created successfully!</p>";
    } else {
        echo "<p style='color: red;'>Error: " . mysqli_error($conn) . "</p>";
    }

    // Close connection
    mysqli_close($conn);
}
?>