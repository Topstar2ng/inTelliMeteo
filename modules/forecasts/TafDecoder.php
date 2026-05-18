<?php
/**
 * Professional AeroMeteo TAF Decoder
 * Tailored to cleanly parse structured ICAO/WMO weather messages into descriptive timelines
 */

class TafDecoder {
    public static function decode($taf) {
        // Clean up formatting tokens, strip double spaces, and isolate components
        $taf = trim(str_replace(['=', "\n", "\r"], ['', ' ', ' '], $taf));
        $parts = array_filter(explode(' ', $taf));
        
        $decoded = [];
        $currentGroup = "Initial Conditions";
        $groupBuffer = [];

        foreach ($parts as $part) {
            
            // 1. Handle Structural Identifiers (TAF / AMD / COR)
            if (in_array($part, ['TAF', 'AMD', 'COR'])) {
                $type = ($part == 'TAF') ? 'Standard Terminal Aerodrome Forecast' : (($part == 'AMD') ? 'Amended Forecast' : 'Corrected Forecast');
                $groupBuffer[] = "<strong>Report Type:</strong> Description: " . $type;
                continue;
            }

            // 2. Handle Location Identifiers (4-Letter ICAO codes starting with D for Nigeria)
            if (preg_match('/^DN[A-Z]{2}$/', $part)) {
                $groupBuffer[] = "<strong>Station Indicator:</strong> Airfield ICAO Code: " . $part;
                continue;
            }

            // 3. Handle Issuance Time (e.g., 171100Z -> Day 17 at 11:00 UTC)
            if (preg_match('/^(\d{2})(\d{2})(\d{2})Z$/', $part, $matches)) {
                $groupBuffer[] = "<strong>Issued On:</strong> Day " . $matches[1] . " at " . $matches[2] . ":" . $matches[3] . " UTC";
                continue;
            }

            // 4. Handle Timeline Shift Groups (BECMG / TEMPO Change Blocks)
            if (in_array($part, ['BECMG', 'TEMPO'])) {
                // If there's pending content in the current block, save it first
                if (!empty($groupBuffer)) {
                    $decoded[$currentGroup] = $groupBuffer;
                }
                // Transition context to the new evolutionary timeline state
                $currentGroup = ($part == 'BECMG') ? "Expected Gradual Change (BECMG)" : "Temporary Fluctuations (TEMPO)";
                $groupBuffer = [];
                continue;
            }

            // 5. Decode Validity Time Ranges (e.g., 1712/1818)
            if (preg_match('/^(\d{2})(\d{2})\/(\d{2})(\d{2})$/', $part, $matches)) {
                $groupBuffer[] = "<strong>Period of Validity:</strong> From Day " . $matches[1] . " at " . $matches[2] . ":00 UTC until Day " . $matches[3] . " at " . $matches[4] . ":00 UTC";
                continue;
            }

            // 6. Decode Wind with optional Gust structural data (e.g., 05012KT, 14010G22KT, VRB02KT, 00000KT)
            if (preg_match('/^(\d{3}|VRB)(\d{2})(G\d{2})?KT$/', $part, $matches)) {
                $dir = ($matches[1] == '000') ? "Calm" : (($matches[1] == 'VRB') ? "Variable" : $matches[1] . "°");
                $speed = (int)$matches[2];
                $windText = "<strong>Surface Wind:</strong> From {$dir} at {$speed} Knots";
                
                if (!empty($matches[3])) {
                    $gustSpeed = (int)substr($matches[3], 1);
                    $windText .= ", with peak gusts up to {$gustSpeed} Knots";
                }
                $groupBuffer[] = $windText;
                continue;
            }

            // 7. Handle CAVOK Standard Shortcut Override
            if ($part == 'CAVOK') {
                $groupBuffer[] = "<strong>Operational Conditions:</strong> CAVOK (Ceiling and Visibility OK: Visibility ≥ 10km, no significant weather, no clouds below 5,000 feet)";
                continue;
            }

            // 8. Decode Horizontal Visibility (e.g., 9999 or 3500)
            if (preg_match('/^(\d{4})$/', $part)) {
                $vis = ($part == '9999') ? "10 kilometers or more" : number_format((int)$part) . " meters";
                $groupBuffer[] = "<strong>Horizontal Visibility:</strong> " . $vis;
                continue;
            }

            // 9. Decode Present / Significant Weather (e.g., TSRA, -RA, BR, HZ)
            if (preg_match('/^(-|\+)?(TS|RA|DZ|GR|HZ|BR|FG|DU|SQ)+$/', $part, $matches)) {
                $weatherMap = [
                    'TS' => 'Thunderstorms', 'RA' => 'Rain', 'DZ' => 'Drizzle', 
                    'GR' => 'Hail', 'HZ' => 'Haze', 'BR' => 'Mist', 
                    'FG' => 'Fog', 'DU' => 'Widespread Dust', 'SQ' => 'Squalls'
                ];
                
                $intensity = '';
                if ($matches[1] == '-') $intensity = 'Light ';
                if ($matches[1] == '+') $intensity = 'Heavy ';
                
                // Break tokens into pairs if combined like TSRA
                $weatherCodes = str_split(str_replace(['-', '+'], '', $part), 2);
                $translatedComponents = [];
                foreach ($weatherCodes as $code) {
                    if (isset($weatherMap[$code])) {
                        $translatedComponents[] = $weatherMap[$code];
                    }
                }
                
                if (!empty($translatedComponents)) {
                    $groupBuffer[] = "<strong>Significant Weather:</strong> " . $intensity . implode(' and ', $translatedComponents);
                }
                continue;
            }

            // 10. Decode Cloud Formations and convective variants (e.g., SCT020, OVC015CB, NSC)
            if ($part == 'NSC') {
                $groupBuffer[] = "<strong>Cloud Layers:</strong> No Significant Clouds detected";
                continue;
            }
            
            if (preg_match('/^([A-Z]{3})(\d{3})(CB)?$/', $part, $matches)) {
                $types = ['SKC' => 'Clear Sky', 'FEW' => 'Few Clouds (1-2 oktas)', 'SCT' => 'Scattered Clouds (3-4 oktas)', 'BKN' => 'Broken Clouds (5-7 oktas)', 'OVC' => 'Overcast (8 oktas)'];
                $desc = $types[$matches[1]] ?? "Cloud Layer";
                $height = (int)$matches[2] * 100;
                $cloudText = "<strong>Cloud Layers:</strong> {$desc} at " . number_format($height) . " feet above field elevation";
                
                if (!empty($matches[3]) && $matches[3] == 'CB') {
                    $cloudText .= " (<span class='text-danger fw-bold'>Cumulonimbus/Storm Clouds</span>)";
                }
                $groupBuffer[] = $cloudText;
                continue;
            }
        }

        // Commit final outstanding structural context remaining inside the execution buffer
        if (!empty($groupBuffer)) {
            $decoded[$currentGroup] = $groupBuffer;
        }

        // Flatten the multi-dimensional array into a single list of strings for the view
        $flattened = [];
        foreach ($decoded as $groupName => $lines) {
            if ($groupName !== "Initial Conditions") {
                $flattened[] = "<div class='mt-2 mb-1 text-warning fw-bold border-bottom border-secondary pb-1'><i class='bi bi-clock-history me-2'></i>" . $groupName . "</div>";
            }
            foreach ($lines as $line) {
                $flattened[] = $line;
            }
        }

        return $flattened;
    }
}