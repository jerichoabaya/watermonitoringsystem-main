<?php
// pages/download.php
session_start();
include("../includes/db.php"); // mysqli connection $conn

$stationId = isset($_GET['station_id']) ? (int)$_GET['station_id'] : null;
$testId    = isset($_GET['test_id']) ? (int)$_GET['test_id'] : null;
$date      = isset($_GET['date']) ? trim($_GET['date']) : null;

// --- Fetch logged-in user info ---
$user = null;
if (isset($_SESSION['user_id'])) {
    $sql = "SELECT fullname, email, number FROM users WHERE user_id = ?";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("i", $_SESSION['user_id']);
        $stmt->execute();
        $res = $stmt->get_result();
        $user = $res->fetch_assoc();
        $stmt->close();
    }
}

// --- Fetch station info ---
$station = null;
if ($stationId) {
    $sql = "SELECT * FROM refilling_stations WHERE station_id = ?";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("i", $stationId);
        $stmt->execute();
        $res = $stmt->get_result();
        $station = $res->fetch_assoc();
        $stmt->close();
    }
}

// --- Fetch water data - FIXED TO GET ONLY ONE RESULT ---
$result = null; // Changed to single result
if ($stationId) {
    if ($testId) {
        // Get specific test by ID
        $sql = "SELECT * FROM water_data WHERE waterdata_id = ? AND station_id = ? LIMIT 1";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("ii", $testId, $stationId);
            $stmt->execute();
            $res = $stmt->get_result();
            $result = $res->fetch_assoc(); // Single result
            $stmt->close();
        }
    } elseif ($date) {
        // Get latest test for specific date
        $sql = "SELECT * FROM water_data WHERE station_id = ? AND DATE(timestamp) = ? ORDER BY timestamp DESC LIMIT 1";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("is", $stationId, $date);
            $stmt->execute();
            $res = $stmt->get_result();
            $result = $res->fetch_assoc(); // Single result
            $stmt->close();
        }
    } else {
        // Get latest test overall
        $sql = "SELECT * FROM water_data WHERE station_id = ? ORDER BY timestamp DESC LIMIT 1";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("i", $stationId);
            $stmt->execute();
            $res = $stmt->get_result();
            $result = $res->fetch_assoc(); // Single result
            $stmt->close();
        }
    }
}

// Pick display date
$displayDate = $date;
if (!$displayDate && $result && isset($result['timestamp'])) {
    $displayDate = substr($result['timestamp'], 0, 10);
}

//statement
$summaryText = null;
$summaryBg = "#28a745"; // green default
$summaryColor = "#fff";

if ($result) {
    $primary = $result; // use the single result

    // helper: convert to float or return null if not numeric
    $toFloat = function($v) {
        return (is_numeric($v) || $v === '0' || $v === 0) ? (float)$v : null;
    };

    $hasWarning = false;
    $hasFailed  = false;

    // Evaluate parameters with the same thresholds you already use
    $v = $toFloat($primary['color']);
    if ($v !== null && $v > 15) $hasFailed = true;

    $v = $toFloat($primary['ph_level']);
    if ($v !== null && ($v < 6.5 || $v > 8.5)) $hasFailed = true;

    $v = $toFloat($primary['turbidity']);
    if ($v !== null && $v > 5) $hasWarning = true;

    $v = $toFloat($primary['tds']);
    if ($v !== null && $v > 500) $hasWarning = true;

    $v = $toFloat($primary['residual_chlorine']);
    if ($v !== null && ($v < 0.2 || $v > 1.0)) $hasFailed = true;

    $v = $toFloat($primary['lead']);
    if ($v !== null && $v > 0.01) $hasFailed = true;

    $v = $toFloat($primary['cadmium']);
    if ($v !== null && $v > 0.003) $hasFailed = true;

    $v = $toFloat($primary['arsenic']);
    if ($v !== null && $v > 0.01) $hasFailed = true;

    $v = $toFloat($primary['nitrate']);
    if ($v !== null && $v > 50) $hasFailed = true;

    // Decide summary text / color (matches your requested wording)
    if ($hasFailed) {
        $summaryText  = "❌ Test Failed — One or more parameter/s exceeded safe limits. Immediate action required.";
        $summaryBg    = "#dc3545"; // red
        $summaryColor = "#ffffff";
    } elseif ($hasWarning) {
        $summaryText  = "⚠️ Test Passed with Warning — One or more parameter/s reached warning levels. Attention is advised.";
        $summaryBg    = "#ffc107"; // yellow
        $summaryColor = "#000000";
    } else {
        $summaryText  = "✅ Test Passed — All parameters are within safe or neutral limits.";
        $summaryBg    = "#28a745"; // green
        $summaryColor = "#ffffff";
    }
}

