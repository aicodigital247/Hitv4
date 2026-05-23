<?php
/**
 * BETELITE - Ads Panel Console (admin/ads.php)
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/security.php';

require_role('admin');

$res = $conn->query("SELECT * FROM `ads` ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><title>Manage Banner Ads - Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-slate-950 text-white min-h-screen p-5">
    <div class="container max-w-4xl bg-slate-900 border border-white/5 rounded-2xl p-4">
        <h4 class="fw-bold mb-4">Promotional Campaign Ads</h4>
        <table class="table table-dark table-borderless text-sm">
            <thead>
                <tr class="border-bottom border-white/5 uppercase text-xs text-white/50">
                    <th>Campaign Header</th>
                    <th>Link Target URL</th>
                    <th>Positioning</th>
                    <th>Clicks Count</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($res && $res->num_rows > 0): ?>
                    <?php while ($row = $res->fetch_assoc()): ?>
                    <tr class="border-bottom border-white/5">
                        <td class="fw-bold"><?= sanitize_input($row['title']); ?></td>
                        <td class="font-mono text-xs"><?= sanitize_input($row['link_url']); ?></td>
                        <td><?= sanitize_input($row['position']); ?></td>
                        <td class="font-mono"><?= $row['clicks']; ?> clicks</td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4" class="text-center py-5 text-white/50">No promotional campaign ad metrics active.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
        <a href="index.php" class="btn btn-outline-light btn-sm rounded-pill mt-3">Back to Panel</a>
    </div>
</body>
</html>
