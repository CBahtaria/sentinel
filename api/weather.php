<?php
/**
 * UEDF SENTINEL - Weather Integration
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// Your OpenWeatherMap API key
$api_key = 'YOUR_API_KEY_HERE'; // Replace with your actual key

// Eswatini cities
$cities = [
    'Mbabane' => ['lat' => -26.316667, 'lon' => 31.133333],
    'Manzini' => ['lat' => -26.483333, 'lon' => 31.366667],
    'Big Bend' => ['lat' => -26.816667, 'lon' => 31.933333],
    'Mhlume' => ['lat' => -26.033333, 'lon' => 31.85],
    'Nhlangano' => ['lat' => -27.116667, 'lon' => 31.2]
];

$city = $_GET['city'] ?? 'Mbabane';
$units = $_GET['units'] ?? 'metric'; // metric = Celsius, imperial = Fahrenheit

if (!isset($cities[$city])) {
    echo json_encode(['error' => 'City not found']);
    exit;
}

$lat = $cities[$city]['lat'];
$lon = $cities[$city]['lon'];

// Fetch weather from OpenWeatherMap
$url = "https://api.openweathermap.org/data/2.5/weather?lat=$lat&lon=$lon&appid=$api_key&units=$units";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$response = curl_exec($ch);
curl_close($ch);

$weather = json_decode($response, true);

if ($weather && isset($weather['main'])) {
    $result = [
        'success' => true,
        'city' => $city,
        'temperature' => round($weather['main']['temp']),
        'feels_like' => round($weather['main']['feels_like']),
        'humidity' => $weather['main']['humidity'],
        'pressure' => $weather['main']['pressure'],
        'wind_speed' => $weather['wind']['speed'],
        'wind_direction' => $weather['wind']['deg'] ?? 0,
        'conditions' => $weather['weather'][0]['description'],
        'icon' => $weather['weather'][0]['icon'],
        'clouds' => $weather['clouds']['all'],
        'timestamp' => time()
    ];
    echo json_encode($result);
} else {
    echo json_encode(['error' => 'Could not fetch weather data']);
}
?>
