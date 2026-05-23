<?php
/**
 * BETELITE - Predictor Lounge Predictions View (predictor/my_predictions.php)
 * Lists all predictions and allows manual results adjustments.
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/security.php';
require_once __DIR__ . '/../config/functions.php';

require_role('predictor');
$predictor_id = $_SESSION['user_id'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Managed Predictions - BETELITE</title>
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

    <nav class="navbar navbar-expand-lg navbar-dark bg-dark/40 border-bottom border-white/5 py-3">
        <div class="container">
            <a class="navbar-brand font-bold" href="../index.php">BET<span class="text-info">ELITE</span></a>
            <a href="dashboard.php" class="btn btn-outline-info btn-sm rounded-pill px-3">Back to Lounge</a>
        </div>
    </nav>

    <div class="container py-5">
        <h2 class="font-bold text-white mb-4">Manage My Published Forecasts</h2>

        <div class="glass-card p-4">
            <div class="table-responsive">
                <table class="table table-dark table-borderless align-middle mb-0">
                    <thead>
                        <tr class="border-bottom border-white/5 text-xs text-white/50">
                            <th>Uploaded</th>
                            <th>Bundle Title</th>
                            <th>Sport Category</th>
                            <th>Total Odds</th>
                            <th>Pricing Ratio</th>
                            <th>Status Result</th>
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
                                while($p = $res->fetch_assoc()) {
                                    $col = 'text-warning';
                                    if ($p['status'] === 'won') $col = 'text-green-400';
                                    if ($p['status'] === 'lost') $col = 'text-red-400';
                                    ?>
                                    <tr class="border-bottom border-white/5 text-sm">
                                        <td><?= date('d M, Y', strtotime($p['created_at'])); ?></td>
                                        <td class="fw-bold text-white"><?= sanitize_input($p['title']); ?></td>
                                        <td><span class="badge bg-white/5 text-white/70"><?= sanitize_input($p['sport_type']); ?></span></td>
                                        <td class="font-mono"><?= number_format($p['total_odds'], 2); ?>x</td>
                                        <td class="font-bold text-info">$<?= number_format($p['price'], 2); ?></td>
                                        <td class="fw-bold text-xs uppercase <?= $col; ?>"><?= $p['status']; ?></td>
                                    </tr>
                                    <?php
                                }
                            } else {
                                ?>
                                <tr>
                                    <td colspan="6" class="text-center py-5 text-white/50">
                                        No managed listings active in records.
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
