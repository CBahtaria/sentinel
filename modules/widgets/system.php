<?php
// System Health Widget for UEDF SENTINEL
$system_metrics = [
    'CPU' => rand(20, 60),
    'MEMORY' => rand(30, 70),
    'DISK' => rand(40, 80),
    'NETWORK' => rand(10, 40),
    'UPTIME' => rand(1, 30) . ' days',
    'TEMP' => rand(45, 65) . '°C'
];
?>
<div class="system-widget">
    <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 15px; margin-bottom: 20px;">
        <div style="text-align: center; background: #0a0f1c; padding: 10px; border-radius: 4px;">
            <i class="fas fa-microchip" style="color: #ff006e;"></i>
            <div style="font-size: 1.2rem; color: #00ff9d;"><?= $system_metrics['CPU'] ?>%</div>
            <small>CPU</small>
        </div>
        <div style="text-align: center; background: #0a0f1c; padding: 10px; border-radius: 4px;">
            <i class="fas fa-memory" style="color: #4cc9f0;"></i>
            <div style="font-size: 1.2rem; color: #00ff9d;"><?= $system_metrics['MEMORY'] ?>%</div>
            <small>RAM</small>
        </div>
    </div>
    
    <div style="margin-bottom: 15px;">
        <div style="display: flex; justify-content: space-between; margin-bottom: 5px;">
            <span>DISK USAGE</span>
            <span style="color: <?= $system_metrics['DISK'] > 80 ? '#ff006e' : '#00ff9d' ?>">
                <?= $system_metrics['DISK'] ?>%
            </span>
        </div>
        <div class="progress-bar">
            <div class="progress-fill" style="width: <?= $system_metrics['DISK'] ?>%;"></div>
        </div>
    </div>
    
    <div style="margin-bottom: 15px;">
        <div style="display: flex; justify-content: space-between; margin-bottom: 5px;">
            <span>NETWORK</span>
            <span style="color: #00ff9d;"><?= $system_metrics['NETWORK'] ?> Mbps</span>
        </div>
        <div class="progress-bar">
            <div class="progress-fill" style="width: <?= ($system_metrics['NETWORK'] / 100) * 100 ?>%; background: linear-gradient(90deg, #4cc9f0, #00ff9d);"></div>
        </div>
    </div>
    
    <div style="display: flex; justify-content: space-between; color: #4a5568; font-size: 0.8rem; margin-top: 10px;">
        <span><i class="fas fa-clock"></i> UPTIME: <?= $system_metrics['UPTIME'] ?></span>
        <span><i class="fas fa-thermometer-half"></i> <?= $system_metrics['TEMP'] ?></span>
    </div>
</div>
