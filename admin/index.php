<?php
/**
 * BETELITE - Admin Suite Home (admin/index.php)
 * Central analytics board: platform fees percentage configs, users/predictor lists, withdrawal approvals toggles...
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/security.php';
require_once __DIR__ . '/../config/functions.php';

require_role('admin');

$admin_id = $_SESSION['user_id'];

// Core Platform metrics
$total_users = 0;
$res = $conn->query("SELECT COUNT(*) as count FROM `users` WHERE role = 'user'");
if ($res && $row = $res->fetch_assoc()) $total_users = $row['count'];

$total_predictors = 0;
$res = $conn->query("SELECT COUNT(*) as count FROM `users` WHERE role = 'predictor'");
if ($res && $row = $res->fetch_assoc()) $total_predictors = $row['count'];

$total_revenue = 12500.00; // Static placeholder or calculate from orders table
$res = $conn->query("SELECT SUM(price_paid) as revenue FROM `orders`");
if ($res && $row = $res->fetch_assoc()) {
    $total_revenue = $row['revenue'] ? (double)$row['revenue'] : 0.00;
}

$pending_withdrawals = 0;
$res = $conn->query("SELECT COUNT(*) as count FROM `transactions` WHERE type = 'withdrawal' AND status = 'pending'");
if ($res && $row = $res->fetch_assoc()) $pending_withdrawals = $row['count'];

// Handle configuration updates if posted
$error = '';
$success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fee = (int)($_POST['platform_fee'] ?? 20);
    $token = $_POST['csrf_token'] ?? '';
    
    if (!validate_csrf_token($token)) {
        $error = "CSRF Verification failed.";
    } else {
        $stmt = $conn->prepare("UPDATE `platform_settings` SET `value` = ? WHERE `key` = 'platform_fee_percent'");
        if ($stmt) {
            $val_str = (string)$fee;
            $stmt->bind_param("s", $val_str);
            $stmt->execute();
            $stmt->close();
            $success = "Platform configuration credentials saved!";
        }
    }
}

$csrf_token = generate_csrf_token();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - BETELITE</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <style>
        body {
            background-color: #020617;
            color: #f8fafc;
            font-family: 'Inter', system-ui, sans-serif;
            background-image: radial-gradient(circle at 10% 20%, rgba(245, 158, 11, 0.08) 0%, transparent 45%);
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

    <!-- Admin header -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-slate-950 py-3 border-bottom border-white/5 shadow-xl">
        <div class="container">
            <a class="navbar-brand font-bold" href="../index.php">BET<span class="text-amber-500 text-lg">ELITE</span> <span class="badge bg-amber-500/20 text-amber-400 text-3xs border border-amber-500/20 uppercase tracking-widest px-2 ms-2">ADMIN</span></a>
            <div class="d-flex gap-2">
                <a href="../dashboard.php" class="btn btn-outline-light btn-sm rounded-pill px-3 text-xs">Buyer Deck</a>
                <a href="../api/logout.php" class="btn btn-danger btn-sm rounded-pill px-3 text-xs">Out</a>
            </div>
        </div>
    </nav>

    <!-- Main Board -->
    <div class="container py-5">
        <div class="d-flex justify-content-between align-items-center mb-5 flex-wrap gap-3">
            <div>
                <h1 class="font-bold text-white mb-1"><i class="bi bi-shield-lock-fill text-amber-500 animate-pulse"></i> Control Hub</h1>
                <p class="text-sm text-white/50 mb-0">Unified telemetry interface. Configure commissions rates, approve withdrawals, block bad actors.</p>
            </div>
        </div>

        <?php if (!empty($success)): ?>
            <div class="alert alert-success bg-green-500/10 text-green-400 border border-green-500/25 rounded-xl text-sm py-2 px-3 mb-4 flex gap-2 align-items-center"><i class="bi bi-check-circle"></i> <?= $success; ?></div>
        <?php endif; ?>

        <!-- Quad Metric Indicators -->
        <div class="row g-3 mb-5">
            <div class="col-md-3">
                <div class="glass-card p-4">
                    <span class="text-white/45 text-2xs uppercase font-semibold">User base size</span>
                    <div class="h2 fw-bold text-white mt-1 font-mono"><?= $total_users; ?> Active</div>
                    <a href="users.php" class="text-amber-500 text-xs font-semibold text-decoration-none">Manage Users &rarr;</a>
                </div>
            </div>

            <div class="col-md-3">
                <div class="glass-card p-4">
                    <span class="text-white/45 text-2xs uppercase font-semibold">Vetted Predictors</span>
                    <div class="h2 fw-bold text-white mt-1 font-mono"><?= $total_predictors; ?> Vetted</div>
                    <a href="predictors.php" class="text-amber-500 text-xs font-semibold text-decoration-none">Manage Predictors &rarr;</a>
                </div>
            </div>

            <div class="col-md-3">
                <div class="glass-card p-4">
                    <span class="text-white/45 text-2xs uppercase font-semibold">Commissions Pool</span>
                    <div class="h2 fw-bold text-green-400 mt-1 font-mono">$<?= number_format($total_revenue, 2); ?></div>
                    <span class="text-3xs text-white/30">Accrued platform commission cuts</span>
                </div>
            </div>

            <div class="col-md-3">
                <div class="glass-card p-4">
                    <span class="text-white/45 text-2xs uppercase font-semibold">Pending Cashouts</span>
                    <div class="h2 fw-bold text-red-400 mt-1 font-mono"><?= $pending_withdrawals; ?> Queued</div>
                    <a href="withdrawals.php" class="text-amber-500 text-xs font-semibold text-decoration-none">Approve Payouts &rarr;</a>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <!-- Left Side Platform Settings form -->
            <div class="col-lg-5">
                <div class="glass-card p-4 h-100">
                    <h5 class="fw-bold text-white mb-4"><i class="bi bi-gear-wide"></i> Global Configs</h5>

                    <form method="POST" action="index.php">
                        <input type="hidden" name="csrf_token" value="<?= $csrf_token; ?>">

                        <div class="mb-3">
                            <label class="form-label text-xs uppercase text-white/60 mb-1">Platform Commission Fee (%)</label>
                            <input type="number" name="platform_fee" value="<?= get_platform_setting($conn, 'platform_fee_percent', '20'); ?>" class="form-control glass-input py-2.5 rounded-xl text-sm" required>
                            <span class="text-3xs text-white/40 block mt-1">Fee % deducted automatically from forecasters sales in checkout.</span>
                        </div>

                        <button type="submit" class="btn btn-warning w-100 text-black fw-bold py-2.5 rounded-xl border-0 bg-amber-500 hover:brightness-110 shadow-lg mt-3">
                            Apply Configurations
                        </button>
                    </form>
                </div>
            </div>

            <!-- Right Side Withdraw approvals grid -->
            <div class="col-lg-7">
                <div class="glass-card p-4 h-100">
                    <h5 class="fw-bold text-white mb-4"><i class="bi bi-wallet2 text-amber-500"></i> Withdrawal Approval Queue</h5>

                    <div class="table-responsive">
                        <table class="table table-dark table-borderless align-middle mb-0 text-sm">
                            <thead>
                                <tr class="border-bottom border-white/5 text-xs text-white/50">
                                    <th>User ID</th>
                                    <th>Amount</th>
                                    <th>Method Log</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $queue_stmt = $conn->prepare("SELECT t.*, u.username FROM `transactions` t JOIN `users` u ON t.user_id = u.id WHERE t.type = 'withdrawal' AND t.status = 'pending' ORDER BY t.created_at DESC");
                                if ($queue_stmt) {
                                    $queue_stmt->execute();
                                    $res = $queue_stmt->get_result();
                                    if ($res && $res->num_rows > 0) {
                                        while($t = $res->fetch_assoc()) {
                                            ?>
                                            <tr class="border-bottom border-white/5">
                                                <td>
                                                    <strong class="text-white block">@<?= sanitize_input($t['username']); ?></strong>
                                                    <span class="text-3xs font-mono text-white/40">Ref: <?= $t['reference']; ?></span>
                                                </td>
                                                <td class="font-mono text-cyan">-$<?= number_format(abs($t['amount']), 2); ?></td>
                                                <td class="text-xs text-white/60"><?= sanitize_input($t['description']); ?></td>
                                                <td>
                                                    <button class="btn btn-sm btn-success px-2.5 py-1 text-xs font-bold rounded-lg border-0 bg-green-500">Accept</button>
                                                </td>
                                            </tr>
                                            <?php
                                        }
                                    } else {
                                        ?>
                                        <tr>
                                            <td colspan="4" class="text-center py-5 text-white/50">
                                                No cashout requirements queued in ledger accounts.
                                            </td>
                                        </tr>
                                        <?php
                                    }
                                    $queue_stmt->close();
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
