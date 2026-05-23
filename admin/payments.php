<?php
/**
 * BETELITE - Payments Auditing Dashboard (admin/payments.php)
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/security.php';

require_role('admin');

$res = $conn->query("SELECT t.*, u.username FROM `transactions` t JOIN `users` u ON t.user_id = u.id WHERE t.type='deposit' ORDER BY t.created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><title>Manage Payments - Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-slate-950 text-white min-h-screen p-5">
    <div class="container max-w-4xl bg-slate-900 border border-white/5 rounded-2xl p-4">
        <h4 class="fw-bold mb-4">Deposit Audit Log</h4>
        <table class="table table-dark table-borderless text-sm">
            <thead>
                <tr class="border-bottom border-white/5 uppercase text-xs text-white/50">
                    <th>User</th>
                    <th>Reference</th>
                    <th>Amount</th>
                    <th>Gateway</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $res->fetch_assoc()): ?>
                <tr class="border-bottom border-white/5">
                    <td>@<?= sanitize_input($row['username']); ?></td>
                    <td class="font-mono text-xs"><?= $row['reference']; ?></td>
                    <td class="text-green-400 font-bold font-mono">+$<?= number_format($row['amount'], 2); ?></td>
                    <td><?= sanitize_input($row['gateway']); ?></td>
                    <td class="text-white/50 text-xs"><?= date('d M Y H:i', strtotime($row['created_at'])); ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        <a href="index.php" class="btn btn-outline-light btn-sm rounded-pill mt-3">Back to Panel</a>
    </div>
</body>
</html>
