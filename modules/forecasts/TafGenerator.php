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

    public static function getIcao(String $city) {
        return self::$icao_map[$city] ?? 'DNXX';
    }

    public static function generate(String $city, Array $forecastList) {
        if (empty($forecastList)) return "DATA UNAVAILABLE";

        $icao = self::getIcao($city);
        $issueTime = date('dHi', $forecastList[0]['dt']) . "Z";
        
        // TAFs usually cover 30h. We'll take the first 10 readings (3h each)
        $validStart = date('dH', $forecastList[0]['dt']);
        $validEnd = date('dH', $forecastList[10]['dt']);
        $validity = "{$validStart}/{$validEnd}";

        $output = "TAF $icao $issueTime $validity ";

        // Process the base conditions (First 3 hours)
        $output .= self::formatBlock($forecastList[0]);

        // Process significant changes (BECMG - Becoming)
        // We'll look at the 12-hour mark for a trend change
        if (isset($forecastList[4])) {
            $changeTime = date('dH', $forecastList[4]['dt']);
            //$changeTime_padded = str_pad($changeTime, 2, '0', STR_PAD_LEFT);
            //$changeTime_padded_end = str_pad($changeTime + 2, 2, '0', STR_PAD_LEFT);
            $output .= " BECMG {$changeTime}/" . (str_pad($changeTime+2, 4, '0', STR_PAD_LEFT)) . " " . self::formatBlock($forecastList[4]);
        }

        return $output . "="; // TAFs always end with =
    }

    private static function formatBlock( Array $data) {
        // Wind
        $speed = round($data['wind']['speed'] * 1.94384);
        $dir = str_pad($data['wind']['deg'], 3, '0', STR_PAD_LEFT);
        $wind = "{$dir}" . str_pad($speed, 2, '0', STR_PAD_LEFT) . "KT";

        // Visibility (Forecasted)
        $visM = $data['visibility'] ?? 10000;
        $vis = ($visM >= 10000) ? "9999" : str_pad($visM, 4, '0', STR_PAD_LEFT);

        // Clouds
        $clouds = $data['clouds']['all'];
        $cloudStr = "NSC"; // No Significant Clouds
        if ($clouds > 75) $cloudStr = "OVC020";
        elseif ($clouds > 50) $cloudStr = "BKN025";
        elseif ($clouds > 25) $cloudStr = "SCT030";

        return "$wind $vis $cloudStr";
    }
}