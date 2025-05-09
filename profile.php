<?php
session_start();
$imageData = null;

// Only process image if POSTed
if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($_FILES)) {
    $rawData = file_get_contents("php://input");
    if ($rawData) {
        $base64 = base64_encode($rawData);
        $imageData = "data:image/jpeg;base64,$base64";
        $_SESSION['temp_image'] = $imageData; // Optional: store for 1 view
    }
} elseif (isset($_SESSION['temp_image'])) {
    $imageData = $_SESSION['temp_image'];
    unset($_SESSION['temp_image']); // Remove after one view
}
?>

<!DOCTYPE html>
<html>
<head><title>Profile | CSK Predator Detector</title></head>
<body style="font-family: tahoma;">

    <h2>Latest Uploaded Image (Temporary)</h2>

    <?php if ($imageData): ?>
        <img src="<?= $imageData ?>" style="max-width: 100%; border: 2px solid black;">
    <?php else: ?>
        <p>No image uploaded or expired.</p>
    <?php endif; ?>

</body>
</html>
