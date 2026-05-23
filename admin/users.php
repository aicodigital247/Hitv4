<?php
/**
 * BETELITE - Manage Users (admin/users.php)
 * Dedicated admin board to view and suspend users.
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/security.php';

require_role('admin');

$res = $conn->query("SELECT id, username, email, role, status, created_at FROM `users` ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><title>Manage Users - Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-slate-950 text-white min-h-screen p-5">
    <div class="container max-w-4xl bg-slate-900 border border-white/5 rounded-2xl p-4">
        <h4 class="fw-bold mb-4">Users Management Registry</h4>
        <table class="table table-dark table-borderless text-sm">
            <thead>
                <tr class="border-bottom border-white/5 uppercase text-xs text-white/50">
                    <th>Username</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th>Registered</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $res->fetch_assoc()): ?>
                <tr class="border-bottom border-white/5">
                    <td class="fw-bold">@<?= sanitize_input($row['username']); ?></td>
                    <td><?= sanitize_input($row['email']); ?></td>
                    <td><span class="badge bg-white/5 text-white"><?= strtoupper($row['role']); ?></span></td>
                    <td><span class="text-green-400 font-bold"><?= strtoupper($row['status']); ?></span></td>
                    <td class="text-white/55 text-xs"><?= date('d M Y', strtotime($row['created_at'])); ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        <a href="index.php" class="btn btn-outline-light btn-sm rounded-pill mt-3">Back to Panel</a>
    </div>
</body>
</html>
