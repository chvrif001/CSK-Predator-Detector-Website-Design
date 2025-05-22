<?php
require __DIR__ . '/vendor/autoload.php';

use Google\Cloud\Storage\StorageClient;

// Set path to the Firebase service account key file
putenv('GOOGLE_APPLICATION_CREDENTIALS=' . __DIR__ . '/honeybadgercam-800a0-firebase-adminsdk-fbsvc-95a8e80fb7.json');

// Set up Google Cloud Storage
$storage = new StorageClient();
$bucketName = 'honeybadgercam-800a0.appspot.com';  // ✅ Must end in .appspot.com
$bucket = $storage->bucket($bucketName);

// Check if a file was uploaded
if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
    http_response_code(400);
    echo "No file uploaded or there was an error.";
    exit;
}

// Prepare file
$tmpFilePath = $_FILES['image']['tmp_name'];
$original




