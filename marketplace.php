<?php
/**
 * BETELITE - Prediction Marketplace (marketplace.php)
 * Vetted forecast bundles. Includes filter criteria, cart operations, VIP tags, confidence gauges...
 */
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/security.php';
require_once __DIR__ . '/config/functions.php';

$user_id = $_SESSION['user_id'] ?? null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Marketplace - BETELITE</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <style>
        body {
            background-color: #020617;
            color: #f8fafc;
            font-family: 'Inter', system-ui, sans-serif;
            background-image: radial-gradient(circle at 10% 20%, rgba(37, 99, 235, 0.08) 0%, transparent 45%);
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
    </style>
</head>
<body class="min-h-screen pb-5">

    <!-- Header -->
    <nav class="navbar navbar-expand-lg glass-nav navbar-dark py-3 sticky-top">
        <div class="container">
            <a class="navbar-brand font-bold tracking-wider" href="index.php">
                <span class="text-white">BET</span><span class="text-blue-500">ELITE</span>
            </a>
            
            <div class="d-flex align-items-center gap-3">
                <a href="cart.php" class="btn btn-outline-cyan rounded-pill btn-sm d-flex align-items-center gap-2 px-3">
                    <i class="bi bi-cart"></i> Cart <span id="cart-indicator" class="badge bg-cyan text-black">0</span>
                </a>
                <?php if ($user_id): ?>
                    <a href="dashboard.php" class="btn btn-sm bg-blue-600 border-0 rounded-pill px-3 text-white">Dashboard</a>
                <?php else: ?>
                    <a href="login.php" class="btn btn-sm btn-outline-light rounded-pill px-3 text-white">Login</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <!-- Marketplace Main Area -->
    <div class="container py-5">
        
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-4 mb-5">
            <div>
                <h1 class="font-bold text-white tracking-tight mb-1">Predictor Marketplace</h1>
                <p class="text-sm text-white/50 mb-0">Vetted sport forecasters offering dynamic odd bundles. Choose, pay with your wallet, unlock tips instantly.</p>
            </div>
            
            <!-- Quick filters -->
            <div class="d-flex gap-2 bg-white/5 p-1 rounded-xl border border-white/5">
                <button class="btn btn-sm btn-outline-light rounded-lg px-3 py-1.5 text-xs active" onclick="filterList('all')">All Sports</button>
                <button class="btn btn-sm btn-outline-light rounded-lg px-3 py-1.5 text-xs" onclick="filterList('Football')">Football</button>
                <button class="btn btn-sm btn-outline-light rounded-lg px-3 py-1.5 text-xs" onclick="filterList('Basketball')">Basketball</button>
            </div>
        </div>

        <div class="row g-4" id="predictions-rack">
            <!-- Cards will list dynamically. For production PHP, let's query raw SQL predictions -->
            <?php
            // Setup demo cards to display, linking index.php
            $sql = "SELECT p.*, u.username FROM `predictions` p JOIN `users` u ON p.predictor_id = u.id ORDER BY p.created_at DESC";
            $res = $conn->query($sql);
            if ($res && $res->num_rows > 0) {
                while($p = $res->fetch_assoc()) {
                    ?>
                    <div class="col-md-6 col-lg-4 prediction-card-item" data-sport="<?= $p['sport_type']; ?>">
                        <div class="glass-card p-4 h-100 flex flex-col justify-between border border-white/8 relative">
                            
                            <!-- Header Info -->
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <span class="badge bg-white/5 text-slate-300 px-2.5 py-1 text-xs border border-white/5"><?= sanitize_input($p['sport_type']); ?></span>
                                <div class="d-flex gap-1.5">
                                    <?php if ($p['is_hot']): ?>
                                        <span class="badge bg-red-500/20 text-red-400 border border-red-500/20 text-2xs fw-bold px-2 py-0.5"><i class="bi bi-fire text-red-500 animate-pulse"></i> HOT</span>
                                    <?php endif; ?>
                                    <?php if ($p['is_vip']): ?>
                                        <span class="badge bg-amber-500/20 text-amber-400 border border-amber-500/20 text-2xs fw-bold px-2 py-0.5"><i class="bi bi-gem"></i> VIP</span>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- Team description & Match representation -->
                            <div class="my-3">
                                <h4 class="fw-bold text-white mb-2 leading-tight"><?= sanitize_input($p['title']); ?></h4>
                                <p class="text-xs text-white/55 mb-3"><?= sanitize_input($p['description']); ?></p>
                                
                                <div class="row g-2 text-center p-2 rounded-xl bg-white/3 border border-white/5 mb-3">
                                    <div class="col-4">
                                        <div class="text-xs text-white/40">Total Odds</div>
                                        <div class="fw-bold text-white"><?= number_format($p['total_odds'], 2); ?>x</div>
                                    </div>
                                    <div class="col-4 border-start border-end border-white/5">
                                        <div class="text-xs text-white/40">Confidence</div>
                                        <span class="text-green-400 font-bold"><?= $p['confidence']; ?>%</span>
                                    </div>
                                    <div class="col-4">
                                        <div class="text-xs text-white/40">Predictor</div>
                                        <span class="text-cyan text-xs font-semibold">@<?= sanitize_input($p['username']); ?></span>
                                    </div>
                                </div>
                            </div>

                            <!-- Purchase / Action Footer -->
                            <div class="d-flex justify-content-between align-items-center pt-3 border-top border-white/5 mt-auto">
                                <div>
                                    <span class="text-3xs block uppercase text-white/40 font-bold leading-none">Unlock Price</span>
                                    <span class="text-xl font-bold text-cyan"><?= CURRENCY_SYMBOL; ?><?= number_format($p['price'], 2); ?></span>
                                </div>
                                
                                <button onclick="addToCart(<?= $p['id']; ?>, '<?= esc_js_pname($p['title']); ?>', <?= $p['price']; ?>)" class="btn bg-blue-600 hover:brightness-110 border-0 btn-sm text-white rounded-xl px-3 py-2 d-flex align-items-center gap-1.5 text-xs">
                                    Unlock Now <i class="bi bi-cart"></i>
                                </button>
                            </div>

                        </div>
                    </div>
                <?php
                }
            } else {
                // Return seed display
                ?>
                <div class="col-12">
                    <div class="glass-card text-center p-5 text-white/50 border border-dashed border-white/10">
                        <i class="bi bi-cart-x text-5xl mb-3 text-cyan"></i>
                        <h4 class="fw-bold text-white">No Forecast Bundles Listed Yet</h4>
                        <p class="text-sm text-white/60">Forecasters will populate dynamic odd listings soon. Check back or simulate predictions!</p>
                    </div>
                </div>
                <?php
            }
            
            function esc_js_pname($str) {
                return addslashes(htmlspecialchars($str, ENT_QUOTES, 'UTF-8'));
            }
            ?>
        </div>
    </div>

    <!-- Javascript integration -->
    <script>
        let cart = JSON.parse(localStorage.getItem('betelite_cart') || '[]');
        updateCartIndicator();

        function addToCart(predId, title, price) {
            if (cart.some(item => item.id === predId)) {
                alert("Bundle already in shopping cart");
                return;
            }
            cart.push({ id: predId, title: title, price: price });
            localStorage.setItem('betelite_cart', JSON.stringify(cart));
            updateCartIndicator();
            alert("Bundle added successfully to cashout cart!");
        }

        function updateCartIndicator() {
            document.getElementById('cart-indicator').innerText = cart.length;
        }

        function filterList(sport) {
            const cards = document.querySelectorAll('.prediction-card-item');
            cards.forEach(card => {
                if (sport === 'all' || card.getAttribute('data-sport') === sport) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        }
    </script>
</body>
</html>
