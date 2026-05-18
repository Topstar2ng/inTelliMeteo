<?php
class TafGenerator {
    private static $icao_map = [
    // Major International Airports
    'Lagos' => 'DNMM', // Murtala Muhammed International Airport
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