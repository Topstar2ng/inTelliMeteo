<?php 
require_once 'api/weatherapi.php';

// 1. Session & Location Management
if (isset($_GET['city'])) {
    $currentCity = $_GET['city'];
    $_SESSION['last_city'] = $currentCity;
} elseif (isset($_SESSION['last_city'])) {
    $currentCity = $_SESSION['last_city'];
} else {
    $currentCity = "Kano";
}

$weather = getWeatherData($currentCity);

// Fetch forecast payload for operational timeline insights (3H and 24H chunks)
// Adjust function name to match your exact backend client declaration
$forecastData = getWeatherDataForecast($currentCity) ?? []; 

if (isset($weather['error'])) {
    $errorMessage = $weather['error'];
    $weather = getWeatherData("Kano");
    $forecastData = getWeatherDataForecast("Kano") ?? [];
}

// 2. Enhanced Weather Condition Asset Selection
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
                $bgPath = "assets/images/weather/clouds.jpg"; // Better contextual fallback
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

$bgStyle = "linear-gradient(rgba(0,0,0,0.55), rgba(0,0,0,0.65)), url('$bgPath')";

// 3. Extracting Outlook Points for the Duty Forecaster
$outlook3H = null;
$outlook24H = null;

if (!empty($forecastData['list'])) {
    // Index 0 or 1 usually represents the next 3-hour timeline window
    $outlook3H = $forecastData['list'][0] ?? null;
    // Index 8 represents roughly 24 hours into the future (8 intervals * 3 hours = 24H)
    $outlook24H = $forecastData['list'][8] ?? null;
}
?>

<?php include 'includes/header.php'; ?>
<style>
    .text-muted {
        font-size: 0.9rem;
        color: rgba(63, 177, 28, 0.75) !important;
    }
