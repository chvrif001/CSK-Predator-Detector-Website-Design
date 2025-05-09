<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

date_default_timezone_set('Africa/Johannesburg');

// Handle image upload
$upload_dir = "uploads/";
$image_path = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['image'])) {
    $file = $_FILES['image'];

    if ($file['error'] === 0) {
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = "img_" . time() . "." . $ext;
        $destination = $upload_dir . $filename;

        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true); // Create uploads/ folder if it doesn't exist
        }

        if (move_uploaded_file($file['tmp_name'], $destination)) {
            $image_path = $destination;
        } else {
            echo "<p style='color:red;'>Failed to upload image.</p>";
        }
    } else {
        echo "<p style='color:red;'>Image error: " . $file['error'] . "</p>";
    }
}

// Get most recent image from uploads/
$latest_image = null;
if (is_dir($upload_dir)) {
    $files = glob($upload_dir . "*.{jpg,jpeg,png,gif}", GLOB_BRACE);
    if ($files) {
        usort($files, function ($a, $b) {
            return filemtime($b) - filemtime($a);
        });
        $latest_image = $files[0];
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Profile | CSK Predator Detector</title>
</head>
<body style="font-family: tahoma;">

    <!-- Header -->
    <div id="blue_bar" style="height: 100px; background-color: green; color: whitesmoke;">
        <div style="width: 800px; margin: auto; font-size: 30px;">
            CSK Predator Detector
            <img src="logo.png" style="width: 100px; float: right;">
        </div>
    </div>

    <!-- Status Message -->
    <div style="width: 800px; margin: auto; margin-top: 10px;">
        <?php if (isset($_GET['status'])): ?>
            <div style="background-color: lightyellow; padding: 10px; margin-bottom: 15px;">
                <?php
                switch ($_GET['status']) {
                    case 'safe_sent':
                        echo "🟢 Message sent: 'Not a threat.'";
                        break;
                    case 'deter_sent':
                        echo "⚠️ Message sent: 'Honeybadger deterred.'";
                        break;
                    default:
                        echo "Status unknown.";
                        break;
                }
                ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Detected Image & Actions -->
    <div style="width: 800px; margin: auto; margin-top: 20px;">
        <h2>Detected Image</h2>

        <?php if ($latest_image): ?>
            <img src="<?php echo $latest_image; ?>" style="max-width: 100%; height: auto; border: 2px solid #ccc;"><br><br>
        <?php else: ?>
            <p>No uploaded images to display.</p>
        <?php endif; ?>

        <!-- Upload Form -->
        <form method="post" enctype="multipart/form-data">
            <label>Upload new image:</label><br>
            <input type="file" name="image" accept="image/*" required>
            <button type="submit">Upload</button>
        </form>
        <br>

        <!-- Action Buttons -->
        <form method="post" action="action_response.php">
            <button name="action" value="safe">Not a Threat</button>
            <button name="action" value="deter">Deter</button>
        </form>
    </div>

</body>
</html>

