<?php 
require_once '../../includes/config.php'; 
require_once '../../api/weatherapi.php';
require_once 'MetarGenerator.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../../index.php");
    exit();
}

    $city = $_GET['city'] ?? ($_SESSION['last_city'] ?? "Kano");
$weather = getWeatherData($city);
$metar = MetarGenerator::generate($weather);

// Standard meter definitions for visibility handling safely
$raw_visibility = $weather['visibility'] ?? 10000; 
?>
<?php include '../../includes/header.php'; ?>
<style>
    body {
        background-color: #121212 !important;
        color: #e0e0e0 !important;
    }
    .metar-box {
        background: #1e1e1e !important;
        border-left: 5px solid #00ff00 !important;
        font-family: 'Courier New', monospace;
        font-size: 1.2rem;
    }
    .card {
        background: #1e1e1e !important;
        border: 1px solid #333 !important;
        color: white !important;
    }
    .text-neon {
        color: #00ff00 !important;
    }
</style>

<div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold text-success"><i class="bi bi-airplane"></i> Aero Reports <label class="bg-primary text-light px-2 rounded"><?php echo htmlspecialchars($city); ?></label> </h3>

        <div class="btn-group" role="group">
            <a href="../../index.php" class="btn btn-primary btn-sm" title="dashboard"><i class="bi bi-house-heart"></i></a>
            <a href="../meteolytics/index.php" class="btn btn-danger btn-sm" title="Meteolytics"><i class="bi bi-graph-up-arrow"></i></a>
            <a href="../agrometeo/index.php" class="btn btn-success btn-sm" title="AgroMeteo"><i class="bi bi-tree-fill"></i></a>
            <a href="../computations/index.php" class="btn btn-danger btn-sm" title="Computations"><i class="bi bi-calculator"></i></a>
            <a href="../forecasts/index.php" class="btn btn-warning btn-sm" title="Forecast"><i class="bi bi-cloud-download"></i></a>
        </div>
        </div>
    
    <div class="row">
            <div class="col-md-8">
            <div class="card p-4">
                <h6>Current Raw METAR (WMO Annex 3 Compliant)</h6>
                <div class="metar-box p-3 mt-2">
                    <span class="text-neon"><?php echo $metar; ?></span>
                </div>
            </div>

            <div class="row g-3 mt-4">
                <div class="col-md-4">
                    <div class="card p-3">
                        <small class="text-light">Station Identifier</small>
                        <h3><?php echo MetarGenerator::getIcao($weather['name'] ?? ''); ?></h3>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card p-3">
                        <small class="text-light">Wind Conditions</small>
                        <h3>
                            <?php 
                            $speed_kt = round(($weather['wind']['speed'] ?? 0) * 1.94384);
                            echo ($speed_kt == 0) ? 'CALM' : ($weather['wind']['deg'] ?? 0) . '° / ' . str_pad($speed_kt, 2, '0', STR_PAD_LEFT) . ' KT';
                            ?>
                        </h3>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card p-3">
                        <small class="text-light">Visibility</small>
                        <h3><?php echo $raw_visibility >= 10000 ? '>= 10' : ($raw_visibility / 1000); ?> KM</h3>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="card p-3">
                        <small class="text-light">Air Temperature</small>
                        <h3><?php echo isset($weather['main']['temp']) ? round($weather['main']['temp']) : 'N/A'; ?>°C</h3>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card p-3">
                        <small class="text-light">QNH (Altimeter)</small>
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
                        <small class="text-light">Sky Conditions & Present Weather</small>
                        <h3><?php echo ucfirst($weather['weather'][0]['description'] ?? 'N/A'); ?> (Clouds: <?php echo $weather['clouds']['all'] ?? 0; ?>%)</h3>
                    </div>
                </div>
            </div>           
        </div>

        <div class="col-md-4">
            <div class="card p-4 h-100 shadow">
                <h5>Pro Flight Assessment</h5>
                <hr>
                <?php if($raw_visibility < 5000): ?>
                    <div class="alert alert-danger"><strong>IFR Alert:</strong> Visibility falls below 5km. Instrumental Flight Rules are required.</div>
                <?php elseif($raw_visibility <= 8000): ?>
                    <div class="alert alert-warning"><strong>MVFR Caution:</strong> Marginal visual flight conditions. Check local terrain obstacles.</div>
                <?php else: ?>
                    <div class="alert alert-success"><strong>VFR Optimal:</strong> Visual conditions safe for standard operations.</div>
                <?php endif; ?>
                
                <p class="small text-light mt-3">
                    Note: This report is dynamically derived using real-time calculations matching WMO/ICAO regulations. Cross-reference with NiMet operations before dispatch.
                </p>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card mt-4 border-0 bg-dark text-light shadow">
                <div class="card-header bg-secondary text-white fw-bold">
                    📊 Technical Decoding Breakdown
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-md-3 border-end border-secondary">
                            <small class="text-light d-block">Identification</small>
                            <span class="text-info fw-bold"><?php echo MetarGenerator::getIcao($weather['name'] ?? ''); ?></span>
                            <p class="small text-light mb-0"><?php echo $weather['name'] ?? 'Unknown'; ?> Region</p>
                        </div>
                        <div class="col-md-3 border-end border-secondary">
                            <small class="text-light d-block">Calculated Dew Point</small>
                            <span class="text-info fw-bold">
                                <?php 
                                $t_val = $weather['main']['temp'] ?? 0;
                                $h_val = $weather['main']['humidity'] ?? 100;
                                echo round($t_val - ((100 - $h_val) / 5)); 
                                ?>°C
                            </span>
                        </div>
                        <div class="col-md-3 border-end border-secondary">
                            <small class="text-light d-block">Cloud Coverage Mode</small>
                            <span class="text-info fw-bold">
                                <?php 
                                $c = $weather['clouds']['all'] ?? 0;
                                if($c == 0) echo "Clear Sky (NSC)";
                                elseif($c <= 25) echo "Few Layers (FEW)";
                                elseif($c <= 50) echo "Scattered (SCT)";
                                elseif($c <= 87.5) echo "Broken (BKN)";
                                else echo "Overcast (OVC)";
                                ?>
                            </span>
                        </div>
                        <div class="col-md-3">
                            <small class="text-light d-block">Flight Class Status</small>
                            <?php 
                                if($raw_visibility > 8000) echo '<span class="badge bg-success py-2 px-3">VFR (Green)</span>';
                                elseif($raw_visibility >= 5000) echo '<span class="badge bg-primary py-2 px-3">MVFR (Blue)</span>';
                                else echo '<span class="badge bg-danger py-2 px-3">IFR (Red)</span>';
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>