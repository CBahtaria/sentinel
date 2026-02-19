<?php
// Quick Actions Widget for UEDF SENTINEL
$quick_actions = [
    ['icon' => 'fa-drone', 'label' => 'LAUNCH DRONE', 'link' => '?module=drones&action=launch', 'color' => '#00ff9d'],
    ['icon' => 'fa-map', 'label' => 'VIEW MAP', 'link' => '?module=map', 'color' => '#4cc9f0'],
    ['icon' => 'fa-brain', 'label' => 'SCAN THREATS', 'link' => '?module=concurrency&action=scan', 'color' => '#ff006e'],
    ['icon' => 'fa-chart-line', 'label' => 'GENERATE REPORT', 'link' => '?module=analytics&action=report', 'color' => '#ffbe0b'],
    ['icon' => 'fa-robot', 'label' => 'AI ASSISTANT', 'link' => '?module=ai-assistant', 'color' => '#9d4edd'],
    ['icon' => 'fa-cog', 'label' => 'SYSTEM CHECK', 'link' => '?module=admin&action=diagnostic', 'color' => '#a0aec0']
];
?>
<div class="actions-widget">
    <div style="margin-bottom: 15px;">
        <span style="font-family: 'Orbitron';">QUICK COMMANDS</span>
    </div>
    
    <div class="quick-actions-grid">
        <?php foreach ($quick_actions as $action): ?>
            <a href="<?= $action['link'] ?>" class="quick-action-btn" 
               style="border: 1px solid <?= $action['color'] ?>; background: linear-gradient(135deg, <?= $action['color'] ?>10, transparent);">
                <i class="fas <?= $action['icon'] ?>" style="color: <?= $action['color'] ?>;"></i>
                <span><?= $action['label'] ?></span>
                <small style="color: <?= $action['color'] ?>;">→</small>
            </a>
        <?php endforeach; ?>
    </div>
    
    <div style="margin-top: 15px; text-align: center;">
        <span style="color: #4a5568; font-size: 0.7rem;">
            <i class="fas fa-keyboard"></i> PRESS 'A' FOR AI ASSISTANT
        </span>
    </div>
</div>

<style>
.quick-actions-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 10px;
}
.quick-action-btn {
    padding: 15px 10px;
    background: #0a0f1c;
    border-radius: 4px;
    text-decoration: none;
    color: #e0e0e0;
    text-align: center;
    transition: 0.3s;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 5px;
}
.quick-action-btn:hover {
    transform: translateY(-3px);
    box-shadow: 0 5px 15px rgba(0,255,157,0.2);
    background: #151f2c;
}
.quick-action-btn i {
    font-size: 1.5rem;
}
.quick-action-btn small {
    font-size: 0.7rem;
}
</style>
