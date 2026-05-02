<?php
/**
 * IntelliMeteo WeatherMath Engine
 */
class WeatherMath {

    /**
     * Magnus-Tetens formula for Dew Point
     */
    public static function calculateDewPoint($T, $RH) {
        if ($RH <= 0) return 0;
        $a = 17.27;
        $b = 237.7;
        $gamma = (($a * $T) / ($b + $T)) + log($RH / 100);
        return round(($b * $gamma) / ($a - $gamma), 1);
    }

    /**
     * Rothfusz Regression for Heat Index
     */
    public static function calculateHeatIndex($T, $RH) {
        if ($T < 26.7) return $T; // HI not valid below 26.7°C
        $HI = -8.78469475556 + 1.61139411 * $T + 2.33854883889 * $RH 
              + -0.14611605 * $T * $RH + -0.012308094 * pow($T, 2) 
              + -0.0164248277778 * pow($RH, 2) + 0.002211732 * pow($T, 2) * $RH 
              + 0.00072546 * $T * pow($RH, 2) + -0.000003582 * pow($T, 2) * pow($RH, 2);
        return round($HI, 1);
    }

    /**
     * Pressure Altitude (Standard 1013.25 hPa reference)
     */
    public static function calculatePressureAlt($alt, $qnh) {
        return round($alt + (1013.25 - $qnh) * 27);
    }

    /**
     * Psychrometric Calculation (Dry vs Wet Bulb)
     * Using the Regnault/Ferrel formula
     */
    public static function psychrometricRH($dry, $wet, $pres = 1013.25) {
        $es = 6.112 * exp((17.67 * $dry) / ($dry + 243.5)); // Saturation Vapor Pressure
        $ew = 6.112 * exp((17.67 * $wet) / ($wet + 243.5)); // Saturation VP at Wet Bulb
        
        // Actual Vapor Pressure (e)
        $e = $ew - 0.00066 * (1 + 0.00115 * $wet) * ($dry - $wet) * $pres;
        
        $rh = ($e / $es) * 100;
        return [
            'rh' => round(max(0, min(100, $rh)), 1),
            'vp' => round($e, 2)
        ];
    }

    /**
     * QNH to QFE (Pressure at field elevation)
     * Approx: QFE = QNH - (Elevation / 27)
     */
    public static function qnhToQfe($qnh, $elevation_meters) {
        return round($qnh - ($elevation_meters / 8.2), 2); // 1hPa per ~8.2m
    }

    /**
     * hPa/mb to Inches of Mercury (inHg)
     */
    public static function hpaToInches($hpa) {
        return round($hpa * 0.02953, 2);
    }

    /**
     * Temperature: Celsius to Fahrenheit
     */
    public static function cToF($c) {
        return round(($c * 9/5) + 32, 1);
    }

    /**
     * Speed: m/s to Knots (Aviation Standard)
     */
    public static function msToKt($ms) {
        return round($ms * 1.94384, 1);
    }

    /**
     * Altitude: Meters to Feet
     */
    public static function mToFt($m) {
        return round($m * 3.28084, 0);
    }

    /**
     * Rainfall: Millimeters to Inches (AgroMet)
     */
    public static function mmToIn($mm) {
        return round($mm * 0.0393701, 2);
    }
}