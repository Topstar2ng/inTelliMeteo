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

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IntelliMeteo | Professional Assessment</title>
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
<div class="container mt-5">
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

<?php include '../../footer.php'; ?>