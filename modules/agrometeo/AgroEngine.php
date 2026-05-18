<?php
/**
 * AgroMeteo Intelligence Engine
 * Specialized logic for Nigerian Agriculture
 */

class AgroEngine { 

    /**
     * Determines if the current conditions are suitable for planting.
     * Evaluates thermal windows and relative humidity vectors.
     */
    public static function getPlantingSuitability($temp, $humidity, $description) {
        $score = 0;
        $adviceStrings = [];

        // 1. Temperature Range Evaluation
        if ($temp >= 22 && $temp <= 32) {
            $score += 40;
        } else {
            $adviceStrings[] = "Extreme thermal readings may impair early cell division.";
        }

        // 2. Moisture / Relative Humidity Evaluation
        if ($humidity >= 60) {
            $score += 40;
        } else {
            $adviceStrings[] = "Low atmospheric vapor pressure indicates dry soil conditions.";
        }

        // 3. Dynamic Hydrological Precipitation Check
        if (strpos(strtolower($description), 'rain') !== false) {
            $score += 20;
            $adviceStrings[] = "Recent precipitation detected; field-capacity soil moisture is highly probable.";
        } else {
            if ($score >= 80) {
                $adviceStrings[] = "Atmospheric conditions are stable, though supplementary irrigation balances soil bed dryness.";
            } else {
                $adviceStrings[] = "No rainfall recorded. Active irrigation required to support germinating root structures.";
            }
        }

        // Aggregate final strings cleanly
        $finalAdvice = implode(' ', $adviceStrings);

        if ($score >= 80) {
            return [
                'status' => 'Excellent', 
                'color' => 'success', 
                'icon' => 'bi-check-circle-fill',
                'advice' => $finalAdvice
            ];
        }
        if ($score >= 50) {
            return [
                'status' => 'Favorable', 
                'color' => 'warning', 
                'icon' => 'bi-exclamation-circle-fill',
                'advice' => !empty($finalAdvice) ? $finalAdvice : "Conditions are fair, but closely monitor evaporation rates."
            ];
        }
        return [
            'status' => 'Critical / Poor', 
            'color' => 'danger', 
            'icon' => 'bi-x-circle-fill',
            'advice' => "Sub-optimal matrix parameters. Delay planting cycles until moisture levels improve."
        ];
    }

    /**
     * Case-insensitive agricultural geographic lookup for Nigerian cultivation zones
     */
    public static function getCropFocus($city) {
        $normalizedCity = trim(strtolower($city));
        
        $northern_cities = ['kano', 'kaduna', 'maiduguri', 'sokoto', 'katsina', 'zaria', 'jos', 'bauchi', 'yola', 'gombe', 'damaturu', 'birnin kebbi', 'jalingo', 'potiskum', 'nguru', 'hadejia', 'gashua', 'bama', 'konduga', 'dikwa', 'marte', 'abadam', 'ngala', 'banki', 'buni yadi', 'gubio', 'kwaya kusar', 'shani', 'hawul', 'bama', 'konduga', 'dikwa', 'marte', 'abadam', 'ngala', 'banki', 'buni yadi', 'gubio', 'kwaya kusar', 'shani', 'hawul'];
        
        if (in_array($normalizedCity, $northern_cities)) {
            return [
                "Cereals" => "Maize, Sorghum, Millet, Rice, Wheat", 
                "Legumes" => "Cowpea, Soybeans, Bambara Groundnut, Groundnuts", 
                "Oilseeds" => "Groundnuts, Sesame, Sunflower, Safflower, Castor Beans"
            ];
        }
        
        return [
            "Tubers" => "White Yam, Cassava, Sweet Potato, Cocoyam, Irish Potato", 
            "Perennials" => "Oil Palm, Cocoa Pods, Cashew Apples, Citrus Fruits", 
            "Cash Crops" => "Rubber, Cashew Nuts, Sugarcane, Coffee"
        ];
    }
}