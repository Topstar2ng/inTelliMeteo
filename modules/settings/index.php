<?php
require_once '../../includes/config.php';
require_once '../../includes/db.php';

// Mock user ID for now (until you implement full login)
$user_id = 1; 

// Fetch current settings
$stmt = $pdo->prepare("SELECT * FROM user_settings WHERE user_id = ?");
$stmt->execute([$user_id]);
$settings = $stmt->fetch() ?: [
    'home_station' => 'Abuja',
    'unit_system' => 'metric',
    'timezone' => 'Africa/Lagos'
];

$updateMessage = null;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $station = $_POST['home_station'];
    $units = $_POST['unit_system'];
    $tz = $_POST['timezone'];

    $sql = "INSERT INTO user_settings (user_id, home_station, unit_system, timezone) 
            VALUES (?, ?, ?, ?) 
            ON DUPLICATE KEY UPDATE home_station = VALUES(home_station), 
                                   unit_system = VALUES(unit_system), 
                                   timezone = VALUES(timezone)";
    
    $stmt = $pdo->prepare($sql);
    if ($stmt->execute([$user_id, $station, $units, $tz])) {
        // Update Session for immediate effect
        $_SESSION['last_city'] = $station;
        $_SESSION['units'] = $units;
        $updateMessage = "Settings updated successfully!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AeroMeteo | Meteorological Intelligence</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="apple-touch-icon" sizes="180x180" href="../../assets/favicons/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="../../assets/favicons/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="../../assets/favicons/favicon-16x16.png">
    <link rel="manifest" href="../../assets/favicons/site.webmanifest">
    <style>
        body { background-color: #121212; color: #e0e0e0; }
        .metar-box { background: #1e1e1e; border-left: 5px solid #00ff00; font-family: 'Courier New', monospace; font-size: 1.2rem; }
        .card { background: #1e1e1e; border: 1px solid #333; color: white; }
        .text-neon { color: #00ff00; }
        .logo-img { width: 30px; height: 30px; margin-right: 10px; border-radius: 50%; box-shadow: 0 0 5px rgba(0,0,0,0.2); }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow">
    <div class="container">
        <a class="navbar-brand fw-bold" href="index.php">
            <img src="../../assets/images/intellimeteo_icon.png" class="logo-img"> 
            IntelliMeteo <span class="d-none d-md-inline">: A Weather & Meteo Analytics Portal</span>
        </a>
        
        <div class="d-flex align-items-center">
            <!-- Search Form (Always Visible) -->
            <form class="d-flex me-3" action="index.php" method="GET">
                <input class="form-control search-input form-control-sm" type="search" name="city" placeholder="City..." aria-label="Search" required>
                <button class="btn btn-primary btn-sm search-btn" type="submit" title="search..."><i class="bi bi-search-heart"></i></button>
            </form>

            <?php if(isset($_SESSION['user_id'])): ?>
                <!-- USER VIEW: Logged In -->
                <span class="text-light me-3 small d-none d-lg-inline">
                    Hi, <strong><?php echo explode(' ', $_SESSION['full_name'])[0]; ?></strong>
                </span>
                
                <!-- Logout Button -->
                <a href="../../logout.php" class="btn btn-outline-danger btn-sm me-2" title="Logout">
                    <i class="bi bi-box-arrow-right"></i>
                </a>

                <!-- Settings Icon -->
                <a href="../../modules/settings/index.php" class="text-white fs-5 lh-1 p-1 hover-rotate" title="Settings">
                    <i class="bi bi-gear-fill"></i>
                </a>
            <?php else: ?>
                <!-- GUEST VIEW: Not Logged In -->
                <a href="login.php" class="btn btn-outline-light btn-sm me-2"><i class="bi bi-box-arrow-in-right"></i> Login</a>
                <a href="register.php" class="btn btn-primary btn-sm"><i class="bi bi-person-add"></i> Register</a>
            <?php endif; ?>
        </div>
    </div>
</nav>

    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="text-end p-4">
                    <a href="../../index.php" class="btn btn-primary btn-sm">
                        <i class="bi bi-house-heart"></i> Dashboard
                    </a>
                </div>
                <div class="card-header bg-primary text-white p-3">
                    <h5 class="mb-0"><i class="bi bi-gear-fill me-2"></i> Portal Preferences</h5>
                </div>
                <div class="card-body p-4">
                    
                    <?php if($updateMessage): ?>
                        <div class="alert alert-success small py-2"><?php echo $updateMessage; ?></div>
                    <?php endif; ?>

                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label fw-bold small">Default Home Station</label>
                            <input type="text" name="home_station" class="form-control" value="<?php echo $settings['home_station']; ?>" placeholder="e.g. Kano, Lagos, Abuja">
                            <div class="form-text">The city that loads by default on your dashboard.</div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold small">Preferred Unit System</label>
                            <select name="unit_system" class="form-select">
                                <option value="metric" <?php echo $settings['unit_system'] == 'metric' ? 'selected' : ''; ?>>Metric (°C, m/s, hPa)</option>
                                <option value="imperial" <?php echo $settings['unit_system'] == 'imperial' ? 'selected' : ''; ?>>Imperial (°F, mph, inHg)</option>
                            </select>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold small">Timezone</label>
                            <select name="timezone" class="form-select">
                                <option value="Africa/Lagos">West Africa Time (Lagos)</option>
                                <option value="UTC">Coordinated Universal Time (UTC/Z)</option>
                            </select>
                        </div>

                        <button type="submit" class="btn btn-primary w-100">Save Preferences</button>
                    </form>
                </div>
            </div>
            
            <div class="text-center mt-4">
                <a href="../../index.php" class="text-muted text-decoration-none small">
                    <i class="bi bi-arrow-left"></i> Return to Dashboard
                </a>
            </div>
        </div>
    </div>
</div>

</body>
</html>