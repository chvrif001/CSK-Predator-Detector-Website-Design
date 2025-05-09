<?php
session_start();
$imageData = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $rawData = file_get_contents("php://input");
    if ($rawData) {
        $base64 = base64_encode($rawData);
        $imageData = "data:image/jpeg;base64,$base64";
        $_SESSION['temp_image'] = $imageData;  // optional for 1-time view
    }
} elseif (isset($_SESSION['temp_image'])) {
    $imageData = $_SESSION['temp_image'];
    unset($_SESSION['temp_image']); // Only show once
}
?>

<!DOCTYPE html>
<html>
<head><title>CSK Predator Detector</title></head>
<body>
    <h2>Live Image</h2>
    <?php if ($imageData): ?>
        <img src="<?= $imageData ?>" style="max-width: 100%; border: 2px solid black;">
    <?php else: ?>
        <p>No image uploaded or expired.</p>
    <?php endif; ?>
</body>
</html>
