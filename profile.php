<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

date_default_timezone_set('Africa/Johannesburg'); // Set timezone to SAST
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

    <!-- Action Buttons (No Images) -->
    <div style="width: 800px; margin: auto; margin-top: 20px;">
        <h2>Detected Images</h2>
        <p>No uploaded images to display.</p>
        <form method="post" action="action_response.php">
            <button name="action" value="safe">Not a Threat</button>
            <button name="action" value="deter">Deter</button>
        </form>
    </div>

</body>
</html>

