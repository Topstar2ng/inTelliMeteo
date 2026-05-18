<?php
/**
 * Professional AeroMeteo TAF Generator
 * Conforming to WMO Annex 3 / ICAO Standard Aerodrome Forecast Structure
 */

class TafGenerator {
    private static $icao_map = [
        'Lagos' => 'DNMM', 'Abuja' => 'DNAA', 'Kano' => 'DNKN', 
        'Port Harcourt' => 'DNPO', 'Enugu' => 'DNEN', 'Kaduna' => 'DNKA',
        'Maiduguri' => 'DNMA', 'Benin' => 'DNBE', 'Ilorin' => 'DNIL',
        'Yola' => 'DNYO', 'Jos' => 'DNJO', 'Owerri' => 'DNOW',
        'Calabar' => 'DNCA', 'Uyo' => 'DNAI', 'Sokoto' => 'DNSO',
        'Katsina' => 'DNKT', 'Zaria' => 'DNZA', 'Bauchi' => 'DNBA',
        'Gombe' => 'DNGO', 'Minna' => 'DNMN', 'Akure' => 'DNAK',
        'Ibadan' => 'DNIB', 'Warri' => 'DNSU', 'Asaba' => 'DNAS',
        'Jalingo' => 'DNJA', 'Birnin Kebbi' => 'DNBI', 'Escravos' => 'DNES',
        'Bonny' => 'DNBN', 'Brass' => 'DNBR', 'Eket' => 'DNEK',
        'Makurdi' => 'DNMK', 'Shasha' => 'DNSH', 'Kontagora' => 'DNKO',
        'Nguru' => 'DNNG', 'Potiskum' => 'DNPM', 'Ibi' => 'DNIBI'
    ];

    /**
     * Primary TAF Builder Entry Point
     */
    public static function generate(String $city, Array $forecastList) {
        if (empty($forecastList)) return "DATA UNAVAILABLE";

        // a, b) Type and Location Indicators
        $icao = self::getIcao($city);
        
        // c, e) Generation of Issue & Validity Structural Context (30-Hour window)
        $now = time();
        $issuanceHours = [5, 11, 17, 23];
        $currentHour = (int)date('H', $now);
        
        $issueH = 5; // Default safe fallback
        foreach ($issuanceHours as $h) {
            if ($currentHour >= $h - 1) $issueH = $h;
        }
        
        $issueTime = date('d', $now) . str_pad($issueH, 2, '0', STR_PAD_LEFT) . "00Z";
        $validStart = str_pad($issueH + 1, 2, '0', STR_PAD_LEFT);
        
        // Calculate end timestamp based on the start of validity + 30 hours
        $validStartTimestamp = strtotime(date('Y-m-d ', $now) . ($issueH + 1) . ":00:00");
        $endTimestamp = $validStartTimestamp + (30 * 3600);
        $validEnd = date('dH', $endTimestamp);
        $validity = date('d', $now) . "$validStart/$validEnd";

        // Start report string assembly
        $output = "TAF {$icao} {$issueTime} {$validity} ";

        // 2. Format Initial Base State via sequence-ordered calls
        $baseState = $forecastList[0];
        $output .= self::formatFullForecastBlock($baseState);

        // 3. Process Evolutionary Change Groups (k)
        $changeGroupsCount = 0;
        // Limit scanning to first 12hr depth (approx 4 points in a 3h-interval API payload)
        $maxScanPoints = min(count($forecastList), 4); 

        for ($i = 1; $i < $maxScanPoints && $changeGroupsCount < 3; $i++) {
            $candidate = $forecastList[$i];
            
            if (self::shouldTriggerChange($baseState, $candidate)) {
                $changeTime = date('dH', $candidate['dt']);
                $endTime = date('H', $candidate['dt'] + 7200); // 2-hour execution transition window
                
                // Determine whether transition is uniform change (BECMG) or fluctuating disruption (TEMPO)
                $weatherDesc = strtolower($candidate['weather'][0]['main'] ?? '');
                $groupType = (strpos($weatherDesc, 'thunderstorm') !== false || strpos($weatherDesc, 'squall') !== false) ? "TEMPO" : "BECMG";

                $output .= " {$groupType} {$changeTime}/" . str_pad($endTime, 2, '0', STR_PAD_LEFT) . " " . self::formatFullForecastBlock($candidate);
                
                $baseState = $candidate; 
                $changeGroupsCount++;
            }
        }

        return $output . "=";
    }

