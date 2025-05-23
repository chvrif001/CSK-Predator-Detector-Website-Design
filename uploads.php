<?php
require __DIR__ . '/vendor/autoload.php';

use Google\Cloud\Storage\StorageClient;

// Set path to Firebase Admin SDK key
putenv('GOOGLE_APPLICATION_CREDENTIALS=' . __DIR__ . '/honeybadgercam-800a0-firebase-adminsdk-fbsvc-95a8e80fb7.json');

// Your Firebase Storage bucket name (must end in .appspot.com)
$bucketName = 'honeybadgercam-800a0.firebasestorage.app';

// Initialize the Storage client
$storage = new StorageClient();
$bucket = $storage->bucket($bucketName);

// Check if file is uploaded via POST
if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
    http_response_code(400);
    echo "No file uploaded or upload error.";
    exit;
}

// File info
$tmpFilePath = $_FILES['image']['tmp_name'];
$originalName = basename($_FILES['image']['name']);
$cloudPath = 'uploads/' . time() . '_' . $originalName;

try {
    $file = fopen($tmpFilePath, 'r');
    $bucket->upload($file, [
        'name' => $cloudPath
    ]);
    fclose($file);

    $url = "https://storage.googleapis.com/$bucketName/$cloudPath";
    echo "✅ File uploaded successfully: <a href='$url'>$url</a>";
} catch (Exception $e) {
    http_response_code(500);
    echo "❌ Upload failed: " . $e->getMessage();
}
?>





