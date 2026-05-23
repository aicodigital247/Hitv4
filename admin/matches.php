<?php
/**
 * BETELITE - Manage Live Matches (admin/matches.php)
 * Real UI form for creating matches and listing them.
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/security.php';

require_role('admin');

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $home = trim($_POST['home_team'] ?? '');
    $away = trim($_POST['away_team'] ?? '');
    $league = trim($_POST['league'] ?? '');
    $time = $_POST['kickoff_time'] ?? '';
    $token = $_POST['csrf_token'] ?? '';

    if (!validate_csrf_token($token)) {
        $error = "CSRF Verification failed.";
    } elseif (empty($home) || empty($away) || empty($league)) {
        $error = "Please fill in all complete fields.";
    } else {
        $stmt = $conn->prepare("INSERT INTO `matches` (home_team, away_team, league, kickoff_time, status) VALUES (?, ?, ?, ?, 'scheduled')");
        if ($stmt) {
            $stmt->bind_param("ssss", $home, $away, $league, $time);
            if ($stmt->execute()) {
                $success = "Live football fixture registered successfully!";
            }
            $stmt->close();
        }
    }
}

$csrf_token = generate_csrf_token();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><title>Manage Matches - Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-slate-950 text-white min-h-screen p-5">
    <div class="container max-w-lg bg-slate-900 border border-white/5 rounded-2xl p-4">
        <h4 class="fw-bold mb-4">Register Live Match Fixture</h4>
        
        <?php if (!empty($success)): ?>
            <div class="alert alert-success bg-green-500/10 border-green-500/25 text-green-300 rounded-xl py-2 px-3 mb-3 text-sm"><?= $success; ?></div>
        <?php endif; ?>

        <form method="POST" action="matches.php">
            <input type="hidden" name="csrf_token" value="<?= $csrf_token; ?>">
            <div class="mb-3">
                <label class="form-label text-xs uppercase text-white/50 mb-1">Home Team</label>
                <input type="text" name="home_team" class="form-control bg-white/5 border-white/10 text-white rounded-xl text-sm py-2.5" required>
            </div>
            <div class="mb-3">
                <label class="form-label text-xs uppercase text-white/50 mb-1">Away Team</label>
                <input type="text" name="away_team" class="form-control bg-white/5 border-white/10 text-white rounded-xl text-sm py-2.5" required>
            </div>
            <div class="mb-3">
                <label class="form-label text-xs uppercase text-white/50 mb-1">League Title</label>
                <input type="text" name="league" class="form-control bg-white/5 border-white/10 text-white rounded-xl text-sm py-2.5" placeholder="e.g. Champions League" required>
            </div>
            <div class="mb-4">
                <label class="form-label text-xs uppercase text-white/50 mb-1">Kickoff Datetime</label>
                <input type="datetime-local" name="kickoff_time" class="form-control bg-white/5 border-white/10 text-white rounded-xl text-sm py-2.5" required>
            </div>
            <button type="submit" class="btn btn-warning w-100 py-2.5 font-bold border-0 bg-yellow-500 text-black rounded-xl">Register Match Fixture</button>
        </form>
        <a href="index.php" class="btn btn-outline-light btn-sm rounded-pill mt-4 d-block text-center w-fit mx-auto">Back to Panel</a>
    </div>
</body>
</html>
