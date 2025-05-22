<?php
require __DIR__ . '/vendor/autoload.php';

use Kreait\Firebase\Factory;
use Kreait\Firebase\Storage;

header('Content-Type: application/json');

// Load Firebase credentials
$factory = (new Factory)->withServiceAccount(__DIR__.'/serviceAccountKey.json');
$storage = $factory->createStorage();

// Get image from POST body
$imageData = file_get_contents("php://input");

if (!$imageData) {
    http_response_code(400);
    echo json_encode(["status" => "fail", "message" => "No image data received"]);
    exit;
}

// Generate unique filename
$filename = 'uploads/cam_' . time() . '.jpg';

// Save temporarily
$tempPath = sys_get_temp_dir() . '/' . basename($filename);
file_put_contents($tempPath, $imageData);

// Upload to Firebase
$bucket = $storage->getBucket();
$object = $bucket->upload(fopen($tempPath, 'r'), [
    'name' => $filename
]);

// Make it publicly accessible
$object->update(['acl' => []], ['predefinedAcl' => 'PUBLICREAD']);
$url = "https://storage.googleapis.com/" . $bucket->name() . "/" . $filename;

// Delete temp file
unlink($tempPath);

// Return image URL
echo json_encode(["status" => "success", "url" => $url]);
?>

