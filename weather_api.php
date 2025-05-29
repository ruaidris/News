<?php
error_reporting(0);
ini_set('display_errors', 0);
require_once __DIR__ . '/config.php';
header('Content-Type: application/json; charset=utf-8');

function getWeatherData($city = 'Hebron') {

    if (!defined('WEATHER_API_KEY') || WEATHER_API_KEY === 'YOUR_OPENWEATHER_API_KEY_HERE') {

        return [
            'success' => true,
            'temperature' => '21°C',
            'icon' => 'fa-cloud',
            'city' => 'الخليل',
            'description' => 'غائم جزئياً',
            'fallback' => true
        ];
    }

    $apiKey = WEATHER_API_KEY;
    $url = "https://api.openweathermap.org/data/2.5/weather?q={$city},PS&appid={$apiKey}&units=metric&lang=ar";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode === 200 && $response) {
        $data = json_decode($response, true);
        
        if ($data && $data['cod'] === 200) {
            $iconMap = [
                '01d' => 'fa-sun', '01n' => 'fa-moon',
                '02d' => 'fa-cloud-sun', '02n' => 'fa-cloud-moon',
                '03d' => 'fa-cloud', '03n' => 'fa-cloud',
                '04d' => 'fa-cloud', '04n' => 'fa-cloud',
                '09d' => 'fa-cloud-rain', '09n' => 'fa-cloud-rain',
                '10d' => 'fa-cloud-sun-rain', '10n' => 'fa-cloud-moon-rain',
                '11d' => 'fa-bolt', '11n' => 'fa-bolt',
                '13d' => 'fa-snowflake', '13n' => 'fa-snowflake',
                '50d' => 'fa-smog', '50n' => 'fa-smog'
            ];
            
            $iconCode = $data['weather'][0]['icon'];
            $iconClass = $iconMap[$iconCode] ?? 'fa-cloud';
            
            return [
                'success' => true,
                'temperature' => round($data['main']['temp']) . '°C',
                'icon' => $iconClass,
                'city' => $data['name'] ?? 'الخليل',
                'description' => $data['weather'][0]['description'] ?? '',
                'humidity' => $data['main']['humidity'] ?? 0,
                'windSpeed' => $data['wind']['speed'] ?? 0,
                'fallback' => false
            ];
        }
    }
    

    return [
        'success' => true,
        'temperature' => '21°C',
        'icon' => 'fa-cloud',
        'city' => 'الخليل',
        'description' => 'غائم جزئياً',
        'fallback' => true
    ];
}


$city = $_GET['city'] ?? 'Hebron';
$weatherData = getWeatherData($city);

echo json_encode($weatherData, JSON_UNESCAPED_UNICODE);
