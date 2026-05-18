<?php
class TafDecoder {
    public static function decode($taf) {
        $parts = explode(' ', str_replace('=', '', $taf));
        $decoded = [];

        foreach ($parts as $part) {
            // Decode Validity (e.g., 0212/0318)
            if (preg_match('/^(\d{2})(\d{2})\/(\d{2})(\d{2})$/', $part, $matches)) {
                $decoded[] = "Valid from the " . $matches[1] . " at " . $matches[2] . ":00Z until the " . $matches[3] . " at " . $matches[4] . ":00Z";
            }
            // Decode Wind (e.g., 05012KT)
            elseif (preg_match('/^(\d{3})(\d{2,3})KT$/', $part, $matches)) {
                $dir = ($matches[1] == '000') ? "Variable" : $matches[1] . "°";
                $decoded[] = "Wind from $dir at " . (int)$matches[2] . " Knots";
            } 
            // Decode Visibility (e.g., 9999 or 0500)
            elseif (preg_match('/^(\d{4})$/', $part)) {
                $vis = ($part == '9999') ? "10km or more" : (int)$part . " meters";
                $decoded[] = "Visibility: $vis";
            }
            // Decode Clouds (e.g., SCT030)
            elseif (preg_match('/^([A-Z]{3})(\d{3})$/', $part, $matches)) {
                $types = ['SKC' => 'Clear sky', 'FEW' => 'Few clouds', 'SCT' => 'Scattered clouds', 'BKN' => 'Broken clouds', 'OVC' => 'Overcast'];
                $desc = $types[$matches[1]] ?? "Cloud layer";
                $height = (int)$matches[2] * 100;
                $decoded[] = "$desc at $height feet";
            }
            // Logic for change indicators
            elseif ($part == 'BECMG') {
                $decoded[] = "--- <strong>Expected Change:</strong> ---";
            }
        }
        return $decoded;
    }
}