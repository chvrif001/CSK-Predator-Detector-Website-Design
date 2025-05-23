<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
date_default_timezone_set('Africa/Johannesburg');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Profile | CSK Predator Detector</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f2f4f8;
            margin: 0;
            padding: 0;
        }

        #blue_bar {
            height: 100px;
            background: linear-gradient(to right, #006400, #228B22);
            color: white;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 40px;
        }

        #blue_bar img {
            height: 80px;
        }

        .container {
            max-width: 800px;
            margin: 30px auto;
            background-color: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }

        h2 {
            color: #333;
            margin-bottom: 20px;
        }

        .status-box {
            background-color: #fff8dc;
            padding: 12px;
            margin-bottom: 20px;
            border-left: 5px solid #ffa500;
        }

        .btn-group {
            display: flex;
            gap: 20px;
            margin-top: 30px;
        }

        .btn-group button {
            flex: 1;
            padding: 12px 20px;
            font-size: 16px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: 0.3s ease;
        }

        .btn-safe {
            background-color: #4CAF50;
            color: white;
        }

        .btn-safe:hover {
            background-color: #45a049;
        }

        .btn-deter {
            background-color: #FF4136;
            color: white;
        }

        .btn-deter:hover {
            background-color: #e3342f;
        }

        .debug-toggle {
            margin-top: 30px;
            color: #0074D9;
            cursor: pointer;
            text-decoration: underline;
        }

        .debug-info {
            display: none;
            margin-top: 10px;
            padding: 10px;
            background-color: #eaeaea;
            border-radius: 5px;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div id="blue_bar">
        <div style="font-size: 28px;">CSK Predator Detector</div>
        <img src="logo.png" alt="Logo">
    </div>

    <div class="container">
        <h2>System Controls</h2>

        <?php if (isset($_GET['status'])): ?>
            <div class="status-box">
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

        <form method="post" action="action_response.php" class="btn-group">
            <button type="submit" name="action" value="safe" class="btn-safe">Not a Threat</button>
            <button type="submit" name="action" value="deter" class="btn-deter">Deter</button>
        </form>

        <!-- Debug Section -->
        <div class="debug-toggle" onclick="document.getElementById('debug-info').style.display = (document.getElementById('debug-info').style.display === 'none' ? 'block' : 'none')">
            Show debug information
        </div>
        <div id="debug-info" class="debug-info">
            <p>No image logic active. Displaying control buttons only.</p>
        </div>
    </div>
</body>
</html>








