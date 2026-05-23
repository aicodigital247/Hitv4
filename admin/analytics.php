<?php
/**
 * BETELITE - Analytics Dashboard Controls (admin/analytics.php)
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/security.php';

require_role('admin');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><title>Analytics Insights - Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-slate-950 text-white min-h-screen p-5">
    <div class="container max-w-lg bg-slate-900 border border-white/5 rounded-2xl p-4 text-center">
        <h4 class="fw-bold mb-3"><i class="bi bi-bar-chart-line text-amber-500"></i> Financial Insights</h4>
        <p class="text-sm text-white/60 mb-4">Telemetric aggregation ratios for prediction payouts, commissions collection pool trends, and VIP counts maps.</p>
        
        <div class="p-3 bg-white/5 border border-white/5 rounded-xl mb-3">
            <span class="text-3xs uppercase tracking-wider text-white/50 block">Commission Revenue Share</span>
            <span class="h3 fw-bold text-green-400 font-mono">+$2,450.00 accrued</span>
        </div>
        
        <a href="index.php" class="btn btn-outline-light btn-sm rounded-pill mt-3">Back to Panel</a>
    </div>
</body>
</html>
