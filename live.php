<?php
/**
 * BETELITE - Live Match Center (live.php)
 * Beautiful real-time game monitoring: possession bars, incident feeds, cards, momentum indicators, etc.
 */
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/security.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Live Football Center - BETELITE</title>
    <!-- Tailwind CDN for fast grids and responsive colors -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <style>
        body {
            background-color: #020617;
            color: #f8fafc;
            font-family: 'Inter', system-ui, sans-serif;
            background-image: radial-gradient(circle at 10% 20%, rgba(37, 99, 235, 0.12) 0%, transparent 40%);
        }
        .glass-card {
            background: rgba(17, 24, 39, 0.65);
            backdrop-filter: blur(24px);
            border: 1px solid rgba(255, 255, 255, 0.08);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.35);
            border-radius: 20px;
        }
        .glass-nav {
            background: rgba(2, 6, 23, 0.85);
            backdrop-filter: blur(20px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.06);
        }
        .momentum-bar {
            height: 8px;
            background-color: rgba(255,255,255,0.05);
            border-radius: 4px;
            overflow: hidden;
        }
    </style>
</head>
<body class="min-h-screen pb-5">

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg glass-nav navbar-dark py-3">
        <div class="container">
            <a class="navbar-brand font-bold tracking-wider" href="index.php">
                <span class="text-white">BET</span><span class="text-blue-500">ELITE</span>
            </a>
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-red-600/30 text-red-500 border border-red-500/20 px-2.5 py-1 text-2xs animate-pulse d-flex align-items-center gap-1.5 font-bold"><i class="bi bi-broadcast"></i> LIVE STATS</span>
                <a href="dashboard.php" class="btn btn-outline-light btn-sm rounded-pill px-3">Dashboard</a>
            </div>
        </div>
    </nav>

    <!-- Main Live Container -->
    <div class="container py-5">
        
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-4 mb-5">
            <div>
                <h1 class="font-bold text-white tracking-tight mb-1">Live Match Center</h1>
                <p class="text-sm text-white/50 mb-0">Track real-time matches: score rates, momentum shifts, and event logs. Autorefreshes every 5s.</p>
            </div>
            <div class="badge bg-white/5 text-white/60 border border-white/5 px-3 py-2 rounded-xl text-xs flex items-center gap-1.5">
                <i class="bi bi-clock-history text-cyan"></i> Next auto fetch: <span id="reload-ctr" class="fw-bold text-white">5s</span>
            </div>
        </div>

        <div class="row g-4">
            
            <!-- Live Matches List -->
            <div class="col-lg-7" id="live-matches-deck">
                <!-- Match item structured dynamically -->
                <div class="glass-card p-4 border border-white/8 mb-3" id="match-pane-1">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="text-xs text-white/55 font-semibold"><i class="bi bi-trophy text-amber-500"></i> Champions League &bull; Group Stage</span>
                        <span class="badge bg-red-600 text-white font-bold text-2xs py-1 px-2.5 rounded-pill animate-pulse">LIVE 72'</span>
                    </div>

                    <div class="row align-items-center justify-content-center text-center py-3">
                        <div class="col-5">
                            <span class="h5 fw-bold text-white mb-0 block">Real Madrid</span>
                            <span class="text-xs text-white/40">Home Team</span>
                        </div>
                        <div class="col-2">
                            <div class="h2 fw-bold text-cyan tracking-widest font-mono">2 - 1</div>
                        </div>
                        <div class="col-5">
                            <span class="h5 fw-bold text-white mb-0 block">Manchester City</span>
                            <span class="text-xs text-white/40">Away Team</span>
                        </div>
                    </div>

                    <!-- Statistics sub panel -->
                    <div class="border-top border-white/5 pt-3 mt-3">
                        <div class="row g-3">
                            <div class="col-6">
                                <div class="text-2xs uppercase text-white/50 font-bold mb-1">Possession Ratio</div>
                                <div class="momentum-bar">
                                    <div class="bg-cyan h-100" style="width: 58%;"></div>
                                </div>
                                <div class="d-flex justify-content-between text-3xs font-semibold mt-1">
                                    <span class="text-cyan">58% Team A</span>
                                    <span class="text-white/40">42% Team B</span>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="text-2xs uppercase text-white/50 font-bold mb-1">Yellow Cards</div>
                                <div class="d-flex justify-content-between text-xs font-bold text-amber-400 mt-1">
                                    <span>2 <i class="bi bi-file-fill text-warning"></i></span>
                                    <span>1 <i class="bi bi-file-fill text-warning"></i></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Incidents & Live Commentary Flow -->
            <div class="col-lg-5">
                <div class="glass-card p-4 border border-white/8 h-100 flex flex-col justify-between">
                    <div>
                        <h5 class="fw-bold text-white mb-4 d-flex align-items-center gap-2">
                            <i class="bi bi-chat-left-dots text-cyan animate-pulse"></i> Real-time Match Commentary
                        </h5>
                        
                        <div class="space-y-3" id="commentary-rack">
                            <div class="p-3 rounded-xl bg-white/3 border border-white/4 text-sm relative">
                                <span class="badge bg-cyan text-black text-3xs font-bold rounded px-1.5 py-0.5 absolute top-3 right-3">71'</span>
                                <div class="fw-bold text-white mb-0.5">Substitution Real Madrid</div>
                                <p class="text-xs text-white/60 mb-0">Luka Modric enters the pitch replacing Federico Valverde to consolidate midfield control.</p>
                            </div>

                            <div class="p-3 rounded-xl bg-white/3 border border-white/4 text-sm relative">
                                <span class="badge bg-cyan text-black text-3xs font-bold rounded px-1.5 py-0.5 absolute top-3 right-3">68'</span>
                                <div class="fw-bold text-yellow-400 mb-0.5"><i class="bi bi-file-fill"></i> Yellow Card Man City</div>
                                <p class="text-xs text-white/60 mb-0">Erling Haaland is booked of a critical sliding challenge on Antonio Rüdiger.</p>
                            </div>
                        </div>
                    </div>

                    <div class="pt-4 border-top border-white/5 text-center mt-4">
                        <p class="text-3xs text-white/40 mb-0">Commentary stream matches actual virtual events. Powered by BETELITE live AJAX processor.</p>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- Script to simulate dynamic polling and match updates -->
    <script>
        let counter = 5;
        const reloadTick = () => {
            counter--;
            if (counter <= 0) {
                counter = 5;
                // Add an event or update scores mock-wise
                updateMockMatch();
            }
            document.getElementById('reload-ctr').innerText = counter + 's';
        };
        setInterval(reloadTick, 1000);

        function updateMockMatch() {
            // Random events generator to simulate actual auto polling
            const events = [
                { min: 73, title: "Goal Attempt", text: "Vinicius Junior strikes with power! The ball curves over the crossbar.", color: "text-white" },
                { min: 74, title: "Offside Trap", text: "Phil Foden is caught offside after a lob pass from Bernardo Silva.", color: "text-white" },
                { min: 75, title: "Foul Call", text: "Real Madrid is awarded a free kick in the opponents half.", color: "text-white" }
            ];
            const randIdx = Math.floor(Math.random() * events.length);
            const selectEv = events[randIdx];

            const commentary = document.getElementById('commentary-rack');
            const itemHTML = `
                <div class="p-3 rounded-xl bg-white/3 border border-white/4 text-sm relative animate-fade-in">
                    <span class="badge bg-cyan text-black text-3xs font-bold rounded px-1.5 py-0.5 absolute top-3 right-3">${selectEv.min}'</span>
                    <div class="fw-bold ${selectEv.color} mb-0.5">${selectEv.title}</div>
                    <p class="text-xs text-white/60 mb-0">${selectEv.text}</p>
                </div>
            `;
            commentary.insertAdjacentHTML('afterbegin', itemHTML);
            if (commentary.children.length > 5) {
                commentary.removeChild(commentary.lastChild);
            }
        }
    </script>
</body>
</html>
