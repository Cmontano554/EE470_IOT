<?php
include 'db_connect.php';

// Initialize variables
$node_name = null;
$temperature = null;
$humidity = null;
$time_received = null;

// --- OPTION 1: Base64 encoded message ---
if (isset($_GET['msg'])) {
    // Decode Base64 message and parse into array
    $decoded = base64_decode($_GET['msg']);
    parse_str($decoded, $data);

    $node_name = $data['nodeId'] ?? null;
    $temperature = $data['nodeTemp'] ?? null;
    $humidity = $data['nodeHum'] ?? null;
    $time_received = $data['timeReceived'] ?? null;

    echo "Received Base64 encoded message. ";
}

// --- OPTION 2: Plain URL parameters ---
elseif (isset($_GET['nodeId']) && isset($_GET['nodeTemp']) && isset($_GET['nodeHum'])) {
    $node_name = $_GET['nodeId'];
    $temperature = $_GET['nodeTemp'];
    $humidity = $_GET['nodeHum'];
    $time_received = $_GET['timeReceived'] ?? null;

    echo "Received plain URL parameters. ";
}

// --- No data provided ---
else {
    die("Error: No data received. Please send either ?msg= (Base64) or plain parameters like ?nodeId=&nodeTemp=&nodeHum=");
}

// Validate required fields
if (!$node_name || $temperature === null || $humidity === null) {
    die("Error: Missing required values (nodeId, nodeTemp, nodeHum).");
}

// Use current time if none provided
if (!$time_received) {
    $time_received = date('Y-m-d H:i:s');
}

// --- Insert into sensor_data ---
$sql = "INSERT INTO sensor_data (node_name, time_received, temperature, humidity)
        VALUES ('$node_name', '$time_received', '$temperature', '$humidity')";

if ($conn->query($sql) === TRUE) {
    echo "Data inserted successfully. ";
} else {
    echo "Error inserting data: " . $conn->error;
}

// --- Update sensor_activity counter ---
$update = "INSERT INTO sensor_activity (node_name, count)
           VALUES ('$node_name', 1)
           ON DUPLICATE KEY UPDATE count = count + 1";

if ($conn->query($update) === TRUE) {
    echo "Counter updated.";
} else {
    echo "Error updating counter: " . $conn->error;
}

$conn->close();
?>
