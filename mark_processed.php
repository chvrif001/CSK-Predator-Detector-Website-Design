<?php

// Path to the commands directory
$commandsDir = '/opt/render/project/uploads/commands/';

// Path to the latest command file
$commandFile = $commandsDir . 'latest_command.json';

// Retrieve the incoming data (POST)
$command = $_POST['command'] ?? '';
$processed = $_POST['processed'] ?? '';

// Check if the required data is available
if ($command && $processed !== '') {
    // Read the current command data
    $commandData = json_decode(file_get_contents($commandFile), true);
    
    // Update the processed flag
    if ($commandData['command'] == $command) {
        $commandData['processed'] = ($processed == 'true');
        file_put_contents($commandFile, json_encode($commandData)); // Save the updated data

        // Respond with success
        echo "Command marked as processed.";
    } else {
        echo "Command mismatch or not found.";
    }
} else {
    echo "Invalid data.";
}
