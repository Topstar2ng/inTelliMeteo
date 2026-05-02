<?php
require_once '../../includes/config.php';
require_once '../../api/weatherapi.php'; // Using your existing API file
require_once 'TafGenerator.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../../index.php");
    exit();
}
//convert last city to word capitalized for better matching with ICAO map
$city = ucfirst(strtolower($_SESSION['last_city'] ?? "Kano")); // Default to Kano if no city in session
$forecastData = getWeatherForecast($city); // Get the 5-day/3-hour data
$taf = TafGenerator::generate($city, $forecastData['list'] ?? []);
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
<div class="container mt-5">
    <div class="mb-4 text-end">
        
        <a href="../../index.php" class="btn btn-primary btn-sm"><i class="bi bi-house-heart"></i> Dashboard</a>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="card border-0 shadow-sm overflow-hidden">
                <div class="card-header bg-warning text-dark fw-bold p-3">
                    <i class="bi bi- megaphone me-2"></i> Terminal Aerodrome Forecast (TAF)
                </div>
                <div class="card-body bg-dark text-success p-4">
                    <h6 class="text-secondary small mb-3 text-uppercase">Generated Aviation Forecast:</h6>
                    <code class="fs-4 d-block mb-3" style="letter-spacing: 2px; color: #00ff00;">
                        <?php echo $taf; ?>
                    </code>
                    <p class="text-secondary x-small mb-0">
                        <i class="bi bi-info-circle"></i> This TAF is still a 'work in progress' and may still be inaccurate, please use it as a guide only.
                        <button class="btn btn-outline-secondary btn-sm" onclick="navigator.clipboard.writeText('<?php echo strip_tags($taf); ?>')" title="click to copy">
                                <i class="bi bi-clipboard"></i>
                            </button>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <?php
    // ... Previous PHP logic ...
    require_once 'TafDecoder.php';
    $decodedTaf = TafDecoder::decode($taf);
    ?>

    <!-- Under the <code> section in your existing index.php -->
    <div class="mt-4 border-top border-secondary pt-3">
        <h6 class="text-warning small fw-bold mb-3"><i class="bi bi-translate me-1"></i> Plain Language Translation</h6>
        <ul class="list-group list-group-flush bg-transparent">
            <?php foreach ($decodedTaf as $line): ?>
                <li class="list-group-item bg-transparent text-light border-0 py-1 ps-0" style="font-size: 0.9rem;">
                    <i class="bi bi-arrow-right-short text-warning"></i> <?php echo $line; ?>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>

    <!-- Warnings Section -->
    <div class="row mt-4">
        <?php 
        $temp = $forecastData['list'][0]['main']['temp'];
        if ($temp > 38): 
        ?>
        <div class="col-12">
            <div class="alert alert-warning border-0 shadow-sm d-flex align-items-center">
                <i class="bi bi-exclamation-triangle-fill fs-2 me-3"></i>
                <div>
                    <strong>Heat Advisory:</strong> Extreme temperatures forecasted. Ensure livestock hydration and avoid midday planting.
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php include '../../footer.php'; ?>