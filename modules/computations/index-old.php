<?php 
require_once '../../includes/config.php';
require_once '../../api/weatherapi.php';
require_once 'WeatherMath.php';

$city = $_SESSION['last_city'] ?? "Lagos";
$weather = getWeatherData($city);

// Live Data for default inputs
$liveT = $weather['main']['temp'] ?? 30;
$liveW = $weather['wind']['speed'] ?? 5;
//wet bulb can be estimated as temp - 5 for a rough default, but we'll leave it to user input
$liveWB = $liveT - 5;    
$liveH = $weather['main']['humidity'] ?? 70;
$liveP = $weather['main']['pressure'] ?? 1013;

// Process manual calculation if submitted
$result = null;
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

$result = null;
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $mode = $_POST['mode'];
    
    if ($mode == 'psychro') {
        $data = WeatherMath::psychrometricRH($_POST['dry'], $_POST['wet'], $_POST['pres']);
        $dew = WeatherMath::calculateDewPoint($_POST['dry'], $data['rh']);
        $result = "RH: <strong>{$data['rh']}%</strong> | VP: <strong>{$data['vp']} hPa</strong> | Dew Point: <strong>$dew °C</strong>";
    } elseif ($mode == 'pressure') {
        $qfe = WeatherMath::qnhToQfe($_POST['qnh'], $_POST['elev']);
        $inhg = WeatherMath::hpaToInches($qfe);
        $result = "QFE: <strong>$qfe hPa</strong> | Altimeter Setting: <strong>$inhg inHg</strong>";
    }
}

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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Computations | IntelliMeteo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="apple-touch-icon" sizes="180x180" href="../../assets/favicons/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="../../assets/favicons/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="../../assets/favicons/favicon-16x16.png">
    <link rel="manifest" href="../../assets/favicons/site.webmanifest">
    <style>
        .logo-img { width: 30px; height: 30px; margin-right: 10px; border-radius: 50%; box-shadow: 0 0 5px rgba(0,0,0,0.2); }
         
    </style>
</head>
<body class="bg-light">
<nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow">
    <div class="container">
        <a class="navbar-brand fw-bold" href="index.php">
                <!-- show the span only on desktop -->
                <img src="../../assets/images/intellimeteo_icon.png" class="logo-img"> IntelliMeteo <span class="d-none d-md-inline">: A Weather & Meteo Analytics Portal for Nigerian Users</span>            
        </a>        
    </div>
</nav>
<div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold text-danger"><i class="bi bi-calculator"></i> Computations</h2>
        <a href="../../index.php" class="btn btn-primary btn-sm"><i class="bi bi-house-heart"></i> Dashboard</a>
    </div>
    <div class="row">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm p-4 text-center">
                <i class="bi bi-cpu text-danger display-4 mb-3"></i>
                <h3>Weather Math</h3>
                <p class="text-muted">Standard meteorological formulas for aviation and environmental safety.</p>
                <hr>
                <div class="text-start">
                    <h6>Current Inputs (<?php echo $city; ?>)</h6>
                    <ul class="small list-unstyled">
                        <li>Temp: <?php echo $liveT; ?>°C</li>
                        <li>Humidity: <?php echo $liveH; ?>%</li>
                        <li>Wind Speed: <?php echo $liveW; ?> m/s</li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="col-md-8">
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
                                    <input type="number" step="0.01" name="qnh" class="form-control" value="<?php echo $liveP; ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small">Field Elevation (m)</label>
                                    <input type="number" step="0.01" name="elev" class="form-control" placeholder="e.g. 418.08 for kano">
                                </div>
                                <button type="submit" class="btn btn-danger mt-3">Calculate QFE & inHg</button>
                            </div>
                        </form>
                    </div>
                </div>

                <?php if($result): ?>
                    <div class="alert alert-danger mt-4"><?php echo $result; ?></div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>