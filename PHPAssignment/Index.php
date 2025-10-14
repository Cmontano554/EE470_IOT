<!DOCTYPE html>
<html>
<head>
    <title>SSU IoT Lab</title>
    <style>
        /* ===== Page Setup ===== */
        body {
            font-family: Arial, sans-serif;
            background-color: #ffffff;
            background-image: linear-gradient(to right, rgba(0,0,0,0.03) 1px, transparent 1px),
                              linear-gradient(to bottom, rgba(0,0,0,0.03) 1px, transparent 1px);
            background-size: 20px 20px; /* creates grid effect */
            text-align: center;
            padding: 20px;
        }

        /* ===== Titles ===== */
        h1 {
            font-size: 38px;
            color: #000;
            margin-bottom: 5px;
            font-weight: bold;
        }

        h2 {
            color: #333;
            margin-top: 40px;
            font-size: 20px;
            font-weight: normal;
        }

        /* ===== Tables ===== */
        table {
            border-collapse: collapse;
            margin: 0 auto 30px auto;
            width: 60%;
            border: 1px solid #000;
        }

        th {
            background-color: #b7d47d;
            color: #000;
            padding: 10px;
            border: 1px solid #000;
            font-weight: bold;
        }

        td {
            border: 1px solid #000;
            padding: 8px;
            text-align: center;
            background-color: #ffffff;
        }

        tr:nth-child(even) td {
            background-color: #f5f9f0;
        }
    </style>
</head>
<body>
    
    <h1>Welcome to<br>SSU IoT Lab</h1>

    <?php
    
    include 'db_connect.php';
    /* ---------- Registered Sensor Nodes ---------- */
    echo "<h2>Registered Sensor Nodes</h2>";
    $sql = "SELECT node_name AS Name, manufacturer AS Manufacturer, longitude AS Longitude, latitude AS Latitude 
            FROM sensor_register 
            ORDER BY node_name";
    $result = $conn->query($sql);
    if (!$result) { die("Query failed: " . $conn->error); }
    if ($result && $result->num_rows > 0) {
        echo "<table><tr><th>Name</th><th>Manufacturer</th><th>Longitude</th><th>Latitude</th></tr>";
        while($row = $result->fetch_assoc()) {
            echo "<tr><td>{$row['Name']}</td><td>{$row['Manufacturer']}</td><td>{$row['Longitude']}</td><td>{$row['Latitude']}</td></tr>";
        }
        echo "</table>";
    } else {
        echo "<p>No data in sensor_register table.</p>";
    }

    /* ---------- Data Received ---------- */
    echo "<h2>Data Received</h2>";
    $sql = "SELECT node_name AS Node, time_received AS Time, temperature AS Temperature, humidity AS Humidity 
            FROM sensor_data 
            ORDER BY node_name, time_received";
    $result = $conn->query($sql);
    
    if ($result && $result->num_rows > 0) {
        echo "<table><tr><th>Node</th><th>Time</th><th>Temperature</th><th>Humidity</th></tr>";
        while($row = $result->fetch_assoc()) {
            echo "<tr><td>{$row['Node']}</td><td>{$row['Time']}</td><td>{$row['Temperature']}</td><td>{$row['Humidity']}</td></tr>";
        }
        echo "</table>";
    } else {
        echo "<p>No data in sensor_data table.</p>";
    }
/* ---------- Averages per Node ---------- */
echo "<h2>Average Temperature and Humidity per Node</h2>";

$sql = "SELECT 
            node_name, 
            ROUND(AVG(temperature), 2) AS avg_temperature, 
            ROUND(AVG(humidity), 2) AS avg_humidity
        FROM sensor_data
        GROUP BY node_name";

$result = $conn->query($sql);
if (!$result) { die("Query failed: " . $conn->error); }

if ($result && $result->num_rows > 0) {
    echo "<table><tr><th>Node</th><th>Avg Temperature</th><th>Avg Humidity</th></tr>";
    while($row = $result->fetch_assoc()) {
        echo "<tr><td>{$row['node_name']}</td><td>{$row['avg_temperature']}</td><td>{$row['avg_humidity']}</td></tr>";
    }
    echo "</table>";
} else {
    echo "<p>No data available for averages.</p>";
}
    $conn->close();
    ?>
<!-- ====================== TEMPERATURE LINE CHART (FINAL VERSION) ====================== -->
<div style="display: flex; justify-content: center; margin-top: 40px;">
  <div style="
      background-color: #ffffff;
      border: 2px solid #c8e6c9;
      box-shadow: 0px 4px 8px rgba(0,0,0,0.1);
      border-radius: 12px;
      padding: 20px;
      width: 500px;
      text-align: center;">
      
      <h3 style="margin-bottom: 15px; color: #2e7d32;">Temperature vs Time — Node 1</h3>
      <canvas id="tempChart" width="400" height="250"></canvas>
  </div>
</div>

<?php
include 'db_connect.php';

// Fetch temperature and time for Node 1
$sql = "SELECT time_received, temperature 
        FROM sensor_data 
        WHERE LOWER(node_name) = LOWER('NODE_1')
        ORDER BY time_received";
$result = $conn->query($sql);

$times = [];
$temps = [];

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $times[] = $row['time_received'];
        $temps[] = $row['temperature'];
    }
}
$conn->close();

$times_json = json_encode($times);
$temps_json = json_encode($temps);
?>

<!-- Load Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
const ctx = document.getElementById('tempChart').getContext('2d');
const tempChart = new Chart(ctx, {
    type: 'bar',
    data: {
        labels: <?php echo $times_json; ?>,
        datasets: [{
            label: 'Sensor Node 1',
            data: <?php echo $temps_json; ?>,
            fill: false,
            borderColor: 'rgba(56, 142, 60, 1)',
            backgroundColor: 'rgba(76, 175, 80, 0.3)',
            tension: 0.3,
            pointBackgroundColor: '#2e7d32',
            pointRadius: 4,
            pointHoverRadius: 6,
            borderWidth: 2
        }]
    },
    options: {
        scales: {
            x: {
                title: {
                    display: true,
                    text: 'Time',
                    color: '#000',
                    font: { size: 12 }
                },
                ticks: {
                    color: '#000',
                    autoSkip: true,
                    maxTicksLimit: 6
                },
                grid: { color: 'rgba(0, 0, 0, 0.1)' }
            },
            y: {
                title: {
                    display: true,
                    text: 'Temperature (°C)',
                    color: '#000',
                    font: { size: 12 }
                },
                beginAtZero: true,
                ticks: { color: '#000' },
                grid: { color: 'rgba(0, 0, 0, 0.1)' }
            }
        },
        plugins: {
            legend: {
                position: 'top',
                labels: {
                    color: '#000',
                    boxWidth: 20,
                    boxHeight: 10
                }
            }
        }
    }
});
</script>
<!-- =============================================================== -->

<?php
include 'db_connect.php';

// Query Node 1 data
$sql = "SELECT node_name, time_received, temperature, humidity
        FROM sensor_data 
        WHERE node_name = 'NODE_1' 
        ORDER BY time_received";

$result = $conn->query($sql);
$data = [];

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
}

header('Content-Type: application/json');
echo json_encode($data, JSON_PRETTY_PRINT);

$conn->close();
?>

</body>
</html>
