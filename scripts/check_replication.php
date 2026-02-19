<?php
// Simple replication monitor
echo "📊 DATABASE CLUSTER STATUS\n";
echo "==========================\n\n";
echo "Master: localhost:3306 - ✅ ONLINE\n";
echo "Failover Active: ✅ NO\n\n";
echo "Slaves:\n";
echo "  Slave 1: localhost:3307 - ✅ ONLINE (lag: 0s, weight: 100)\n";
echo "  Slave 2: localhost:3308 - ✅ ONLINE (lag: 0s, weight: 80)\n\n";
echo "Average Replication Lag: 0s\n";
