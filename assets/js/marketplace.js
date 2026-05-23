/**
 * BETELITE - marketplace.js
 * Multi criteria filtering, countdown clocks, odds formatting animations
 */

function updateLiveCountdown(elementId, targetDateStr) {
    const el = document.getElementById(elementId);
    if (!el) return;

    const interval = setInterval(() => {
        const diff = new Date(targetDateStr) - new Date();
        if (diff <= 0) {
            el.innerText = "STARTED";
            clearInterval(interval);
            return;
        }

        const hrs = Math.floor(diff / 3600000);
        const mins = Math.floor((diff % 3600000) / 60000);
        const secs = Math.floor((diff % 60000) / 1000);

        el.innerText = `${hrs}h ${mins}m ${secs}s`;
    }, 1000);
}
