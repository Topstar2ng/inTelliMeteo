<?php
  //define base URL for assets
  define('BASE_URL', 'http://localhost/intelliMeteo-v.0.1/');
  define('ASSETS_URL', BASE_URL . 'assets/');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IntelliMeteo | Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons CDN -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="apple-touch-icon" sizes="180x180" href="<?php echo ASSETS_URL; ?>favicons/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="<?php echo ASSETS_URL; ?>favicons/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="<?php echo ASSETS_URL; ?>favicons/favicon-16x16.png">
    <link rel="manifest" href="<?php echo ASSETS_URL; ?>favicons/site.webmanifest">
    <link rel="stylesheet" href="<?php echo ASSETS_URL; ?>css/style.css" type="text/css">
</head>
<body>

    <div id="preloader">
        <div class="loader-content">
            <img src="<?php echo ASSETS_URL; ?>images/intellimeteo_icon.png" class="loader-logo mb-3">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-2 text-muted fw-bold">Fetching Meteo Data...</p>
        </div>
    </div>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow">
    <div class="container">
        <a class="navbar-brand fw-bold" href="<?php echo BASE_URL; ?>index.php">
            <img src="<?php echo ASSETS_URL; ?>images/intellimeteo_icon.png" class="logo-img"> 
            IntelliMeteo <span class="d-none d-md-inline">: A Weather & Meteo Analytics Portal</span>
            <span class="time-gmt gmt-time "></span>
        </a>
        
        <div class="d-flex align-items-center">
            <!-- Search Form (Always Visible) -->
            <form class="d-flex me-3" action="<?php echo BASE_URL; ?>index.php" method="GET">
                <input class="form-control search-input form-control-sm" type="search" name="city" placeholder="City..." aria-label="Search" required>
                <button class="btn btn-primary btn-sm search-btn" type="submit" title="search..."><i class="bi bi-search-heart"></i></button>
            </form>

            <?php if(isset($_SESSION['user_id'])): ?>
                <!-- USER VIEW: Logged In -->
                <span class="text-light me-3 small d-none d-lg-inline">
                    Hi, <strong><?php echo explode(' ', $_SESSION['full_name'])[0]; ?></strong>
                    
                </span>
                
                <!-- Logout Button -->
                <a href="<?php echo BASE_URL; ?>logout.php" class="btn btn-outline-danger btn-sm me-2" title="Logout">
                    <i class="bi bi-box-arrow-right"></i>
                </a>

                <!-- Settings Icon -->
                <a href="<?php echo BASE_URL; ?>modules/settings/index.php" class="text-white fs-5 lh-1 p-1 hover-rotate" title="Settings">
                    <i class="bi bi-gear-fill"></i>
                </a>
            <?php else: ?>
                <!-- GUEST VIEW: Not Logged In -->
                <a href="<?php echo BASE_URL; ?>login.php" class="btn btn-outline-light btn-sm me-2"><i class="bi bi-box-arrow-in-right"></i> Login</a>
                <a href="<?php echo BASE_URL; ?>register.php" class="btn btn-primary btn-sm"><i class="bi bi-person-add"></i> Register</a>
            <?php endif; ?>
        </div>
    </div>
</nav>
