<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

date_default_timezone_set('Africa/Johannesburg');

// Scan uploads folder for latest image
$uploadsDir = 'uploads/';
$latestImagePath = null;
$allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];

if (is_dir($uploadsDir)) {
    $files = array_diff(scandir($uploadsDir, SCANDIR_SORT_DESCENDING), array('.', '..'));
    foreach ($files as $file) {
        $filePath = $uploadsDir . $file;
        $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        if (in_array($ext, $allowedExtensions)) {
            $latestImagePath = $filePath;
            break;
        }
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

    <!-- Latest Uploaded Image and Response Buttons -->
    <div style="width: 800px; margin: auto; margin-top: 20px;">
        <h2>Detected Images</h2>
        <?php if ($latestImagePath): ?>
            <p>Most recent image detected:</p>
            <img src="<?php echo htmlspecialchars($latestImagePath); ?>" style="max-width: 100%; height: auto; border: 2px solid black;">
        <?php else: ?>
            <p>No uploaded images to display.</p>
        <?php endif; ?>

        <form method="post" action="action_response.php" style="margin-top: 20px;">
            <button name="action" value="safe">Not a Threat</button>
            <button name="action" value="deter">Deter</button>
        </form>
    </div>

</body>
</html>





