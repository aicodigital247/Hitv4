<?php
/**
 * BETELITE - Predictor Lounge Withdraw requests (predictor/withdraw.php)
 * Form for requesting bank account payments out of available forecaster earnings.
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/security.php';
require_once __DIR__ . '/../config/functions.php';

require_role('predictor');

$predictor_id = $_SESSION['user_id'];
$error = '';
$success = '';

// Load balance
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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $amount = (double)($_POST['amount'] ?? 0);
    $bank_name = trim($_POST['bank_name'] ?? '');
    $account_number = trim($_POST['account_number'] ?? '');
    $token = $_POST['csrf_token'] ?? '';

    if (!validate_csrf_token($token)) {
        $error = "CSRF Token validation failed.";
    } elseif ($amount <= 0 || empty($bank_name) || empty($account_number)) {
        $error = "Please fill in all complete fields.";
    } elseif ($amount > $balance) {
        $error = "Insufficient funds in your predictor balance.";
    } else {
        // Log transaction pending standard review
        $ref = 'WTH-' . strtoupper(bin2hex(random_bytes(6)));
        $res = change_wallet_balance($conn, $predictor_id, -$amount, 'withdrawal', "Withdrawal of $amount requested to $bank_name ($account_number)", $ref, 'bank_transfer');
        if ($res['success']) {
            $success = "Your withdrawal request of $" . number_format($amount, 2) . " has been logged and queued for audit review.";
            $balance = $res['new_balance'];
        } else {
            $error = $res['error'];
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
    <title>Withdraw Earnings - BETELITE</title>
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
        .glass-input {
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.06);
            color: #fff;
        }
    </style>
</head>
<body class="min-h-screen">

    <nav class="navbar navbar-expand-lg navbar-dark bg-dark/40 border-bottom border-white/5 py-3">
        <div class="container">
            <a class="navbar-brand font-bold" href="../index.php">BET<span class="text-info">ELITE</span></a>
            <a href="dashboard.php" class="btn btn-outline-info btn-sm rounded-pill px-3">Back to Lounge</a>
        </div>
    </nav>

    <div class="container py-5 max-w-md">
        <h2 class="font-bold text-white text-center mb-4">Request Payout</h2>

        <div class="glass-card p-4">
            <div class="mb-4 text-center">
                <span class="text-xs uppercase text-white/45 block mb-1">Withdrawable Balance</span>
                <span class="h3 fw-bold text-green-400 font-mono block">$<?= number_format($balance, 2); ?></span>
            </div>

            <?php if (!empty($error)): ?>
                <div class="alert alert-danger bg-red-950/40 border-red-500/30 text-red-100 rounded-xl mb-3 text-sm py-2 px-3 flex gap-2"><i class="bi bi-exclamation-triangle"></i> <?= $error; ?></div>
            <?php endif; ?>
            <?php if (!empty($success)): ?>
                <div class="alert alert-success bg-green-950/40 border-green-500/30 text-green-100 rounded-xl mb-3 text-sm py-2 px-3 flex gap-2"><i class="bi bi-check-circle"></i> <?= $success; ?></div>
            <?php endif; ?>

            <form method="POST" action="withdraw.php">
                <input type="hidden" name="csrf_token" value="<?= $csrf_token; ?>">

                <div class="mb-3">
                    <label class="form-label text-xs uppercase text-white/60 mb-1">Bank Issuer Name</label>
                    <input type="text" name="bank_name" class="form-control glass-input py-2.5 rounded-xl text-sm" placeholder="e.g. United Bank for Africa" required>
                </div>

                <div class="mb-3">
                    <label class="form-label text-xs uppercase text-white/60 mb-1">10-Digit Account Number</label>
                    <input type="text" name="account_number" class="form-control glass-input py-2.5 rounded-xl text-sm" placeholder="e.g. 1023456789" required>
                </div>

                <div class="mb-4">
                    <label class="form-label text-xs uppercase text-white/60 mb-1">Withdrawal Amount ($)</label>
                    <input type="number" step="0.01" name="amount" min="10" class="form-control glass-input py-2.5 rounded-xl text-sm" placeholder="e.g. 50.00" required>
                </div>

                <button type="submit" class="btn btn-info w-100 py-2.5 rounded-xl border-0 bg-info hover:brightness-110 font-bold shadow-lg">
                    Submit Payout Request
                </button>
            </form>
        </div>
    </div>

</body>
</html>
