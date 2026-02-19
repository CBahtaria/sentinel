<?php
// News/Intel Feed Widget for UEDF SENTINEL
$news_items = [
    ['time' => '10:30', 'msg' => 'Drone patrol route optimized for better coverage', 'type' => 'info'],
    ['time' => '09:45', 'msg' => 'New threat detection algorithm deployed successfully', 'type' => 'success'],
    ['time' => '08:15', 'msg' => 'System update v4.0.1 completed', 'type' => 'info'],
    ['time' => '07:30', 'msg' => 'Night surveillance report generated', 'type' => 'info'],
    ['time' => '06:00', 'msg' => 'All systems operational - green status', 'type' => 'success'],
    ['time' => '04:20', 'msg' => 'Suspicious activity detected near northern border', 'type' => 'warning'],
    ['time' => '02:15', 'msg' => 'Drone battery levels recharging', 'type' => 'info']
];

$type_colors = [
    'info' => '#4cc9f0',
    'success' => '#00ff9d',
    'warning' => '#ffbe0b',
    'alert' => '#ff006e'
];
?>
<div class="news-widget">
    <div style="display: flex; justify-content: space-between; margin-bottom: 15px;">
        <span style="font-family: 'Orbitron';">LIVE INTEL FEED</span>
        <span style="color: #ff006e; font-size: 0.8rem;">
            <i class="fas fa-circle"></i> LIVE
        </span>
    </div>
    
    <div class="news-feed">
        <?php foreach (array_slice($news_items, 0, 5) as $item): ?>
            <div class="news-item" style="border-left: 3px solid <?= $type_colors[$item['type']] ?>;">
                <div style="display: flex; justify-content: space-between; margin-bottom: 5px;">
                    <span class="news-time">[<?= $item['time'] ?>]</span>
                    <span style="color: <?= $type_colors[$item['type']] ?>; font-size: 0.7rem;">
                        <?= strtoupper($item['type']) ?>
                    </span>
                </div>
                <div style="color: #e0e0e0;"><?= $item['msg'] ?></div>
            </div>
        <?php endforeach; ?>
    </div>
    
    <div style="margin-top: 15px; animation: pulse 2s infinite;">
        <div style="display: flex; gap: 5px; justify-content: center;">
            <i class="fas fa-circle" style="color: #ff006e; font-size: 0.5rem;"></i>
            <i class="fas fa-circle" style="color: #00ff9d; font-size: 0.5rem;"></i>
            <i class="fas fa-circle" style="color: #4cc9f0; font-size: 0.5rem;"></i>
        </div>
    </div>
</div>

<style>
.news-feed {
    max-height: 250px;
    overflow-y: auto;
}
.news-item {
    background: #0a0f1c;
    padding: 12px;
    margin-bottom: 8px;
    border-radius: 4px;
}
.news-time {
    color: #00ff9d;
    font-size: 0.8rem;
    font-family: 'Share Tech Mono';
}
</style>
