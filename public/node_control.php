<?php
require_once 'session.php';
requireLogin();

// Get user info
$username = $_SESSION['username'] ?? 'Unknown';
$role = $_SESSION['user_role'] ?? $_SESSION['role'] ?? 'viewer';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BARTARIAN DEFENCE - NODE COMMAND</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;500;600;700;800;900&family=Share+Tech+Mono&display=swap" rel="stylesheet">
    <style>
        /* (keep all existing styles from previous version) */
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Share Tech Mono', monospace; }
        body {
            background: #0a0f1c;
            color: #e0e0e0;
            padding: 20px;
            overflow: hidden;
        }
        .header {
            background: #151f2c;
            border: 2px solid #00ff9d;
            padding: 20px;
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-radius: 8px;
            position: relative;
            z-index: 10;
        }
        .header h1 {
            font-family: 'Orbitron', sans-serif;
            color: #00ff9d;
        }
        .back-btn {
            padding: 10px 20px;
            background: transparent;
            border: 1px solid #ff006e;
            color: #ff006e;
            text-decoration: none;
            border-radius: 4px;
            transition: all 0.3s;
        }
        .back-btn:hover {
            background: #ff006e;
            color: #0a0f1c;
        }
        .main-container {
            display: grid;
            grid-template-columns: 300px 1fr;
            gap: 20px;
            height: calc(100vh - 120px);
        }
        .sidebar {
            background: #151f2c;
            border: 2px solid #00ff9d;
            border-radius: 8px;
            padding: 20px;
            overflow-y: auto;
        }
        .sidebar h3 {
            color: #ff006e;
            margin-bottom: 15px;
            font-family: 'Orbitron', sans-serif;
        }
        .node-list {
            list-style: none;
        }
        .node-item {
            background: #0a0f1c;
            border: 1px solid #ff006e;
            border-radius: 4px;
            padding: 12px;
            margin-bottom: 10px;
            cursor: pointer;
            transition: all 0.3s;
            position: relative;
        }
        .node-item:hover {
            border-color: #00ff9d;
            transform: translateX(5px);
        }
        .node-item.selected {
            border: 2px solid #00ff9d;
            background: #1a2a3a;
        }
        .node-name {
            color: #00ff9d;
            font-weight: bold;
        }
        .node-status {
            display: inline-block;
            width: 8px;
            height: 8px;
            border-radius: 50%;
            margin-right: 5px;
        }
        .status-online { background: #00ff9d; }
        .status-offline { background: #ff006e; }
        .status-warning { background: #ffae00; }
        .node-details {
            margin-top: 20px;
            background: #0a0f1c;
            border: 1px solid #ff006e;
            border-radius: 4px;
            padding: 15px;
        }
        .detail-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #ff006e40;
        }
        .detail-label {
            color: #00ff9d;
        }
        .detail-value {
            color: #ff006e;
        }
        .graph-container {
            background: #151f2c;
            border: 2px solid #00ff9d;
            border-radius: 8px;
            padding: 20px;
            position: relative;
            overflow: hidden;
        }
        #nodeGraph {
            width: 100%;
            height: 100%;
            background: #0a0f1c;
            cursor: grab;
        }
        #nodeGraph:active {
            cursor: grabbing;
        }
        .zoom-controls {
            position: absolute;
            bottom: 20px;
            right: 20px;
            display: flex;
            gap: 10px;
            z-index: 20;
        }
        .zoom-btn {
            width: 40px;
            height: 40px;
            background: #151f2c;
            border: 2px solid #ff006e;
            color: #00ff9d;
            border-radius: 50%;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            transition: all 0.3s;
        }
        .zoom-btn:hover {
            background: #ff006e;
            color: #0a0f1c;
            transform: scale(1.1);
        }
        .zoom-level {
            position: absolute;
            top: 20px;
            right: 20px;
            background: #151f2c;
            border: 1px solid #ff006e;
            padding: 5px 10px;
            border-radius: 4px;
            color: #00ff9d;
            font-size: 12px;
            z-index: 20;
        }
        .node-tooltip {
            position: absolute;
            background: #151f2c;
            border: 2px solid #ff006e;
            padding: 10px;
            border-radius: 4px;
            pointer-events: none;
            z-index: 100;
            display: none;
        }
        .search-box {
            margin-bottom: 15px;
            width: 100%;
            padding: 8px;
            background: #0a0f1c;
            border: 1px solid #ff006e;
            color: #00ff9d;
            border-radius: 4px;
        }
        .search-box::placeholder {
            color: #ff006e80;
        }
        .stats-panel {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 10px;
            margin-top: 20px;
        }
        .stat-card {
            background: #0a0f1c;
            border: 1px solid #ff006e;
            border-radius: 4px;
            padding: 10px;
            text-align: center;
        }
        .stat-value {
            color: #00ff9d;
            font-size: 20px;
            font-weight: bold;
        }
        .stat-label {
            color: #ff006e;
            font-size: 10px;
        }
        .refresh-btn {
            position: absolute;
            top: 20px;
            left: 20px;
            background: #151f2c;
            border: 2px solid #ff006e;
            color: #00ff9d;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 20;
            transition: all 0.3s;
        }
        .refresh-btn:hover {
            background: #ff006e;
            color: #0a0f1c;
            transform: rotate(180deg);
        }
        .loading-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(10, 15, 28, 0.8);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1000;
            display: none;
        }
        .loading-spinner {
            border: 4px solid #ff006e;
            border-top: 4px solid #00ff9d;
            border-radius: 50%;
            width: 50px;
            height: 50px;
            animation: spin 1s linear infinite;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        @keyframes pulse {
            0% { box-shadow: 0 0 0 0 rgba(255, 0, 110, 0.7); }
            70% { box-shadow: 0 0 0 10px rgba(255, 0, 110, 0); }
            100% { box-shadow: 0 0 0 0 rgba(255, 0, 110, 0); }
        }
        .pulse {
            animation: pulse 2s infinite;
        }
    </style>
<link rel="stylesheet" href="assets/css/cyber-framework.css">
<script src="assets/js/cyber-framework.js" defer></script>
</head>
<body>
    <div class="cyber-header">
        <h1><i class="fas fa-project-diagram"></i> NODE COMMAND CENTER</h1>
        <div>
            <span style="color: #00ff9d; margin-right: 20px;">Welcome, <?= htmlspecialchars($username) ?> (<?= htmlspecialchars($role) ?>)</span>
            <a href="dashboard.php" class="back-btn"><i class="fas fa-arrow-left"></i> BACK</a>
        </div>
    </div>

    <div class="main-container">
        <div class="sidebar">
            <h3><i class="fas fa-search"></i> NODE DIRECTORY</h3>
            <input type="text" class="search-box" id="searchNodes" placeholder="Search nodes...">
            
            <div class="stats-panel">
                <div class="stat-card">
                    <div class="stat-value" id="totalNodes">0</div>
                    <div class="stat-label">TOTAL</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value" id="onlineNodes">0</div>
                    <div class="stat-label">ONLINE</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value" id="warningNodes">0</div>
                    <div class="stat-label">WARNING</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value" id="offlineNodes">0</div>
                    <div class="stat-label">OFFLINE</div>
                </div>
            </div>

            <div class="node-list" id="nodeList"></div>

            <div class="node-details" id="nodeDetails">
                <h3 style="color: #ff006e; margin-bottom: 10px;">NODE DETAILS</h3>
                <div class="detail-row">
                    <span class="detail-label">ID:</span>
                    <span class="detail-value" id="detail-id">-</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Name:</span>
                    <span class="detail-value" id="detail-name">-</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Status:</span>
                    <span class="detail-value" id="detail-status">-</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Type:</span>
                    <span class="detail-value" id="detail-type">-</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">CPU:</span>
                    <span class="detail-value" id="detail-cpu">-</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Memory:</span>
                    <span class="detail-value" id="detail-memory">-</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Connections:</span>
                    <span class="detail-value" id="detail-connections">-</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Last Seen:</span>
                    <span class="detail-value" id="detail-lastSeen">-</span>
                </div>
            </div>
        </div>

        <div class="graph-container">
            <div class="refresh-btn" id="refreshBtn" title="Refresh Data">
                <i class="fas fa-sync-alt"></i>
            </div>
            <canvas id="nodeGraph"></canvas>
            <div class="zoom-level" id="zoomLevel">100%</div>
            <div class="zoom-controls">
                <div class="zoom-btn" id="zoomIn"><i class="fas fa-plus"></i></div>
                <div class="zoom-btn" id="zoomOut"><i class="fas fa-minus"></i></div>
                <div class="zoom-btn" id="resetZoom"><i class="fas fa-expand"></i></div>
            </div>
            <div class="node-tooltip" id="nodeTooltip"></div>
            <div class="loading-overlay" id="loadingOverlay">
                <div class="loading-spinner"></div>
            </div>
        </div>
    </div>

    <script>
        // Sample fallback nodes
        const sampleNodes = [
            { id: 1, name: 'CMD-NODE', type: 'Command', status: 'online', cpu: 45, memory: 62, connections: [2, 3, 4, 5], x: 400, y: 300 },
            { id: 2, name: 'DRONE-CTRL', type: 'Control', status: 'online', cpu: 32, memory: 45, connections: [1, 6, 7], x: 600, y: 200 },
            { id: 3, name: 'THREAT-DB', type: 'Database', status: 'online', cpu: 78, memory: 85, connections: [1, 8], x: 200, y: 200 },
            { id: 4, name: 'SURVEILLANCE', type: 'Sensor', status: 'warning', cpu: 92, memory: 76, connections: [1, 9, 10], x: 300, y: 450 },
            { id: 5, name: 'COMM-LINK', type: 'Communication', status: 'online', cpu: 23, memory: 34, connections: [1, 11], x: 500, y: 400 },
            { id: 6, name: 'EAGLE-1', type: 'Drone', status: 'online', cpu: 56, memory: 43, connections: [2], x: 700, y: 100 },
            { id: 7, name: 'HAWK-2', type: 'Drone', status: 'online', cpu: 61, memory: 52, connections: [2], x: 700, y: 300 },
            { id: 8, name: 'THREAT-ANALYZER', type: 'Analysis', status: 'online', cpu: 45, memory: 38, connections: [3], x: 100, y: 100 },
            { id: 9, name: 'SECTOR-7-CAM', type: 'Camera', status: 'offline', cpu: 0, memory: 0, connections: [4], x: 250, y: 550 },
            { id: 10, name: 'SECTOR-9-RADAR', type: 'Radar', status: 'warning', cpu: 88, memory: 91, connections: [4], x: 350, y: 550 },
            { id: 11, name: 'SAT-LINK', type: 'Satellite', status: 'online', cpu: 34, memory: 45, connections: [5], x: 550, y: 500 }
        ];

        // Graph state
        let scale = 1;
        let offsetX = 0;
        let offsetY = 0;
        let isDragging = false;
        let lastX, lastY;
        let selectedNode = null;
        let hoveredNode = null;
        let nodes = [];

        const canvas = document.getElementById('nodeGraph');
        const ctx = canvas.getContext('2d');
        const tooltip = document.getElementById('nodeTooltip');
        const zoomLevel = document.getElementById('zoomLevel');
        const loadingOverlay = document.getElementById('loadingOverlay');

        // ============================================
        // DATABASE FUNCTIONS
        // ============================================

        /**
         * Fetch real nodes from database
         * This is the main function you requested
         */
        async function loadNodesFromDB() {
            try {
                // Show loading indicator
                loadingOverlay.style.display = 'flex';
                
                // Fetch from API endpoint
                const response = await fetch('api/nodes.php');
                
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                const data = await response.json();
                
                // Check if we got valid node data
                if (data && Array.isArray(data)) {
                    return data;
                } else if (data && data.nodes && Array.isArray(data.nodes)) {
                    return data.nodes;
                } else {
                    console.warn('Unexpected API response format, using sample data');
                    return sampleNodes;
                }
            } catch (error) {
                console.error('Failed to load nodes from database:', error);
                
                // Show error notification (could be enhanced)
                alert('Database connection failed. Using sample data.');
                
                return sampleNodes; // Fallback to sample data
            } finally {
                // Hide loading indicator
                loadingOverlay.style.display = 'none';
            }
        }

        /**
         * Fetch node details from database
         */
        async function loadNodeDetails(nodeId) {
            try {
                const response = await fetch(`api.php?endpoint=nodes/${nodeId}`);
                const data = await response.json();
                return data;
            } catch (error) {
                console.error('Failed to load node details:', error);
                return null;
            }
        }

        /**
         * Fetch node connections from database
         */
        async function loadNodeConnections(nodeId) {
            try {
                const response = await fetch(`api.php?endpoint=nodes/${nodeId}/connections`);
                const data = await response.json();
                return data;
            } catch (error) {
                console.error('Failed to load node connections:', error);
                return [];
            }
        }

        /**
         * Update node status in database
         */
        async function updateNodeStatus(nodeId, newStatus) {
            try {
                const response = await fetch(`api.php?endpoint=nodes/${nodeId}/status`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ status: newStatus })
                });
                return await response.json();
            } catch (error) {
                console.error('Failed to update node status:', error);
                return null;
            }
        }

        /**
         * Refresh data from database
         */
        async function refreshData() {
            nodes = await loadNodesFromDB();
            
            // Generate positions if not present
            nodes = nodes.map((node, index) => {
                if (!node.x || !node.y) {
                    // Auto-position nodes in a circle pattern
                    const angle = (index / nodes.length) * 2 * Math.PI;
                    node.x = 400 + Math.cos(angle) * 250;
                    node.y = 300 + Math.sin(angle) * 200;
                }
                return node;
            });
            
            updateNodeList();
            updateStats();
            drawGraph();
        }

        // ============================================
        // UI FUNCTIONS (keep all existing functions)
        // ============================================

        // Initialize canvas
        function resizeCanvas() {
            const container = canvas.parentElement;
            canvas.width = container.clientWidth;
            canvas.height = container.clientHeight;
        }

        // Update node list
        function updateNodeList(filterText = '') {
            const list = document.getElementById('nodeList');
            const filtered = nodes.filter(node => 
                node.name.toLowerCase().includes(filterText.toLowerCase()) ||
                (node.type || '').toLowerCase().includes(filterText.toLowerCase())
            );
            
            list.innerHTML = filtered.map(node => `
                <div class="node-item ${selectedNode?.id === node.id ? 'selected' : ''}" 
                     onclick="selectNode(${node.id})"
                     ondblclick="centerOnNode(${node.id})">
                    <span class="node-status status-${node.status || 'offline'}"></span>
                    <span class="node-name">${node.name}</span>
                    <div style="font-size: 10px; color: #888; margin-top: 5px;">
                        ${node.type || 'Unknown'} | CPU: ${node.cpu || 0}%
                    </div>
                </div>
            `).join('');
        }

        // Update statistics
        function updateStats() {
            const total = nodes.length;
            const online = nodes.filter(n => n.status === 'online').length;
            const warning = nodes.filter(n => n.status === 'warning').length;
            const offline = nodes.filter(n => n.status === 'offline').length;
            
            document.getElementById('totalNodes').textContent = total;
            document.getElementById('onlineNodes').textContent = online;
            document.getElementById('warningNodes').textContent = warning;
            document.getElementById('offlineNodes').textContent = offline;
        }

        // Select node
        function selectNode(id) {
            selectedNode = nodes.find(n => n.id === id);
            updateNodeList(document.getElementById('searchNodes').value);
            updateNodeDetails();
            drawGraph();
        }

        // Update node details panel
        function updateNodeDetails() {
            if (!selectedNode) return;
            
            document.getElementById('detail-id').textContent = selectedNode.id;
            document.getElementById('detail-name').textContent = selectedNode.name;
            document.getElementById('detail-status').textContent = (selectedNode.status || 'unknown').toUpperCase();
            document.getElementById('detail-type').textContent = selectedNode.type || 'Unknown';
            document.getElementById('detail-cpu').textContent = (selectedNode.cpu || 0) + '%';
            document.getElementById('detail-memory').textContent = (selectedNode.memory || 0) + '%';
            document.getElementById('detail-connections').textContent = (selectedNode.connections || []).length;
            document.getElementById('detail-lastSeen').textContent = new Date().toLocaleTimeString();
        }

        // Center on specific node
        function centerOnNode(id) {
            const node = nodes.find(n => n.id === id);
            if (!node) return;
            
            const centerX = canvas.width / 2;
            const centerY = canvas.height / 2;
            
            offsetX = centerX - node.x * scale;
            offsetY = centerY - node.y * scale;
            
            drawGraph();
        }

        // Zoom function
        function zoom(delta) {
            const oldScale = scale;
            scale = Math.max(0.5, Math.min(3, scale + delta));
            
            const centerX = canvas.width / 2;
            const centerY = canvas.height / 2;
            
            offsetX = centerX - (centerX - offsetX) * (scale / oldScale);
            offsetY = centerY - (centerY - offsetY) * (scale / oldScale);
            
            zoomLevel.textContent = Math.round(scale * 100) + '%';
            drawGraph();
        }

        // Reset view
        function resetView() {
            scale = 1;
            offsetX = 50;
            offsetY = 50;
            zoomLevel.textContent = '100%';
            drawGraph();
        }

        // Mouse wheel zoom
        function onWheel(e) {
            e.preventDefault();
            const delta = e.deltaY > 0 ? -0.1 : 0.1;
            zoom(delta);
        }

        // Drag start
        function startDrag(e) {
            isDragging = true;
            lastX = e.clientX;
            lastY = e.clientY;
            canvas.style.cursor = 'grabbing';
        }

        // Drag move
        function onMouseMove(e) {
            const rect = canvas.getBoundingClientRect();
            const mouseX = e.clientX - rect.left;
            const mouseY = e.clientY - rect.top;
            
            // Check for node hover
            hoveredNode = null;
            for (const node of nodes) {
                const screenX = node.x * scale + offsetX;
                const screenY = node.y * scale + offsetY;
                const dist = Math.hypot(mouseX - screenX, mouseY - screenY);
                
                if (dist < 20) {
                    hoveredNode = node;
                    break;
                }
            }
            
            // Show tooltip
            if (hoveredNode) {
                tooltip.style.display = 'block';
                tooltip.style.left = (mouseX + 20) + 'px';
                tooltip.style.top = (mouseY + 20) + 'px';
                tooltip.innerHTML = `
                    <div style="color: #00ff9d;">${hoveredNode.name}</div>
                    <div style="color: #ff006e; font-size: 10px;">${hoveredNode.type || 'Node'}</div>
                    <div style="font-size: 10px;">CPU: ${hoveredNode.cpu || 0}% | MEM: ${hoveredNode.memory || 0}%</div>
                    <div style="font-size: 10px;">Status: ${hoveredNode.status || 'unknown'}</div>
                `;
            } else {
                tooltip.style.display = 'none';
            }
            
            if (isDragging) {
                const dx = e.clientX - lastX;
                const dy = e.clientY - lastY;
                offsetX += dx;
                offsetY += dy;
                lastX = e.clientX;
                lastY = e.clientY;
                drawGraph();
            }
        }

        // Drag end
        function endDrag() {
            isDragging = false;
            canvas.style.cursor = 'grab';
        }

        // Click handler
        function onClick(e) {
            const rect = canvas.getBoundingClientRect();
            const mouseX = e.clientX - rect.left;
            const mouseY = e.clientY - rect.top;
            
            for (const node of nodes) {
                const screenX = node.x * scale + offsetX;
                const screenY = node.y * scale + offsetY;
                const dist = Math.hypot(mouseX - screenX, mouseY - screenY);
                
                if (dist < 20) {
                    selectNode(node.id);
                    break;
                }
            }
        }

        // Draw the graph
        function drawGraph() {
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            
            // Draw grid
            ctx.strokeStyle = '#ff006e20';
            ctx.lineWidth = 1;
            
            const gridSize = 50 * scale;
            const startX = offsetX % gridSize;
            const startY = offsetY % gridSize;
            
            for (let x = startX; x < canvas.width; x += gridSize) {
                ctx.beginPath();
                ctx.moveTo(x, 0);
                ctx.lineTo(x, canvas.height);
                ctx.stroke();
            }
            
            for (let y = startY; y < canvas.height; y += gridSize) {
                ctx.beginPath();
                ctx.moveTo(0, y);
                ctx.lineTo(canvas.width, y);
                ctx.stroke();
            }
            
            // Draw connections
            ctx.strokeStyle = '#ff006e60';
            ctx.lineWidth = 2;
            
            for (const node of nodes) {
                if (!node.connections) continue;
                const x1 = node.x * scale + offsetX;
                const y1 = node.y * scale + offsetY;
                
                for (const connId of node.connections) {
                    const target = nodes.find(n => n.id === connId);
                    if (!target) continue;
                    
                    const x2 = target.x * scale + offsetX;
                    const y2 = target.y * scale + offsetY;
                    
                    ctx.beginPath();
                    ctx.moveTo(x1, y1);
                    ctx.lineTo(x2, y2);
                    ctx.stroke();
                }
            }
            
            // Draw nodes
            for (const node of nodes) {
                const x = node.x * scale + offsetX;
                const y = node.y * scale + offsetY;
                
                if (x < -50 || x > canvas.width + 50 || y < -50 || y > canvas.height + 50) continue;
                
                ctx.shadowColor = node.status === 'online' ? '#00ff9d' : 
                                 node.status === 'warning' ? '#ffae00' : '#ff006e';
                ctx.shadowBlur = node.id === selectedNode?.id ? 20 : 10;
                
                ctx.beginPath();
                ctx.arc(x, y, node.id === selectedNode?.id ? 15 : 10, 0, 2 * Math.PI);
                ctx.fillStyle = node.status === 'online' ? '#00ff9d' : 
                               node.status === 'warning' ? '#ffae00' : '#ff006e';
                ctx.fill();
                
                if (node.id === selectedNode?.id) {
                    ctx.shadowBlur = 30;
                    ctx.beginPath();
                    ctx.arc(x, y, 8, 0, 2 * Math.PI);
                    ctx.fillStyle = '#0a0f1c';
                    ctx.fill();
                }
                
                ctx.shadowBlur = 0;
                ctx.font = `${12 * scale}px 'Share Tech Mono'`;
                ctx.fillStyle = '#fff';
                ctx.textAlign = 'center';
                ctx.fillText(node.name, x, y - 20 * scale);
                
                ctx.font = `${10 * scale}px 'Share Tech Mono'`;
                ctx.fillStyle = '#ff006e';
                ctx.fillText(`${node.cpu || 0}%`, x, y + 25 * scale);
            }
            
            ctx.shadowBlur = 0;
        }

        // Filter nodes
        function filterNodes(e) {
            updateNodeList(e.target.value);
        }

        // Initialize everything
        async function init() {
            resizeCanvas();
            
            // Load nodes from database
            nodes = await loadNodesFromDB();
            
            // Auto-position nodes if needed
            nodes = nodes.map((node, index) => {
                if (!node.x || !node.y) {
                    const angle = (index / nodes.length) * 2 * Math.PI;
                    node.x = 400 + Math.cos(angle) * 250;
                    node.y = 300 + Math.sin(angle) * 200;
                }
                return node;
            });
            
            updateNodeList();
            updateStats();
            resetView();
            
            // Event listeners
            canvas.addEventListener('mousedown', startDrag);
            canvas.addEventListener('mousemove', onMouseMove);
            canvas.addEventListener('mouseup', endDrag);
            canvas.addEventListener('mouseleave', endDrag);
            canvas.addEventListener('wheel', onWheel);
            canvas.addEventListener('click', onClick);
            
            document.getElementById('zoomIn').addEventListener('click', () => zoom(0.2));
            document.getElementById('zoomOut').addEventListener('click', () => zoom(-0.2));
            document.getElementById('resetZoom').addEventListener('click', resetView);
            document.getElementById('refreshBtn').addEventListener('click', refreshData);
            document.getElementById('searchNodes').addEventListener('input', filterNodes);
            
            window.addEventListener('resize', () => {
                resizeCanvas();
                drawGraph();
            });
        }

        // Start the application
        init();
    </script>
</body>
</html>




