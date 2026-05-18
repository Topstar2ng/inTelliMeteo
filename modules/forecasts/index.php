<?php
require_once '../../includes/config.php';
require_once '../../api/weatherapi.php'; // Using your existing API file
require_once 'TafGenerator.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../../index.php");
    exit();
}

// Convert last city to word capitalized for better matching with ICAO map
$city = ucfirst(strtolower($_SESSION['last_city'] ?? "Kano")); // Default to Kano if no city in session
$forecastData = getWeatherForecast($city); // Get the 5-day/3-hour data
$taf = TafGenerator::generate($city, $forecastData['list'] ?? []);
?>
<?php include '../../includes/header.php'; ?>

<style>
    body { 
        background-color: #0f111a !important; 
        color: #0449a4 !important; 
    }
    .taf-card {
        background: #161925;
        border: 1px solid #23293e;
        border-radius: 12px;
    }
    .taf-header {
        background: #1e2235;
        border-bottom: 1px solid #23293e;
        color: #ffc107; /* Consistent warning accent color */
    }
    .raw-taf-container {
        background: #090b11;
        border-left: 4px solid #ffc107;
        border-radius: 6px;
        position: relative;
    }
    .raw-taf-code {
        font-family: 'Courier New', Courier, monospace;
        letter-spacing: 1px;
        line-height: 1.6;
        color: #38bdf8 !important; /* Cleaner aviation blue/cyan look instead of harsh neon green */
    }
    .translation-item {
        background: rgba(255, 255, 255, 0.02);
        border: 1px solid rgba(255, 255, 255, 0.05);
        border-radius: 8px;
        transition: background 0.2s ease;
    }
    .translation-item:hover {
        background: rgba(255, 255, 255, 0.04);
    }
    .btn-action {
        transition: all 0.2s ease;
    }
    .btn-action:hover {
        transform: translateY(-1px);
    }
</style>

<div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-0 fw-bold"><i class="bi bi-clouds me-2 text-primary"></i>Aviation Briefing</h4>
            <small class="text-muted">Terminal Aerodrome Forecasts for <?php echo htmlspecialchars($city); ?></small>
        </div>
        <div class="btn-group" role="group">
            <a href="../../index.php" class="btn btn-primary btn-sm" title="dashboard"><i class="bi bi-house-heart"></i></a>
            <a href="../meteolytics/index.php" class="btn btn-danger btn-sm" title="Meteolytics"><i class="bi bi-graph-up-arrow"></i></a>
            <a href="../agrometeo/index.php" class="btn btn-success btn-sm" title="AgroMeteo"><i class="bi bi-tree-fill"></i></a>
            <a href="../computations/index.php" class="btn btn-danger btn-sm" title="Computations"><i class="bi bi-calculator"></i></a>
            <a href="../aerometeo/index.php" class="btn btn-warning btn-sm" title="AeroMet"><i class="bi bi-airplane-fill"></i></a>
        </div>
    </div>

    <div class="row mb-4">
        <?php 
        $temp = $forecastData['list'][0]['main']['temp'] ?? 0;
        if ($temp > 38): 
        ?>
        <div class="col-12">
            <div class="alert alert-danger bg-danger-subtle border-0 shadow-sm d-flex align-items-center mb-0 text-white" style="background-color: rgba(220, 53, 69, 0.2) !important;">
                <i class="bi bi-exclamation-triangle-fill fs-3 me-3 text-danger"></i>
                <div>
                    <strong class="text-danger">Heat Advisory:</strong> Extreme temperatures forecasted (<?php echo round($temp); ?>°C). Ensure livestock hydration and avoid midday planting.
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card taf-card border-0 shadow-lg overflow-hidden">
                <div class="card-header taf-header p-3 d-flex justify-content-between align-items-center">
                    <span class="fw-bold fs-5">
                        <i class="bi bi-megaphone me-2"></i>Terminal Aerodrome Forecast (TAF)
                    </span>
                    <span class="badge bg-dark text-warning border border-secondary px-3 py-2 uppercase font-monospace">
                        ICAO: <?php echo htmlspecialchars($city); ?>
                    </span>
                </div>
                
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="text-muted small text-uppercase mb-0 tracking-wider">Raw Bulletin Data</h6>
                        <button class="btn btn-sm btn-dark text-light border border-secondary btn-action" 
                                onclick="navigator.clipboard.writeText('<?php echo addslashes(strip_tags($taf)); ?>')" 
                                title="Copy raw TAF data">
                            <i class="bi bi-clipboard me-1"></i> Copy Code
                        </button>
                    </div>

                    <div class="raw-taf-container p-4 mb-4">
                        <code class="fs-4 d-block raw-taf-code">
                            <?php echo $taf; ?>
                        </code>
                    </div>

                    <div class="d-flex align-items-center bg-dark p-3 rounded border border-secondary mb-4">
                        <i class="bi bi-info-circle-fill text-info me-2 fs-5"></i>
                        <span class="flex-grow-1 text-light">This automated TAF synthesis is under verification and experimental evaluation. Utilize context rules as an operational guide only.</span>
                    </div>

                    <?php
                    require_once 'TafDecoder.php';
                    $decodedTaf = TafDecoder::decode($taf);
                    ?>

                    <div class="border-top border-secondary pt-4">
                        <h6 class="text-warning text-uppercase small fw-bold mb-3 tracking-wider">
                            <i class="bi bi-translate me-2"></i>Plain Language Meteorological Translation
                        </h6>
                        
                        <div class="row g-4">
                            <?php if (!empty($decodedTaf)): ?>
                                <?php foreach ($decodedTaf as $line): ?>
                                    <div class="col-12">
                                        <div class="row g-2 ps-2">
                                            <div class="col-12">
                                                <div class="translation-item p-3 text-light d-flex align-items-start">
                                                    <?php if (strpos($line, 'bi-clock-history') !== false): ?>
                                                        <span class="w-100"><?php echo $line; ?></span>
                                                    <?php else: ?>
                                                        <i class="bi bi-arrow-right-short text-warning me-2 flex-shrink-0 fs-5"></i> 
                                                        <span style="font-size: 0.95rem; line-height: 1.5;"><?php echo $line; ?></span>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="col-12">
                                    <div class="text-muted italic py-2 ps-2">No translation strings generated for this timeframe.</div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>                                                    

                </div>
            </div>
        </div>
</div>

<?php include '../../includes/footer.php'; ?>
