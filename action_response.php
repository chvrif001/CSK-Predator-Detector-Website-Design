<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $botToken = "8065240956:AAEJT7DigtGISpkjkjaKQYNYrGJkpGO07Jc";
    $chatId = "7595966011";
    $message = "";
    $espCommand = "";

    if ($action === "safe") {
        $message = "/photo";
        $espCommand = "neglect";
    } elseif ($action === "deter") {
        $message = "⚠️ Honeybadger deterrent Activated.";
        $espCommand = "buzz_on";
    }

    // Save the command to a text file
    if ($espCommand) {
        file_put_contents("latest_command.txt", $espCommand);
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

    // Redirect back to profile or wherever
    header("Location: profile.php?status=" . $action . "_sent");
    exit;
}
?>



