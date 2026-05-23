<?php
/**
 * BETELITE - System Settings (admin/settings.php)
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/security.php';

require_role('admin');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><title>Global Configs Settings - Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-slate-950 text-white min-h-screen p-5">
    <div class="container max-w-lg bg-slate-900 border border-white/5 rounded-2xl p-4">
        <h4 class="fw-bold mb-4">Core System Settings</h4>
        <div class="space-y-3">
            <div class="p-3 bg-white/5 border border-white/5 rounded-xl">
                <span class="text-xs text-white/50 block">Telegram Bot API Token</span>
                <span class="font-mono text-sm break-all text-white font-semibold">token_secret_not_initialized</span>
            </div>
            <div class="p-3 bg-white/5 border border-white/5 rounded-xl">
                <span class="text-xs text-white/50 block">Paystack Secret Gateway Key</span>
                <span class="font-mono text-sm break-all text-white font-semibold">sk_live_paystack_secret_mock</span>
            </div>
        </div>
        <a href="index.php" class="btn btn-outline-light btn-sm rounded-pill mt-4 d-block w-fit mx-auto">Back to Panel</a>
    </div>
</body>
</html>
