<?php
/**
 * BETELITE - Dynamic Glassmorphic Login Page (login.php)
 * Authenticates users and handles standard session management
 */
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/security.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $token = $_POST['csrf_token'] ?? '';

    if (!validate_csrf_token($token)) {
        $error = "CSRF Token verification failed.";
    } elseif (empty($username) || empty($password)) {
        $error = "Please fill in all database login requirements.";
    } else {
        // Query users table
        $stmt = $conn->prepare("SELECT id, username, password, role, status FROM `users` WHERE username = ? OR email = ?");
        if ($stmt) {
            $stmt->bind_param("ss", $username, $username);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result && $row = $result->fetch_assoc()) {
                if ($row['status'] === 'suspended') {
                    $error = "This account has been temporarily suspended.";
                } elseif (password_verify($password, $row['password'])) {
                    // Success! Store session parameters
                    $_SESSION['user_id'] = $row['id'];
                    $_SESSION['username'] = $row['username'];
                    $_SESSION['role'] = $row['role'];
                    
                    header("Location: dashboard.php");
                    exit;
                } else {
                    $error = "Invalid credential combinations provided.";
                }
            } else {
                $error = "User not registered in the system.";
            }
            $stmt->close();
        } else {
            $error = "Systems processing failure. Please retry.";
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
    <title>Login - BETELITE</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <style>
        body {
            background-color: #020617;
            color: #f8fafc;
            font-family: 'Inter', system-ui, sans-serif;
            background-image: radial-gradient(circle at 10% 20%, rgba(37, 99, 235, 0.12) 0%, transparent 40%);
        }
        .glass-card {
            background: rgba(17, 24, 39, 0.65);
            backdrop-filter: blur(24px);
            border: 1px solid rgba(255, 255, 255, 0.08);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.35);
            border-radius: 24px;
        }
        .glass-input {
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.06);
            color: #fff;
        }
        .glass-input:focus {
            background: rgba(255, 255, 255, 0.06);
            border-color: #2563eb;
            color: #fff;
            box-shadow: 0 0 10px rgba(37, 99, 235, 0.2);
        }
    </style>
</head>
<body class="min-h-screen d-flex align-items-center justify-content-center p-3">

    <div class="w-100 max-w-sm">
        <div class="text-center mb-4">
            <a href="index.php" class="h2 text-white font-bold tracking-wider text-decoration-none">
                BET<span class="text-blue-500">ELITE</span>
            </a>
            <p class="text-sm text-white/50 mt-1">SaaS Football Forecast & Marketplace</p>
        </div>

        <div class="glass-card p-4">
            <h4 class="text-center fw-bold mb-4">Account Login</h4>

            <?php if (!empty($error)): ?>
                <div class="alert alert-danger bg-red-950/40 border-red-500/30 text-red-200 text-sm rounded-xl py-2 px-3 mb-3 d-flex align-items-center gap-2">
                    <i class="bi bi-exclamation-triangle"></i> <?= sanitize_input($error); ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="login.php">
                <input type="hidden" name="csrf_token" value="<?= $csrf_token; ?>">

                <div class="mb-3">
                    <label class="form-label text-xs uppercase text-white/60 font-semibold mb-1">Username or Email</label>
                    <input type="text" name="username" class="form-control glass-input py-2.5 rounded-xl text-sm" placeholder="e.g. admin or tipster" required>
                </div>

                <div class="mb-3">
                    <label class="form-label text-xs uppercase text-white/60 font-semibold mb-1">Password</label>
                    <input type="password" name="password" class="form-control glass-input py-2.5 rounded-xl text-sm" placeholder="••••••••••••" required>
                </div>

                <button type="submit" class="btn btn-primary w-100 rounded-xl py-2.5 font-bold tracking-wide border-0 shadow-lg bg-blue-600 hover:brightness-110 mt-3">
                    Authenticate Account
                </button>
            </form>

            <div class="text-center mt-4">
                <p class="text-xs text-white/40 mb-1">No account yet?</p>
                <a href="register.php" class="text-blue-400 text-xs font-bold text-decoration-none hover:underline">Get started in under 1 minute &rarr;</a>
            </div>
            
            <hr class="border-white/10 my-4">
            
            <div class="text-xs text-white/30 text-center">
                User profile mocks: <br>
                Admin profile: <strong class="text-white/60">admin / AdminPass123!</strong><br>
                Predictor profile: <strong class="text-white/60">tipster / TipsterPass123!</strong>
            </div>
        </div>
    </div>

</body>
</html>
