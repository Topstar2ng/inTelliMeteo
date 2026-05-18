<?php
require_once __DIR__ . '/../includes/config.php';

function getWeatherData($city = "Kano") {
    $apiKey = WEATHER_API_KEY;
    
    if (empty($apiKey)) {
        return ['error' => 'API Key missing'];
    }

    // Clean the input to prevent basic injection or errors
    $city = urlencode(trim($city));
    $url = "https://api.openweathermap.org/data/2.5/weather?q={$city},NG&units=metric&appid={$apiKey}";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $data = json_decode($response, true);

    if ($httpCode !== 200) {
        // If city not found, return an error so we can show it to the user
        return ['error' => "Location '" . urldecode($city) . "' not found in Nigeria."];
    }

    return $data;
}

/**
 * Fetch 5-Day / 3-Hour Forecast
 */
function getWeatherForecast($city = "Kano") {
    $apiKey = WEATHER_API_KEY;
    $city = urlencode(trim($city));
    // Note the endpoint change to /forecast
    $url = "https://api.openweathermap.org/data/2.5/forecast?q={$city},NG&units=metric&appid={$apiKey}";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 
    $response = curl_exec($ch);
    curl_close($ch);

    return json_decode($response, true);
}