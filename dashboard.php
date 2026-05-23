<?php
/**
 * BETELITE - User Dashboard (dashboard.php)
 * Clean glassmorphism layout displaying wallet balance, recent active tickets, ads, and referral statistics.
 */
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/security.php';
require_once __DIR__ . '/config/functions.php';

require_login();

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];
$role = $_SESSION['role'];

// Fetch wallet balance
$wallet_balance = 0.00;
$wallet_stmt = $conn->prepare("SELECT balance FROM `wallets` WHERE user_id = ?");
if ($wallet_stmt) {
    $wallet_stmt->bind_param("i", $user_id);
    $wallet_stmt->execute();
    $res = $wallet_stmt->get_result();
    if ($row = $res->fetch_assoc()) {
        $wallet_balance = (double)$row['balance'];
    }
    $wallet_stmt->close();
}

// Fetch referrals count
$ref_count = 0;
$ref_count_stmt = $conn->prepare("SELECT COUNT(*) as count FROM `referrals` WHERE referrer_id = ?");
if ($ref_count_stmt) {
    $ref_count_stmt->bind_param("i", $user_id);
    $ref_count_stmt->execute();
    $res = $ref_count_stmt->get_result();
    if ($row = $res->fetch_assoc()) {
        $ref_count = $row['count'];
    }
    $ref_count_stmt->close();
}

