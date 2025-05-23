<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $botToken = "8065240956:AAEJT7DigtGISpkjkjaKQYNYrGJkpGO07Jc";
    $chatId = "7595966011";
    $message = "";
    $espCommand = "";
    
    if ($action === "safe") {
        $message = "🟢 Not a threat.";
        // No need to send command to ESP32 for safe option
    } elseif ($action === "deter") {
        $message = "⚠️ Honeybadger deterrent Activated.";
        $espCommand = "alarm"; // This matches the ESP32's expected command
    }
    
    // Send message to Telegram
    if ($message) {
        $url = "https://api.telegram.org/bot$botToken/sendMessage";
        $data = [
            'chat_id' => $chatId,
            'text' => $message
        ];
        $options = [
            "http" => [
                "header" => "Content-type: application/x-www-form-urlencoded\r\n",
                "method" => "POST",
                "content" => http_build_query($data),
            ]
        ];
        $context = stream_context_create($options);
        file_get_contents($url, false, $context);
    }
    
    // Store the command in a file for the ESP32 to retrieve
    // This is better than directly connecting to the ESP32
    if ($espCommand) {
        // Create a commands directory if it doesn't exist
        $commandsDir = '/opt/render/project/uploads/commands/';
        if (!file_exists($commandsDir)) {
            mkdir($commandsDir, 0755, true);
        }
        
        // Store the command with timestamp
        $commandFile = $commandsDir . 'latest_command.json';
        $commandData = [
            'command' => $espCommand,
            'timestamp' => time(),
            'processed' => false
        ];
        file_put_contents($commandFile, json_encode($commandData));
        
        // Log the command
        error_log("Command saved: " . $espCommand);
    }
    
    // Redirect back to profile
    header("Location: profile.php?status=" . $action . "_sent");
    exit;
}
?>



