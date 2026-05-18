<?php
/**
 * Professional AeroMeteo METAR Generator
 * Following ICAO/WMO Reporting Standards
 */

class MetarGenerator {
    
    // Official ICAO Mapping for Nigerian Airports
    private static $icao_map = [
    // Major International Airports
    'Lagos' => 'DNMM', // Murtala Muhammed International Airport
    'Ikeja' => 'DNMM', // Murtala Muhammed International Airport
    'Abuja' => 'DNAA', // Nnamdi Azikiwe International Airport
    'Kano' => 'DNKN', // Mallam Aminu Kano International Airport
    'Port Harcourt' => 'DNPO', // Port Harcourt International Airport
    'Enugu' => 'DNEN', // Akanu Ibiam International Airport

    // Major Domestic Airports
    'Kaduna' => 'DNKA',
    'Maiduguri' => 'DNMA',
    'Benin' => 'DNBE',
    'Ilorin' => 'DNIL',
    'Yola' => 'DNYO',
    'Jos' => 'DNJO',
    'Owerri' => 'DNOW',
    'Calabar' => 'DNCA',
    'Uyo' => 'DNAI',
    'Sokoto' => 'DNSO',
    'Katsina' => 'DNKT',
    'Zaria' => 'DNZA',
    'Bauchi' => 'DNBA',
    'Gombe' => 'DNGO',
    'Minna' => 'DNMN',
    'Akure' => 'DNAK',
    'Ibadan' => 'DNIB',
    'Warri' => 'DNSU', // Osubi Airstrip
    'Asaba' => 'DNAS',
    'Jalingo' => 'DNJA',
    'Birnin Kebbi' => 'DNBI',

    // Oil & Private / Airstrips
    'Escravos' => 'DNES',
    'Bonny' => 'DNBN',
    'Brass' => 'DNBR',
    'Eket' => 'DNEK',

    // Military / Special Use (limited civil relevance but useful for completeness)
    'Makurdi' => 'DNMK',
    'Shasha' => 'DNSH',

    // Lesser-known / regional strips (may have limited ops or seasonal use)
    'Kontagora' => 'DNKO',
    'Nguru' => 'DNNG',
    'Potiskum' => 'DNPM',
    'Ibi' => 'DNIBI' // Rare / not always officially standardized
];

    /**
     * Helper to get ICAO code from City Name
     */
    public static function getIcao($cityName) {
        // Iuse the map, or return a generic code if city isn't in our aviation list
        return self::$icao_map[$cityName] ?? 'DNXX';
    }

    public static function generate($data) {
        if (!isset($data['name'])) return "NIL";

        // 1. Station Identification
        $icao = self::getIcao($data['name']);

        // 2. Date & Time (UTC/Z)
        $timestamp = gmdate('dHi') . 'Z';

        // 3. Wind Logic
        $dir = str_pad($data['wind']['deg'] ?? 0, 3, '0', STR_PAD_LEFT);
        $speed = str_pad(round(($data['wind']['speed'] ?? 0) * 1.94384), 2, '0', STR_PAD_LEFT);
        $wind = "{$dir}{$speed}KT";

        // 4. CAVOK & Visibility Logic
        // ICAO Standard: Visibility > 10km, No clouds below 5000ft, No significant weather
        $vis_meters = $data['visibility'] ?? 10000;
        // Check for significant weather conditions (e.g., Rain, Thunderstorm, Snow)
        $has_bad_weather = !empty($data['weather'][0]['main']) && !in_array($data['weather'][0]['main'], ['Clear', 'Clouds']);
        
        $cavok = false;
        if ($vis_meters >= 10000 && !$has_bad_weather) {
            $cavok = true;
            $vis_string = "CAVOK";
        } else {
            $vis_string = str_pad($vis_meters, 4, '0', STR_PAD_LEFT) . " ";
        }

        // 5. Temperature and Dew Point
        $tempVal = round($data['main']['temp']);
        $hum = $data['main']['humidity'];
        $dewVal = round($tempVal - ((100 - $hum) / 5));
        
        // Handle negative temps (M05)
        $t = ($tempVal < 0) ? "M" . str_pad(abs($tempVal), 2, '0', STR_PAD_LEFT) : str_pad($tempVal, 2, '0', STR_PAD_LEFT);
        $d = ($dewVal < 0) ? "M" . str_pad(abs($dewVal), 2, '0', STR_PAD_LEFT) : str_pad($dewVal, 2, '0', STR_PAD_LEFT);
        $temp_dew = "{$t}/{$d}";

        // 6. Altimeter (QNH)
        $qnh = "Q" . ($data['main']['pressure'] ?? 1013);

        // Assemble Final METAR
        $output = "METAR {$icao} {$timestamp} {$wind} ";
        $output .= $cavok ? "CAVOK " : "{$vis_string} ";
        $output .= "{$temp_dew} {$qnh} NOSIG";

        return $output;
    }

    //generate TAF (simplified for demo)
    public static function generateTAF($data) {
        if (!isset($data['name'])) return "NIL";

        $icao = self::getIcao($data['name']);
        $timestamp = gmdate('dHi') . 'Z';

        // For simplicity, we'll just create a basic TAF with a 24-hour forecast
        $taf = "TAF {$icao} {$timestamp} ";
        $taf .= "FM" . gmdate('dHi', strtotime('+1 hour')) . " 00000KT CAVOK ";
        $taf .= "FM" . gmdate('dHi', strtotime('+6 hours')) . " 18010KT 5000 RA ";
        $taf .= "FM" . gmdate('dHi', strtotime('+12 hours')) . " 36015KT SCT020 BKN100 ";
        $taf .= "FM" . gmdate('dHi', strtotime('+18 hours')) . " 27005KT CAVOK ";

        return $taf;
    }

    //generate pressure altitude (simplified)
    public static function generatePressureAlt($data) {
        if (!isset($data['name'])) return "NIL";

        $qnh = $data['main']['pressure'] ?? 1013;
        $elevation_meters = $data['main']['elevation'] ?? 0;

        // Pressure Altitude = Elevation + (Standard Pressure - QNH) * 27
        $pressure_alt = round($elevation_meters + (1013.25 - $qnh) * 27);

        // Convert to inches for aviation standard
        $pressure_alt_inches = round($qnh * 0.02953, 2); // Convert meters to feet
        return "{$pressure_alt_inches} inHg";
    }
}