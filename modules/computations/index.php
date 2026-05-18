<?php 
require_once '../../includes/config.php';
require_once '../../api/weatherapi.php';
require_once 'WeatherMath.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../../index.php");
    exit();
}

$city = $_SESSION['last_city'] ?? "Kano";
$weather = getWeatherData($city);

// Live Data for default inputs
$liveT = $weather['main']['temp'] ?? 30;
$liveW = $weather['wind']['speed'] ?? 5;
//wet bulb can be estimated as temp - 5 for a rough default, but we'll leave it to user input
$liveWB = $liveT - 5;    
$liveH = $weather['main']['humidity'] ?? 70;
$liveP = $weather['main']['pressure'] ?? 1013;

// 1. Process Psychrometric or Pressure forms (These use 'mode')
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['mode'])) {
    $mode = $_POST['mode'];
    $alertType = "success"; 
    $result = null;
    
    if ($mode == 'psychro') {
        $data = WeatherMath::psychrometricRH($_POST['dry'], $_POST['wet'], $_POST['pres']);
        $dew = WeatherMath::calculateDewPoint($_POST['dry'], $data['rh']);
        $result = "RH: <strong>{$data['rh']}%</strong> | VP: <strong>{$data['vp']} hPa</strong> | Dew Point: <strong>$dew °C</strong>";
    } elseif ($mode == 'pressure') {
        $qfe = WeatherMath::qnhToQfe($_POST['qnh'], $_POST['elev']);
        $qfe_inhg = WeatherMath::hpaToInches($qfe);
        $qnh = $_POST['qnh'];
        $qnh_inhg = WeatherMath::hpaToInches($qnh);
        $result = "QFE: <strong>$qfe hPa</strong> | Altimeter Setting: <strong>$qfe_inhg inHg</strong><br>QNH: <strong>$qnh hPa</strong> | Altimeter Setting: <strong>$qnh_inhg inHg</strong>";
    }
    
}

// 2. Process Unit Converter (This uses 'convert')
$convResult = null;
if (isset($_POST['convert'])) {
    $val = $_POST['conv_val'];
    $type = $_POST['conv_type'];
    
    switch ($type) {
        case 'ctof': $convResult = "$val °C = " . WeatherMath::cToF($val) . " °F"; break;
        case 'mskt': $convResult = "$val m/s = " . WeatherMath::msToKt($val) . " KT"; break;
        case 'mft':  $convResult = "$val m = " . WeatherMath::mToFt($val) . " FT"; break;
        case 'mmin': $convResult = "$val mm = " . WeatherMath::mmToIn($val) . " IN"; break;
    }
}

// 3. Process Old Calculator (This uses 'calc_type')
if (isset($_POST['calc_type'])) {
    $t = $_POST['temp'];
    $h = $_POST['hum'];
    
    if ($_POST['calc_type'] == 'dew') {
        $val = WeatherMath::calculateDewPoint($t, $h);
        $result = "Calculated Dew Point: <strong>$val °C</strong>";
    } else {
        $val = WeatherMath::calculateHeatIndex($t, $h);
        $result = "Calculated Heat Index: <strong>$val °C</strong>";
    }
}
?>

