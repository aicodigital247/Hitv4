<?php
/**
 * BETELITE - Withdrawals Management (admin/withdrawals.php)
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/security.php';

require_role('admin');

$res = $conn->query("SELECT t.*, u.username FROM `transactions` t JOIN `users` u ON t.user_id = u.id WHERE t.type='withdrawal' ORDER BY t.created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><title>Manage Withdrawals - Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-slate-950 text-white min-h-screen p-5">
    <div class="container max-w-4xl bg-slate-900 border border-white/5 rounded-2xl p-4">
        <h4 class="fw-bold mb-4">Withdrawals Approval Dashboard</h4>
        <table class="table table-dark table-borderless text-sm">
            <thead>
                <tr class="border-bottom border-white/5 uppercase text-xs text-white/50">
                    <th>User</th>
                    <th>Reference</th>
                    <th>Amount Requested</th>
                    <th>Bank Description</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $res->fetch_assoc()): ?>
                <tr class="border-bottom border-white/5 text-sm">
                    <td>@<?= sanitize_input($row['username']); ?></td>
                    <td class="font-mono text-xs"><?= $row['reference']; ?></td>
                    <td class="text-red-400 font-bold font-mono">-$<?= number_format(abs($row['amount']), 2); ?></td>
                    <td><?= sanitize_input($row['description']); ?></td>
                    <td><span class="badge bg-green-500/10 text-green-300 border border-green-500/20 px-2 py-1 text-2xs uppercase fw-bold"><?= $row['status']; ?></span></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        <a href="index.php" class="btn btn-outline-light btn-sm rounded-pill mt-3">Back to Panel</a>
    </div>
</body>
</html>