    /**
     * Orchestrates sequential composition of standard weather strings
     */
    private static function formatFullForecastBlock(Array $data) {
        $weatherMain = $data['weather'][0]['main'] ?? '';
        $weatherDesc = $data['weather'][0]['description'] ?? '';
        $visMeters = $data['visibility'] ?? 10000;
        $cloudsPct = $data['clouds']['all'] ?? 0;

        // Parse codes prior to determining CAVOK
        $weatherCode = self::formatPresentWeather($weatherMain, $weatherDesc);
        $isHighlySignificant = (bool)preg_match('/(TS|RA|DZ|GR|VA|SQ)/i', $weatherCode);

        // CAVOK Evaluation: Vis >= 10km, no significant weather, no operational cloud bases below 5000ft
        if ($visMeters >= 10000 && !$isHighlySignificant && $cloudsPct <= 25) {
            return self::formatSurfaceWind($data['wind'] ?? []) . " CAVOK";
        }

        // Standard Sequential Compilation Structure: Wind -> Visibility -> Weather -> Cloud
        $parts = [
            self::formatSurfaceWind($data['wind'] ?? []),
            self::formatVisibility($visMeters),
            $weatherCode,
            self::formatClouds($cloudsPct, $weatherCode)
        ];

        // Clean out empty values (e.g. empty weather strings) and combine
        return implode(' ', array_filter($parts));
    }

    /**
     * g) Distinct Handler: Surface Wind Specification
     */
    private static function formatSurfaceWind(Array $wind) {
        $speedMs = $wind['speed'] ?? 0;
        $speedKt = round($speedMs * 1.94384);
        $deg = $wind['deg'] ?? 0;
        $gustMs = $wind['gust'] ?? 0;
        $gustKt = round($gustMs * 1.94384);

        if ($speedKt === 0) return "00000KT";
        if ($speedKt <= 2) return "VRB" . str_pad($speedKt, 2, '0', STR_PAD_LEFT) . "KT";

        $dirStr = str_pad($deg, 3, '0', STR_PAD_LEFT);
        $spdStr = str_pad($speedKt, 2, '0', STR_PAD_LEFT);

        if ($gustKt >= $speedKt + 10 && $gustKt >= 10) {
            return "{$dirStr}{$spdStr}G" . str_pad($gustKt, 2, '0', STR_PAD_LEFT) . "KT";
        }
        return "{$dirStr}{$spdStr}KT";
    }

    /**
     * h) Distinct Handler: Horizontal Visibility Specification
     */
    private static function formatVisibility($meters) {
        if ($meters >= 10000) return "9999";
        return str_pad($meters, 4, '0', STR_PAD_LEFT);
    }

    /**
     * i) Distinct Handler: Present / Expected Significant Operational Weather
     */
    private static function formatPresentWeather($main, $desc) {
        $main = strtolower($main); 
        $desc = strtolower($desc);

        if (strpos($main, 'thunderstorm') !== false) {
            return (strpos($desc, 'rain') !== false) ? "TSRA" : "TS";
        }
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

        return ""; // No significant cross-cutting weather code to append
    }

    /**
     * j) Distinct Handler: Cloud Layer Amount and Height Bases
     */
    private static function formatClouds($cloudsPct, $weatherCode) {
        if ($cloudsPct === 0) return "NSC";

        // Append operational convective descriptors if structural storms are present
        $convectiveMod = (strpos($weatherCode, 'TS') !== false) ? "CB" : "";
        $heightBaseToken = "020"; // Standardized operational vertical cloud level notation layer (2000ft)

        if ($cloudsPct <= 25) return "FEW" . $heightBaseToken . $convectiveMod;
        if ($cloudsPct <= 50) return "SCT" . $heightBaseToken . $convectiveMod;
        if ($cloudsPct <= 87) return "BKN" . $heightBaseToken . $convectiveMod;
        return "OVC" . $heightBaseToken . $convectiveMod;
    }

