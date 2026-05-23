<?php
/**
 * BETELITE - Profile Page (profile.php)
 * Displays user info and allows passwords updating / profile detail adjustments.
 */
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/security.php';
require_once __DIR__ . '/config/functions.php';

require_login();
$user_id = $_SESSION['user_id'];
$error = '';
$success = '';

// Fetch detailed record
$user = null;
$stmt = $conn->prepare("SELECT username, email, role, vip_until, telegram_id, created_at FROM `users` WHERE id = ?");
if ($stmt) {
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($row = $res->fetch_assoc()) {
        $user = $row;
    }
    $stmt->close();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $telegram_id = trim($_POST['telegram_id'] ?? '');
    $email = filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL);
    $token = $_POST['csrf_token'] ?? '';

    if (!validate_csrf_token($token)) {
        $error = "CSRF Token validation failed.";
    } elseif (!$email) {
        $error = "Please provide a valid email format.";
    } else {
        $update = $conn->prepare("UPDATE `users` SET email = ?, telegram_id = ? WHERE id = ?");
        $update->bind_param("ssi", $email, $telegram_id, $user_id);
        if ($update->execute()) {
            $success = "Profile metrics saved successfully!";
            $user['email'] = $email;
            $user['telegram_id'] = $telegram_id;
        } else {
            $error = "Updating halted due to databases overlap.";
        }
        $update->close();
    }
}

$csrf_token = generate_csrf_token();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile - BETELITE</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <style>
        body {
            background-color: #020617;
            color: #f8fafc;
            font-family: 'Inter', system-ui, sans-serif;
            background-image: radial-gradient(circle at 10% 20%, rgba(37, 99, 235, 0.1) 0%, transparent 45%);
        }
        .glass-card {
            background: rgba(17, 24, 39, 0.65);
            backdrop-filter: blur(24px);
            border: 1px solid rgba(255, 255, 255, 0.08);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.35);
            border-radius: 20px;
        }
        .glass-nav {
            background: rgba(2, 6, 23, 0.85);
            backdrop-filter: blur(20px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.06);
        }
        .glass-input {
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.06);
            color: #fff;
        }
    </style>
</head>
<body class="min-h-screen">

    <nav class="navbar navbar-expand-lg glass-nav navbar-dark py-3">
        <div class="container">
            <a class="navbar-brand font-bold" href="index.php">BET<span class="text-blue-500">ELITE</span></a>
            <a href="dashboard.php" class="btn btn-outline-light btn-sm rounded-pill px-3">Dashboard</a>
        </div>
    </nav>

    <div class="container py-5 max-w-lg">
        <h1 class="font-bold text-white mb-4 text-center">Platform Settings</h1>

        <div class="glass-card p-4">
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger bg-red-950/40 border-red-500/30 text-red-100 rounded-xl mb-3 text-sm py-2 px-3 flex gap-2"><i class="bi bi-exclamation-triangle"></i> <?= $error; ?></div>
            <?php endif; ?>
            <?php if (!empty($success)): ?>
                <div class="alert alert-success bg-green-950/40 border-green-500/30 text-green-100 rounded-xl mb-3 text-sm py-2 px-3 flex gap-2"><i class="bi bi-check-circle"></i> <?= $success; ?></div>
            <?php endif; ?>

            <form method="POST" action="profile.php">
                <input type="hidden" name="csrf_token" value="<?= $csrf_token; ?>">

                <div class="mb-3 text-center">
                    <span class="text-xs uppercase text-white/40 block mb-1">Account Role</span>
                    <span class="badge bg-blue-600/30 text-blue-400 border border-blue-500/20 px-3 py-1.5 rounded-full font-bold text-xs"><?= strtoupper($user['role'] ?? 'USER'); ?></span>
                </div>

                <div class="mb-3">
                    <label class="form-label text-xs uppercase text-white/60 mb-1">Username</label>
                    <input type="text" class="form-control glass-input py-2.5 rounded-xl text-sm" value="<?= sanitize_input($user['username'] ?? ''); ?>" disabled>
                    <span class="text-3xs text-white/30 block mt-1">Username values cannot be adjusted once signed up.</span>
                </div>

                <div class="mb-3">
                    <label class="form-label text-xs uppercase text-white/60 mb-1">Email Address</label>
                    <input type="email" name="email" class="form-control glass-input py-2.5 rounded-xl text-sm" value="<?= sanitize_input($user['email'] ?? ''); ?>" required>
                </div>

                <div class="mb-3">
                    <label class="form-label text-xs uppercase text-white/60 mb-1">Telegram Chat ID (Mini App Integration)</label>
                    <input type="text" name="telegram_id" class="form-control glass-input py-2.5 rounded-xl text-sm" value="<?= sanitize_input($user['telegram_id'] ?? ''); ?>" placeholder="e.g. 581230491">
                    <span class="text-3xs text-white/30 block mt-1">Needed to match automatic bot notification logs inside Telegram groups.</span>
                </div>

                <button type="submit" class="btn btn-primary w-100 py-2.5 rounded-xl border-0 bg-blue-600 hover:brightness-110 font-bold shadow-lg mt-3">
                    Update Profile Stats
                </button>
            </form>
        </div>
    </div>

</body>
</html>
