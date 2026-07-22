<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UEDF SENTINEL — LCE Metrics</title>
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
            font-size: 36px;
            font-weight: bold;
            color: #00ff9d;
        }
        .kpi-unit { font-size: 14px; color: #4a5568; margin-top: 4px; }
        .section {
            background: #151f2c;
            border: 1px solid #ff006e;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
        }
        .section h2 { color: #ff006e; margin-bottom: 16px; font-size: 16px; }
        .pending-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px;
            border-bottom: 1px solid #ff006e30;
        }
        .pending-item:last-child { border-bottom: none; }
        .pending-id { font-size: 12px; color: #4a5568; font-family: monospace; }
        .btn-group { display: flex; gap: 8px; }
        .btn {
            padding: 6px 14px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 12px;
            font-family: monospace;
        }
        .btn-approve { background: #00ff9d; color: #0a0f1c; }
        .btn-approve:hover { background: #00cc7a; }
        .btn-reject { background: #ff006e; color: #0a0f1c; }
        .btn-reject:hover { background: #cc0059; }
        .status-bar {
            font-size: 11px;
            color: #4a5568;
            margin-top: 16px;
            display: flex;
            justify-content: space-between;
        }
        .status-ok { color: #00ff9d; }
        .status-err { color: #ff006e; }
        .empty-msg { color: #4a5568; font-size: 13px; padding: 12px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>LCE — Lets Connect Eswatini Metrics</h1>
        <div class="menu">
            <a href="/dashboard">Dashboard</a>
            <a href="/ecosystem">Ecosystem</a>
            <a href="/logout">Logout</a>
        </div>
    </div>

    <div class="kpi-grid">
        <div class="kpi-card">
            <div class="kpi-label">User Count</div>
            <div class="kpi-value" id="kpi-user-count">—</div>
            <div class="kpi-unit">registered users</div>
        </div>
        <div class="kpi-card">
            <div class="kpi-label">Mod Queue Depth</div>
            <div class="kpi-value" id="kpi-mod-queue">—</div>
            <div class="kpi-unit">items pending review</div>
        </div>
        <div class="kpi-card">
            <div class="kpi-label">Ban Rate</div>
            <div class="kpi-value" id="kpi-ban-rate">—</div>
            <div class="kpi-unit">% of actions resulting in ban</div>
        </div>
        <div class="kpi-card">
            <div class="kpi-label">Spam Blocked (24h)</div>
            <div class="kpi-value" id="kpi-spam-blocked">—</div>
            <div class="kpi-unit">messages intercepted</div>
        </div>
        <div class="kpi-card">
            <div class="kpi-label">Adaptive Pending</div>
            <div class="kpi-value" id="kpi-adaptive-pending">—</div>
            <div class="kpi-unit">awaiting Commander approval</div>
        </div>
    </div>

    <div class="section" id="adaptive-section">
        <h2>Adaptive Config — Pending Approvals</h2>
        <div id="adaptive-list"><span class="empty-msg">Loading…</span></div>
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

        function loadMetrics() {
            fetch('/api/v1/lce', { credentials: 'same-origin' })
                .then(function (res) {
                    var cacheHit = res.headers.get('X-Cache') === 'HIT';
                    document.getElementById('cache-status').textContent =
                        'Cache: ' + (cacheHit ? 'HIT' : 'MISS');
                    if (!res.ok) throw new Error('HTTP ' + res.status);
                    return res.json();
                })
                .then(function (data) {
                    setText('kpi-user-count', data.user_count != null ? data.user_count : '—');
                    setText('kpi-mod-queue', data.mod_queue_depth != null ? data.mod_queue_depth : '—');
                    setText('kpi-ban-rate', data.ban_rate_pct != null ? data.ban_rate_pct.toFixed(1) + '%' : '—');
                    setText('kpi-spam-blocked', data.spam_blocked_24h != null ? data.spam_blocked_24h : '—');
                    setText('kpi-adaptive-pending', data.adaptive_pending != null ? data.adaptive_pending : '—');
                    setText('last-updated', data.timestamp ? new Date(data.timestamp).toLocaleTimeString() : new Date().toLocaleTimeString());
                })
                .catch(function (err) {
                    console.error('LCE metrics fetch failed:', err);
                });
        }

        function loadAdaptivePending() {
            fetch('/api/v1/adaptive', { credentials: 'same-origin' })
                .then(function (res) {
                    if (!res.ok) throw new Error('HTTP ' + res.status);
                    return res.json();
                })
                .then(function (items) {
                    var list = document.getElementById('adaptive-list');
                    if (!Array.isArray(items) || items.length === 0) {
                        list.innerHTML = '<span class="empty-msg">No pending adaptive changes.</span>';
                        return;
                    }
                    list.innerHTML = '';
                    items.forEach(function (item) {
                        var row = document.createElement('div');
                        row.className = 'pending-item';
                        row.innerHTML =
                            '<div>' +
                            '  <div>' + escapeHtml(item.description || item.id) + '</div>' +
                            '  <div class="pending-id">' + escapeHtml(item.id) + '</div>' +
                            '</div>' +
                            '<div class="btn-group">' +
                            '  <button class="btn btn-approve" data-id="' + escapeHtml(item.id) + '">Approve</button>' +
                            '  <button class="btn btn-reject" data-id="' + escapeHtml(item.id) + '">Reject</button>' +
                            '</div>';
                        list.appendChild(row);
                    });

                    list.querySelectorAll('.btn-approve').forEach(function (btn) {
                        btn.addEventListener('click', function () { approveItem(btn.dataset.id, btn); });
                    });
                    list.querySelectorAll('.btn-reject').forEach(function (btn) {
                        btn.addEventListener('click', function () { rejectItem(btn.dataset.id, btn); });
                    });
                })
                .catch(function (err) {
                    document.getElementById('adaptive-list').innerHTML =
                        '<span class="empty-msg" style="color:#ff006e">Failed to load pending items.</span>';
                    console.error('Adaptive fetch failed:', err);
                });
        }

        function approveItem(id, btn) {
            btn.disabled = true;
            btn.textContent = '…';
            fetch('/api/v1/adaptive/approve/' + encodeURIComponent(id), {
                method: 'POST',
                credentials: 'same-origin'
            })
            .then(function (res) {
                if (!res.ok) throw new Error('HTTP ' + res.status);
                return res.json();
            })
            .then(function () {
                loadAdaptivePending();
                loadMetrics();
            })
            .catch(function (err) {
                btn.disabled = false;
                btn.textContent = 'Approve';
                alert('Approve failed: ' + err.message);
            });
        }

        function rejectItem(id, btn) {
            btn.disabled = true;
            btn.textContent = '…';
            fetch('/api/v1/adaptive/reject/' + encodeURIComponent(id), {
                method: 'POST',
                credentials: 'same-origin'
            })
            .then(function (res) {
                if (!res.ok) throw new Error('HTTP ' + res.status);
                return res.json();
            })
            .then(function () {
                loadAdaptivePending();
                loadMetrics();
            })
            .catch(function (err) {
                btn.disabled = false;
                btn.textContent = 'Reject';
                alert('Reject failed: ' + err.message);
            });
        }

        function escapeHtml(s) {
            return String(s)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;');
        }

        // Initial load
        loadMetrics();
        loadAdaptivePending();

        // Refresh metrics every 30s
        setInterval(loadMetrics, 30000);
        // Refresh pending list every 60s
        setInterval(loadAdaptivePending, 60000);
    }());
    </script>
</body>
</html>
