<?php
require_once 'api/weatherapi.php';

// 1. Prioritize Search, then Session, then Default to kano
if (isset($_GET['city'])) {
    $currentCity = $_GET['city'];
    $_SESSION['last_city'] = $currentCity; // Save to session
} elseif (isset($_SESSION['last_city'])) {
    $currentCity = $_SESSION['last_city'];
} else {
    $currentCity = "Kano";
}

$weather = getWeatherData($currentCity);

// If there's an error (like city not found), fall back to Lagos but keep the error message
if (isset($weather['error'])) {
    $errorMessage = $weather['error'];
    $weather = getWeatherData("Kano"); // Fallback data
}


?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IntelliMeteo | Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons CDN -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="apple-touch-icon" sizes="180x180" href="assets/favicons/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="assets/favicons/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="assets/favicons/favicon-16x16.png">
    <link rel="manifest" href="assets/favicons/site.webmanifest">
    <style>
        body { background-color: #f4f7f6; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .hero-card { background: linear-gradient(45deg, #1e3c72, #2a5298); color: white; border-radius: 15px; }
        .search-btn { border-radius: 0 5px 5px 0; }
        .search-input { border-radius: 5px 0 0 5px; }
        .logo-img { width: 30px; height: 30px; margin-right: 10px; border-radius: 50%; box-shadow: 0 0 5px rgba(0,0,0,0.2); }
         @media (max-width: 576px) {
            .hero-card { text-align: center; }
            .hero-card .row { flex-direction: column; }
            .hero-card .col-md-6 { text-align: center; }
        }

    /* Subtle rotation effect for the gear icon on hover */
    .hover-rotate:hover i {
        display: inline-block;
        transform: rotate(45deg);
        transition: transform 0.3s ease;
        color: #ffc107; /* Warning yellow color to match your theme */
    }
</style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow">
    <div class="container">
        <a class="navbar-brand fw-bold" href="index.php">
            <img src="assets/images/intellimeteo_icon.png" class="logo-img"> 
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
                    Hi, <strong><?php echo explode(' ', $_SESSION['full_name'])[1]; ?></strong>
                </span>
                
                <!-- Logout Button -->
                <a href="logout.php" class="btn btn-outline-danger btn-sm me-2" title="Logout">
                    <i class="bi bi-box-arrow-right"></i>
                </a>

                <!-- Settings Icon -->
                <a href="modules/settings/index.php" class="text-white fs-5 lh-1 p-1 hover-rotate" title="Settings">
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



<div class="container mt-4">
    
    <!-- Error Alert -->
    <?php if (isset($errorMessage)): ?>
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            <strong>Notice:</strong> <?php echo $errorMessage; ?> Showing Kano instead.
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    
    <!-- Main Display -->
    <div class="row mb-5">
        <div class="col-md-12">
            <div class="card hero-card shadow-lg p-5">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <h1 class="display-4 fw-bold"><?php echo $weather['name']; ?></h1>
                        <p class="lead text-capitalize"><?php echo $weather['weather'][0]['description']; ?></p>
                        <hr class="bg-white">
                        <div class="d-flex gap-4">
                            <div>
                                <small class="d-block text-light"><i class="bi bi-droplet"></i> Humidity</small>
                                <strong><?php echo $weather['main']['humidity']; ?>%</strong>
                            </div>
                            <div>
                                <small class="d-block text-light"><i class="bi bi-wind"></i> Wind Speed</small>
                                <strong><?php echo $weather['wind']['speed']; ?> m/s</strong>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 text-md-end text-center">
                        <h1 class="display-1 fw-bold"><i class="bi bi-thermometer-sun"></i> <?php echo round($weather['main']['temp']); ?>°C</h1>
                        <p>Feels like <?php echo round($weather['main']['feels_like']); ?>°C</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Features Section (Placeholders) -->
    <div class="row text-center g-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm p-4 h-100">
                <div class="mb-2"><i class="bi bi-graph-up-arrow text-primary fs-1"></i></div>
                <h4>Meteolytics</h4>
                <p class="text-muted">Analyze trends for <?php echo $weather['name']; ?>.</p>
                <a href="modules/meteolytics/" class="btn btn-outline-primary btn-sm mt-auto">
                    <i class="bi bi-graph-up-arrow me-1"></i> View Charts
                </a>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm p-4 h-100">
                <div class="mb-2"><i class="bi bi-airplane text-dark fs-1"></i></div>
                <h4>AeroMeteo</h4>
                <p class="text-muted">Aviation reports for local airports.</p>
                <!-- Change the button link to include the current city -->
                    <?php if(isset($_SESSION['user_id'])): ?>
                        <a href="modules/aerometeo/index.php?city=<?php echo urlencode($currentCity); ?>" 
                            class="btn btn-outline-dark btn-sm mt-auto">
                            <i class="bi bi-airplane me-1"></i> Open Tools
                        </a>
                    <?php else: ?>
                        <a href="login.php" class="btn btn-outline-secondary btn-sm mt-auto">
                            <i class="bi bi-airplane me-1"></i> Login to Access
                        </a>
                    <?php endif; ?>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm p-4 h-100">
                <div class="mb-2"><i class="bi bi-tree-fill text-success fs-1"></i></div>
                <h4>AgroMeteo</h4>
                <p class="text-muted">Farming insights for this region.</p>
                <?php if(isset($_SESSION['user_id'])): ?>
                    <a href="modules/agrometeo/index.php?city=<?php echo urlencode($currentCity); ?>" 
                        class="btn btn-outline-success btn-sm mt-auto">
                        <i class="bi bi-tree-fill me-1"></i> Get Advice
                    </a>
                <?php else: ?>
                    <a href="login.php" class="btn btn-outline-secondary btn-sm mt-auto">
                        <i class="bi bi-tree-fill me-1"></i> Login to Access
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="row text-center g-4 mt-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm p-4 h-100">
                <div class="mb-2"><i class="bi bi-mortarboard text-info fs-1"></i></div>
                <h4>Competency Test</h4>
                <p class="text-muted">Test your knowledge with our interactive assessment.</p>
                <?php if(isset($_SESSION['user_id'])): ?>
                    <a href="modules/assessment/index.php" class="btn btn-outline-info btn-sm mt-auto">
                        <i class="bi bi-mortarboard me-1"></i> Take Test
                    </a>
                <?php else: ?>
                    <a href="login.php" class="btn btn-outline-secondary btn-sm mt-auto">
                        <i class="bi bi-mortarboard me-1"></i> Login to Access
                    </a>
                <?php endif; ?>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm p-4 h-100">
                <div class="mb-2"><i class="bi bi-calculator text-danger fs-1"></i></div>
                <h4>Computations</h4>
                <p class="text-muted">Perform weather-related calculations.</p>
                <?php if(isset($_SESSION['user_id'])): ?>
                    <a href="modules/computations/" class="btn btn-outline-danger btn-sm mt-auto">
                        <i class="bi bi-calculator me-1"></i> Open Tools
                    </a>
                <?php else: ?>
                    <a href="login.php" class="btn btn-outline-secondary btn-sm mt-auto">
                        <i class="bi bi-calculator me-1"></i> Login to Access
                    </a>
                <?php endif; ?>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm p-4 h-100">
                <div class="mb-2"><i class="bi bi-cloud-download text-warning fs-1"></i></div>
                <h4>Forecast & Warnings</h4>
                <p class="text-muted">Stay informed about upcoming weather conditions and potential hazards.</p>
                <?php if(isset($_SESSION['user_id'])): ?>
                    <a href="modules/forecasts/" class="btn btn-outline-warning btn-sm mt-auto">
                        <i class="bi bi-cloud-download me-1"></i> View Forecasts
                    </a>
                <?php else: ?>
                    <a href="login.php" class="btn btn-outline-secondary btn-sm mt-auto">
                        <i class="bi bi-cloud-download me-1"></i> Login to Access
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>
