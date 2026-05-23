<?php
/**
 * BETELITE - Public Modern Landing Page (index.php)
 * Beautiful glassmorphic sports forecasting marketplace and live hub
 */
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/security.php';
require_once __DIR__ . '/config/functions.php';

$csrf = generate_csrf_token();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BETELITE - Sports Forecasters Marketplace</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Bootstrap 5 CDN for CSS & JS components -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <style>
        body {
            background-color: #020617;
            color: #f8fafc;
            font-family: 'Inter', system-ui, sans-serif;
            background-image: radial-gradient(circle at 10% 20%, rgba(37, 99, 235, 0.15) 0%, transparent 40%),
                              radial-gradient(circle at 90% 80%, rgba(6, 182, 212, 0.12) 0%, transparent 40%);
            background-attachment: fixed;
        }

        .glass-card {
            background: rgba(17, 24, 39, 0.65);
            backdrop-filter: blur(24px);
            border: 1px solid rgba(255, 255, 255, 0.08);
            box-shadow: 0 8px 32px rgba(0,0,0,0.35), inset 0 1px 0 rgba(255,255,255,0.05);
            border-radius: 24px;
        }

        .glass-nav {
            background: rgba(2, 6, 23, 0.85);
            backdrop-filter: blur(20px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.06);
        }

        .glass-input {
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.06);
            color: #fff;
        }
        
        .glass-input:focus {
            background: rgba(255, 255, 255, 0.06);
            border-color: #2563eb;
            color: #fff;
            box-shadow: 0 0 10px rgba(37, 99, 235, 0.25);
        }

        .text-cyan { color: #06b6d4; }
        .text-electric { color: #2563eb; }
        .bg-electric { background-color: #2563eb; }
        .bg-cyan { background-color: #06b6d4; }
    </style>
</head>
<body class="min-h-screen flex flex-col justify-between">

    <!-- Header / Navigation -->
    <nav class="navbar navbar-expand-lg glass-nav navbar-dark fixed-top py-3">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center font-bold tracking-wider" href="index.php">
                <span class="text-white h3 mb-0">BET</span><span class="text-electric h3 mb-0">ELITE</span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
                <ul class="navbar-nav gap-2">
                    <li class="nav-item"><a class="nav-link text-white/80 hover:text-white" href="marketplace.php"><i class="bi bi-cart"></i> Predictions</a></li>
                    <li class="nav-item"><a class="nav-link text-white/80 hover:text-white" href="live.php"><i class="bi bi-broadcast"></i> Live Scores</a></li>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <li class="nav-item"><a class="btn btn-outline-light px-4 rounded-pill btn-sm d-flex align-items-center" href="dashboard.php">Dashboard</a></li>
                    <?php else: ?>
                        <li class="nav-item"><a class="btn btn-link text-white text-decoration-none" href="login.php">Log In</a></li>
                        <li class="nav-item"><a class="btn bg-electric text-white border-0 px-4 rounded-pill shadow-lg hover:brightness-110" href="register.php">Sign Up</a></li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Hero -->
    <div class="container flex-grow d-flex align-items-center py-5 mt-5">
        <div class="row w-100 g-5 align-items-center mt-3">
            <div class="col-lg-7 text-center text-lg-start">
                <div class="badge bg-cyan/10 text-cyan px-3 py-2 rounded-full mb-3 border border-cyan/25">
                    <i class="bi bi-fire"></i> THE FORECASTING REVOLUTION
                </div>
                <h1 class="display-3 fw-bold text-white mb-4 leading-tight">
                    Win More with Verified <span class="bg-gradient-to-r from-blue-500 to-cyan-500 bg-clip-text text-transparent">Pro Experts</span>
                </h1>
                <p class="lead text-white/70 mb-5 max-w-xl mx-auto mx-lg-0">
                    Buy high-confidence sport bundles from vetted, profitable predictors, or monetize your predictions on Africa's elite marketplace. Fully automated wallet backend.
                </p>
                <div class="d-flex flex-column flex-sm-row gap-3 justify-content-center justify-content-lg-start">
                    <a href="marketplace.php" class="btn bg-electric text-white d-flex align-items-center justify-content-center gap-2 px-5 py-3 rounded-xl shadow-lg border-0 hover:brightness-110">
                        Explore Verified Bundles <i class="bi bi-arrow-right"></i>
                    </a>
                    <a href="live.php" class="btn btn-outline-light d-flex align-items-center justify-content-center gap-2 px-5 py-3 rounded-xl hover:bg-white/5">
                        Live Match Center <i class="bi bi-broadcast text-danger animate-pulse"></i>
                    </a>
                </div>
                
                <!-- Quick stats -->
                <div class="row g-4 mt-5 pt-3 border-top border-white/5">
                    <div class="col-6 col-sm-4 text-start">
                        <div class="h2 fw-bold text-white mb-0">94.2%</div>
                        <div class="text-sm text-white/50">Verified Max Winrate</div>
                    </div>
                    <div class="col-6 col-sm-4 text-start">
                        <div class="h2 fw-bold text-cyan mb-0">150+</div>
                        <div class="text-sm text-white/50">Expert Predictors</div>
                    </div>
                    <div class="col-12 col-sm-4 text-start">
                        <div class="h2 fw-bold text-white mb-0">Paystack / Crypto</div>
                        <div class="text-sm text-white/50">Secure Transactions</div>
                    </div>
                </div>
            </div>

            <!-- Features Card -->
            <div class="col-lg-5">
                <div class="glass-card p-5 border border-white/8">
                    <h3 class="fw-bold text-white text-center mb-4">Elite Predictor Spotlight</h3>
                    
                    <div class="d-flex align-items-center gap-3 p-3 rounded-xl bg-white/5 mb-3 border border-white/5">
                        <div class="bg-cyan/20 w-12 h-12 rounded-full flex items-center justify-content-center text-cyan text-xl">
                            <i class="bi bi-shield-check"></i>
                        </div>
                        <div>
                            <div class="fw-bold text-white mb-0">MegaTipster Pro <span class="badge bg-amber-500 text-black text-xs font-bold">HOT</span></div>
                            <div class="text-xs text-white/60">Win-rate: <span class="text-green-400 fw-bold">88.5%</span> | Football Exclusively</div>
                        </div>
                    </div>

                    <div class="d-flex align-items-center gap-3 p-3 rounded-xl bg-white/5 mb-3 border border-white/5">
                        <div class="bg-blue-500/20 w-12 h-12 rounded-full flex items-center justify-content-center text-blue-400 text-xl">
                            <i class="bi bi-trophy"></i>
                        </div>
                        <div>
                            <div class="fw-bold text-white mb-0">Alpha Predictions <span class="badge bg-blue-500 text-white text-xs font-bold">VIP</span></div>
                            <div class="text-xs text-white/60">Win-rate: <span class="text-green-400 fw-bold">92.1%</span> | Basketball & Football</div>
                        </div>
                    </div>
                    
                    <hr class="border-white/10 my-4">
                    
                    <div class="text-center">
                        <p class="text-sm text-white/60 mb-3">Earn 10% commission on referrals instantly. Shared hosting compliant, works flawlessly in Telegram!</p>
                        <a href="register.php" class="text-cyan text-decoration-none font-bold text-sm hover:underline">
                            Register now and claim free $5 credit <i class="bi bi-chevron-right text-xs"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="py-4 border-top border-white/5 text-center text-white/40 text-sm">
        <p>&copy; 2026 BETELITE. Vetted predictions, instant earnings, cPanel ready. Verified payouts guaranteed.</p>
    </footer>

    <!-- Bootstrap 5 Bundle with Popper JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
