<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IntelliMeteo | Terms and Conditions</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons CDN -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="apple-touch-icon" sizes="180x180" href="../assets/favicons/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="../assets/favicons/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="../assets/favicons/favicon-16x16.png">
    <link rel="manifest" href="../assets/favicons/site.webmanifest">
    <style>
        body { background-color: #f8f9fa; }
        .logo-img { width: 30px; height: 30px; margin-right: 10px; border-radius: 50%; box-shadow: 0 0 5px rgba(0,0,0,0.2); }
         @media (max-width: 576px) {
            .container { padding: 15px; }
        }

</style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow">
    <div class="container">
        <a class="navbar-brand fw-bold" href="../index.php">
            <img src="../assets/images/intellimeteo_icon.png" class="logo-img"> 
            IntelliMeteo <span class="d-none d-md-inline">: A Weather & Meteo Analytics Portal</span>
        </a>        
    </div>
</nav>
<div class="container mt-5">
    <div class="card border-0 shadow-sm p-5">
        <h2 class="text-danger fw-bold">Terms of Reference & Legal Indemnity</h2>
        <p class="text-muted">Last Updated: May 2026</p>
        <hr>
        
        <h5>1. Nature of Service</h5>
        <p>IntelliMeteo is an analytics portal providing meteorological data and automated computations. Data is sourced from third-party APIs and manual observations. We do not guarantee 100% accuracy for mission-critical operations.</p>

        <h5>2. Limitation of Liability & Indemnity</h5>
        <p>By using this portal, the user agrees to <strong>indemnify and hold harmless</strong> the developer, Ogunjobi Temitope and Tecspectra Technologies Ltd. from any claims, losses, or legal issues arising from:</p>
        <ul>
            <li>Decisions made based on the weather data (e.g., flight planning, agricultural planting).</li>
            <li>Inaccuracies in automated TAF/METAR decoding.</li>
            <li>System downtime or data loss.</li>
        </ul>

        <h5>3. Professional Use Only</h5>
        <p>Aviation-related computations (QFE, QNH, TAF) are for educational and secondary reference only. Always consult official NIMET or ICAO sources for active flight operations.</p>
        
        <div class="bg-light p-3 border-start border-danger border-4">
            <strong>Acceptance:</strong> By clicking "Register," you acknowledge that you have read, understood, and agreed to be bound by these terms.
        </div>
    </div>
</div>

<?php include '../footer.php'; ?>