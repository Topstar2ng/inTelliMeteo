<?php
/**
 * Professional AeroMeteo METAR Generator
 * Following ICAO/WMO Annex 3 Reporting Standards
 * Includes Dynamic 2-Hour Trend Forecast Engine (BECMG / TEMPO / NOSIG)
 */

class MetarGenerator {
    
    private static $icao_map = [
        'Lagos' => 'DNMM', 'Ikeja' => 'DNMM', 'Abuja' => 'DNAA', 'Kano' => 'DNKN', 
        'Port Harcourt' => 'DNPO', 'Enugu' => 'DNEN', 'Kaduna' => 'DNKA',
        'Maiduguri' => 'DNMA', 'Benin' => 'DNBE', 'Ilorin' => 'DNIL',
        'Yola' => 'DNYO', 'Jos' => 'DNJO', 'Owerri' => 'DNOW',
        'Calabar' => 'DNCA', 'Uyo' => 'DNAI', 'Sokoto' => 'DNSO',
        'Katsina' => 'DNKT', 'Zaria' => 'DNZA', 'Bauchi' => 'DNBA',
        'Gombe' => 'DNGO', 'Minna' => 'DNMN', 'Akure' => 'DNAK',
        'Ibadan' => 'DNIB', 'Warri' => 'DNSU', 'Asaba' => 'DNAS',
        'Jalingo' => 'DNJA', 'Birnin Kebbi' => 'DNBI'
    ];

    public static function getIcao($cityName) {
        return self::$icao_map[$cityName] ?? 'DNXX';
    }

    /**
     * Primary METAR generation method
     * @param array $current - Current weather data
     * @param array|null $forecast2h - Weather data representing the condition ~2 hours out
     */
    public static function generate($current, $forecast2h = null) {
        if (!isset($current['name'])) return "NIL";

        // a, b) Identification & Location
        $icao = self::getIcao($current['name']);

        // c) Time of Observation (UTC)
        $timestamp = gmdate('dHi') . 'Z';

        // d) Automated Identifier
        $auto = "AUTO";

        // e) Surface Wind Logic
        $wind = self::formatWind($current['wind'] ?? []);

        // Weather Assessment Setup
        $weather_main = $current['weather'][0]['main'] ?? 'Clear';
        $weather_desc = $current['weather'][0]['description'] ?? '';
        $sig_weather_code = self::parseSignificantWeather($weather_main, $weather_desc);
        $is_highly_significant = (bool)preg_match('/(TS|RA|DZ|SN|GR|GS|VA|SQ)/i', $sig_weather_code);

        // f) Visibility Logic
        $vis_meters = $current['visibility'] ?? 10000;
        $clouds_pct = $current['clouds']['all'] ?? 0;
        $has_low_clouds = ($clouds_pct > 0); 

        $cavok = false;
        $vis_string = "";

        if ($vis_meters >= 10000 && !$is_highly_significant && !$has_low_clouds) {
            $cavok = true;
            $vis_string = "CAVOK";
        } else {
            $vis_string = ($vis_meters >= 10000) ? "9999" : str_pad($vis_meters, 4, '0', STR_PAD_LEFT);
        }

        // h) Present Weather Reporting
        $weather_string = "";
        if ($is_highly_significant || (!empty($sig_weather_code) && $vis_meters <= 5000)) {
            $weather_string = $sig_weather_code . " ";
        }

        // i) Cloud Layers Logic
        $cloud_string = "";
        if (!$cavok) {
            $cloud_string = self::formatClouds($clouds_pct, $sig_weather_code);
        }

        // j) Temperature & Dew Point
        $tempVal = round($current['main']['temp'] ?? 0);
        $hum = $current['main']['humidity'] ?? 50;
        $dewVal = round($tempVal - ((100 - $hum) / 5));
        
        $t = ($tempVal < 0) ? "M" . str_pad(abs($tempVal), 2, '0', STR_PAD_LEFT) : str_pad($tempVal, 2, '0', STR_PAD_LEFT);
        $d = ($dewVal < 0) ? "M" . str_pad(abs($dewVal), 2, '0', STR_PAD_LEFT) : str_pad($dewVal, 2, '0', STR_PAD_LEFT);
        $temp_dew = "{$t}/{$d}";

        // k) QNH
        $qnh = "Q" . ($current['main']['pressure'] ?? 1013);

        // l) Generate Trend Forecast Group (WMO Annex 3 2-Hour Validity)
        $trend_string = self::generateTrend($current, $forecast2h);

        // Compile Complete Standard Report
        $output = "METAR {$icao} {$timestamp} {$auto} {$wind} ";
        if ($cavok) {
            $output .= "CAVOK ";
        } else {
            $output .= "{$vis_string} " . $weather_string . $cloud_string;
        }
        $output .= "{$temp_dew} {$qnh} {$trend_string}";

        return $output;
    }

