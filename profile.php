<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
date_default_timezone_set('Africa/Johannesburg');

// Set error reporting for debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Define the upload directory - use an environment variable to make it configurable
$uploadsDir = "/data/uploads/";
$logFile = $uploadsDir . 'uploads.log';

// Ensure the uploads directory has a trailing slash
if (substr($uploadsDir, -1) !== '/') {
    $uploadsDir .= '/';
}

// Function to get all uploaded image files
function getUploadedImages($dir) {
    $images = [];
    $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
    
    if (is_dir($dir)) {
        $files = array_diff(scandir($dir, SCANDIR_SORT_DESCENDING), array('.', '..'));
        foreach ($files as $file) {
            $filePath = $dir . $file;
            if (!is_dir($filePath)) {
                $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
                if (in_array($ext, $allowedExtensions)) {
                    $images[] = $file;
                }
            }
        }
    }
    
    return $images;
}

// Try to get images from the log file first (if it exists)
$imageFiles = [];
if (file_exists($logFile)) {
    $logContents = file_get_contents($logFile);
    $imageFiles = array_reverse(array_filter(explode("\n", $logContents)));
} 

// If no images found in log or log doesn't exist, scan the directory
if (empty($imageFiles)) {
    $imageFiles = getUploadedImages($uploadsDir);
}

// Get the latest image
$latestImagePath = null;
if (!empty($imageFiles)) {
    $latestImagePath = $uploadsDir . $imageFiles[0];
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Profile | CSK Predator Detector</title>
    <style>
        .image-gallery {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            margin-top: 20px;
        }
        .image-item {
            width: 200px;
            margin-bottom: 15px;
            border: 1px solid #ddd;
            padding: 10px;
            background-color: #f9f9f9;
        }
        .image-item img {
            max-width: 100%;
            height: auto;
            border: 1px solid #ccc;
        }
        .image-timestamp {
            font-size: 12px;
            color: #666;
            margin-top: 5px;
        }
        .debug-info {
            margin-top: 30px;
            padding: 10px;
            background-color: #f0f0f0;
            border: 1px solid #ddd;
            display: none;
        }
        .debug-toggle {
            cursor: pointer;
            color: blue;
            text-decoration: underline;
        }
    </style>
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
        <img src="get_image.php?file=<?php echo urlencode(basename($latestImagePath)); ?>" style="max-width: 100%; height: auto; border: 2px solid black;">
        
        <form method="post" action="action_response.php" style="margin-top: 20px;">
            <button name="action" value="safe">Not a Threat</button>
            <button name="action" value="deter">Deter</button>
        </form>
        
        <?php if (count($imageFiles) > 1): ?>
            <h3>Previous Detections</h3>
            <div class="image-gallery">
                <?php 
                // Skip the first image (already displayed as latest)
                $olderImages = array_slice($imageFiles, 1, 10); // Show up to 10 older images
                foreach ($olderImages as $image): 
                    // Extract timestamp from filename if available
                    $timestamp = null;
                    if (preg_match('/^(\d+)_/', $image, $matches)) {
                        $timestamp = date('Y-m-d H:i:s', (int)$matches[1]);
                    }
                ?>
                    <div class="image-item">
                        <img src="get_image.php?file=<?php echo urlencode($image); ?>" alt="Detected image">
                        <?php if ($timestamp): ?>
                            <div class="image-timestamp">Detected: <?php echo $timestamp; ?></div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        
    <?php else: ?>
        <p>No uploaded images to display.</p>
    <?php endif; ?>
        
        <!-- Debug information (hidden by default) -->
        <p class="debug-toggle" onclick="document.getElementById('debug-info').style.display = document.getElementById('debug-info').style.display === 'none' ? 'block' : 'none'">
            Show debug information
        </p>
        <div id="debug-info" class="debug-info">
            <h4>Debug Information</h4>
            <ul>
                <li>Upload directory: <?php echo htmlspecialchars($uploadsDir); ?></li>
                <li>Log file path: <?php echo htmlspecialchars($logFile); ?></li>
                <li>Log file exists: <?php echo file_exists($logFile) ? 'Yes' : 'No'; ?></li>
                <li>Upload directory exists: <?php echo is_dir($uploadsDir) ? 'Yes' : 'No'; ?></li>
                <?php if (is_dir($uploadsDir)): ?>
                    <li>Files in upload directory: <?php echo implode(', ', array_diff(scandir($uploadsDir), array('.', '..'))); ?></li>
                <?php endif; ?>
                <li>Number of image files found: <?php echo count($imageFiles); ?></li>
                <?php if (!empty($imageFiles)): ?>
                    <li>Latest image filename: <?php echo htmlspecialchars($imageFiles[0]); ?></li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</body>
</html>







