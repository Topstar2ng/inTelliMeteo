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

    .logo-img {
        width: 30px;
        height: 30px;
        margin-right: 10px;
        border-radius: 50%;
        box-shadow: 0 0 5px rgba(0,0,0,0.2);
    }
</style>

<div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold text-success"><i class="bi bi-airplane"></i> Aero Reports <label class="bg-primary text-light"><?php echo $city; ?></label> </h3>

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