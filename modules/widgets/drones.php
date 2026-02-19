<?php
// Drones Widget for UEDF SENTINEL
$status_colors = [
    'ACTIVE' => '#00ff9d',
    'STANDBY' => '#ffbe0b',
    'MAINTENANCE' => '#ff006e',
    'DEPLOYED' => '#4cc9f0'
];
?>
<div class="drone-widget">
    <div style="display: flex; justify-content: space-between; margin-bottom: 20px;">
        <span style="font-family: 'Orbitron'; color: #00ff9d;">FLEET STATUS</span>
        <span><?= $active_drones ?>/<?= $drone_count ?> ACTIVE</span>
    </div>
    
    <div class="drone-grid">
        <?php foreach ($status_colors as $status => $color): ?>
            <?php $count = $drone_status[$status] ?? rand(0, 5); ?>
            <div class="drone-status-item" style="border-left: 3px solid <?= $color ?>;">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <span style="color: <?= $color ?>;"><?= $status ?></span>
                    <span class="drone-count"><?= $count ?></span>
                </div>
                <div style="height: 4px; background: #0a0f1c; margin-top: 8px;">
                    <div style="height: 100%; width: <?= ($count / max(1, $drone_count)) * 100 ?>%; background: <?= $color ?>;"></div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    
    <div style="margin-top: 15px; display: flex; gap: 10px;">
        <div style="flex: 1; text-align: center; background: #0a0f1c; padding: 8px; border-radius: 4px;">
            <i class="fas fa-battery-full" style="color: #00ff9d;"></i>
            <span style="margin-left: 5px;">95%</span>
        </div>
        <div style="flex: 1; text-align: center; background: #0a0f1c; padding: 8px; border-radius: 4px;">
            <i class="fas fa-signal" style="color: #4cc9f0;"></i>
            <span style="margin-left: 5px;">STRONG</span>
        </div>
    </div>
</div>

<style>
.drone-status-item {
    background: #0a0f1c;
    padding: 12px;
    border-radius: 4px;
    margin-bottom: 8px;
}
</style>