    /**
     * Change Group Structural Threshold Evaluation Rule Logic (WMO / ICAO)
     */
    private static function shouldTriggerChange($base, $new) {
        // Wind Change Criteria (Direction shift >= 60deg or Speed Delta >= 10KT)
        $dirDiff = abs(($base['wind']['deg'] ?? 0) - ($new['wind']['deg'] ?? 0));
        if ($dirDiff > 180) $dirDiff = 360 - $dirDiff;
        
        $baseSpd = ($base['wind']['speed'] ?? 0) * 1.94384;
        $newSpd = ($new['wind']['speed'] ?? 0) * 1.94384;

        if ($dirDiff >= 60 && $newSpd > 10) return true;
        if (abs($baseSpd - $newSpd) >= 10) return true;

        // Visibility Crossings (Critical aviation cutoffs: 1000, 1500, 3000, 5000m)
        $v1 = $base['visibility'] ?? 10000;
        $v2 = $new['visibility'] ?? 10000;
        $thresholds = [1000, 1500, 3000, 5000];
        foreach ($thresholds as $t) {
            if (($v1 >= $t && $v2 < $t) || ($v1 < $t && $v2 >= $t)) return true;
        }

        // Sky Cloud Cover Crossings (Passing beneath or above operational ceiling limits)
        $c1 = $base['clouds']['all'] ?? 0;
        $c2 = $new['clouds']['all'] ?? 0;
        if (($c1 <= 50 && $c2 > 50) || ($c1 > 50 && $c2 <= 50)) return true;

        return false;
    }

public static function generate(String $city, Array $forecastList) {
        if (empty($forecastList)) return "DATA UNAVAILABLE";

        $icao = self::getIcao($city);
        
        // 1. Standardize Issuance and Validity (30h)
        $now = time();
        $issuanceHours = [5, 11, 17, 23];
        $currentHour = (int)date('H', $now);
        
        // Find the closest issuance time (1 hour before validity)
        $issueH = 0;
        foreach ($issuanceHours as $h) {
            if ($currentHour >= $h - 1) $issueH = $h;
        }
        
        $issueTime = date('d', $now) . str_pad($issueH, 2, '0', STR_PAD_LEFT) . "00Z";
        $validStart = str_pad($issueH + 1, 2, '0', STR_PAD_LEFT);
        
        // Validity spans 30 hours
        $endTimestamp = $now + (30 * 3600);
        $validEnd = date('dH', $endTimestamp);
        $validity = date('d', $now) . "$validStart/$validEnd";

        $output = "TAF $icao $issueTime $validity ";

        // 2. Set Initial Base State
        $baseState = $forecastList[0];
        $output .= self::formatBlock($baseState);

        // 3. Detect Change Groups (Maximum 3 groups to keep it readable)
        $changeGroupsCount = 0;
        for ($i = 1; $i < count($forecastList) && $changeGroupsCount < 3; $i++) {
            $candidate = $forecastList[$i];
            
            if (self::shouldTriggerChange($baseState, $candidate)) {
                $changeTime = date('dH', $candidate['dt']);
                $endTime = date('H', $candidate['dt'] + 7200); // 2-hour transition
                
                $output .= " BECMG $changeTime/" . str_pad($endTime, 2, '0', STR_PAD_LEFT) . " " . self::formatBlock($candidate);
                
                // Update base state to the new condition to look for the NEXT change
                $baseState = $candidate;
                $changeGroupsCount++;
            }
        }

        return $output . "=";
    }

    private static function shouldTriggerChange($base, $new) {
        // --- Wind Thresholds ---
        $dirDiff = abs($base['wind']['deg'] - $new['wind']['deg']);
        if ($dirDiff > 180) $dirDiff = 360 - $dirDiff;
        
        $baseSpd = $base['wind']['speed'] * 1.94;
        $newSpd = $new['wind']['speed'] * 1.94;

        if ($dirDiff >= 60) return true;
        if ($baseSpd > 10 || $newSpd > 10) return true;
        if (abs($baseSpd - $newSpd) >= 10) return true;

        // --- Visibility Thresholds (ICAO: 5000, 3000, 1500, 1000m) ---
        $v1 = $base['visibility'] ?? 10000;
        $v2 = $new['visibility'] ?? 10000;
        $thresholds = [1000, 1500, 3000, 5000];
        foreach ($thresholds as $t) {
            if (($v1 >= $t && $v2 < $t) || ($v1 < $t && $v2 >= $t)) return true;
        }

        // --- Cloud Ceiling Thresholds (NSC/SCT -> BKN/OVC) ---
        $c1 = $base['clouds']['all'];
        $c2 = $new['clouds']['all'];
        if (($c1 <= 50 && $c2 > 50) || ($c1 > 50 && $c2 <= 50)) return true;

        return false;
    }

    private static function formatBlock(Array $data) {
        $speed = round($data['wind']['speed'] * 1.94384);
        $dir = str_pad($data['wind']['deg'], 3, '0', STR_PAD_LEFT);
        $wind = "{$dir}" . str_pad($speed, 2, '0', STR_PAD_LEFT) . "KT";

        $visM = $data['visibility'] ?? 10000;
        $vis = ($visM >= 10000) ? "9999" : str_pad($visM, 4, '0', STR_PAD_LEFT);

        $clouds = $data['clouds']['all'];
        if ($clouds > 75) $cloudStr = "OVC020";
        elseif ($clouds > 50) $cloudStr = "BKN025";
        elseif ($clouds > 25) $cloudStr = "SCT030";
        else $cloudStr = "NSC";

        return "$wind $vis $cloudStr";
    }

    public static function getIcao(String $city) {
        return self::$icao_map[$city] ?? 'DNXX';
    }
}
