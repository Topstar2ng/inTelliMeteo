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

// Safe Extraction Logic 
$temp = $weather['main']['temp'] ?? 0;
$hum = $weather['main']['humidity'] ?? 0;
$desc = $weather['weather'][0]['description'] ?? '';
$cityName = $weather['name'] ?? $city;

$suitability = AgroEngine::getPlantingSuitability($temp, $hum, $desc);
$crops = AgroEngine::getCropFocus($cityName);
?>
<?php include '../../includes/header.php'; ?>

<style>
    body { 
        background-color: #0b0d13 !important; 
        color: #e2e8f0 !important; 
    }
    .agro-card { 
        background: #121622;
        border: 1px solid #1e2538;
        border-radius: 14px;
    }
    .accent-border-green {
        border-left: 5px solid #10b981 !important;
    }
    .badge-crop-category {
        background: rgba(16, 185, 129, 0.1);
        border: 1px solid rgba(16, 185, 129, 0.2);
        color: #34d399;
        font-size: 0.85rem;
    }
    .metric-pill {
        background: #191f32;
        border: 1px solid #242e47;
        border-radius: 10px;
    }
    .btn-nav-accent {
        transition: all 0.2s ease;
    }
    .btn-nav-accent:hover {
        transform: translateY(-1px);
        opacity: 0.9;
    }
</style>

<div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center mb-4 pb-3 border-bottom border-secondary" style="border-color: rgba(255,255,255,0.08) !important;">
        <div>
            <h2 class="fw-bold text-success mb-0 d-flex align-items-center">
                <i class="bi bi-tree-fill me-2 text-emerald"></i> Agro Insights
            </h2>
            <small class="text-dark">Bioclimatic analysis framework for <span class="fw-bold"><?php echo htmlspecialchars($cityName); ?></span></small>
        </div>
        
        <div class="btn-group shadow-sm" role="group">
            <a href="../../index.php" class="btn btn-outline-secondary btn-sm btn-nav-accent" title="Dashboard"><i class="bi bi-house-heart"></i></a>
            <a href="../meteolytics/index.php" class="btn btn-outline-secondary btn-sm btn-nav-accent" title="Meteolytics"><i class="bi bi-graph-up-arrow text-danger"></i></a>
            <a href="../computations/index.php" class="btn btn-outline-secondary btn-sm btn-nav-accent" title="Computations"><i class="bi bi-calculator text-danger"></i></a>
            <a href="../forecasts/index.php" class="btn btn-outline-secondary btn-sm btn-nav-accent" title="Forecast"><i class="bi bi-cloud-download text-warning"></i></a>
            <a href="../aerometeo/index.php" class="btn btn-outline-secondary btn-sm btn-nav-accent" title="AeroMet"><i class="bi bi-airplane-fill text-info"></i></a>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-md-8">
            <div class="card agro-card shadow-lg p-4 mb-4 accent-border-green">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <span class="text-uppercase tracking-wider text-warning small font-monospace">Edafic Classification Engine</span>
                        <h5 class="text-white mt-1">Planting Suitability Profile</h5>
                        
                        <div class="d-flex align-items-center gap-2 my-3">
                            <i class="bi <?php echo $suitability['icon']; ?> text-<?php echo $suitability['color']; ?> fs-2"></i>
                            <h2 class="fw-bold mb-0 text-<?php echo $suitability['color']; ?>">
                                <?php echo $suitability['status']; ?>
                            </h2>
                        </div>
                        
                        <p class="text-white bg-dark p-3 rounded border border-secondary style-message" style="background-color: #090b10 !important; border-color: rgba(255,255,255,0.05) !important; font-size: 0.95rem; line-height: 1.6;">
                            <?php echo $suitability['advice']; ?>
                        </p>
                    </div>
                    <div class="col-md-4 text-center d-none d-md-block">
                         <i class="bi bi-sun-fill text-warning opacity-75 animate-pulse" style="font-size: 5.5rem; filter: drop-shadow(0 0 15px rgba(255,193,7,0.2));"></i>
                    </div>
                </div>
            </div>

            <div class="card agro-card shadow-lg p-4">
                <h5 class="fw-bold text-white mb-3 d-flex align-items-center">
                    <i class="bi bi-basket2-fill text-success me-2"></i>Cultivation Strategy Focus
                </h5>
                <p class="text-warning small mb-3">Optimal crops recommended for execution based on local soil zones and baseline parameters:</p>
                
                <div class="row g-3">
                    <?php foreach ($crops as $category => $cropList): ?>
                        <div class="col-md-4">
                            <div class="p-3 metric-pill h-100">
                                <span class="badge badge-crop-category mb-2 text-uppercase font-monospace"><?php echo $category; ?></span>
                                <div class="text-white fw-semibold small"><?php echo $cropList; ?></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card agro-card shadow-lg p-4 mb-4 text-center">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="text-warning small text-uppercase font-monospace">Saturation Index</span>
                    <i class="bi bi-droplet-half text-primary fs-4"></i>
                </div>
                <h6 class="text-warning small mb-1">Atmospheric Relative Humidity</h6>
                <h2 class="fw-bold text-white"><?php echo $hum; ?>%</h2>
                
                <div class="progress mt-3 bg-dark" style="height: 8px; border-radius: 20px;">
                    <div class="progress-bar bg-primary progress-bar-striped progress-bar-animated" 
                         role="progressbar" 
                         style="width: <?php echo $hum; ?>%; border-radius: 20px;"></div>
                </div>
                <small class="text-warning d-block mt-2" style="font-size: 0.75rem;">Corresponds with immediate canopy evapotranspiration load.</small>
            </div>

            <div class="card agro-card shadow-lg p-4 text-center">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="text-warning small text-uppercase font-monospace">Kinetic Index</span>
                    <i class="bi bi-thermometer-sun text-danger fs-4"></i>
                </div>
                <h6 class="text-warning small mb-1">Ambient Air Boundary</h6>
                <h2 class="fw-bold text-white"><?php echo round($temp); ?>°C</h2>
                
                <div class="mt-3 p-2 rounded bg-dark border border-secondary text-warning" style="background-color: #090b10 !important; border-color: rgba(255,255,255,0.04) !important; font-size: 0.8rem;">
                    <i class="bi bi-cpu me-1 text-success"></i> Computing thermal optimal constraints...
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>