<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    echo "POST received. ";
   
    // Debug: Print the contents of $_FILES
    echo "Contents of \$_FILES: ";
    print_r($_FILES);
   
    if (isset($_FILES['image'])) {
        $targetDir = "uploads/";
        // Check if directory exists and is writable
        if (!file_exists($targetDir)) {
            echo "Error: Directory does not exist.";
            mkdir($targetDir, 0755, true);
            echo " Created directory.";
        }
        if (!is_writable($targetDir)) {
            echo "Error: Directory is not writable.";
        }
       
        $targetFile = $targetDir . basename($_FILES["image"]["name"]);
        echo "Target file: " . $targetFile . ". ";
       
        if (move_uploaded_file($_FILES["image"]["tmp_name"], $targetFile)) {
            echo "Upload successful.";
        } else {
            echo "Upload failed. Error: " . $_FILES["image"]["error"];
        }
    } else {
        echo "No image received in \$_FILES['image'].";
    }
} else {
    echo "Invalid request method.";
}
?>

