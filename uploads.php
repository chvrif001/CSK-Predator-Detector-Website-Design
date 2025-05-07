<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_FILES['image'])) {
        $targetDir = "uploads/";
        $targetFile = $targetDir . basename($_FILES["image"]["name"]);

        if (move_uploaded_file($_FILES["image"]["tmp_name"], $targetFile)) {
            echo "Upload successful.";
        } else {
            echo "Upload failed.";
        }
    } else {
        echo "No image received.";
    }
} else {
    echo "Invalid request method.";
}
?>

