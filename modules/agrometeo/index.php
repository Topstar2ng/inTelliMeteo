<?php 
require_once '../../includes/config.php';
require_once '../../api/weatherapi.php';
require_once 'AgroEngine.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../../index.php");
    exit();
}

$city = $_GET['city'] ?? ($_SESSION['last_city'] ?? "Kano");
$weather = getWeatherData($city);

// Logic Extraction
$temp = $weather['main']['temp'] ?? 0;
$hum = $weather['main']['humidity'] ?? 0;
$desc = $weather['weather'][0]['description'] ?? '';
$suitability = AgroEngine::getPlantingSuitability($temp, $hum, $desc);
$crops = AgroEngine::getCropFocus($weather['name'] ?? $city);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AgroMeteo | Farming Intelligence</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="apple-touch-icon" sizes="180x180" href="../../assets/favicons/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="../../assets/favicons/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="../../assets/favicons/favicon-16x16.png">
    <link rel="manifest" href="../../assets/favicons/site.webmanifest">
    <style>
        body { background-color: #f0f4f0; } /* Light green tint */
        .agro-card { border-radius: 15px; border: none; }
        .suitability-badge { font-size: 1.2rem; padding: 10px 20px; border-radius: 50px; }
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
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold text-success"><i class="bi bi-tree"></i> AgroMeteo Insights</h2>
        <a href="../../index.php" class="btn btn-primary btn-sm"><i class="bi bi-house-heart"></i> Dashboard</a>
    </div>

    <div class="row g-4">
        <!-- Main Suitability Card -->
        <div class="col-md-8">
            <div class="card agro-card shadow-sm p-4 mb-4">
                <div class="row align-items-center">
                    <div class="col-md-7">
                        <h5 class="text-muted">Planting Suitability for <?php echo $weather['name'] ?? $city; ?></h5>
                        <h2 class="display-5 fw-bold text-<?php echo $suitability['color']; ?>">
                            <?php echo $suitability['status']; ?>
                        </h2>
                        <p class="lead mt-3"><?php echo $suitability['advice']; ?></p>
                    </div>
                    <div class="col-md-5 text-center">
                         <i class="bi bi- sun-fill text-warning display-1"></i>
                    </div>
                </div>
            </div>

            <!-- Crop Recommendations -->
            <div class="card agro-card shadow-sm p-4">
                <h5 class="fw-bold mb-3"><i class="bi bi-basket-fill text-success me-2"></i>Recommended Crops for this Region</h5>
                <div class="d-flex flex-wrap gap-2">
                    <?php foreach ($crops as $crop): ?>
                        <span class="badge bg-light text-dark border p-2 px-3"><?php echo $crop; ?></span>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Sidebar Stats -->
        <div class="col-md-4">
            <div class="card agro-card shadow-sm p-4 mb-4 bg-white text-center">
                <i class="bi bi-droplet-half text-primary fs-1"></i>
                <h6 class="mt-2 text-muted">Soil Moisture Index</h6>
                <h3 class="fw-bold"><?php echo $hum; ?>% <small class="fs-6">(Rel.)</small></h3>
                <div class="progress mt-2" style="height: 10px;">
                    <div class="progress-bar bg-primary" style="width: <?php echo $hum; ?>%"></div>
                </div>
            </div>

            <div class="card agro-card shadow-sm p-4 bg-white text-center">
                <i class="bi bi-thermometer-sun text-danger fs-1"></i>
                <h6 class="mt-2 text-muted">Thermal Environment</h6>
                <h3 class="fw-bold"><?php echo round($temp); ?>°C</h3>
                <p class="small text-muted">Optimizing for photosynthesis...</p>
            </div>
        </div>
    </div>
</div>

<?php include '../../footer.php'; ?>