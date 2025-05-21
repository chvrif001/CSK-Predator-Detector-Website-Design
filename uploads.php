<?php
// uploads.php - Modified to use persistent storage

// This is just to see if anything is upload, to test uploads
file_put_contents('/tmp/upload_hit.txt', "Uploads.php was hit\n", FILE_APPEND);

// Set error reporting for debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Define the upload directory - use an environment variable to make it configurable
$upload_dir = "/data/uploads/";

// Make sure the directory exists
if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0755, true);
    echo "Created directory: " . $upload_dir . "\n";
}

// Check if we received a file
if(isset($_FILES['image'])) {
    $errors = array();
    $file_name = $_FILES['image']['name'];
    $file_size = $_FILES['image']['size'];
    $file_tmp = $_FILES['image']['tmp_name'];
    
    // Generate a unique filename to prevent overwriting
    $unique_filename = time() . '_' . $file_name;
    $target_file = $upload_dir . $unique_filename;
    
    // Log some information for debugging
    error_log("Receiving file: " . $file_name);
    error_log("Temporary file: " . $file_tmp);
    error_log("Target location: " . $target_file);
    
    // Move the uploaded file to our upload directory
    if(move_uploaded_file($file_tmp, $target_file)) {
        // Also store the filename in a database or file for tracking
        $log_file = $upload_dir . 'uploads.log';
        file_put_contents($log_file, $unique_filename . "\n", FILE_APPEND);
        
        echo "Upload successful";
        error_log("File uploaded successfully: " . $target_file);
    } else {
        echo "Error uploading file";
        error_log("Failed to move uploaded file from $file_tmp to $target_file");
    }
} else {
    echo "No image file received";
    error_log("No file received in the request");
}
?>
