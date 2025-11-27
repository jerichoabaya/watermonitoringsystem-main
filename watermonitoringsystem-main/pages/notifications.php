<?php
include("../includes/db.php");
include("../includes/fetch_user.php");

// Get user role and LGU location
$userRole = $user['role'] ?? '';
$userLguLocation = $user['lgu_location'] ?? '';
$userId = $_SESSION['user_id'] ?? 0;

// Build the base query with role-based filtering
$baseQuery = "SELECT w.*, r.name AS station_name, r.station_id, r.lgu_location
        FROM water_data w
        INNER JOIN refilling_stations r ON w.station_id = r.station_id
        WHERE 1=1";

// Add role-based filtering
if ($userRole === 'lgu_menro' && !empty($userLguLocation)) {
    // LGU MENRO: Show all stations in their LGU location
    $sql = $baseQuery . " AND r.lgu_location = '" . $conn->real_escape_string($userLguLocation) . "'";
} elseif ($userRole === 'refilling_station_owner' || $userRole === 'personal_user') {
    // Refilling station owners and personal users: Show only their linked stations
    $sql = $baseQuery . " AND r.station_id IN (
        SELECT station_id FROM user_stations WHERE user_id = " . (int)$userId . "
    )";
} else {
    // Super admin or other roles: Show all stations (original behavior)
    $sql = $baseQuery;
}

$sql .= " ORDER BY w.timestamp DESC LIMIT 50";

$result = $conn->query($sql);

function checkParameter($name, $value) {
    switch ($name) {
        case 'color': return ($value <= 15) ? null : "exceeds safe limit of 15 TCU";
        case 'ph_level': return ($value >= 6.5 && $value <= 8.5) ? null : "outside safe range 6.5–8.5";
        case 'turbidity': return ($value <= 5) ? null : "exceeds safe limit of 5 NTU";
        case 'tds': return ($value <= 500) ? null : "exceeds safe limit of 500 ppm";
        case 'residual_chlorine': return ($value >= 0.2 && $value <= 1.0) ? null : "outside safe range 0.2–1.0 mg/L";
        case 'lead': return ($value <= 0.01) ? null : "exceeds safe limit of 0.01 mg/L";
        case 'cadmium': return ($value <= 0.003) ? null : "exceeds safe limit of 0.003 mg/L";
        case 'arsenic': return ($value <= 0.01) ? null : "exceeds safe limit of 0.01 mg/L";
        case 'nitrate': return ($value <= 50) ? null : "exceeds safe limit of 50 mg/L";
        default: return null;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Notifications - Water Quality Testing & Monitoring System</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"/>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css"/>
  <style>

    .navbar .nav-link.active {
  background: transparent !important; /* remove black background */
  color: #00c6ff !important;          /* highlight in blue instead */
}


    body { background: #0e1117; color: white; font-family: 'Segoe UI', sans-serif; }
    .navbar { background-color: #1f2733; box-shadow: 0 2px 10px rgba(0, 198, 255, 0.2); }
    .nav-link, .navbar-brand { color: white; }
    .nav-link:hover { color: #00c6ff; }
    .container { margin-top: 30px; }
    .alert-card {
      background: #1f2733;
      border-left: 5px solid #dc3545;
      padding: 20px;
      margin-bottom: 20px;
      border-radius: 10px;
      box-shadow: 0 0 10px rgba(255, 0, 0, 0.2);
      cursor: pointer;
      transition: 0.2s ease;
    }
    .alert-card:hover { background:#222b3a; }
    .alert-card h5 { color: #00c6ff; }
    .parameter-name { color: #ffc107; }
    .fa-exclamation-triangle { color: #dc3545; margin-right: 10px; }
    .account-icon { width: 24px; height: 24px; border:1px solid white; box-shadow:0 0 8px white; object-fit:cover; }
    .no-alerts { 
        text-align: center; 
        padding: 40px 0; 
        color: #bbb; 
        font-style: italic; 
    }
    .role-info {
        text-align: center;
        color: #00c6ff;
        margin-bottom: 20px;
        font-size: 14px;
    }
  </style>
</head>
<body>
<nav class="navbar navbar-expand-lg">
  <div class="container-fluid">
    <a class="navbar-brand" href="#"><i class="fas fa-tint"></i> Water Quality Testing & Monitoring System</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav ms-auto">
        <li class="nav-item"><a class="nav-link" href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
        <li class="nav-item"><a class="nav-link" href="stations.php"><i class="fas fa-building"></i> Devices/Stations</a></li>
          <li class="nav-item"><a class="nav-link" href="reports.php"><i class="fas fa-chart-line"></i> Reports</a></li>
          <li class="nav-item"><a class="nav-link active" href="notifications.php"><i class="fas fa-bell"></i> Notifications</a></li>
         
        <li class="nav-item">
          <a class="nav-link d-flex align-items-center" href="account.php" style="gap: 8px;">
            <img src="<?php echo htmlspecialchars($user['profile_pic']); ?>" alt="Account" class="rounded-circle account-icon">
            <span class="account-text">Account</span>
          </a>
        </li>
        
      </ul>
    </div>
  </div>
</nav>

<div class="container">
  <h3 class="mb-4 text-center">⚠️ Parameter Alerts</h3>

  <?php
  $hasAlerts = false;
  
  if ($result && $result->num_rows > 0):
      while ($row = $result->fetch_assoc()):
          $alerts = [];
          foreach (['color','ph_level','turbidity','tds','residual_chlorine','lead','cadmium','arsenic','nitrate'] as $param) {
              if ($row[$param] !== null && $row[$param] !== '') {
                  $check = checkParameter($param, $row[$param]);
                  if ($check) {
                      $alerts[] = [
                          'name' => $param,
                          'value' => $row[$param],
                          'message' => $check
                      ];
                  }
              }
          }

          if (!empty($alerts)) {
              $hasAlerts = true;
              foreach ($alerts as $alert):
                  $date = date("Y-m-d", strtotime($row['timestamp']));
                  $time = date("h:i A", strtotime($row['timestamp']));
  ?>
    <div class="alert-card" onclick="window.location.href='download.php?station_id=<?= $row['station_id'] ?>&test_id=<?= $row['waterdata_id'] ?>'">
      <h5><i class="fas fa-exclamation-triangle"></i> <?= htmlspecialchars($row['station_name']) ?></h5>
      <p>
        <span class="parameter-name"><?= ucfirst(str_replace('_',' ',$alert['name'])) ?></span> level is
        <strong><?= htmlspecialchars($alert['value']) ?></strong> — <?= $alert['message'] ?>.
      </p>
      <p>Date: <?= $date ?> | Time: <?= $time ?></p>
    </div>
  <?php
              endforeach;
          }
      endwhile;
      
      if (!$hasAlerts):
          echo '<div class="no-alerts">No parameter alerts found for your stations.</div>';
      endif;
  else:
      echo '<div class="no-alerts">No test data found for your stations.</div>';
  endif;
  ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>