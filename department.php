<?php
// Database connection
$servername = "localhost";      // usually "localhost"
$username = "root";             // default XAMPP username
$password = "";                 // leave empty unless you set one
$dbname = "defense_project";    // your database name

// Create the connection
$conn = mysqli_connect($servername, $username, $password, $internship_db);

// Check the connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $name = $_POST['name'];

    
    // Sanitize input to prevent SQL injection
    $name = mysqli_real_escape_string($conn, $name);


    // Insert data into "department" table
    $sql = "INSERT INTO department (name)
            VALUES ('$name')";

    if (mysqli_query($conn, $sql)) {
        echo "<p style='color: green;'>New Department</p>";
    } else {
        echo "<p style='color: red;'>Error: " . mysqli_error($conn) . "</p>";
    }

    // Close connection
    mysqli_close($conn);
}
?>