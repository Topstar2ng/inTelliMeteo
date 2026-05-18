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
<?php include '../../includes/header.php'; ?>

    <style>
        body { background-color: #f0f4f0; } /* Light green tint */
        .agro-card { border-radius: 15px; border: none; }
        .suitability-badge { font-size: 1.2rem; padding: 10px 20px; border-radius: 50px; }
        .logo-img { width: 30px; height: 30px; margin-right: 10px; border-radius: 50%; box-shadow: 0 0 5px rgba(0,0,0,0.2); }
         
    </style>

<div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold text-success"><i class="bi bi-tree"></i> AgroMeteo Insights</h2>
        <div class="btn-group" role="group">
            <a href="../../index.php" class="btn btn-primary btn-sm" title="dashboard"><i class="bi bi-house-heart"></i></a>
            <a href="../meteolytics/index.php" class="btn btn-danger btn-sm" title="Meteolytics"><i class="bi bi-graph-up-arrow"></i></a>
            <a href="../computations/index.php" class="btn btn-danger btn-sm" title="Computations"><i class="bi bi-calculator"></i></a>
            <a href="../forecasts/index.php" class="btn btn-warning btn-sm" title="Forecast"><i class="bi bi-cloud-download"></i></a>
            <a href="../aerometeo/index.php" class="btn btn-info btn-sm" title="AeroMet"><i class="bi bi-airplane-fill"></i></a>
        </div>
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