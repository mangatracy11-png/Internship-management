<?php
$pdo = new PDO('mysql:host=localhost;dbname=internship_db', 'root', '');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
    $file = $_FILES['file'];

    $filename = $file['name'];
    $filetype = $file['type'];
    $filesize = $file['size'];
    $filedata = file_get_contents($file['tmp_name']);

    $stmt = $pdo->prepare("INSERT INTO uploaded_files (filename, filetype, filesize, filedata) VALUES (?, ?, ?, ?)");
    $stmt->bindParam(1, $filename);
    $stmt->bindParam(2, $filetype);
    $stmt->bindParam(3, $filesize);
    $stmt->bindParam(4, $filedata, PDO::PARAM_LOB);

    if ($stmt->execute()) {
        echo "File uploaded successfully!";
    } else {
        echo "Upload failed.";
    }
}
?>

<form action="upload.php" method="POST" enctype="multipart/form-data">
    <input type="file" name="file" required>
    <button type="submit">Upload</button>
</form>