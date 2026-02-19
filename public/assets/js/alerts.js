// Simple Alert System
const alerts = {
    show: function(msg, type) {
        const div = document.createElement('div');
        div.style.cssText = `
            position:fixed;
            top:20px;
            right:20px;
            background:#151f2c;
            border-left:4px solid ${type === 'critical' ? '#ff006e' : '#00ff9d'};
            padding:15px;
            color:#e0e0e0;
            z-index:9999;
            animation:slide 0.3s;
        `;
        div.innerHTML = `<strong>${type.toUpperCase()}</strong><br>${msg}`;
        document.body.appendChild(div);
        setTimeout(() => div.remove(), 3000);
    }
};

// Add CSS animation
const style = document.createElement('style');
style.textContent = `
    @keyframes slide {
        from { transform: translateX(100%); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }
`;
document.head.appendChild(style);