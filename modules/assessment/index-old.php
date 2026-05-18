<?php
require_once '../../includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../../index.php");
    exit();
}
// 1. Fetch a random record from the last year to use as a "Challenge"
$stmt = $pdo->query("SELECT w.*, n.name FROM weather_data w LEFT JOIN ngcities n ON w.location_id = n.tblid ORDER BY RAND() LIMIT 1");
$challenge = $stmt->fetch();

// 2. Pre-calculate the correct answers (hidden from user)
$correctWind = round($challenge['wind_speed'] * 1.94384); // Convert to Knots
$isCavok = ($challenge['visibility'] >= 10000 && $challenge['cloud'] < 20) ? 'Yes' : 'No';
?>
 
<?php include '../../includes/header.php'; ?>

<div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold text-success"><i class="bi bi-airplane"></i> Competency Assessment </h3>

        <div class="btn-group" role="group">
            <a href="../../index.php" class="btn btn-primary btn-sm" title="dashboard"><i class="bi bi-house-heart"></i></a>
            <a href="../meteolytics/index.php" class="btn btn-danger btn-sm" title="Meteolytics"><i class="bi bi-graph-up-arrow"></i></a>
            <a href="../agrometeo/index.php" class="btn btn-success btn-sm" title="AgroMeteo"><i class="bi bi-tree-fill"></i></a>
            <a href="../computations/index.php" class="btn btn-danger btn-sm" title="Computations"><i class="bi bi-calculator"></i></a>
            <a href="../forecasts/index.php" class="btn btn-warning btn-sm" title="Forecast"><i class="bi bi-cloud-download"></i></a>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow border-0">
                
                <div class="card-header bg-dark text-white p-3">
                    <div class="row">
                        <div class="col-8">
                            <h5 class="mb-0"><i class="bi bi-mortarboard me-2"></i> Competency Test</h5>
                        </div>
                        <div class="col-4 text-end"> 
                            <a href="../../index.php" class="btn btn-primary btn-sm"><i class="bi bi-house-heart"></i> Dashboard</a>
                        </div>
                    </div>
                </div>
                <div class="card-body p-4">
                    <p class="text-muted">Analyze the following historical data log from <strong><?php echo $challenge['name']; ?></strong> on <strong><?php echo date('M d, Y', strtotime($challenge['data_date'])); ?></strong>:</p>
                    
                    <div class="bg-light p-3 border rounded mb-4 font-monospace">
                        TEMP: <?php echo $challenge['temperature']; ?>°C | 
                        HUM: <?php echo $challenge['humidity']; ?>% | 
                        VIS: <?php echo $challenge['visibility']; ?>m |
                        CLOUD: <?php echo $challenge['weather_description']; ?> |
                        WIND: <?php echo $challenge['wind_speed']; ?>m/s at <?php echo $challenge['wind_direction']; ?>°
                    </div>

                    <form id="quizForm">
                        <div class="mb-3">
                            <label class="form-label">1. Convert the wind speed to Knots (KT):</label>
                            <input type="number" class="form-control" name="user_wind" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">2. Based on the visibility and clouds, would this be reported as CAVOK?</label>
                            <select class="form-select" name="user_cavok">
                                <option value="Yes">Yes</option>
                                <option value="No">No</option>
                            </select>
                        </div>

                        <button type="button" onclick="checkAnswers()" class="btn btn-primary w-100">Submit Assessment</button>
                    </form>

                    <div id="result" class="mt-4 d-none">
                        <!-- Results will be injected here -->
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>



<?php include '../../includes/footer.php'; ?>
<script>
function checkAnswers() {
    const correctWind = <?php echo $correctWind; ?>;
    const correctCavok = "<?php echo $isCavok; ?>";
    
    const userWind = document.querySelector('[name="user_wind"]').value;
    const userCavok = document.querySelector('[name="user_cavok"]').value;
    
    let score = 0;
    if(userWind == correctWind) score += 50;
    if(userCavok == correctCavok) score += 50;

    const resultDiv = document.getElementById('result');
    resultDiv.classList.remove('d-none');
    resultDiv.innerHTML = `
        <div class="alert ${score === 100 ? 'alert-success' : 'alert-warning'}">
            <h4>Your Score: ${score}%</h4>
            <p>The correct wind was <strong>${correctWind} KT</strong>. CAVOK status: <strong>${correctCavok}</strong>.</p>
            <button class="btn btn-sm btn-outline-dark" onclick="location.reload()">Try Another Case</button>
        </div>
    `;
}
</script>