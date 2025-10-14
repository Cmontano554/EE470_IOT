<?php
include 'db_connect.php';

// Check if Base64 message was sent
if (isset($_GET['msg'])) {
    // Decode Base64 message
    $decoded = base64_decode($_GET['msg']);

    // Parse the decoded string into an array (handles & and =)
    parse_str($decoded, $data);

    // Extract values safely
    $node_name = $data['nodeId'] ?? null;
    $temperature = $data['nodeTemp'] ?? null;
    $time_received = $data['timeReceived'] ?? null;

    // Validate required fields
    if (!$node_name || !$temperature) {
        die("Error: Missing required values (nodeId or nodeTemp).");
    }

    // If time is missing, use current time
    if (!$time_received) {
        $time_received = date('Y-m-d H:i:s');
    }

    // Insert into database
    $sql = "INSERT INTO sensor_data (node_name, time_received, temperature)
            VALUES ('$node_name', '$time_received', '$temperature')";

    if ($conn->query($sql) === TRUE) {
        echo "Decoded and inserted successfully: $node_name, $temperature, $time_received";
    } else {
        echo "Error inserting data: " . $conn->error;
    }

} else {
    echo "Error: No 'msg' parameter received.";
}

$conn->close();
?>
