<?php
// get_image.php - Safely serves image files from the uploads directory
// Set error reporting for debugging
ini_set('display_errors', 0); // Don't display errors to users

// Define the upload directory - use the same path as uploads.php
$upload_dir = '/opt/render/project/uploads/';

// Check if file parameter exists
if (isset($_GET['file'])) {
    // Sanitize filename to prevent directory traversal attacks
    $filename = basename($_GET['file']);
    $filepath = $upload_dir . $filename;
    
    // Check if file exists and is a regular file
    if (file_exists($filepath) && is_file($filepath)) {
        // Determine MIME type based on file extension
        $ext = strtolower(pathinfo($filepath, PATHINFO_EXTENSION));
        
        $mime_types = [
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif'
        ];
        
        // Set the appropriate Content-Type header
        if (isset($mime_types[$ext])) {
            header('Content-Type: ' . $mime_types[$ext]);
        } else {
            header('Content-Type: application/octet-stream');
        }
        
        // Output the file
        readfile($filepath);
        exit;
    }
}

// If we reach here, file doesn't exist or isn't accessible
header("HTTP/1.0 404 Not Found");
echo "Image not found.";