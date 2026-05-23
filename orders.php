<?php
/**
 * BETELITE - Historical Orders List (orders.php)
 * Clean glassmorphism layout rendering all past forecasts purchases.
 */
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/security.php';
require_once __DIR__ . '/config/functions.php';

require_login();
$user_id = $_SESSION['user_id'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Purchase History - BETELITE</title>
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

    <nav class="navbar navbar-expand-lg glass-nav navbar-dark py-3">
        <div class="container">
            <a class="navbar-brand font-bold" href="index.php">BET<span class="text-blue-500">ELITE</span></a>
            <a href="dashboard.php" class="btn btn-outline-light btn-sm rounded-pill px-3">Dashboard</a>
        </div>
    </nav>

    <div class="container py-5">
        <h1 class="font-bold text-white mb-4">Historical Orders</h1>

        <div class="glass-card p-4">
            <div class="table-responsive">
                <table class="table table-dark table-borderless align-middle mb-0">
                    <thead>
                        <tr class="border-bottom border-white/5 text-xs text-white/50 uppercase">
                            <th>Purchase Date</th>
                            <th>Bundle Reference</th>
                            <th>Total Odds</th>
                            <th>Price Paid</th>
                            <th>Prediction Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $orders_stmt = $conn->prepare("
                            SELECT o.price_paid, o.created_at, p.id as p_id, p.title, p.confidence, p.status, p.total_odds 
                            FROM `orders` o 
                            JOIN `predictions` p ON o.prediction_id = p.id 
                            WHERE o.user_id = ? 
                            ORDER BY o.created_at DESC
                        ");
                        if ($orders_stmt) {
                            $orders_stmt->bind_param("i", $user_id);
                            $orders_stmt->execute();
                            $res = $orders_stmt->get_result();
                            if ($res && $res->num_rows > 0) {
                                while ($o_row = $res->fetch_assoc()) {
                                    $col = 'text-warning';
                                    if ($o_row['status'] === 'won') $col = 'text-green-400';
                                    if ($o_row['status'] === 'lost') $col = 'text-red-400';
                                    ?>
                                    <tr class="border-bottom border-white/5 text-sm">
                                        <td class="text-xs text-white/50"><?= date('d M, Y H:i', strtotime($o_row['created_at'])); ?></td>
                                        <td>
                                            <div class="fw-bold text-white"><?= sanitize_input($o_row['title']); ?></div>
                                            <span class="text-3xs text-cyan">Package ID: #<?= $o_row['p_id']; ?></span>
                                        </td>
                                        <td class="font-mono"><?= number_format($o_row['total_odds'], 2); ?>x</td>
                                        <td class="font-bold text-cyan"><?= CURRENCY_SYMBOL; ?><?= number_format($o_row['price_paid'], 2); ?></td>
                                        <td>
                                            <span class="fw-bold text-xs uppercase <?= $col; ?>">
                                                <?= strtoupper($o_row['status']); ?>
                                            </span>
                                        </td>
                                    </tr>
                                    <?php
                                }
                            } else {
                                ?>
                                <tr>
                                    <td colspan="5" class="text-center py-5 text-white/50">
                                        <i class="bi bi-folder-x text-3xl mb-2 d-block"></i> No purchase items found in transaction records.
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

</body>
</html>
