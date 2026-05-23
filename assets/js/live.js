/**
 * BETELITE - live.js
 * Event updates, interval AJAX simulation loops, cards polling triggers
 */

function setupLivepolling(matchId, callback) {
    setInterval(() => {
        // Mock AJAX fetch
        const mockResponse = {
            status: "success",
            live_minute: 74,
            home_score: 2,
            away_score: 1,
            momentum: Math.floor(Math.random() * 40) + 30
        };
        callback(mockResponse);
    }, 5000);
}
