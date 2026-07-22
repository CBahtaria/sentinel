<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UEDF SENTINEL — Ecosystem Status</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            background: #0a0f1c;
            color: #00ff9d;
            font-family: 'Segoe UI', monospace;
            padding: 20px;
        }
        .header {
            background: #151f2c;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            border: 2px solid #ff006e;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .header h1 { font-size: 20px; }
        .menu { display: flex; gap: 12px; }
        .menu a {
            color: #ff006e;
            text-decoration: none;
            padding: 5px 10px;
            border: 1px solid #ff006e;
            border-radius: 5px;
            font-size: 13px;
        }
        .menu a:hover { background: #ff006e; color: #0a0f1c; }
        .kpi-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 16px;
            margin-bottom: 24px;
        }
        .kpi-card {
            background: #151f2c;
            padding: 20px;
            border-radius: 10px;
            border: 1px solid #ff006e;
        }
        .kpi-label {
            color: #ff006e;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 8px;
        }
        .kpi-value {
            font-size: 32px;
            font-weight: bold;
            color: #00ff9d;
        }
        .kpi-unit { font-size: 12px; color: #4a5568; margin-top: 4px; }
        .progress-wrap { margin-top: 10px; }
        .progress-track {
            background: #0a0f1c;
            border-radius: 4px;
            height: 8px;
            overflow: hidden;
            border: 1px solid #ff006e40;
        }
        .progress-fill {
            height: 100%;
            border-radius: 4px;
            background: #00ff9d;
            transition: width 0.4s ease;
        }
        .progress-fill.warn { background: #ffd93d; }
        .progress-fill.danger { background: #ff006e; }
        .badge {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 12px;
            font-size: 12px;
            margin-top: 8px;
        }
        .badge-on  { background: #00ff9d; color: #0a0f1c; }
        .badge-off { background: #4a5568; color: #0a0f1c; }
        .badge-fog { background: #ffd93d; color: #0a0f1c; }
        .status-bar {
            font-size: 11px;
            color: #4a5568;
            margin-top: 16px;
            display: flex;
            justify-content: space-between;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Ecosystem Status</h1>
        <div class="menu">
            <a href="/dashboard">Dashboard</a>
            <a href="/lce">LCE Metrics</a>
            <a href="/logout">Logout</a>
        </div>
    </div>

    <div class="kpi-grid">
        <div class="kpi-card">
            <div class="kpi-label">Reservoir</div>
            <div class="kpi-value" id="kpi-reservoir">—</div>
            <div class="kpi-unit">% capacity</div>
            <div class="progress-wrap">
                <div class="progress-track">
                    <div class="progress-fill" id="bar-reservoir" style="width:0%"></div>
                </div>
            </div>
        </div>
        <div class="kpi-card">
            <div class="kpi-label">Capacitor SoC</div>
            <div class="kpi-value" id="kpi-soc">—</div>
            <div class="kpi-unit">% state of charge</div>
            <div class="progress-wrap">
                <div class="progress-track">
                    <div class="progress-fill" id="bar-soc" style="width:0%"></div>
                </div>
            </div>
        </div>
        <div class="kpi-card">
            <div class="kpi-label">WLAN Nodes Online</div>
            <div class="kpi-value" id="kpi-wlan-nodes">—</div>
            <div class="kpi-unit">mesh nodes active</div>
        </div>
        <div class="kpi-card">
            <div class="kpi-label">Avg RSSI</div>
            <div class="kpi-value" id="kpi-rssi">—</div>
            <div class="kpi-unit">dBm</div>
        </div>
        <div class="kpi-card">
            <div class="kpi-label">Solar</div>
            <div class="kpi-value" id="kpi-solar">—</div>
            <div id="badge-solar"></div>
        </div>
        <div class="kpi-card">
            <div class="kpi-label">Fog Density</div>
            <div class="kpi-value" id="kpi-fog">—</div>
            <div id="badge-fog"></div>
        </div>
    </div>

    <div class="status-bar">
        <span>Last updated: <span id="last-updated">—</span></span>
        <span id="cache-status"></span>
    </div>

    <script>
    (function () {
        'use strict';

        function setText(id, val) {
            var el = document.getElementById(id);
            if (el) el.textContent = val;
        }

        function setWidth(id, pct, el) {
            var bar = document.getElementById(id);
            if (!bar) return;
            var clamped = Math.max(0, Math.min(100, pct));
            bar.style.width = clamped + '%';
            bar.className = 'progress-fill';
            if (clamped < 25) bar.classList.add('danger');
            else if (clamped < 50) bar.classList.add('warn');
        }

        function loadStatus() {
            fetch('/api/v1/ecosystem', { credentials: 'same-origin' })
                .then(function (res) {
                    var cacheHit = res.headers.get('X-Cache') === 'HIT';
                    document.getElementById('cache-status').textContent =
                        'Cache: ' + (cacheHit ? 'HIT' : 'MISS');
                    if (!res.ok) throw new Error('HTTP ' + res.status);
                    return res.json();
                })
                .then(function (data) {
                    var resPct = data.reservoir_pct != null ? parseFloat(data.reservoir_pct) : 0;
                    var socPct = data.capacitor_soc != null ? parseFloat(data.capacitor_soc) : 0;

                    setText('kpi-reservoir', resPct.toFixed(1) + '%');
                    setWidth('bar-reservoir', resPct);

                    setText('kpi-soc', socPct.toFixed(1) + '%');
                    setWidth('bar-soc', socPct);

                    setText('kpi-wlan-nodes', data.wlan_nodes != null ? data.wlan_nodes : '—');

                    var rssi = data.avg_rssi_dbm != null ? parseFloat(data.avg_rssi_dbm) : null;
                    setText('kpi-rssi', rssi !== null ? rssi.toFixed(1) : '—');

                    var solarActive = !!data.solar_active;
                    setText('kpi-solar', solarActive ? 'Active' : 'Inactive');
                    var solarBadge = document.getElementById('badge-solar');
                    if (solarBadge) {
                        solarBadge.innerHTML = '<span class="badge ' + (solarActive ? 'badge-on' : 'badge-off') + '">' +
                            (solarActive ? 'GENERATING' : 'OFFLINE') + '</span>';
                    }

                    var fog = data.fog_density ? String(data.fog_density) : 'unknown';
                    setText('kpi-fog', fog.charAt(0).toUpperCase() + fog.slice(1));
                    var fogBadge = document.getElementById('badge-fog');
                    if (fogBadge) {
                        var fogClass = fog === 'unknown' ? 'badge-off' : (fog === 'none' ? 'badge-on' : 'badge-fog');
                        fogBadge.innerHTML = '<span class="badge ' + fogClass + '">' + fog.toUpperCase() + '</span>';
                    }

                    setText('last-updated', data.timestamp ? new Date(data.timestamp).toLocaleTimeString() : new Date().toLocaleTimeString());
                })
                .catch(function (err) {
                    console.error('Ecosystem status fetch failed:', err);
                });
        }

        loadStatus();
        setInterval(loadStatus, 30000);
    }());
    </script>
</body>
</html>
