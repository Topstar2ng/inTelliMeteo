<?php
/**
 * IntelliMeteo Data Logger
 * Persists reports to the MySQL database
 */
require_once 'db.php';

function logWeatherReport($data, $metar, $agro) {
    global $pdo;

    $sql = "INSERT INTO weather_reports 
            (city_name, icao_code, temp, humidity, pressure, wind_speed_kt, metar_string, planting_status, agro_advice) 
            VALUES (:city, :icao, :temp, :hum, :pres, :wind, :metar, :agro_stat, :agro_adv)";

    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':city'     => $data['name'],
            ':icao'     => MetarGenerator::getIcao($data['name']),
            ':temp'     => $data['main']['temp'],
            ':hum'      => $data['main']['humidity'],
            ':pres'     => $data['main']['pressure'],
            ':wind'     => round($data['wind']['speed'] * 1.94384, 2),
            ':metar'    => $metar,
            ':agro_stat' => $agro['status'],
            ':agro_adv'  => $agro['advice']
        ]);
        return true;
    } catch (PDOException $e) {
        error_log("Database Logging Error: " . $e->getMessage());
        return false;
    }
}