<?php include '../../includes/header.php'; ?>
<div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold text-danger"><i class="bi bi-calculator"></i> Computations</h2>
        <div class="btn-group shadow-sm" role="group">
            <a href="../../index.php" class="btn btn-outline-secondary btn-sm btn-nav-accent" title="Dashboard"><i class="bi bi-house-heart"></i></a>
            <a href="../meteolytics/index.php" class="btn btn-outline-secondary btn-sm btn-nav-accent" title="Meteolytics"><i class="bi bi-graph-up-arrow text-danger"></i></a>
            <a href="../agrometeo/index.php" class="btn btn-outline-secondary btn-sm btn-nav-accent" title="AgroMet"><i class="bi bi-tree-fill text-success"></i></a>
            <a href="../forecasts/index.php" class="btn btn-outline-secondary btn-sm btn-nav-accent" title="Forecast"><i class="bi bi-cloud-download text-warning"></i></a>
            <a href="../aerometeo/index.php" class="btn btn-outline-secondary btn-sm btn-nav-accent" title="AeroMet"><i class="bi bi-airplane-fill text-info"></i></a>
        </div>
    </div>
    <div class="row">
        <div class="col-lg-3 mb-4">
        <div class="card border-0 shadow-sm p-4 text-center h-100">
            <i class="bi bi-cpu text-danger display-4 mb-3"></i>
            <h3>Live Stats</h3>
            <div class="text-start mt-3">
                <p class="bg-primary text-white p-2 rounded"><strong><i class="bi bi-geo-alt"></i> <?php echo $city; ?></strong></p>
                <div class="row">
                    <div class="col-6 mb-2"><strong>Temp:</strong></div>
                    <div class="col-6 mb-2"><?php echo $liveT; ?>°C (<?php echo WeatherMath::cToF($liveT); ?>°F)</div>
                    <div class="col-6 mb-2"><strong>Pres:</strong></div>
                    <div class="col-6 mb-2"><?php echo $liveP; ?> hPa (<?php echo WeatherMath::hpaToInches($liveP); ?>")</div>
                    <div class="col-6 mb-2"><strong>Hum:</strong></div>
                    <div class="col-6 mb-2"><?php echo $liveH; ?>%</div>
                    <div class="col-6 mb-2"><strong>Wind:</strong></div>
                    <div class="col-6 mb-2"><?php echo $liveW; ?> m/s (<?php echo WeatherMath::msToKt($liveW); ?> KT)</div>
                    <?php
                        $val = WeatherMath::calculateHeatIndex($liveT, $liveH);
                        $dp = WeatherMath::calculateDewPoint($liveT, $liveH);
                    ?>
                    <div class="col-6 mb-2"><strong>Heat Index:</strong></div>
                    <div class="col-6 mb-2"><?php echo $val; ?>°C</div>
                    <div class="col-6 mb-2"><strong>Dew Point:</strong></div>
                    <div class="col-6 mb-2"><?php echo $dp; ?>°C</div>
                </div>
            </div>
        </div>
    </div>

        <!-- COLUMN 2: MAIN CALCULATORS (Updated with Tabs) -->
        <div class="col-lg-6 mb-4">
            <div class="card border-0 shadow-sm p-4">
                <ul class="nav nav-pills mb-4" id="pills-tab" role="tablist">
                    <li class="nav-item"><button class="nav-link active" data-bs-toggle="pill" data-bs-target="#tab-psychro">Psychrometric</button></li>
                    <li class="nav-item"><button class="nav-link" data-bs-toggle="pill" data-bs-target="#tab-pressure">Aviation Pressure</button></li>
                </ul>

                <div class="tab-content">
                    <!-- Psychrometric Form -->
                    <div class="tab-pane fade show active" id="tab-psychro">
                        <form method="POST">
                            <input type="hidden" name="mode" value="psychro">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label small">Dry Bulb (°C)</label>
                                    <input type="number" step="0.1" name="dry" class="form-control" required value="<?php echo $liveT; ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small">Wet Bulb (°C)</label>
                                    <input type="number" step="0.1" name="wet" class="form-control" required value="<?php echo $liveWB; ?>"  >
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label small">Station Pressure (hPa)</label>
                                    <input type="number" name="pres" class="form-control" value="1013">
                                </div>
                                <button type="submit" class="btn btn-danger mt-3">Calculate RH & Vapor Pressure</button>
                            </div>
                        </form>
                    </div>

                    <!-- Aviation Pressure Form -->
                    <div class="tab-pane fade" id="tab-pressure">
                        <form method="POST">
                            <input type="hidden" name="mode" value="pressure">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label small">Current QNH (hPa)</label>
                                    <input type="number" step="0.01" required name="qnh" class="form-control" value="<?php echo $liveP; ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small">Field Elevation (m)</label>
                                    <input type="number" required step="0.01" name="elev" class="form-control" placeholder="e.g. 418.08 for kano">
                                </div>
                                <button type="submit" class="btn btn-danger mt-3">Calculate QFE & inHg</button>
                            </div>
                        </form>
                    </div>
                </div>

                <?php if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['mode'])): ?>
                    <div class="mt-4 p-3 border-start border-danger border-4 bg-white shadow-sm rounded">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <small class="text-muted d-block text-uppercase fw-bold" style="font-size: 0.7rem;">Computation Result</small>
                                <span class="fs-5"><?php echo $result; ?></span>
                            </div>
                            <button class="btn btn-outline-secondary btn-sm" onclick="navigator.clipboard.writeText('<?php echo strip_tags($result); ?>')" title="click to copy">
                                <i class="bi bi-clipboard"></i>
                            </button>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- COLUMN 3: UNIT CONVERTER SIDEBAR -->
        <div class="col-lg-3 mb-4">
            <div class="card border-0 shadow-sm p-3 bg-white h-100">
                <h6 class="fw-bold text-danger border-bottom pb-2 mb-3">
                    <i class="bi bi-arrow-left-right"></i> Quick Converter
                </h6>
                
                <form method="POST" id="quickconverter">
                    <div class="mb-2">
                        <select name="conv_type" class="form-select form-select-sm">
                            <option value="ctof">Celsius to Fahrenheit</option>
                            <option value="mskt">m/s to Knots (KT)</option>
                            <option value="mft">Meters to Feet (FT)</option>
                            <option value="mmin">Rainfall (mm to in)</option>
                        </select>
                    </div>
                    <div class="mb-2">
                        <input type="number" step="0.01" name="conv_val" class="form-control form-control-sm" placeholder="Value" required>
                    </div>
                    
                    <button type="submit" name="convert" class="btn btn-dark btn-sm w-100">Convert</button>
                </form>

                <?php if($convResult): ?>
                    <div class="mt-3 p-2 bg-light border-start border-danger border-4 small fw-bold">
                        <?php echo $convResult; ?>
                    </div>
                <?php endif; ?>

                <div class="mt-auto pt-3">
                    <div class="alert alert-secondary p-2 mb-0" style="font-size: 0.75rem;">
                        <strong>Note:</strong> Standard ISO/ICAO constants applied.
                    </div>
                </div>
            </div>
        </div>

    </div>
    <!-- Quick Reference Card -->
            <div class="card border-0 shadow-sm p-4 mt-4 bg-dark text-white">
                <h6><i class="bi bi-info-circle me-2"></i>Formula Reference</h6>
                <p class="small mb-0 text-secondary">
                    Pressure Altitude = Elevation + (1013.25 - QNH) × 27. <br>
                    Dew point is calculated using the Magnus-Tetens approximation.
                </p>
            </div>
</div>

<?php include '../../includes/footer.php'; ?>