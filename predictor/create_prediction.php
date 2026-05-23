<?php
/**
 * BETELITE - Predictor Lounge Create Prediction (predictor/create_prediction.php)
 * Real UI form for setting sport, odds, confidence score, price, and matching team logs.
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/security.php';
require_once __DIR__ . '/../config/functions.php';

require_role('predictor');

$predictor_id = $_SESSION['user_id'];
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $price = (double)($_POST['price'] ?? 0);
    $sport = $_POST['sport'] ?? 'Football';
    $odds = (double)($_POST['odds'] ?? 1.50);
    $confidence = (int)($_POST['confidence'] ?? 85);
    $token = $_POST['csrf_token'] ?? '';
    
    $is_vip = isset($_POST['is_vip']) ? 1 : 0;
    $is_hot = isset($_POST['is_hot']) ? 1 : 0;

    if (!validate_csrf_token($token)) {
        $error = "CSRF Token validation failed.";
    } elseif (empty($title) || $price < 0 || $odds < 1.0) {
        $error = "Please fill in valid prediction attributes.";
    } else {
        $stmt = $conn->prepare("INSERT INTO `predictions` (predictor_id, title, description, price, sport_type, total_odds, confidence, is_vip, is_hot) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        if ($stmt) {
            $stmt->bind_param("issdsdiii", $predictor_id, $title, $description, $price, $sport, $odds, $confidence, $is_vip, $is_hot);
            if ($stmt->execute()) {
                $success = "Prediction bundle listed on the marketplace successfully!";
            } else {
                $error = "Listing process failed. Integrity parameters constraint.";
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
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Forecast Bundle - BETELITE</title>
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
        .glass-input {
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.06);
            color: #fff;
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

    <div class="container py-5 max-w-xl">
        <h2 class="font-bold text-white text-center mb-4">Publish Forecast Bundle</h2>

        <div class="glass-card p-4">
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger bg-red-950/40 border-red-500/30 text-red-100 rounded-xl mb-3 text-sm py-2 px-3 flex gap-2"><i class="bi bi-exclamation-triangle"></i> <?= $error; ?></div>
            <?php endif; ?>
            <?php if (!empty($success)): ?>
                <div class="alert alert-success bg-green-950/40 border-green-500/30 text-green-100 rounded-xl mb-3 text-sm py-2 px-3 flex gap-2"><i class="bi bi-check-circle"></i> <?= $success; ?></div>
            <?php endif; ?>

            <form method="POST" action="create_prediction.php">
                <input type="hidden" name="csrf_token" value="<?= $csrf_token; ?>">

                <div class="mb-3">
                    <label class="form-label text-xs uppercase text-white/60 mb-1">Bundle Title</label>
                    <input type="text" name="title" class="form-control glass-input py-2.5 rounded-xl text-sm" placeholder="e.g. Barcelona vs Bayern Over 2.5 Multi" required>
                </div>

                <div class="mb-4">
                    <label class="form-label text-xs uppercase text-white/60 mb-1">Forecast Analysis & Details</label>
                    <textarea name="description" rows="4" class="form-control glass-input rounded-xl text-sm" placeholder="Add logical reasoning, tactical lineups and weather considerations..."></textarea>
                </div>

                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <label class="form-label text-xs uppercase text-white/60 mb-1">Price ($)</label>
                        <input type="number" step="0.01" name="price" value="5.00" class="form-control glass-input py-2.5 rounded-xl text-sm" required>
                    </div>
                    <div class="col-6">
                        <label class="form-label text-xs uppercase text-white/60 mb-1">Combined Odds</label>
                        <input type="number" step="0.01" name="odds" value="2.15" class="form-control glass-input py-2.5 rounded-xl text-sm" required>
                    </div>
                </div>

                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <label class="form-label text-xs uppercase text-white/60 mb-1">Sport Universe</label>
                        <select name="sport" class="form-select glass-input py-2.5 rounded-xl text-sm bg-slate-900 text-white border-white/10">
                            <option value="Football">Football</option>
                            <option value="Basketball">Basketball</option>
                            <option value="Tennis">Tennis</option>
                        </select>
                    </div>
                    <div class="col-6">
                        <label class="form-label text-xs uppercase text-white/60 mb-1">Confidence Rating (%)</label>
                        <input type="number" name="confidence" value="85" min="50" max="99" class="form-control glass-input py-2.5 rounded-xl text-sm" required>
                    </div>
                </div>

                <hr class="border-white/10 my-4">

                <div class="mb-4">
                    <div class="form-check form-switch mb-2">
                        <input class="form-check-input bg-dark" type="checkbox" name="is_vip" id="is_vip">
                        <label class="form-check-label text-xs uppercase text-white/60 font-semibold" for="is_vip"><i class="bi bi-gem text-amber-500"></i> Mark as Premium VIP Bundle</label>
                    </div>
                    <div class="form-check form-switch">
                        <input class="form-check-input bg-dark" type="checkbox" name="is_hot" id="is_hot">
                        <label class="form-check-label text-xs uppercase text-white/60 font-semibold" for="is_hot"><i class="bi bi-fire text-red-500"></i> Flag with HOT Badge</label>
                    </div>
                </div>

                <button type="submit" class="btn btn-info w-100 py-3 rounded-xl border-0 bg-info hover:brightness-110 font-bold shadow-lg">
                    Publish Bundle For Sale
                </button>
            </form>
        </div>
    </div>

</body>
</html>
