<?php
// Threats Widget for UEDF SENTINEL
$threat_types = ['CYBER ATTACK', 'DRONE INTRUSION', 'UNAUTHORIZED ACCESS', 'SUSPICIOUS ACTIVITY', 'BORDER CROSSING'];
$severities = ['CRITICAL', 'HIGH', 'MEDIUM', 'LOW'];
$display_threats = [];

for ($i = 0; $i < min(5, $threat_count); $i++) {
    $display_threats[] = [
        'name' => $threat_types[array_rand($threat_types)],
        'severity' => $severities[array_rand($severities)],
        'time' => rand(1, 60) . ' min ago'
    ];
}
?>
<div class="threat-widget">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
        <span style="font-size: 2rem; color: #ff006e; font-family: 'Orbitron';"><?= $threat_count ?></span>
        <span style="color: #a0aec0;">ACTIVE THREATS</span>
        <?php if ($critical_threats > 0): ?>
            <span style="background: #ff006e; color: white; padding: 3px 8px; border-radius: 4px;">
                <?= $critical_threats ?> CRITICAL
            </span>
        <?php endif; ?>
    </div>
    
    <div class="threat-list">
        <?php if (!empty($display_threats)): ?>
            <?php foreach ($display_threats as $threat): ?>
                <div class="threat-item">
                    <div>
                        <div style="font-weight: bold;"><?= htmlspecialchars($threat['name']) ?></div>
                        <small style="color: #4a5568;"><?= $threat['time'] ?></small>
                    </div>
                    <span class="threat-severity severity-<?= strtolower($threat['severity']) ?>">
                        <?= $threat['severity'] ?>
                    </span>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div style="text-align: center; padding: 30px; color: #00ff9d;">
                <i class="fas fa-check-circle" style="font-size: 2rem; margin-bottom: 10px;"></i>
                <div>NO ACTIVE THREATS</div>
                <small style="color: #4a5568;">ALL SYSTEMS NOMINAL</small>
            </div>
        <?php endif; ?>
    </div>
    
    <div style="margin-top: 15px; text-align: right;">
        <a href="?module=concurrency" style="color: #00ff9d; text-decoration: none; font-size: 0.8rem;">
            VIEW ALL <i class="fas fa-arrow-right"></i>
        </a>
    </div>
</div>
