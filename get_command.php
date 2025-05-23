<?php
$commandFile = "latest_command.txt";

if (file_exists($commandFile)) {
    $command = trim(file_get_contents($commandFile));
    echo json_encode(["command" => $command]);

    // Optionally clear the command after reading (one-time execution)
    file_put_contents($commandFile, "none");
} else {
    echo json_encode(["command" => "none"]);
}
?>
