<?php
require __DIR__ . '/vendor/autoload.php';

use Kreait\Firebase\Factory;
use Kreait\Firebase\Storage;

$serviceAccountPath = __DIR__ . '/honeybadgercam-800a0-firebase-adminsdk-fbsvc-95a8e80fb7.json';

$factory = (new Factory)->withServiceAccount($serviceAccountPath);
$storage = $factory->createStorage();

// Get the uploaded file
if (isset($_FILES['image'])) {
    $file = $_FILES['image']['tmp_name'];
    $name = basename($_FILES['image']['name']);

    // Upload to Firebase Storage
    $bucket = $storage->getBucket();
    $bucket->upload(
        fopen($file, 'r'),
        ['name' => "uploads/{$name}"]
    );

    echo "File uploaded successfully!";
} else {
    echo "No file uploaded.";
}
