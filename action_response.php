<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    $botToken = "8065240956:AAEJT7DigtGISpkjkjaKQYNYrGJkpGO07Jc";
    $chatId = "7595966011";
    $message = "";
    $espCommand = "";

    if ($action === "safe") {
        $message = "🟢 Not a threat.";
        $espCommand = "neglect";
    } elseif ($action === "deter") {
        $message = "⚠️ Honeybadger deterrent Activated.";
        $espCommand = "deter";
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

    // Send command to ESP32
    if ($espCommand) {
        $espUrl = "http://172.20.10.5/$espCommand";
        @file_get_contents($espUrl);  // Suppress warnings in case ESP32 is unreachable
    }

    // Redirect back to profile
    header("Location: profile.php?status=" . $action . "_sent");
    exit;
}





