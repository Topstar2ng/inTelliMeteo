<?php
session_start();
/**
 * IntelliMeteo Configuration Loader
 * Handles environment variables for security.
 */

$preferredUnits = $_SESSION['units'] ?? 'metric'; 
$unitParam = ($preferredUnits == 'metric') ? 'metric' : 'imperial';

// Pass this into API call
// Example: .../weather?q=$city&units=$unitParam&appid=...

function loadEnv($path) {
    if (!file_exists($path)) return;

    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        list($name, $value) = explode('=', $line, 2);
        $_ENV[trim($name)] = trim($value);
    }
}

loadEnv(__DIR__ . '/../.env');

// Define constants for easy access across the app
define('DB_HOST', $_ENV['DB_HOST'] ?? 'localhost');
define('DB_NAME', $_ENV['DB_NAME'] ?? '');
define('DB_USER', $_ENV['DB_USER'] ?? '');
define('DB_PASS', $_ENV['DB_PASS'] ?? '');
define('WEATHER_API_KEY', $_ENV['OPENWEATHER_API_KEY'] ?? '');