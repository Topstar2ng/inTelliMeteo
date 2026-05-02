<?php 
require_once '../../includes/config.php';
require_once '../../api/weatherapi.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../../index.php");
    exit();
}

$city = $_GET['city'] ?? ($_SESSION['last_city'] ?? "Kano");
$forecastData = getWeatherForecast($city);

// Initialize Chart Arrays
$labels = [];
$temps = [];
$humidity = [];
$pressure = [];
$dewPoints = [];

// Initialize Table Aggregate Array
$dailyAggregates = [];

if (isset($forecastData['list'])) {
    foreach ($forecastData['list'] as $reading) {
        $timestamp = $reading['dt'];
        $date = date('Y-m-d', $timestamp);
        $time = date('H:i', $timestamp);
        
        // --- PART 1: POPULATE CHART DATA (First 8 readings = 24 Hours) ---
        if (count($labels) < 8) {
            $labels[] = $time;
            $temps[] = (float)$reading['main']['temp'];
            $humidity[] = (float)$reading['main']['humidity'];
            $pressure[] = (float)$reading['main']['pressure'];
            // Professional Dew Point Approximation
            $dp = $reading['main']['temp'] - ((100 - $reading['main']['humidity']) / 5);
            $dewPoints[] = round($dp, 2);
        }

        // --- PART 2: POPULATE 5-DAY TABLE AGGREGATES ---
        if (!isset($dailyAggregates[$date])) {
            $dailyAggregates[$date] = [
                'max_temp' => -99,
                'max_dew' => -99,
                'max_wind' => 0,
                'weather_counts' => [],
                'weather_icon' => '',
                'weather_desc' => ''
            ];
        }
        
        $currentDP = $reading['main']['temp'] - ((100 - $reading['main']['humidity']) / 5);
        $dailyAggregates[$date]['max_temp'] = max($dailyAggregates[$date]['max_temp'], $reading['main']['temp']);
        $dailyAggregates[$date]['max_dew'] = max($dailyAggregates[$date]['max_dew'], $currentDP);
        $dailyAggregates[$date]['max_wind'] = max($dailyAggregates[$date]['max_wind'], $reading['wind']['speed'] * 1.94384);
        
        // Track dominant weather
        $mainWeather = $reading['weather'][0]['main'];
        $dailyAggregates[$date]['weather_counts'][$mainWeather] = ($dailyAggregates[$date]['weather_counts'][$mainWeather] ?? 0) + 1;
        
        // Update icon/desc based on most frequent weather so far
        arsort($dailyAggregates[$date]['weather_counts']);
        $dominant = array_key_first($dailyAggregates[$date]['weather_counts']);
        if ($mainWeather === $dominant) {
            $dailyAggregates[$date]['weather_icon'] = $reading['weather'][0]['icon'];
            $dailyAggregates[$date]['weather_desc'] = $reading['weather'][0]['description'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meteolytics | Weather Analytics</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="apple-touch-icon" sizes="180x180" href="../../assets/favicons/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="../../assets/favicons/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="../../assets/favicons/favicon-16x16.png">
    <link rel="manifest" href="../../assets/favicons/site.webmanifest">
    <!-- Chart.js CDN -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        /* Prevent the "infinite growth" bug */
        .chart-container {
            position: relative;
            height: 300px; /* Fixed height for the chart */
            width: 100%;
        }
        .logo-img { width: 30px; height: 30px; margin-right: 10px; border-radius: 50%; box-shadow: 0 0 5px rgba(0,0,0,0.2); }
    </style>
</head>
<body class="bg-light">
<nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow">
    <div class="container">
        <a class="navbar-brand fw-bold" href="index.php">
            <img src="../../assets/images/intellimeteo_icon.png" class="logo-img"> 
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
                    Hi, <strong><?php echo explode(' ', $_SESSION['full_name'])[0]; ?></strong>
                </span>
                
                <!-- Logout Button -->
                <a href="../../logout.php" class="btn btn-outline-danger btn-sm me-2" title="Logout">
                    <i class="bi bi-box-arrow-right"></i>
                </a>

                <!-- Settings Icon -->
                <a href="../../modules/settings/index.php" class="text-white fs-5 lh-1 p-1 hover-rotate" title="Settings">
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
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold text-success"><i class="bi bi-tree"></i> Meteolytics for <?php echo $city; ?></h2>
        <a href="../../index.php" class="btn btn-primary btn-sm"><i class="bi bi-house-heart"></i> Dashboard</a>
    </div>
    <div class="row g-4">
        <!-- Combined Temp & Humidity -->
        <div class="col-md-6">
            <div class="card border-0 shadow-sm p-3">
                <h6 class="fw-bold">Temperature vs Humidity (24h)</h6>
                <div class="chart-container">
                    <canvas id="tempHumChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Combined Pressure & Dew Point -->
        <div class="col-md-6">
            <div class="card border-0 shadow-sm p-3">
                <h6 class="fw-bold">Pressure vs Dew Point (24h)</h6>
                <div class="chart-container">
                    <canvas id="pressDewChart"></canvas>
                </div>
            </div>
        </div>

        <!-- 5-Day Comparison Table -->
        <div class="col-12 mt-4">
            <div class="card border-0 shadow-sm overflow-hidden">
                <div class="card-header bg-dark text-white fw-bold d-flex justify-content-between align-items-center">
                    <span><i class="bi bi-table me-2"></i> 5-Day Historical & Outlook Comparison</span>
                    <span class="badge bg-primary"><?php echo $city; ?></span>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Date</th>
                                <th>Weather</th>
                                <th>Max Temp</th>
                                <th>Max Dew Pt</th>
                                <th>Max Wind</th>
                                <th>Alerts</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($dailyAggregates as $date => $val): ?>
                            <tr>
                                <td class="fw-semibold">
                                    <?php echo date('D, M d', strtotime($date)); ?>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <img src="https://openweathermap.org/img/wn/<?php echo $val['weather_icon']; ?>.png" 
                                            alt="weather icon" width="40" title="<?php echo ucfirst($val['weather_desc']); ?>">
                                        <span class="small text-capitalize text-muted ms-1"><?php echo $val['weather_desc']; ?></span>
                                    </div>
                                </td>
                                <td>
                                    <span class="text-danger fw-bold"><?php echo round($val['max_temp'], 1); ?>°C</span>
                                </td>
                                <td>
                                    <span class="text-warning"><?php echo round($val['max_dew'], 1); ?>°C</span>
                                </td>
                                <td>
                                    <span class="text-info"><?php echo round($val['max_wind'], 1); ?> <small>KT</small></span>
                                </td>
                                <td>
                                    <?php 
                                        if($val['max_wind'] > 25) echo '<span class="badge rounded-pill bg-warning text-dark">High Wind</span>';
                                        elseif($val['max_temp'] > 35) echo '<span class="badge rounded-pill bg-danger">Extreme Heat</span>';
                                        else echo '<span class="badge rounded-pill bg-light text-dark border">Stable</span>';
                                    ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <div class="table-responsive">
                    
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// JSON_NUMERIC_CHECK ensures JavaScript receives numbers, not strings
const labels = <?php echo json_encode($labels); ?>;
const tempValues = <?php echo json_encode($temps, JSON_NUMERIC_CHECK); ?>;
const humValues = <?php echo json_encode($humidity, JSON_NUMERIC_CHECK); ?>;
const pressValues = <?php echo json_encode($pressure, JSON_NUMERIC_CHECK); ?>;
const dewValues = <?php echo json_encode($dewPoints, JSON_NUMERIC_CHECK); ?>;

const commonOptions = { 
    responsive: true, 
    maintainAspectRatio: false, // CRITICAL: Stop the chart from growing infinitely
    plugins: {
        legend: { position: 'top', labels: { boxWidth: 12, usePointStyle: true } }
    },
    scales: { 
        y: { 
            beginAtZero: false, 
            display: true, 
            title: { display: true, text: 'Temp / Pressure', font: { size: 10 } } 
        }, 
        y1: { 
            position: 'right', 
            grid: { drawOnChartArea: false }, 
            title: { display: true, text: 'Hum / DewPt', font: { size: 10 } } 
        } 
    } 
};
// 1. Temp & Humidity Combined
new Chart(document.getElementById('tempHumChart'), {
    type: 'line',
    data: {
        labels: labels,
        datasets: [
            { label: 'Temp (°C)', data: tempValues, borderColor: '#ff4757', backgroundColor: 'rgba(255, 71, 87, 0.1)', yAxisID: 'y', fill: true },
            { label: 'Humidity (%)', data: humValues, borderColor: '#2ed573', borderDash: [5, 5], yAxisID: 'y1' }
        ]
    },
    options: commonOptions
});

// 2. Pressure & Dew Point Combined
new Chart(document.getElementById('pressDewChart'), {
    type: 'line',
    data: {
        labels: labels,
        datasets: [
            { label: 'Pressure (hPa)', data: pressValues, borderColor: '#3742fa', yAxisID: 'y' },
            { label: 'Dew Point (°C)', data: dewValues, borderColor: '#ffa502', yAxisID: 'y1', fill: false }
        ]
    },
    options: commonOptions
});
</script>

<?php include '../../footer.php'; ?>