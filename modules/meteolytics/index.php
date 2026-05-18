<?php 
require_once '../../includes/config.php';
require_once '../../api/weatherapi.php';

$city = $_GET['city'] ?? ($_SESSION['last_city'] ?? "Kano");
$forecastData = getWeatherForecast($city);

// Initialize Chart Arrays
$labels = [];
$temps = [];
$humidity = [];
$pressure = [];
$dewPoints = [];

// Initialize Table Aggregate Array
$allReadings = [];
$dailyAggregates = [];

if (isset($forecastData['list'])) {

    foreach ($forecastData['list'] as $reading) {

        $time = date('H:i', $reading['dt']);
        $date = date('Y-m-d', $reading['dt']);

        $temp = (float)$reading['main']['temp'];
        $hum = (float)$reading['main']['humidity'];
        $press = (float)$reading['main']['pressure'];
        $feelsLike = (float)$reading['main']['feels_like'];
        $pop = isset($reading['pop']) ? ($reading['pop'] * 100) : 0; // Convert 0-1 decimal to percentage
        $clouds = $reading['clouds']['all'] ?? 0; // Percentage 0 - 100

        $dp = $temp - ((100 - $hum) / 5);

        $allReadings[] = [
            'time' => date('D H:i', $reading['dt']),
            'temp' => round($temp, 2),
            'humidity' => round($hum, 2),
            'pressure' => round($press, 2),
            'dew' => round($dp, 2),
            'feels_like' => round($feelsLike, 2),
            'pop' => round($pop, 2),
            'clouds' => $clouds
        ];

        // =========================================
        // DAILY AGGREGATE CODE BELOW HERE
        // =========================================

        // --- PART 2: POPULATE 5-DAY TABLE AGGREGATES ---
        if (!isset($dailyAggregates[$date])) {
            $dailyAggregates[$date] = [
                'max_temp' => -99,
                'max_dew' => -99,
                'max_wind' => 0,
                'weather_counts' => [],
                'weather_icon' => '',
                'weather_desc' => '',
                'max_feels_like' => -99,
                'max_pop' => 0,
                'avg_clouds' => 0

            ];
        }
        
        $currentDP = $reading['main']['temp'] - ((100 - $reading['main']['humidity']) / 5);
        $dailyAggregates[$date]['max_temp'] = max($dailyAggregates[$date]['max_temp'], $reading['main']['temp']);
        $dailyAggregates[$date]['max_dew'] = max($dailyAggregates[$date]['max_dew'], $currentDP);
        $windSpeed = $reading['wind']['speed'] ?? 0;
        $dailyAggregates[$date]['max_wind'] = max($dailyAggregates[$date]['max_wind'], $windSpeed * 1.94384);
        $dailyAggregates[$date]['max_feels_like'] = max($dailyAggregates[$date]['max_feels_like'], $feelsLike);
        $dailyAggregates[$date]['max_pop'] = max($dailyAggregates[$date]['max_pop'], $pop);
        $dailyAggregates[$date]['avg_clouds'] = max($dailyAggregates[$date]['avg_clouds'], $clouds);

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
<?php include '../../includes/header.php'; ?>
<style>
.chart-container{
    position: relative;
    height: 320px;
}

#periodSelector .btn{
    border-radius: 0;
    font-weight: 600;
    font-size: 13px;
    letter-spacing: .5px;
}

#periodSelector .btn:first-of-type{
    border-top-left-radius: 50rem;
    border-bottom-left-radius: 50rem;
}

#periodSelector .btn:last-of-type{
    border-top-right-radius: 50rem;
    border-bottom-right-radius: 50rem;
}

.card{
    border-radius: 18px;
}

