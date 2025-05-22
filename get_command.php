<?php

// Path to the commands directory
$commandsDir = '/opt/render/project/uploads/commands/';

// Path to the latest command file
$commandFile = $commandsDir . 'latest_command.json';

// Check if the command file exists
if (file_exists($commandFile)) {
    // Read the command data
    $commandData = json_decode(file_get_contents($commandFile), true);

    // Return the command data as JSON
    header('Content-Type: application/json');
    echo json_encode($commandData);
} else {
    // Return an empty response if no command file exists
    header('Content-Type: application/json');
    echo json_encode([
        'command' => '',
        'timestamp' => time(),
        'processed' => true
    ]);
}
