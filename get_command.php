<?php
// get_command.php - Stores and retrieves commands for ESP32

// Simple file-based command storage
$commandFile = 'esp_command.txt';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Store new command (called by action_response.php)
    $command = $_POST['command'] ?? '';
    
    if (in_array($command, ['deter', 'neglect', 'buzzer', 'none'])) {
        file_put_contents($commandFile, $command);
        echo "Command stored: $command";
    } else {
        echo "Invalid command";
    }
    
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Retrieve command (called by ESP32)
    $clear = $_GET['clear'] ?? '';
    
    if (file_exists($commandFile)) {
        $command = trim(file_get_contents($commandFile));
        
        // If ESP32 requests to clear the command after reading
        if ($clear === 'true') {
            file_put_contents($commandFile, 'none');
        }
        
        // Return command in simple text format
        echo $command;
    } else {
        echo 'none';
    }
} else {
    // Show current status (for debugging)
    echo "<h2>ESP32 Command Status</h2>";
    
    if (file_exists($commandFile)) {
        $command = trim(file_get_contents($commandFile));
        echo "<p>Current command: <strong>$command</strong></p>";
    } else {
        echo "<p>No command file found</p>";
    }
    
    echo "<p>Last updated: " . date('Y-m-d H:i:s') . "</p>";
    
    // Manual command form for testing
    echo "<hr>";
    echo "<h3>Manual Command Test</h3>";
    echo "<form method='post'>";
    echo "<select name='command'>";
    echo "<option value='none'>None</option>";
    echo "<option value='deter'>Deter</option>";
    echo "<option value='neglect'>Neglect</option>";
    echo "<option value='buzzer'>Buzzer</option>";
    echo "</select>";
    echo "<button type='submit'>Set Command</button>";
    echo "</form>";
}
?>