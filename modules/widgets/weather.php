<?php
// Weather Widget for UEDF SENTINEL
$weather_data = [
    'temp' => rand(18, 32),
    'condition' => ['SUNNY', 'PARTLY CLOUDY', 'CLEAR', 'LIGHT RAIN'][rand(0,3)],
    'humidity' => rand(40, 80),
    'wind' => rand(5, 25),
    'pressure' => rand(1010, 1025)
];
?>
<div class="weather-widget">
    <div style="text-align: center;">
        <i class="fas fa-<?= $weather_data['condition'] == 'LIGHT RAIN' ? 'cloud-rain' : 'sun' ?>" 
           style="font-size: 3rem; color: #00ff9d; margin-bottom: 10px;"></i>
        <div class="weather-temp"><?= $weather_data['temp'] ?>°C</div>
        <div style="color: #ff006e; font-size: 1.2rem; margin: 10px 0;"><?= $weather_data['condition'] ?></div>
        <div class="weather-details">
            <div class="weather-detail">
                <i class="fas fa-tint" style="color: #4cc9f0;"></i>
                <div><?= $weather_data['humidity'] ?>%</div>
                <small>HUMIDITY</small>
            </div>
            <div class="weather-detail">
                <i class="fas fa-wind" style="color: #a0aec0;"></i>
                <div><?= $weather_data['wind'] ?> km/h</div>
                <small>WIND</small>
            </div>
        </div>
        <div style="margin-top: 15px; color: #4a5568; font-size: 0.8rem;">
            <i class="fas fa-map-marker-alt"></i> MBABANE, ESWATINI
        </div>
    </div>
</div>

<style>
.weather-details {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 10px;
    margin-top: 15px;
}
.weather-detail {
    background: #0a0f1c;
    padding: 10px;
    border-radius: 4px;
    text-align: center;
    border: 1px solid #ff006e20;
}
.weather-detail small {
    color: #4a5568;
    font-size: 0.7rem;
}
</style>
