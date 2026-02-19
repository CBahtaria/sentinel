/**
 * UEDF SENTINEL v4.0 - WebSocket Client
 * Real-time communication with server
 */

class SentinelWebSocket {
    constructor(options = {}) {
        this.serverUrl = options.serverUrl || 'ws://localhost:8081';
        this.userId = options.userId || null;
        this.userRole = options.userRole || 'viewer';
        this.token = options.token || 'sentinel-websocket-token';
        this.reconnectAttempts = 0;
        this.maxReconnectAttempts = 10;
        this.reconnectDelay = 3000;
        this.subscriptions = new Set();
        this.callbacks = {
            onOpen: [],
            onMessage: [],
            onClose: [],
            onError: [],
            onDroneUpdate: [],
            onThreatUpdate: [],
            onNotification: [],
            onStatsUpdate: []
        };
        
        this.connect();
    }
    
    connect() {
        console.log('🔌 Connecting to Sentinel WebSocket server...');
        
        try {
            this.ws = new WebSocket(this.serverUrl);
            
            this.ws.onopen = (event) => {
                console.log('✅ Connected to WebSocket server');
                this.reconnectAttempts = 0;
                
                // Authenticate
                this.send({
                    type: 'auth',
                    payload: {
                        user_id: this.userId,
                        token: this.token,
                        role: this.userRole
                    }
                });
                
                // Send ping every 30 seconds to keep connection alive
                this.keepAliveInterval = setInterval(() => {
                    this.ping();
                }, 30000);
                
                this.triggerCallbacks('onOpen', event);
            };
            
            this.ws.onmessage = (event) => {
                try {
                    const data = JSON.parse(event.data);
                    this.handleMessage(data);
                } catch (e) {
                    console.error('Error parsing message:', e);
                }
            };
            
            this.ws.onclose = (event) => {
                console.log('❌ WebSocket connection closed');
                clearInterval(this.keepAliveInterval);
                
                this.triggerCallbacks('onClose', event);
                
                // Attempt to reconnect
                if (this.reconnectAttempts < this.maxReconnectAttempts) {
                    this.reconnectAttempts++;
                    console.log(`🔄 Reconnecting (attempt ${this.reconnectAttempts}/${this.maxReconnectAttempts})...`);
                    setTimeout(() => this.connect(), this.reconnectDelay);
                }
            };
            
            this.ws.onerror = (error) => {
                console.error('WebSocket error:', error);
                this.triggerCallbacks('onError', error);
            };
            
        } catch (error) {
            console.error('Failed to connect:', error);
        }
    }
    
    handleMessage(data) {
        // console.log('📨 Received:', data.type);
        
        this.triggerCallbacks('onMessage', data);
        
        switch(data.type) {
            case 'welcome':
                console.log('👋', data.message);
                break;
                
            case 'auth_success':
                console.log('🔐 Authentication successful');
                // Resubscribe to channels
                this.resubscribe();
                break;
                
            case 'auth_failed':
                console.error('❌ Authentication failed');
                break;
                
            case 'drone_data':
            case 'drone_updates':
                this.triggerCallbacks('onDroneUpdate', data.data || data);
                break;
                
            case 'threat_data':
            case 'threat_updated':
            case 'new_threat':
                this.triggerCallbacks('onThreatUpdate', data.data || data);
                break;
                
            case 'system_stats':
                this.triggerCallbacks('onStatsUpdate', data.data);
                break;
                
            case 'notification':
                this.triggerCallbacks('onNotification', data);
                this.showNotification(data.title, data.message);
                break;
                
            case 'pong':
                // console.log('🏓 Pong received');
                break;
                
            case 'error':
                console.error('Server error:', data.message);
                break;
        }
    }
    
    send(data) {
        if (this.ws && this.ws.readyState === WebSocket.OPEN) {
            this.ws.send(JSON.stringify(data));
        } else {
            console.warn('WebSocket not connected, message queued');
            // Queue message for retry
            setTimeout(() => this.send(data), 1000);
        }
    }
    
    ping() {
        this.send({
            type: 'ping',
            timestamp: Date.now()
        });
    }
    
    subscribe(channels) {
        if (!Array.isArray(channels)) {
            channels = [channels];
        }
        
        channels.forEach(channel => this.subscriptions.add(channel));
        
        this.send({
            type: 'subscribe',
            payload: { channels }
        });
    }
    
    unsubscribe(channels) {
        if (!Array.isArray(channels)) {
            channels = [channels];
        }
        
        channels.forEach(channel => this.subscriptions.delete(channel));
        
        this.send({
            type: 'unsubscribe',
            payload: { channels }
        });
    }
    
    resubscribe() {
        if (this.subscriptions.size > 0) {
            this.subscribe(Array.from(this.subscriptions));
        }
    }
    
    sendDroneCommand(droneId, command) {
        this.send({
            type: 'drone_command',
            payload: {
                drone_id: droneId,
                command: command
            }
        });
    }
    
    updateThreatStatus(threatId, status) {
        this.send({
            type: 'threat_update',
            payload: {
                threat_id: threatId,
                status: status
            }
        });
    }
    
    requestData(dataType = 'all') {
        this.send({
            type: 'request_data',
            payload: { data_type: dataType }
        });
    }
    
    on(event, callback) {
        if (this.callbacks[event]) {
            this.callbacks[event].push(callback);
        }
    }
    
    triggerCallbacks(event, data) {
        if (this.callbacks[event]) {
            this.callbacks[event].forEach(callback => callback(data));
        }
    }
    
    showNotification(title, message) {
        // Check if browser supports notifications
        if (!("Notification" in window)) {
            return;
        }
        
        if (Notification.permission === "granted") {
            new Notification(title, { body: message });
        } else if (Notification.permission !== "denied") {
            Notification.requestPermission().then(permission => {
                if (permission === "granted") {
                    new Notification(title, { body: message });
                }
            });
        }
    }
    
    disconnect() {
        if (this.ws) {
            clearInterval(this.keepAliveInterval);
            this.ws.close();
        }
    }
    
    getConnectionStatus() {
        if (!this.ws) return 'DISCONNECTED';
        
        switch(this.ws.readyState) {
            case WebSocket.CONNECTING: return 'CONNECTING';
            case WebSocket.OPEN: return 'CONNECTED';
            case WebSocket.CLOSING: return 'CLOSING';
            case WebSocket.CLOSED: return 'DISCONNECTED';
            default: return 'UNKNOWN';
        }
    }
}

// Export for use in other modules
window.SentinelWebSocket = SentinelWebSocket;