</style>
<div class="container mt-4">
    
    <?php if (isset($errorMessage)): ?>
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            <strong>Notice:</strong> <?php echo $errorMessage; ?> Showing Kano instead.
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card hero-card shadow-lg p-5" style="background: <?php echo $bgStyle; ?>; background-size: cover; background-position: center; transition: background 0.5s ease; border: 1px solid rgba(255,255,255,0.1);">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <span class="badge bg-info mb-2">Station Observation Block</span>
                        <h1 class="display-4 fw-bold text-white"><?php echo $weather['name']; ?>, <?php echo $weather['sys']['country']; ?></h1>
                        <p class="lead text-capitalize text-light d-flex align-items-center gap-2">
                            <?php 
                            $weatherDescriptionClean = $weather['weather'][0]['description'];
                            if(strtolower($weatherDescriptionClean) == 'overcast clouds') { $weatherDescriptionClean = "Medium Clouds"; }
                            echo $weatherDescriptionClean;
                            
                            $iconCode = $weather['weather'][0]['icon'];
                            $iconUrl = "https://openweathermap.org/img/wn/" . $iconCode . "@2x.png";
                            ?>
                            <img src="<?php echo $iconUrl; ?>" alt="Weather Icon" style="width: 45px; height: 45px;">
                        </p>
                        <hr class="bg-white opacity-25">
                        <div class="d-flex gap-4 text-white">
                            <div>
                                <small class="d-block text-muted"><i class="bi bi-droplet"></i> Humidity</small>
                                <strong><?php echo $weather['main']['humidity']; ?>%</strong>
                            </div>
                            <div>
                                <small class="d-block text-muted"><i class="bi bi-wind"></i> Wind</small>
                                <strong>
                                    <?php 
                                    $mainWindMs = $weather['wind']['speed'];;
                                    $mainWindKt = round($mainWindMs * 1.94384);
                                    ?>
                                    <?php echo $mainWindKt; ?> KT (<?php echo round($mainWindMs); ?> m/s)
                                </strong>
                            </div>
                            <div>
                                <small class="d-block text-muted"><i class="bi bi-eye"></i> Visibility</small>
                                <strong><?php echo isset($weather['visibility']) ? ($weather['visibility'] / 1000) . " km" : "N/A"; ?></strong>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 text-md-end text-center text-white">
                        <h1 class="display-1 fw-bold"><i class="bi bi-thermometer-sun text-warning"></i> <?php echo round($weather['main']['temp']); ?>°C</h1>
                        <p class="text-light opacity-75">Feels like <?php echo round($weather['main']['feels_like']); ?>°C</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-5 g-4">
        <div class="col-md-6">
            <div class="card h-100 bg-dark text-white border-secondary shadow-sm">
                <div class="card-header border-secondary d-flex justify-content-between align-items-center py-3">
                    <h5 class="mb-0 text-info"><i class="bi bi-clock-history me-2"></i> 3-Hour Landing Trend Outlook</h5>
                    <small class="badge bg-secondary">METAR Trend Check</small>
                </div>
                <div class="card-body">
                    <?php if ($outlook3H): ?>
                        <div class="row align-items-center">
                            <div class="col-6">
                                <span class="text-muted d-block small">Expected Temp</span>
                                <h3 class="mb-0"><?php echo round($outlook3H['main']['temp']); ?>°C</h3>
                            </div>
                            <div class="col-6 text-end">
                                <span class="text-muted d-block small">Condition</span>
                                <strong class="text-capitalize text-warning"><?php echo $outlook3H['weather'][0]['description']; ?></strong>
                            </div>
                        </div>
                        <hr class="border-secondary my-3">
                        <div class="row text-center small">
                            <div class="col-4 border-end border-secondary">
                                <span class="text-muted d-block">Wind Speed</span>
                                <?php 
                                    $windMs = $outlook3H['wind']['speed'];
                                    $windKt = round($windMs * 1.94384);
                                ?>
                                <strong><?php echo $windKt; ?> KT (<?php echo round($windMs); ?> m/s)</strong>
                            </div>
                            <div class="col-4 border-end border-secondary">
                                <!--<span class="text-muted d-block">Humidity</span>-->
                                <!--<strong><?php //echo $outlook3H['main']['humidity']; ?>%</strong>-->
                                <span class="text-muted d-block" title="significant weather outlook">Outlook</span>
                                <strong>
                                    <?php
                                    /* For the 3H outlook, check the first interval (3 hours) for any significant weather conditions that could impact operations. */

                                    $foundSignificant3H = false;
                                    $watchOut3H = ['Thunderstorm', 'Rain', 'Drizzle', 'Snow', 'Fog', 'Mist', 'Haze', 'Dust', 'Sand', 'Squall'];

                                    // Check only the first interval (next 3 hours)
                                    for ($i = 0; $i < 1; $i++) {
                                        if (isset($forecastData['list'][$i])) {
                                            $condition = $forecastData['list'][$i]['weather'][0]['main'];
                                            if (in_array($condition, $watchOut3H)) {
                                                $time = date("H:i", $forecastData['list'][$i]['dt']);
                                                $desc = ucwords($forecastData['list'][$i]['weather'][0]['description']);
                                                echo "<span title='Expected around " . $time . " GMT'>$desc</span>";
                                                $foundSignificant3H = true;
                                                break;
                                            }
                                        }
                                    }
                                    if (!$foundSignificant3H) echo "Nil";
                                    ?>
                                </strong>
                            </div>
                            <div class="col-4">
                                <span class="text-muted d-block">Prob. Rain</span>
                                <strong><?php echo isset($outlook3H['pop']) ? ($outlook3H['pop'] * 100) . "%" : "0%"; ?></strong>
                            </div>
                        </div>
                    <?php else: ?>
                        <p class="text-muted text-center my-4">Timeline data temporarily missing or API limit reached.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card h-100 bg-dark text-white border-secondary shadow-sm">
                <div class="card-header border-secondary d-flex justify-content-between align-items-center py-3">
                    <h5 class="mb-0 text-success"><i class="bi bi-calendar-range me-2"></i> 24-Hour Synoptic Outlook</h5>
                    <small class="badge bg-secondary">TAF Window Check</small>
                </div>
                <div class="card-body">
                    <?php if ($outlook24H): ?>
                        <div class="row align-items-center">
                            <div class="col-6">
                                <span class="text-muted d-block small">Target Horizon Temp</span>
                                <h3 class="mb-0"><?php echo round($outlook24H['main']['temp']); ?>°C</h3>
                            </div>
                            <div class="col-6 text-end">
                                <span class="text-muted d-block small">Expected Category</span>
                                <strong class="text-capitalize text-info"><?php echo $outlook24H['weather'][0]['main']; ?></strong>
                            </div>
                        </div>
                        <hr class="border-secondary my-3">
                        <div class="row text-center small">
                            <div class="col-4 border-end border-secondary">
                                <span class="text-muted d-block">Outlook</span>
                                <strong>
                                    <?php
                                    /* For the 24H outlook, check the first 8 intervals (24 hours) for any significant weather conditions that could impact operations. Prioritize showing the most impactful condition if multiple are present.*/

                                    $foundSignificant = false;
                                    $watchOut = ['Thunderstorm', 'Rain', 'Drizzle', 'Snow', 'Fog', 'Mist', 'Haze', 'Dust', 'Sand', 'Squall'];

                                    // Check the first 8 intervals (approx 24 hours)
                                    for ($i = 0; $i < 8; $i++) {
                                        if (isset($forecastData['list'][$i])) {
                                            $condition = $forecastData['list'][$i]['weather'][0]['main'];
                                            if (in_array($condition, $watchOut)) {
                                                $time = date("H:i", $forecastData['list'][$i]['dt']);
                                                $desc = ucwords($forecastData['list'][$i]['weather'][0]['description']);
                                                echo "<span title='Expected around " . $time . " GMT'>$desc</span>";
                                                $foundSignificant = true;
                                                break;
                                            }
                                        }
                                    }
                                    if (!$foundSignificant) echo "Nil";
                                    ?>
                                </strong>
                            </div>
                            <div class="col-4 border-end border-secondary">
                                <span class="text-muted d-block">Wind Gusts</span>
                                <?php 
                                    $gustMs = $outlook24H['wind']['gust'] ?? $outlook24H['wind']['speed'];
                                    $gustKt = round($gustMs * 1.94384);
                                ?>
                                <strong><?php echo $gustKt; ?> KT (<?php echo round($gustMs); ?> m/s)</strong>
                            </div>
                            <div class="col-4">
                                <span class="text-muted d-block">Cloud Cover</span>
                                <strong><?php echo $outlook24H['clouds']['all']; ?>%</strong>
                            </div>
                        </div>
                    <?php else: ?>
                        <p class="text-muted text-center my-4">Timeline data temporarily missing or API limit reached.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

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
                <?php if(isset($_SESSION['user_id'])): ?>
                    <a href="modules/aerometeo/index.php?city=<?php echo urlencode($currentCity); ?>" class="btn btn-outline-warning btn-sm mt-auto">
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
                    <a href="modules/agrometeo/index.php?city=<?php echo urlencode($currentCity); ?>" class="btn btn-outline-success btn-sm mt-auto">
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
                <p class="text-muted">Stay informed about upcoming weather conditions and hazards.</p>
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