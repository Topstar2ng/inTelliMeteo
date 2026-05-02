<?php
/**
 * AgroMeteo Intelligence Engine
 * Specialized logic for Nigerian Agriculture
 */

class AgroEngine {

    /**
     * Determines if the current conditions are suitable for planting.
     * Criteria: Temp between 20-35°C and Humidity > 60% (indicating moisture)
     */
    public static function getPlantingSuitability($temp, $humidity, $description) {
        $score = 0;
        $advice = "";

        // Temperature Check
        if ($temp >= 22 && $temp <= 32) {
            $score += 40;
        }

        // Moisture/Humidity Check (Crucial for rain-fed agriculture)
        if ($humidity >= 60) {
            $score += 40;
        }

        // Weather Condition Check
        if (strpos(strtolower($description), 'rain') !== false) {
            $score += 20;
            $advice = "Recent rainfall detected. Soil moisture is likely optimal.";
        } else {
            $advice = "No recent rain. Irrigation may be required for new seedlings.";
        }

        if ($score >= 80) return ['status' => 'Excellent', 'color' => 'success', 'advice' => $advice];
        if ($score >= 50) return ['status' => 'Good', 'color' => 'warning', 'advice' => "Conditions are fair, but monitor soil moisture closely."];
        return ['status' => 'Poor', 'color' => 'danger', 'advice' => "Conditions are sub-optimal for planting. Wait for better moisture."];
    }

    /**
     * Provides specific crop advice based on the region
     */
    public static function getCropFocus($city) {
        $northern_cities = ['Kano', 'Kaduna', 'Maiduguri', 'Sokoto', 'Katsina'];
        if (in_array($city, $northern_cities)) {
            return ["Cereals (Maize, Sorghum)", "Legumes (Cowpea)", "Groundnuts"];
        }
        return ["Root Crops (Yam, Cassava)", "Oil Palm", "Cocoa"];
    }
}