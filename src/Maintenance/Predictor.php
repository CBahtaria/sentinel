<?php
/**
 * Predictive Maintenance Demo
 */
class MaintenancePredictor {
    public function analyze($drone) {
        $health = 100;
        $health -= (100 - $drone['battery']) * 0.3;
        if ($drone['status'] == 'critical') $health -= 30;
        if ($drone['status'] == 'warning') $health -= 15;
        return max(0, min(100, round($health)));
    }
}
?>