// --- Helper function for status badge ---
function badge($text, $type = "safe") {
    $class = "badge-" . strtolower($type);
    return "<span class='badge $class'>$text</span>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Download Report</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <style>
    body { background:#f0f0f0; font-family:Arial, sans-serif; margin:0; padding:20px; }
    .btn { background:#007bff; color:#fff; border:none; padding:8px 16px; border-radius:4px; cursor:pointer; margin-left:5px; }
    .btn:hover { background:#0056b3; }
    .btn:disabled { background:#6c757d; cursor:not-allowed; }
    .pdf-document { background:#fff; color:#000; width:800px; min-height:1000px; margin:20px auto; padding:40px; border:1px solid #ccc; box-shadow:0 0 10px rgba(0,0,0,0.2); display:flex; flex-direction:column; justify-content:space-between; }
    h2,h3 { margin:0; }
    .meta { text-align:center; margin:10px 0 20px; font-size:14px; color:#333; }
    table { width:100%; border-collapse:collapse; margin-top:20px; }
    table, th, td { border:1px solid #000; }
    th, td { padding:8px; text-align:center; }
    .no-results { text-align:center; padding:40px 0; color:#666; }
    .footer { text-align:right; margin-top:50px; font-size:14px; padding-top:40px; }
    /* Badge styles */
    .badge { 
      padding: 4px 10px; 
      border-radius: 12px; 
      font-size: 12px; 
      color: #fff; 
      font-weight: bold; 
    }
    .badge-safe { background: #0051ff; }
    .badge-neutral { background: #28a745; }
    .badge-warning { background: #ff8800; }
    .badge-failed { background: #dc3545; }

    /* --- Print fixes for badge colors --- */
    @media print {
      body { 
        -webkit-print-color-adjust: exact !important; 
        print-color-adjust: exact !important;
        background: white !important;
        margin: 0 !important;
        padding: 0 !important;
      }
      
      .btn { display: none !important; }

      .badge-safe    { background: #0051ff !important; color:#fff !important; border:1px solid #0051ff; }
      .badge-neutral { background: #28a745 !important; color:#fff !important; border:1px solid #28a745; }
      .badge-warning { background: #ff8800 !important; color:#fff !important; border:1px solid #ff8800; }
      .badge-failed  { background: #dc3545 !important; color:#fff !important; border:1px solid #dc3545; }
      
      .pdf-document { 
        box-shadow: none !important; 
        border: none !important; 
        margin: 0 !important; 
        padding: 20px !important;
        width: 100% !important;
      }
    }
    
    .loading { opacity: 0.7; pointer-events: none; }
  </style>
</head>
<body>
  <div style="text-align:right; margin-bottom:20px;">
    <button id="downloadBtn" class="btn">Download PDF</button>
    <button id="printBtn" class="btn">Print</button>
  </div>

  <div class="pdf-document" id="reportContent">
    <div>
      <h2 style="text-align:center;"><br>WATER QUALITY TESTING & MONITORING SYSTEM<br><br> WATER QUALITY TEST RESULTS<br></h2>
      <h3 style="text-align:center; margin-top:8px;"><br><?= $station ? htmlspecialchars($station['name']) : 'Unknown Station' ?></h3>

      <?php if ($station && !empty($station['location'])): ?>
        <p style="text-align:center; margin:4px 0; font-size:14px; color:#000000;">
          <span style="font-weight:bold;">📍 Address:</span> <?= htmlspecialchars($station['location']) ?>
        </p>
      <?php endif; ?>

      <?php if (!empty($station['device_sensor_id'])): ?>
    <p style="text-align:center; margin:2px 0; font-size:14px; color:#000000;">
      <span style="font-weight:bold;">Sensor ID:</span> <?= htmlspecialchars($station['device_sensor_id']) ?>
    </p>
  <?php endif; ?>

      <?php if ($result): ?>
        <?php 
          $runDate = date("Y-m-d", strtotime($result['timestamp']));
          $runTime = date("h:i A", strtotime($result['timestamp']));
        ?>
        <p class="meta">
          <strong>Date:</strong> <?= htmlspecialchars($runDate) ?>  
          <strong>| Time:</strong> <?= htmlspecialchars($runTime) ?>
        </p>
      <?php else: ?>
        <p class="meta"><strong>Date:</strong> — <strong>| Time:</strong> —</p>
      <?php endif; ?>

      <?php if (!$result): ?>
        <div class="no-results">No results found for this station/date.</div>
      <?php else: ?>
        <br><br>
        <table>
          <thead>
            <tr><th>Parameter</th><th>Value</th><th>Status</th></tr>
          </thead>
          <tbody>
            <!-- SINGLE RESULT DISPLAY - NO LOOP -->
            <tr><td>Color (TCU)</td><td><?= htmlspecialchars($result['color']) ?></td><td><?= badge(($result['color'] <= 15) ? "Safe" : "Failed", ($result['color'] <= 15) ? "safe" : "failed") ?></td></tr>
            <tr><td>pH</td><td><?= htmlspecialchars($result['ph_level']) ?></td><td><?= badge(($result['ph_level'] >= 6.5 && $result['ph_level'] <= 8.5) ? "Neutral" : "Failed", ($result['ph_level'] >= 6.5 && $result['ph_level'] <= 8.5) ? "neutral" : "failed") ?></td></tr>
            <tr><td>Turbidity (NTU)</td><td><?= htmlspecialchars($result['turbidity']) ?></td><td><?= badge(($result['turbidity'] <= 5) ? "Safe" : "Warning", ($result['turbidity'] <= 5) ? "safe" : "warning") ?></td></tr>
            <tr><td>TDS (ppm)</td><td><?= htmlspecialchars($result['tds']) ?></td><td><?= badge(($result['tds'] <= 500) ? "Safe" : "Warning", ($result['tds'] <= 500) ? "safe" : "warning") ?></td></tr>
            <tr><td>Residual Chlorine (mg/L)</td><td><?= htmlspecialchars($result['residual_chlorine']) ?></td><td><?= badge(($result['residual_chlorine'] >= 0.2 && $result['residual_chlorine'] <= 1.0) ? "Safe" : "Failed", ($result['residual_chlorine'] >= 0.2 && $result['residual_chlorine'] <= 1.0) ? "safe" : "failed") ?></td></tr>
            <tr><td>Lead (mg/L)</td><td><?= htmlspecialchars($result['lead']) ?></td><td><?= badge(($result['lead'] <= 0.01) ? "Safe" : "Failed", ($result['lead'] <= 0.01) ? "safe" : "failed") ?></td></tr>
            <tr><td>Cadmium (mg/L)</td><td><?= htmlspecialchars($result['cadmium']) ?></td><td><?= badge(($result['cadmium'] <= 0.003) ? "Safe" : "Failed", ($result['cadmium'] <= 0.003) ? "safe" : "failed") ?></td></tr>
            <tr><td>Arsenic (mg/L)</td><td><?= htmlspecialchars($result['arsenic']) ?></td><td><?= badge(($result['arsenic'] <= 0.01) ? "Safe" : "Failed", ($result['arsenic'] <= 0.01) ? "safe" : "failed") ?></td></tr>
            <tr><td>Nitrate (mg/L)</td><td><?= htmlspecialchars($result['nitrate']) ?></td><td><?= badge(($result['nitrate'] <= 50) ? "Safe" : "Failed", ($result['nitrate'] <= 50) ? "safe" : "failed") ?></td></tr>
          </tbody>
        </table>
      <?php endif; ?>

      <?php if ($summaryText !== null): ?>
  <div style="text-align:center; margin:10px 0 20px; padding:12px; border-radius:8px;
              background:<?= htmlspecialchars($summaryBg) ?>; color:<?= htmlspecialchars($summaryColor) ?>;
              font-weight:700; box-shadow: 0 2px 8px rgba(0,0,0,0.12);">
    <?= htmlspecialchars($summaryText) ?>
  </div>
<?php endif; ?>

      <?php if ($user): ?>
        <br>
        <h3 style="margin-top:30px;">Test Conducted By:</h3>
        <table style="width:100%; border-collapse:collapse; margin-top:10px;">
          <tr>
            <th style="border:1px solid #000; padding:8px; text-align:left;">Name</th>
            <td style="border:1px solid #000; padding:8px;"><?= htmlspecialchars($user['fullname']) ?></td>
          </tr>
          <tr>
            <th style="border:1px solid #000; padding:8px; text-align:left;">Email</th>
            <td style="border:1px solid #000; padding:8px;"><?= htmlspecialchars($user['email']) ?></td>
          </tr>
          <tr>
            <th style="border:1px solid #000; padding:8px; text-align:left;">Contact No.</th>
            <td style="border:1px solid #000; padding:8px;"><?= htmlspecialchars($user['number']) ?></td>
          </tr>
        </table>
      <?php endif; ?>
    </div>
  </div>

  <!-- JS Libraries -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>

  <script>
    // Download PDF button - Fixed version
    document.getElementById("downloadBtn").addEventListener("click", function () {
        console.log("Download button clicked");
        
        // Check if libraries are loaded
        if (typeof html2canvas === 'undefined') {
            alert("Error: html2canvas library not loaded. Please check your internet connection.");
            return;
        }
        
        if (typeof jspdf === 'undefined') {
            alert("Error: jsPDF library not loaded. Please check your internet connection.");
            return;
        }

        const { jsPDF } = window.jspdf;
        const element = document.getElementById("reportContent");
        
        // Show loading state
        const originalText = this.textContent;
        this.textContent = "Generating PDF...";
        this.disabled = true;
        document.body.classList.add('loading');

        console.log("Starting html2canvas...");
        
        html2canvas(element, {
            scale: 2,
            useCORS: true,
            logging: true,
            backgroundColor: "#ffffff",
            allowTaint: true,
            foreignObjectRendering: false
        }).then(canvas => {
            console.log("Canvas created successfully");
            const imgData = canvas.toDataURL("image/png", 1.0);
            console.log("Image data generated");
            
            const pdf = new jsPDF("p", "mm", "a4");
            const pageWidth = pdf.internal.pageSize.getWidth();
            const pageHeight = pdf.internal.pageSize.getHeight();
            
            const imgWidth = pageWidth;
            const imgHeight = (canvas.height * imgWidth) / canvas.width;

            // Add image to PDF
            pdf.addImage(imgData, "PNG", 0, 0, imgWidth, imgHeight);
            
            // Generate filename
            const stationName = document.querySelector("h3") ? 
                document.querySelector("h3").innerText.trim().replace(/[^a-zA-Z0-9]/g, "_") : "Report";
            const dateElement = document.querySelector(".meta");
            let dateText = "UnknownDate";
            if (dateElement) {
                const dateMatch = dateElement.innerText.match(/Date:\s*([^|]+)/);
                dateText = dateMatch ? dateMatch[1].trim().replace(/[^a-zA-Z0-9]/g, "_") : "UnknownDate";
            }
            
            const filename = stationName + "_" + dateText + ".pdf";

            console.log("Saving PDF as: " + filename);
            pdf.save(filename);
            console.log("PDF saved successfully");
            
        }).catch(error => {
            console.error("Error generating PDF:", error);
            alert("Error generating PDF: " + error.message + "\nPlease try the Print button instead.");
        }).finally(() => {
            // Restore button state
            this.textContent = originalText;
            this.disabled = false;
            document.body.classList.remove('loading');
        });
    });

    // Print button - Fixed version
    document.getElementById("printBtn").addEventListener("click", function () {
        const printContents = document.getElementById("reportContent").innerHTML;
        const originalContents = document.body.innerHTML;

        document.body.innerHTML = `
            <!DOCTYPE html>
            <html>
            <head>
                <title>Water Quality Report</title>
                <style>
                    body { font-family: Arial, sans-serif; margin: 20px; }
                    .badge { padding: 4px 10px; border-radius: 12px; font-size: 12px; color: #fff; font-weight: bold; }
                    .badge-safe { background: #0051ff !important; }
                    .badge-neutral { background: #28a745 !important; }
                    .badge-warning { background: #ff8800 !important; }
                    .badge-failed { background: #dc3545 !important; }
                    table, th, td { border: 1px solid #000; border-collapse: collapse; padding: 8px; }
                </style>
            </head>
            <body>${printContents}</body>
            </html>
        `;
        
        window.print();
        
        // Short delay before reloading to ensure print dialog opens
        setTimeout(() => {
            location.reload();
        }, 100);
    });
  </script>
</body>
</html>