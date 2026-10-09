<?php
include 'db_connect.php'; // DB connection

// Internship application form processing
$message = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['Name'] ?? '';
    $school = $_POST['school'] ?? '';
    $department = $_POST['department'] ?? '';
    $start_date = $_POST['start_date'] ?? '';
    $end_date = $_POST['end_date'] ?? '';
    $date_of_birth = $_POST['date_of_birth '] ?? '';
    $internship_type = $_POST['internship_type'] ?? '';
    $contact = $_POST['Contact'] ?? '';
     $email = $_POST['email'] ?? '';
    $file = '';
    echo"".$Name;
    echo"".$school;
    echo"".$department;
    echo"".$start_date;
    echo"".$end_date;
    echo"".$date_of_birth;
    echo"".$internship_type;
    echo"".$Contact;
    echo"".$email;
}

        if ($stmt->execute()) {
            $message = '<div class="alert alert-success">Application submitted successfully!</div>';
        } else {
            $message = '<div class="alert alert-danger">Submission failed: ' . $conn->error . '</div>';
        }
?>
<!DOCTYPE html>
<html>
    <head>
        <title></title>
    </head>
    <style>
    .btn {
    display: inline-block;
    padding: 10px 20px;
    background-color: #007bff;
    color: white;
    text-decoration: none;
    border-radius: 5px;
}
.btn:hover {
    background-color: #0056b3;
}
    </style>
    <body>
        <h1>Before final submission,verify and confirm that all provided information is accurate.</h1>
        <button  type="submit" align-item="center" class="btn btn-primary btn-lg w-40"><i class="bi bi-send-fill"></i> Submit Application</button>
    </body>
</html>