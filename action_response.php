<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    $botToken = "8065240956:AAEJT7DigtGISpkjkjaKQYNYrGJkpGO07Jc";
    $chatId = "7595966011";
    $message = "";

    if ($action === "safe") {
        $message = "🟢 Not a threat.";
    } elseif ($action === "deter") {
        $message = "⚠️ Honeybadger deterred.";
    }

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
        $result = file_get_contents($url, false, $context);
    }

    // Redirect back to profile
    header("Location: profile.php?status=" . $action . "_sent");
    exit;
}

