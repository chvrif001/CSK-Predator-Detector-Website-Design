<?php
require __DIR__ . '/vendor/autoload.php'; // Ensure Composer autoload is included

use Kreait\Firebase\Factory;
use Kreait\Firebase\Exception\FirebaseException;
use Kreait\Firebase\Storage;

header('Content-Type: application/json');

try {
    // Initialize Firebase
    $factory = (new Factory)
        ->withServiceAccount(__DIR__ . '/honeybadgercam-800a0-firebase-adminsdk-fbsvc-5ffab772db.json')
        ->withDefaultStorageBucket('honeybadgercam-800a0.appspot.com');

    $storage = $factory->createStorage();
    $bucket = $storage->getBucket();

    // Check file upload
    if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
        throw new Exception('No image uploaded or upload error');
    }

    $file = $_FILES['image'];
    $tempFilePath = $file['tmp_name'];
    $originalName = basename($file['name']);
    $firebasePath = 'uploads/' . uniqid() . '_' . $originalName;

    // Upload file
    $uploadedFile = $bucket->upload(
        fopen($tempFilePath, 'r'),
        [
            'name' => $firebasePath,
            'predefinedAcl' => 'publicRead', // Make it publicly accessible
        ]
    );

    $url = sprintf(
        'https://firebasestorage.googleapis.com/v0/b/%s/o/%s?alt=media',
        $bucket->name(),
        rawurlencode($firebasePath)
    );

    echo json_encode(['success' => true, 'url' => $url]);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