    /**
     * WMO Annex 3 Trend Forecast Calculation Engine (2-Hour Validation Window)
     */
    private static function generateTrend($current, $future) {
        // If no short-term trend forecast data is provided, fall back safely to NOSIG
        if (!$future || !isset($future['wind']) || !isset($future['visibility'])) {
            return "NOSIG";
        }

        $change_indicators = [];
        $is_temporary = false;

        // 1. Wind Trend Evaluation (Significant changes: speed change >= 10KT or change in direction)
        $curr_wind_kt = round(($current['wind']['speed'] ?? 0) * 1.94384);
        $fut_wind_kt = round(($future['wind']['speed'] ?? 0) * 1.94384);
        $curr_deg = $current['wind']['deg'] ?? 0;
        $fut_deg = $future['wind']['deg'] ?? 0;

        if (abs($curr_wind_kt - $fut_wind_kt) >= 10 || (abs($curr_deg - $fut_deg) >= 60 && $fut_wind_kt > 10)) {
            $change_indicators[] = self::formatWind($future['wind']);
        }

        // 2. Visibility Trend Evaluation (Significant threshold boundaries crossing 1500m, 3000m, or 5000m)
        $curr_vis = $current['visibility'] ?? 10000;
        $fut_vis = $future['visibility'] ?? 10000;
        
        if (self::crossedVisibilityThreshold($curr_vis, $fut_vis)) {
            $change_indicators[] = ($fut_vis >= 10000) ? "9999" : str_pad($fut_vis, 4, '0', STR_PAD_LEFT);
        }

        // 3. Present Weather Trend Evaluation (Onset or cessation of hazardous operational conditions)
        $curr_weather = self::parseSignificantWeather($current['weather'][0]['main'] ?? '', $current['weather'][0]['description'] ?? '');
        $fut_weather = self::parseSignificantWeather($future['weather'][0]['main'] ?? '', $future['weather'][0]['description'] ?? '');

        if ($curr_weather !== $fut_weather) {
            if (!empty($fut_weather)) {
                $change_indicators[] = $fut_weather;
                // Convective thunderstorm lines imply highly fluctuating/temporary conditions
                if (strpos($fut_weather, 'TS') !== false) {
                    $is_temporary = true;
                }
            } else {
                $change_indicators[] = "NSW"; // No Significant Weather (Cessation)
            }
        }

        // 4. Cloud Trend Evaluation (Crossing operational minimum bases below 1500m / 5000ft)
        $curr_clouds = $current['clouds']['all'] ?? 0;
        $fut_clouds = $future['clouds']['all'] ?? 0;
        if (abs($curr_clouds - $fut_clouds) >= 30) { 
            $change_indicators[] = self::formatClouds($fut_clouds, $fut_weather);
        }

        // Determine matching change indicator syntax structure
        if (empty($change_indicators)) {
            return "NOSIG";
        }

        $trend_type = $is_temporary ? "TEMPO" : "BECMG";
        return $trend_type . " " . implode(' ', $change_indicators);
    }

    private static function formatWind($wind_data) {
        $speed_ms = $wind_data['speed'] ?? 0;
        $speed_kt = round($speed_ms * 1.94384);
        $deg = $wind_data['deg'] ?? 0;
        $gust_ms = $wind_data['gust'] ?? 0;
        $gust_kt = round($gust_ms * 1.94384);

        if ($speed_kt === 0.0 || $speed_kt === 0) return "00000KT";
        if ($speed_kt <= 2) return "VRB" . str_pad($speed_kt, 2, '0', STR_PAD_LEFT) . "KT";
        
        $dir = str_pad($deg, 3, '0', STR_PAD_LEFT);
        $spd = str_pad($speed_kt, 2, '0', STR_PAD_LEFT);
        
        if ($gust_kt >= 10) {
            return "{$dir}{$spd}G" . str_pad($gust_kt, 2, '0', STR_PAD_LEFT) . "KT";
        }
        return "{$dir}{$spd}KT";
    }

    private static function formatClouds($clouds_pct, $weather_code) {
        if ($clouds_pct == 0) return "NSC ";
        
        $cloud_type = (strpos($weather_code, 'TS') !== false) ? "CB" : "";
        $height_token = "020"; // 2000ft standard baseline representation 

        if ($clouds_pct <= 25)  return "FEW" . $height_token . $cloud_type . " ";
        if ($clouds_pct <= 50)  return "SCT" . $height_token . $cloud_type . " ";
        if ($clouds_pct <= 87.5) return "BKN" . $height_token . $cloud_type . " ";
        return "OVC" . $height_token . $cloud_type . " ";
    }

    private static function crossedVisibilityThreshold($curr, $fut) {
        $thresholds = [1500, 3000, 5000, 10000];
        foreach ($thresholds as $t) {
            if (($curr < $t && $fut >= $t) || ($curr >= $t && $fut < $t)) {
                return true;
            }
        }
        return false;
    }

    private static function parseSignificantWeather($main, $desc) {
        $main = strtolower($main); $desc = strtolower($desc);
        if (strpos($main, 'thunderstorm') !== false) return (strpos($desc, 'rain') !== false) ? "TSRA" : "TS";
        if (strpos($main, 'drizzle') !== false) return "DZ";
        if (strpos($main, 'rain') !== false) {
            if (strpos($desc, 'light') !== false) return "-RA";
            if (strpos($desc, 'heavy') !== false) return "+RA";
            return "RA";
        }
        if (strpos($main, 'dust') !== false) return "DU";
        if (strpos($main, 'haze') !== false) return "HZ";
        if (strpos($main, 'mist') !== false) return "BR";
        if (strpos($main, 'fog') !== false) return "FG";
        return "";
    }

    public static function generatePressureAlt($data) {
        $qnh = $data['main']['pressure'] ?? 1013;
        return round($qnh * 0.02953, 2) . " inHg";
    }
}