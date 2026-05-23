/**
 * BETELITE - app.js
 * SPA navigation transitions, beautiful HTML toasters, pull to refresh simulations, haptic feedbacks
 */

document.addEventListener('DOMContentLoaded', () => {
    console.log("BETELITE Core assets initialized successfully.");
});

// Universal Toast message helper
function showToast(title, msg, type = 'success') {
    const borderCol = type === 'success' ? 'border-green-500/20' : 'border-red-500/20';
    const textCol = type === 'success' ? 'text-green-400' : 'text-red-400';
    
    const toastHTML = `
        <div class="fixed top-4 right-4 z-1000 glass-card p-3 border ${borderCol} ${textCol} animate-fade-in shadow-xl max-w-sm">
            <div class="fw-bold text-xs uppercase mb-0.5">${title}</div>
            <p class="text-xs text-white/70 mb-0">${msg}</p>
        </div>
    `;
    
    document.body.insertAdjacentHTML('beforeend', toastHTML);
    const nodes = document.querySelectorAll('.fixed.top-4.right-4');
    const lastNode = nodes[nodes.length - 1];
    setTimeout(() => {
        if (lastNode && lastNode.parentNode) {
            lastNode.parentNode.removeChild(lastNode);
        }
    }, 4000);
}

// Emulate mobile haptic buzz
function triggerHapticFeedback() {
    if (window.navigator && window.navigator.vibrate) {
        window.navigator.vibrate(50); // 50ms pulse
    }
}
