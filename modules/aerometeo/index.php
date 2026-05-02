<?php 
require_once '../../includes/config.php'; // Ensure session_start() is called via config
require_once '../../api/weatherapi.php';
require_once 'MetarGenerator.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../../index.php");
    exit();
}

// Check GET first, then fall back to Session
$city = $_GET['city'] ?? ($_SESSION['last_city'] ?? "Kano");

$weather = getWeatherData($city);
$metar = MetarGenerator::generate($weather);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AeroMeteo | Aviation Intelligence</title>
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

<div class="container mt-5">
    <div class="mb-4 text-end">
        
        <a href="../../index.php" class="btn btn-primary btn-sm"><i class="bi bi-house-heart"></i> Dashboard</a>
    </div>
    
    <div class="row">
        <div class="col-md-8">
            <h2 class="fw-bold mb-3">✈️ AeroMeteo <span class="text-info fs-5">| Aviation Reports</span></h2>
            
            <div class="card p-4">
                <h6>Current Raw METAR (Automated)</h6>
                <div class="metar-box p-3 mt-2">
                    <span class="text-neon"><?php echo $metar; ?></span>
                </div>
            </div>

            <div class="row g-3 mt-4">
                <div class="col-md-4">
                    <div class="card p-3">
                        <small class="text-light">Station</small>
                        <h3><?php echo $weather['name'] ?? 'Unknown'; ?></h3>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card p-3">
                        <small class="text-light">Wind</small>
                        <h3><?php echo $weather['wind']['deg'] ?? 0; ?>° / <?php echo str_pad(round(($weather['wind']['speed'] ?? 0) * 1.94384), 2, '0', STR_PAD_LEFT); ?> KT</h3>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card p-3">
                        <small class="text-light">Visibility</small>
                        <h3><?php echo ($weather['visibility'] ?? 0) / 1000; ?> KM</h3>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="card p-3">
                        <small class="text-light">Temperature</small>
                        <h3><?php echo $weather['main']['temp'] ?? 'N/A'; ?>°C</h3>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card p-3">
                        <small class="text-light">QNH</small>
                        <h3><?php echo $weather['main']['pressure'] ?? '1013'; ?> hPa</h3>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card p-3">
                        <small class="text-light">Altimeter Settings</small>
                        <h3><?php echo MetarGenerator::generatePressureAlt($weather); ?></h3>
                    </div>
                </div>

                <div class="col-md-12">
                    <div class="card p-3">
                        <small class="text-light">Weather</small>
                        <h3><?php echo $weather['weather'][0]['description'] ?? 'N/A'; ?></h3>
                    </div>
                </div>
            </div>           
            
        </div>

        <div class="col-md-4">
            <div class="card p-4 h-100 shadow">
                <h5>Pro Assessment</h5>
                <hr>
                <?php if(($weather['visibility'] ?? 10000) < 5000): ?>
                    <div class="alert alert-danger"><strong>VFR Caution:</strong> Low visibility detected. Instrumented flight rules may apply.</div>
                <?php else: ?>
                    <div class="alert alert-success"><strong>VFR Optimal:</strong> Clear conditions for visual flight.</div>
                <?php endif; ?>
                
                <p class="small text-light mt-3">
                    Note: This report is generated based on standard meteo-analytics and should be cross-referenced with official NiMet briefings.
                </p>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card mt-4 border-0 bg-dark text-light shadow">
                <div class="card-header bg-secondary text-white fw-bold">
                    📊 Technical Decoding (ICAO Standards)
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-md-3 border-end border-secondary">
                            <small class="text-light d-block">Identification</small>
                            <span class="text-info fw-bold"><?php echo MetarGenerator::getIcao($weather['name']); ?></span>
                            <p class="small text-light mb-0"><?php echo $weather['name']; ?> Airport</p>
                        </div>
                        <div class="col-md-3 border-end border-secondary">
                            <small class="text-light d-block">Wind Conditions</small>
                            <span class="text-info fw-bold"><?php echo $weather['wind']['deg']; ?>° at <?php echo round($weather['wind']['speed'] * 1.94384); ?> KT</span>
                        </div>
                        <div class="col-md-3 border-end border-secondary">
                            <small class="text-light d-block">Dew Point</small>
                            <span class="text-info fw-bold"><?php echo round($weather['main']['temp'] - ((100 - $weather['main']['humidity']) / 5)); ?>°C</span>
                        </div>
                        <div class="col-md-3">
                            <small class="text-light d-block">Flight Category</small>
                            <?php 
                                $v = $weather['visibility'] ?? 10000;
                                if($v > 8000) echo '<span class="badge bg-success">VFR (Green)</span>';
                                elseif($v > 5000) echo '<span class="badge bg-blue" style="background: blue;">MVFR (Blue)</span>';
                                else echo '<span class="badge bg-danger">IFR (Red)</span>';
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../../footer.php'; ?>