// Fetch user code
$user_code = 'N/A';
$user_code_stmt = $conn->prepare("SELECT referral_code FROM `users` WHERE id = ?");
if ($user_code_stmt) {
    $user_code_stmt->bind_param("i", $user_id);
    $user_code_stmt->execute();
    $res = $user_code_stmt->get_result();
    if ($row = $res->fetch_assoc()) {
        $user_code = $row['referral_code'];
    }
    $user_code_stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - BETELITE</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <style>
        body {
            background-color: #020617;
            color: #f8fafc;
            font-family: 'Inter', system-ui, sans-serif;
            background-image: radial-gradient(circle at 10% 20%, rgba(37, 99, 235, 0.1) 0%, transparent 45%);
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
<body class="min-h-screen">

    <!-- Navigation Header -->
    <nav class="navbar navbar-expand-lg glass-nav navbar-dark py-3">
        <div class="container">
            <a class="navbar-brand font-bold tracking-wider" href="index.php">
                <span class="text-white">BET</span><span class="text-blue-500">ELITE</span>
            </a>
            <div class="d-flex align-items-center gap-3">
                <span class="text-xs text-white/50 d-none d-sm-inline">Logged as <strong class="text-white/80"><?= sanitize_input($username); ?></strong> (<?= strtoupper(sanitize_input($role)); ?>)</span>
                <a href="api/logout.php" class="btn btn-outline-danger btn-sm rounded-pill px-3 py-1 text-xs"><i class="bi bi-box-arrow-right"></i> Out</a>
            </div>
        </div>
    </nav>

    <!-- Content Group -->
    <div class="container py-5">
        <div class="row g-4">
            
            <!-- Left Sidebar Navigation Menu -->
            <div class="col-lg-3">
                <div class="glass-card p-3 mb-4">
                    <div class="p-3 text-center border-bottom border-white/5">
                        <div class="w-16 h-16 rounded-full bg-blue-600/20 text-blue-400 flex items-center justify-content-center mx-auto mb-2 text-2xl fw-bold">
                            <?= strtoupper(substr($username, 0, 2)); ?>
                        </div>
                        <h5 class="fw-bold mb-0 text-white"><?= sanitize_input($username); ?></h5>
                        <p class="text-xs text-white/40 mb-0">Referral ID: <?= $user_code; ?></p>
                    </div>
                    <div class="list-group list-group-flush bg-transparent border-0 mt-3 rounded-none">
                        <a href="dashboard.php" class="list-group-item list-group-item-action bg-transparent border-0 text-white hover:bg-white/5 py-2.5 rounded-lg active bg-blue-600/20 text-blue-400"><i class="bi bi-grid-1x2"></i> Overview</a>
                        <a href="marketplace.php" class="list-group-item list-group-item-action bg-transparent border-0 text-white/70 hover:bg-white/5 py-2.5 rounded-lg"><i class="bi bi-cart"></i> Buy Predictions</a>
                        <a href="wallet.php" class="list-group-item list-group-item-action bg-transparent border-0 text-white/70 hover:bg-white/5 py-2.5 rounded-lg"><i class="bi bi-wallet2"></i> My Wallet</a>
                        <a href="live.php" class="list-group-item list-group-item-action bg-transparent border-0 text-white/70 hover:bg-white/5 py-2.5 rounded-lg"><i class="bi bi-broadcast"></i> Match Center</a>
                        <?php if ($role === 'predictor'): ?>
                            <a href="predictor/dashboard.php" class="list-group-item list-group-item-action bg-transparent border-0 text-cyan hover:bg-white/5 py-2.5 rounded-lg fw-bold"><i class="bi bi-cpu"></i> Predictor Lounge</a>
                        <?php elseif ($role === 'admin'): ?>
                            <a href="admin/index.php" class="list-group-item list-group-item-action bg-transparent border-0 text-amber-400 hover:bg-white/5 py-2.5 rounded-lg fw-bold"><i class="bi bi-shield-lock"></i> Admin Suite</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Central Content Deck -->
            <div class="col-lg-9">
                
                <!-- Announcement Banner -->
                <div class="glass-card p-4 bg-gradient-to-r from-blue-950/40 to-cyan-950/40 border border-blue-500/20 mb-4 d-flex align-items-center justify-content-between">
                    <div>
                        <h4 class="fw-bold text-white mb-1"><i class="bi bi-award-fill text-amber-500"></i> Elite Subscription Active</h4>
                        <p class="text-xs text-white/60 mb-0">Access premium verified bundles and automatic push predictions via our Telegram Mini App interface.</p>
                    </div>
                    <a href="marketplace.php" class="btn btn-sm btn-outline-cyan rounded-pill px-3 py-1 text-xs">Explore</a>
                </div>

                <!-- Three Columns Grid Overview Stats -->
                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <div class="glass-card p-4 text-start">
                            <span class="text-white/50 text-xs uppercase font-semibold">Wallet Funds Available</span>
                            <div class="h2 fw-bold text-white mb-2 mt-1"><?= CURRENCY_SYMBOL; ?><?= number_format($wallet_balance, 2); ?></div>
                            <a href="wallet.php" class="text-blue-400 text-xs font-bold text-decoration-none hover:underline"><i class="bi bi-plus-circle"></i> Quick Deposit &rarr;</a>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="glass-card p-4 text-start">
                            <span class="text-white/50 text-xs uppercase font-semibold">Referral Network</span>
                            <div class="h2 fw-bold text-cyan mb-2 mt-1"><?= $ref_count; ?> Invitations</div>
                            <p class="text-xs text-white/40 mb-0">Referral reward balance: <strong class="text-green-400">$<?= $ref_count * get_platform_setting($conn, 'referral_bonus_amount', '5.00'); ?></strong></p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="glass-card p-4 text-start">
                            <span class="text-white/50 text-xs uppercase font-semibold">Purchased Bundles</span>
                            <div class="h2 fw-bold text-white mb-2 mt-1">
                                <?php
                                $purch_count = 0;
                                $purch_stmt = $conn->prepare("SELECT COUNT(*) as count FROM `orders` WHERE user_id = ?");
                                if ($purch_stmt) {
                                    $purch_stmt->bind_param("i", $user_id);
                                    $purch_stmt->execute();
                                    $res = $purch_stmt->get_result();
                                    if ($row = $res->fetch_assoc()) {
                                        $purch_count = $row['count'];
                                    }
                                    $purch_stmt->close();
                                }
                                echo $purch_count;
                                ?> Active
                            </div>
                            <a href="orders.php" class="text-cyan text-xs font-bold text-decoration-none hover:underline"><i class="bi bi-clock-history"></i> See History &rarr;</a>
                        </div>
                    </div>
                </div>

                <!-- Purchased Predictions Table -->
                <div class="glass-card p-4">
                    <h5 class="fw-bold text-white mb-4"><i class="bi bi-shield-check text-green-400 animate-pulse"></i> My Active Purchased Forecasts</h5>
                    
                    <div class="table-responsive">
                        <table class="table table-dark table-borderless align-middle mb-0">
                            <thead>
                                <tr class="border-bottom border-white/5 text-xs text-white/50 uppercase">
                                    <th>Match Details</th>
                                    <th>Suggested Selection</th>
                                    <th>Odds</th>
                                    <th>Total Price</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $orders_stmt = $conn->prepare("
                                    SELECT o.price_paid, o.created_at, p.title, p.confidence, p.status, p.total_odds 
                                    FROM `orders` o 
                                    JOIN `predictions` p ON o.prediction_id = p.id 
                                    WHERE o.user_id = ? 
                                    ORDER BY o.created_at DESC LIMIT 5
                                ");
                                if ($orders_stmt) {
                                    $orders_stmt->bind_param("i", $user_id);
                                    $orders_stmt->execute();
                                    $res = $orders_stmt->get_result();
                                    if ($res && $res->num_rows > 0) {
                                        while ($o_row = $res->fetch_assoc()) {
                                            $state_col = 'text-warning';
                                            if ($o_row['status'] === 'won') $state_col = 'text-green-400';
                                            if ($o_row['status'] === 'lost') $state_col = 'text-red-400';
                                            ?>
                                            <tr class="border-bottom border-white/5 text-sm">
                                                <td>
                                                    <div class="fw-bold text-white"><?= sanitize_input($o_row['title']); ?></div>
                                                    <span class="text-xs text-white/40">Purchased on <?= date('d M, Y', strtotime($o_row['created_at'])); ?></span>
                                                </td>
                                                <td>
                                                    <span class="badge bg-white/5 text-white/90 border border-white/5">Forecaster Tip</span>
                                                </td>
                                                <td class="font-mono"><?= number_format($o_row['total_odds'], 2); ?></td>
                                                <td class="font-bold text-cyan"><?= CURRENCY_SYMBOL; ?><?= number_format($o_row['price_paid'], 2); ?></td>
                                                <td>
                                                    <span class="fw-bold text-xs uppercase <?= $state_col; ?>">
                                                        <i class="bi bi-circle-fill text-3xs me-1"></i> <?= strtoupper($o_row['status']); ?>
                                                    </span>
                                                </td>
                                            </tr>
                                            <?php
                                        }
                                    } else {
                                        ?>
                                        <tr>
                                            <td colspan="5" class="text-center py-5 text-white/50">
                                                <i class="bi bi-cart-x text-3xl mb-2 d-block"></i> No purchased forecasts recorded. Go to predictions shop to purchase!
                                            </td>
                                        </tr>
                                        <?php
                                    }
                                    $orders_stmt->close();
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </div>

</body>
</html>
