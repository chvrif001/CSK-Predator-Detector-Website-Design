<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_FILES['image'])) {
        $file = $_FILES['image'];
        echo 'File Name: ' . $file['name'] . '<br>';
        echo 'File Size: ' . $file['size'] . '<br>';
        echo 'File Type: ' . $file['type'] . '<br>';
        
        // Move the uploaded file to the uploads directory
        $targetPath = 'uploads/' . basename($file['name']);
        if (move_uploaded_file($file['tmp_name'], $targetPath)) {
            echo 'File uploaded successfully!';
        } else {
            echo 'Failed to upload file.';
        }
    } else {
        echo 'No file uploaded.';
    }
}
?>

<form method="POST" enctype="multipart/form-data">
    Upload an image: <input type="file" name="image"><br>
    <input type="submit" value="Upload">
</form>
