<?php
class HistoricalEngine {
    public static function getMonthlyAverage($pdo, $city, $month) {
        $sql = "SELECT AVG(temp) as avg_temp 
                FROM weather_reports 
                WHERE city_name = :city 
                AND MONTH(created_at) = :month";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['city' => $city, 'month' => $month]);
        $result = $stmt->fetch();
        return round($result['avg_temp'], 1);
    }

    public static function getYearlyTrend($pdo, $city) {
        // Gets average temp per month for the current year
        $sql = "SELECT MONTHNAME(created_at) as month, AVG(temp) as avg_temp 
                FROM weather_reports 
                WHERE city_name = :city AND YEAR(created_at) = YEAR(CURDATE())
                GROUP BY MONTH(created_at)
                ORDER BY MONTH(created_at)";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['city' => $city]);
        return $stmt->fetchAll();
    }
}