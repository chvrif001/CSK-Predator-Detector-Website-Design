<?php
// command_check.php - Endpoint for ESP32 to poll for commands

// Set error reporting for debugging
ini_set('display_errors', 0); // Don't show errors to clients
error_reporting(E_ALL);

// Define the commands directory
$commandsDir = '/opt/render/project/uploads/commands/';
$commandFile = $commandsDir . 'latest_command.json';

// Get device ID if provided
$deviceId = $_GET['device_id'] ?? 'unknown';

// Default response
$response = [
    'status' => 'no_command',
    'command' => null,
    'timestamp' => time()
];

// Check if command file exists and read it
if (file_exists($commandFile)) {
    $commandData = json_decode(file_get_contents($commandFile), true);
    
    // Check if command is valid and not too old (within last 5 minutes)
    if ($commandData && 
        isset($commandData['command']) && 
        isset($commandData['timestamp']) &&
        isset($commandData['processed']) &&
        !$commandData['processed'] &&
        $commandData['timestamp'] > (time() - 300)) {
        
        // Valid command found
        $response = [
            'status' => 'ok', 
            'command' => $commandData['command'],
            'timestamp' => $commandData['timestamp']
        ];
        
        // Mark as processed
        $commandData['processed'] = true;
        file_put_contents($commandFile, json_encode($commandData));
        
        // Log this action
        $logMessage = date('Y-m-d H:i:s') . " - Command '{$commandData['command']}' sent to device: $deviceId\n";
        file_put_contents($commandsDir . 'command_log.txt', $logMessage, FILE_APPEND);
    }
}

// Send JSON response
header('Content-Type: application/json');
echo json_encode($response);
?>