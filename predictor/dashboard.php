<?php
/**
 * BETELITE - Predictor Lounge Home (predictor/dashboard.php)
 * Real-time indicators showing total revenue, customer counts, bundle rates, and pending withdrawals.
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/security.php';
require_once __DIR__ . '/../config/functions.php';

require_role('predictor');

$predictor_id = $_SESSION['user_id'];
$stats = get_predictor_stats($conn, $predictor_id);

// Load wallet balance
$balance = 0.00;
$wallet_stmt = $conn->prepare("SELECT balance FROM `wallets` WHERE user_id = ?");
if ($wallet_stmt) {
    $wallet_stmt->bind_param("i", $predictor_id);
    $wallet_stmt->execute();
    $res = $wallet_stmt->get_result();
    if ($row = $res->fetch_assoc()) {
        $balance = (double)$row['balance'];
    }
    $wallet_stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Predictor Lounge - BETELITE</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <style>
        body {
            background-color: #020617;
            color: #f8fafc;
            font-family: 'Inter', system-ui, sans-serif;
            background-image: radial-gradient(circle at 10% 20%, rgba(6, 182, 212, 0.1) 0%, transparent 45%);
        }
        .glass-card {
            background: rgba(17, 24, 39, 0.65);
            backdrop-filter: blur(24px);
            border: 1px solid rgba(255, 255, 255, 0.08);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.35);
            border-radius: 20px;
        }
    </style>
</head>
<body class="min-h-screen">

    <nav class="navbar navbar-expand-lg navbar-dark bg-dark/40 border-bottom border-white/5 py-3 shadow-lg">
        <div class="container">
            <a class="navbar-brand font-bold" href="../index.php">BET<span class="text-info">ELITE</span></a>
            <div class="d-flex gap-2">
                <a href="../dashboard.php" class="btn btn-outline-light btn-sm rounded-pill px-3">Standard Deck</a>
                <a href="../api/logout.php" class="btn btn-danger btn-sm rounded-pill px-3">Out</a>
            </div>
        </div>
    </nav>

    <div class="container py-5">
        <div class="d-flex justify-content-between align-items-center mb-5 flex-wrap gap-3">
            <div>
                <h1 class="font-bold text-white mb-1"><i class="bi bi-shield-check text-info animate-pulse"></i> Predictor Lounge</h1>
                <p class="text-sm text-white/50 mb-0">Monetize sports forecasts under secure and transparent conditions. Set confidence scores, track payouts.</p>
            </div>
            <a href="create_prediction.php" class="btn btn-info bg-cyan text-black border-0 rounded-xl px-4 py-2.5 font-bold"><i class="bi bi-plus-circle"></i> Publish New Bundle</a>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="glass-card p-4">
                    <span class="text-white/45 text-2xs uppercase font-semibold">Predictor Wallet</span>
                    <div class="h2 fw-bold text-white mt-2 font-mono"><?= CURRENCY_SYMBOL; ?><?= number_format($balance, 2); ?></div>
                    <a href="withdraw.php" class="text-info text-xs font-bold text-decoration-none">Request Withdrawal &rarr;</a>
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="glass-card p-4">
                    <span class="text-white/45 text-2xs uppercase font-semibold">Forecasting Accuracy</span>
                    <div class="h2 fw-bold text-green-400 mt-2 font-mono"><?= $stats['win_rate']; ?>% Win Rate</div>
                    <span class="text-3xs text-white/30">Based on <?= $stats['total_predictions']; ?> checked bundles</span>
                </div>
            </div>

            <div class="col-md-3">
                <div class="glass-card p-4">
                    <span class="text-white/45 text-2xs uppercase font-semibold">Total Bundles Sold</span>
                    <div class="h2 fw-bold text-white mt-2 font-mono">
                        <?php
                        $sales = 0;
                        $sales_stmt = $conn->prepare("SELECT COUNT(*) as count FROM `orders` o JOIN `predictions` p ON o.prediction_id = p.id WHERE p.predictor_id = ?");
                        if ($sales_stmt) {
                            $sales_stmt->bind_param("i", $predictor_id);
                            $sales_stmt->execute();
                            $res = $sales_stmt->get_result();
                            if ($row = $res->fetch_assoc()) {
                                $sales = $row['count'];
                            }
                            $sales_stmt->close();
                        }
                        echo $sales;
                        ?> sales
                    </div>
                    <span class="text-3xs text-white/30">Vetted buyer transactions</span>
                </div>
            </div>

            <div class="col-md-3">
                <div class="glass-card p-4">
                    <span class="text-white/45 text-2xs uppercase font-semibold">Customer Ratings</span>
                    <div class="h2 fw-bold text-info mt-2 font-mono"><?= number_format($stats['rating'], 1); ?> / 5.0</div>
                    <span class="text-3xs text-white/30">Vetted buyers satisfaction index</span>
                </div>
            </div>
        </div>

        <!-- Predictions monitoring table -->
        <div class="glass-card p-4 mt-4">
            <h5 class="fw-bold text-white mb-4"><i class="bi bi-grid-3x3-gap"></i> My Published Bundles</h5>

            <div class="table-responsive">
                <table class="table table-dark table-borderless align-middle mb-0">
                    <thead>
                        <tr class="border-bottom border-white/5 text-xs text-white/50 uppercase">
                            <th>Created On</th>
                            <th>Bundle Name</th>
                            <th>Subtype</th>
                            <th>Price</th>
                            <th>Odds</th>
                            <th>Outcome</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $list_stmt = $conn->prepare("SELECT * FROM `predictions` WHERE predictor_id = ? ORDER BY created_at DESC");
                        if ($list_stmt) {
                            $list_stmt->bind_param("i", $predictor_id);
                            $list_stmt->execute();
                            $res = $list_stmt->get_result();
                            if ($res && $res->num_rows > 0) {
                                while($p_row = $res->fetch_assoc()) {
                                    $col = 'text-warning';
                                    if ($p_row['status'] === 'won') $col = 'text-green-400';
                                    if ($p_row['status'] === 'lost') $col = 'text-red-400';
                                    ?>
                                    <tr class="border-bottom border-white/5 text-sm">
                                        <td class="text-xs text-white/45"><?= date('d M, Y', strtotime($p_row['created_at'])); ?></td>
                                        <td class="fw-bold text-white"><?= sanitize_input($p_row['title']); ?></td>
                                        <td><span class="badge bg-white/5 border border-white/5 text-white/70 text-2xs"><?= sanitize_input($p_row['sport_type']); ?></span></td>
                                        <td class="font-bold text-info">$<?= number_format($p_row['price'], 2); ?></td>
                                        <td class="font-mono text-xs text-info"><?= number_format($p_row['total_odds'], 2); ?>x</td>
                                        <td class="fw-bold text-xs uppercase <?= $col; ?>"><?= $p_row['status']; ?></td>
                                    </tr>
                                    <?php
                                }
                            } else {
                                ?>
                                <tr>
                                    <td colspan="6" class="text-center py-5 text-white/50">
                                        <i class="bi bi-terminal-dash text-3xl mb-2 d-block text-cyan"></i> No bundles published yet! Publish a forecast to begin earning.
                                    </td>
                                </tr>
                                <?php
                            }
                            $list_stmt->close();
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>

</body>
</html>
