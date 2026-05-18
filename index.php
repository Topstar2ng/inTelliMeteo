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

// Define local background based on weather condition
$weatherMain = strtolower($weather['weather'][0]['main']); 
$weatherDescription = strtolower($weather['weather'][0]['description']); 
$bgPath = "";

switch ($weatherMain) {
    case 'thunderstorm':
        $bgPath = "assets/images/weather/thunderstorm.jpg";
        break;
    case 'clouds':
        switch ($weatherDescription) {
            case 'few clouds':
                $bgPath = "assets/images/weather/cloud-few.jpg";
                break;
            case 'scattered clouds':
                $bgPath = "assets/images/weather/cloud-sct.jpg";
                break;
            case 'broken clouds':
                $bgPath = "assets/images/weather/cloud-bkn.jpg";
                break;
            default:
                $bgPath = "assets/images/weather/clear.jpg";
                break;
        }
        break;
    case 'fog':
    case 'mist':
        $bgPath = "assets/images/weather/fog.jpg";
        break;
    case 'rain':
    case 'drizzle':
        $bgPath = "assets/images/weather/rain.jpg";
        break;
    case 'dust':
    case 'sand':
        $bgPath = "assets/images/weather/dust.jpg";
        break;
    case 'haze':
        $bgPath = "assets/images/weather/haze.jpg";
        break;
    case 'clear':
    default:
        $bgPath = "assets/images/weather/clear.jpg";
        break;
}

// Wrap it in the gradient for readability
$bgStyle = "linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.5)), url('$bgPath')";

?>


<?php include 'includes/header.php'; ?>


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
            <div class="card hero-card shadow-lg p-5" 
     style="background: <?php echo $bgStyle; ?>; background-size: cover; background-position: center; transition: background 0.5s ease;">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <h1 class="display-4 fw-bold"><?php echo $weather['name']; ?>, <?php echo $weather['sys']['country']; ?></h1>
                        <p class="lead text-capitalize">
                            <?php 
                            $weatherDescription = $weather['weather'][0]['description'];
                            if(strtolower($weatherDescription) == 'overcast clouds'){
                                $weatherDescription = "Medium Clouds";
                            }
                            echo $weatherDescription;
                            ?>
                        <?php
                            //include weather icon
                            $iconCode = $weather['weather'][0]['icon'];
                            $iconUrl = "https://openweathermap.org/img/wn/" . $iconCode . "@2x.png";
                        ?>
                        <img src="<?php echo $iconUrl; ?>" alt="Weather Icon" class="img-fluid mb-2">
                        </p>
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
                            <div>
                                <small class="d-block text-light"><i class="bi bi-map"></i> Location</small>
                                <strong><?php echo $weather['coord']['lat']; ?>, <?php echo $weather['coord']['lon']; ?></strong>
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
                <div class="mb-2"><i class="bi bi-airplane text-warning fs-1"></i></div>
                <h4>AeroMeteo</h4>
                <p class="text-muted">Aviation reports for local airports.</p>
                <!-- Change the button link to include the current city -->
                    <?php if(isset($_SESSION['user_id'])): ?>
                        <a href="modules/aerometeo/index.php?city=<?php echo urlencode($currentCity); ?>" 
                            class="btn btn-outline-warning btn-sm mt-auto">
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

<?php include 'includes/footer.php'; ?>
