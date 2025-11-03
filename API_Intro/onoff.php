<?php
$FILE = __DIR__ . "/results.txt";

// Load or initialize data
if (file_exists($FILE)) {
  $data = json_decode(file_get_contents($FILE), true);
  if (!is_array($data)) $data = ["led" => "OFF", "r" => 0, "g" => 0, "b" => 0];
} else {
  $data = ["led" => "OFF", "r" => 0, "g" => 0, "b" => 0];
}

// Handle LED ON/OFF control (via PUT)
if ($_SERVER["REQUEST_METHOD"] === "PUT") {
  $body = trim(file_get_contents("php://input"));
  if (in_array($body, ["ON", "OFF"])) {
    $data["led"] = $body;
    file_put_contents($FILE, json_encode($data, JSON_PRETTY_PRINT));
  }
  exit;
}

// Handle RGB sliders save
if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $data["r"] = max(0, min(255, intval($_POST["r"] ?? 0)));
  $data["g"] = max(0, min(255, intval($_POST["g"] ?? 0)));
  $data["b"] = max(0, min(255, intval($_POST["b"] ?? 0)));
  file_put_contents($FILE, json_encode($data, JSON_PRETTY_PRINT));
  header("Location: " . $_SERVER["PHP_SELF"]);
  exit;
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>ESP LED + Full RGB Control</title>
  <style>
    body { font-family: system-ui, sans-serif; margin: 24px; }
    h1 { margin-bottom: 10px; }
    section { border: 1px solid #ddd; padding: 16px; border-radius: 8px; margin-bottom: 24px; }
    input[type=range]{ width: 320px; }
    .preview { width: 60px; height: 30px; border: 1px solid #aaa; display: inline-block; vertical-align: middle; border-radius: 6px; margin-left: 10px; }
    button { padding: 8px 16px; margin: 5px; font-size: 1em; border-radius: 6px; cursor: pointer; border: none; }
    .on { background-color: #4CAF50; color: white; }
    .off { background-color: #f44336; color: white; }
  </style>
</head>
<body>
  <h1>ESP LED Control Dashboard</h1>

  <!-- LED ON/OFF Control -->
  <section>
    <h2>Power Control</h2>
    <p>Status: <strong id="status"><?php echo htmlspecialchars($data["led"]); ?></strong></p>
    <button class="on" onclick="send('ON')">Turn ON</button>
    <button class="off" onclick="send('OFF')">Turn OFF</button>
  </section>

  <!-- Full RGB Control -->
  <section>
    <h2>RGB LED Control</h2>
    <form method="post" oninput="updatePreview()">
      <p>Red: <strong id="rval"><?php echo intval($data["r"]); ?></strong></p>
      <input type="range" name="r" min="0" max="255" value="<?php echo intval($data["r"]); ?>">

      <p>Green: <strong id="gval"><?php echo intval($data["g"]); ?></strong></p>
      <input type="range" name="g" min="0" max="255" value="<?php echo intval($data["g"]); ?>">

      <p>Blue: <strong id="bval"><?php echo intval($data["b"]); ?></strong></p>
      <input type="range" name="b" min="0" max="255" value="<?php echo intval($data["b"]); ?>">

      <div class="preview" id="pv"></div><br>
      <button type="submit">Save</button>
    </form>
  </section>
  <!-- Data Visualization -->
  <section>
    <h2>Live Data Chart</h2>
    
    <!-- RGB Intensity Chart -->
    <h3>RGB Channel Intensities</h3>
    <iframe class = "Chart" width="600" height="371" seamless frameborder="0" scrolling="no" src="https://docs.google.com/spreadsheets/d/e/2PACX-1vSKdac-qeP8e2xriKjvAx_xAsWwAeFXIe3gokhjtCZ7cKjNs_OjqeMKJb0jkPjxyCzGCRhPg_pEouUP/pubchart?oid=40903891&amp;format=interactive"></iframe>

  </section>
<script>
  // Refresh both charts every 60 seconds
  setInterval(() => {
    document.querySelectorAll("iframe.chart").forEach(frame => {
      const src = frame.src;
      frame.src = src.split("?")[0] + "?_=" + new Date().getTime(); // cache-busting
    });
  }, 10000); // 
</script>

  <script>
    function updatePreview() {
      const r = document.querySelector('input[name=r]').value;
      const g = document.querySelector('input[name=g]').value;
      const b = document.querySelector('input[name=b]').value;
      document.getElementById('rval').textContent = r;
      document.getElementById('gval').textContent = g;
      document.getElementById('bval').textContent = b;
      document.getElementById('pv').style.backgroundColor = `rgb(${r},${g},${b})`;
    }
    updatePreview();

    async function send(state) {
      await fetch(location.href, {
        method: "PUT",
        headers: { "Content-Type": "text/plain" },
        body: state
      });
      document.getElementById("status").textContent = state;
    }
  </script>
</body>
</html>