.btn-check:checked + .btn{
    background: linear-gradient(135deg,#198754,#20c997);
    color: #fff;
    border-color: #198754;
    box-shadow: 0 4px 14px rgba(25,135,84,.35);
}

</style>
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold text-success"><i class="bi bi-graph-up-arrow"></i> <label class="bg-primary text-light"><?php echo $city; ?></label> - Meteolytics</h3>

        <div class="btn-group" role="group">
        <a href="../../index.php" class="btn btn-primary btn-sm"><i class="bi bi-house-heart"></i> Dashboard</a>
        <a href="../aerometeo/index.php" class="btn btn-danger btn-sm" title="AeroMeteo"><i class="bi bi-airplane"></i></a>
        <a href="../agrometeo/index.php" class="btn btn-success btn-sm" title="AgroMeteo"><i class="bi bi-tree-fill"></i></a>
        <a href="../computations/index.php" class="btn btn-danger btn-sm" title="Computations"><i class="bi bi-calculator"></i></a>
        <a href="../forecasts/index.php" class="btn btn-warning btn-sm" title="Forecast"><i class="bi bi-cloud-download"></i></a>
        </div>
    </div>
    <div class="row g-4">   
        <div class="col-12 text-end">
            <div class="btn-group shadow-sm rounded-pill overflow-hidden" role="group" id="periodSelector">
    
                    <input type="radio" class="btn-check" name="period" id="p24" value="24" checked>
                    <label class="btn btn-outline-success px-3" for="p24">24H</label>

                    <input type="radio" class="btn-check" name="period" id="p36" value="36">
                    <label class="btn btn-outline-success px-3" for="p36">36H</label>

                    <input type="radio" class="btn-check" name="period" id="p48" value="48">
                    <label class="btn btn-outline-success px-3" for="p48">48H</label>

                    <input type="radio" class="btn-check" name="period" id="p60" value="60">
                    <label class="btn btn-outline-success px-3" for="p60">60H</label>

            </div> 
        </div>   
    </div>
    <div class="row g-4">
        <!-- Combined Temp & Humidity -->
        <div class="col-md-6">
            <div class="card border-0 shadow-sm p-3">
                <h6 class="fw-bold chart-title-temp">
                        Temperature vs Humidity (24H)
                    </h6>
                <div class="chart-container">
                    <canvas id="tempHumChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Combined Pressure & Dew Point -->
       <div class="col-md-6">
            <div class="card border-0 shadow-sm p-3">
                <h6 class="fw-bold chart-title-pressure">Pressure vs Dew Point (24h)</h6>
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
                                <th>Temp / Feels Like</th>
                                <th>Precip. Chance</th>    <!-- New -->
                                <th>Max Dew Pt</th>
                                <th>Max Wind</th>
                                <th>Cloud Cover</th>
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
                                <!-- Combined Temperature Display -->
                                <td>
                                    <span class="text-danger fw-bold"><?php echo round($val['max_temp'], 1); ?>°C</span>
                                    <br>
                                    <small class="text-muted">Feels: <?php echo round($val['max_feels_like'], 1); ?>°C</small>
                                </td>
                                <!-- Rain Probability Badge -->
                                <td>
                                    <span class="text-primary fw-bold">
                                        <i class="bi bi-droplet-fill small"></i> <?php echo round($val['max_pop']); ?>%
                                    </span>
                                </td>
                                <td>
                                    <span class="text-warning"><?php echo round($val['max_dew'], 1); ?>°C</span>
                                </td>
                                <td>
                                    <span class="text-info"><?php echo round($val['max_wind']); ?> <small>KT</small></span>
                                </td>
                                <!-- Cloud Cover Info -->
                                <td>
                                    <?php 
                                        //$cloudAvg = count($val['avg_clouds']) > 0 ? (array_sum($val['avg_clouds']) / count($val['avg_clouds'])) : 0;
                                        switch (true) {
                                            case ($val['avg_clouds'] >= 87.5):
                                                echo '<span class="text-secondary"><i class="bi bi-cloud-fill"></i> OVC</span>';
                                                break;
                                            case ($val['avg_clouds'] >= 50):
                                                echo '<span class="text-secondary"><i class="bi bi-cloud-fill"></i> BKN</span>';
                                                break;
                                            case ($val['avg_clouds'] >= 25):
                                                echo '<span class="text-secondary"><i class="bi bi-cloud-fill"></i> FEW</span>';
                                                break;
                                            default:
                                                echo '<span class="text-secondary"><i class="bi bi-cloud-fill"></i> FEW</span>';
                                        }
                                    ?>
                                    <span class="text-secondary"><i class="bi bi-cloud-sun"></i> <?php echo $val['avg_clouds']; ?>%</span>
                                </td>
                                <!-- Alerts -->

                                <td>
                                    <?php 
                                        if($val['max_wind'] > 25) echo '<span class="badge rounded-pill bg-warning text-dark"><i class="bi bi-wind"></i> High Wind</span>';
                                        elseif($val['max_temp'] > 35) echo '<span class="badge rounded-pill bg-danger"><i class="bi bi-fire"></i> Extreme Heat</span>';
                                        else echo '<span class="badge rounded-pill bg-success text-light"><i class="bi bi-check-circle"></i> Stable</span>';
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



<?php include '../../includes/footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>

const forecastDataset = <?php echo json_encode($allReadings, JSON_NUMERIC_CHECK); ?>;

let tempHumChart;
let pressDewChart;

const commonOptions = {
    responsive: true,
    maintainAspectRatio: false,
    interaction: {
        mode: 'index',
        intersect: false
    },
    plugins: {
        legend: {
            position: 'top',
            labels: {
                boxWidth: 12,
                usePointStyle: true
            }
        }
    },
    scales: {
        y: {
            beginAtZero: false,
            title: {
                display: true,
                text: 'Temperature / Pressure'
            }
        },
        y1: {
            position: 'right',
            grid: {
                drawOnChartArea: false
            },
            title: {
                display: true,
                text: 'Humidity / Dew Point'
            }
        }
    }
};

function buildCharts(hours = 24) {

    // OpenWeather returns 3-hour interval data
    const readingsNeeded = hours / 3;

    const selectedData = forecastDataset.slice(0, readingsNeeded);

    const labels = selectedData.map(item => item.time);
    const temps = selectedData.map(item => item.temp);
    const humidity = selectedData.map(item => item.humidity);
    const pressure = selectedData.map(item => item.pressure);
    const dew = selectedData.map(item => item.dew);

    document.querySelector('.chart-title-temp')
    .innerHTML = `Temperature vs Humidity (${hours}H)`;

    document.querySelector('.chart-title-pressure')
    .innerHTML = `Pressure (${hours}H)`;

    // Destroy existing charts before recreating
    if (tempHumChart) tempHumChart.destroy();
    if (pressDewChart) pressDewChart.destroy();

    // TEMP + HUMIDITY
    tempHumChart = new Chart(
        document.getElementById('tempHumChart'),
        {
            type: 'line',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Temp (°C)',
                        data: temps,
                        borderColor: '#ff4757',
                        backgroundColor: 'rgba(255,71,87,0.1)',
                        fill: true,
                        tension: 0.4,
                        pointRadius: 3,
                        pointHoverRadius: 6,
                        yAxisID: 'y'

                    },
                    {
                        label: 'Humidity (%)',
                        data: humidity,
                        borderColor: '#2ed573',
                        backgroundColor: 'rgba(46,213,115,0.1)',
                        fill: true,
                        tension: 0.4,
                        pointRadius: 3,
                        pointHoverRadius: 6,
                        yAxisID: 'y1'
                    },
                    {
                        label: 'Dew Point (°C)',
                        data: dew,
                        borderDash: [5, 5],
                        tension: 0.4,
                        pointRadius: 3,
                        pointHoverRadius: 6,
                        yAxisID: 'y1'
                    }
                ]
            },
            options: commonOptions
        }
    );

    // PRESSURE + DEW POINT
    pressDewChart = new Chart(
        document.getElementById('pressDewChart'),
        {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Pressure (hPa)',
                        data: pressure,
                        fill: true,
                        /*use gradient fill*/
                        backgroundColor: function(context) {
                            const chart = context.chart;
                            const {ctx, chartArea} = chart;

                            if (!chartArea) {
                                // This case happens on initial chart load
                                return null;
                            }
                            const gradient = ctx.createLinearGradient(0, chartArea.top, 0, chartArea.bottom);
                            gradient.addColorStop(0, 'rgba(55, 71, 250, 0.8)');
                            gradient.addColorStop(1, 'rgba(55, 71, 250, 0.2)');
                            return gradient;
                        },                        
                        borderColor: '#3742fa',
                        tension: 0.4,
                        yAxisID: 'y',
                        pointRadius: 3,
                        pointHoverRadius: 6
                    }
                ]
            },
            options: commonOptions
        }
    );
}

document.querySelectorAll('input[name="period"]').forEach(btn => {

    btn.addEventListener('change', function () {

        const selectedHours = parseInt(this.value);

        buildCharts(selectedHours);

    });

});

// Initial Load
buildCharts(24);
</script>