<?php
/**
 * BETELITE - Manage Predictions (admin/predictions.php)
 * Audit forecast bundles as admin and mark as won or lost.
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/security.php';

require_role('admin');

$res = $conn->query("SELECT p.*, u.username FROM `predictions` p JOIN `users` u ON p.predictor_id = u.id ORDER BY p.created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><title>Manage Predictions - Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-slate-950 text-white min-h-screen p-5">
    <div class="container max-w-4xl bg-slate-900 border border-white/5 rounded-2xl p-4">
        <h4 class="fw-bold mb-4">Predictions Auditing Console</h4>
        <table class="table table-dark table-borderless text-sm">
            <thead>
                <tr class="border-bottom border-white/5 uppercase text-xs text-white/50">
                    <th>Title</th>
                    <th>Predictor</th>
                    <th>Price</th>
                    <th>Odds</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $res->fetch_assoc()): ?>
                <tr class="border-bottom border-white/5">
                    <td class="fw-bold"><?= sanitize_input($row['title']); ?></td>
                    <td class="text-info">@<?= sanitize_input($row['username']); ?></td>
                    <td>$<?= number_format($row['price'], 2); ?></td>
                    <td><?= number_format($row['total_odds'], 2); ?>x</td>
                    <td class="uppercase fw-bold"><?= $row['status']; ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        <a href="index.php" class="btn btn-outline-light btn-sm rounded-pill mt-3">Back to Panel</a>
    </div>
</body>
</html>
