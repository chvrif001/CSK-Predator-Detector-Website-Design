<?php
session_start();

// Directory where images are uploaded
$imageDir = "uploads/";
$images = array_diff(scandir($imageDir), array('..', '.'));
?>

<!DOCTYPE html>
<html>
<head>
    <title>Profile | CSK Predator Detector</title>
</head>
<body style="font-family: tahoma;">
    <div id="blue_bar" style="height: 100px; background-color: green; color: whitesmoke;">
        <div style="width: 800px; margin: auto; font-size: 30px;">
            CSK Predator Detector
            <img src="logo.png" style="width: 100px; float: right;">
        </div>
    </div>

    <div style="width: 800px; margin: auto; margin-top: 20px;">
        <h2>Detected Images</h2>
        <?php foreach ($images as $img): ?>
            <div style="margin-bottom: 20px;">
                <img src="<?php echo $imageDir . $img; ?>" width="400"><br>
                <form method="post" action="action_response.php">
                    <input type="hidden" name="image" value="<?php echo $img; ?>">
                    <button name="action" value="safe">Not a Threat</button>
                    <button name="action" value="deter">Deter</button>
                </form>
            </div>
        <?php endforeach; ?>
    </div>
</body>
</html>
