<?php
/**
 * BETELITE - Dynamic Glassmorphic User Registration Page (register.php)
 * Registers users and links referrals correctly, initializing empty wallets.
 */
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/security.php';
require_once __DIR__ . '/config/functions.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = preg_replace('/[^a-zA-Z0-9_]/', '', $_POST['username'] ?? '');
    $email = filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL);
    $password = $_POST['password'] ?? '';
    $role = $_POST['role'] ?? 'user';
    $ref_code = trim($_POST['referral'] ?? '');
    $token = $_POST['csrf_token'] ?? '';

    if (!validate_csrf_token($token)) {
        $error = "CSRF Token validation failed.";
    } elseif (strlen($username) < 3) {
        $error = "Username must be at least 3 characters.";
    } elseif (!$email) {
        $error = "Please provide a valid email address.";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters.";
    } elseif (!in_array($role, ['user', 'predictor'])) {
        $error = "Invalid role specified.";
    } else {
        // Enforce role and checks
        $password_hash = password_hash($password, PASSWORD_BCRYPT);
        
        // Check uniqueness of username and email
        $check = $conn->prepare("SELECT id FROM `users` WHERE username = ? OR email = ?");
        $check->bind_param("ss", $username, $email);
        $check->execute();
        $check->store_result();
        
        if ($check->num_rows > 0) {
            $error = "Username or Email already takes space in BETELITE.";
            $check->close();
        } else {
            $check->close();
            
            // Generate a unique referral code
            $my_ref_code = 'REF-' . strtoupper(substr(md5($username . time()), 0, 8));
            
            // Check if referred by someone
            $referred_by_id = null;
            if (!empty($ref_code)) {
                $ref_stmt = $conn->prepare("SELECT id FROM `users` WHERE referral_code = ?");
                $ref_stmt->bind_param("s", $ref_code);
                $ref_stmt->execute();
                $ref_res = $ref_stmt->get_result();
                if ($ref_row = $ref_res->fetch_assoc()) {
                    $referred_by_id = $ref_row['id'];
                }
                $ref_stmt->close();
            }
            
            // Perform actual database insert
            $conn->begin_transaction();
            try {
                $stmt = $conn->prepare("INSERT INTO `users` (username, email, password, role, referral_code, referred_by) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("sssssi", $username, $email, $password_hash, $role, $my_ref_code, $referred_by_id);
                $stmt->execute();
                $new_id = $conn->insert_id;
                $stmt->close();
                
                // Initialize wallet with standard $0.00
                $wallet_stmt = $conn->prepare("INSERT INTO `wallets` (user_id, balance) VALUES (?, 0.00)");
                $wallet_stmt->bind_param("i", $new_id);
                $wallet_stmt->execute();
                $wallet_stmt->close();
                
                // Log and process Referral reward if relevant
                if ($referred_by_id) {
                    // Create referral link track
                    $ref_track = $conn->prepare("INSERT INTO `referrals` (referrer_id, referred_id) VALUES (?, ?)");
                    $ref_track->bind_param("ii", $referred_by_id, $new_id);
                    $ref_track->execute();
                    $ref_track->close();
                    
                    // Award referrer instantly with $5 dynamically
                    $bonus = (double)get_platform_setting($conn, 'referral_bonus_amount', '5.00');
                    change_wallet_balance($conn, $referred_by_id, $bonus, 'referral_bonus', "Referral bonus for inviting $username", 'REF-B-' . strtoupper(bin2hex(random_bytes(6))));
                    
                    // Welcome bonus for registerer
                    change_wallet_balance($conn, $new_id, 2.00, 'deposit', "Sign up welcome bonus!", 'SGN-' . strtoupper(bin2hex(random_bytes(6))));
                } else {
                    // Free $1 standard registration reward
                    change_wallet_balance($conn, $new_id, 1.00, 'deposit', "Sign up welcome bonus!", 'SGN-' . strtoupper(bin2hex(random_bytes(6))));
                }

                $conn->commit();
                $success = "Registration successful! You may now login and trade.";
                
            } catch (Exception $e) {
                $conn->rollback();
                $error = "Registration process halted: " . $e->getMessage();
            }
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
    <title>Register - BETELITE</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <style>
        body {
            background-color: #020617;
            color: #f8fafc;
            font-family: 'Inter', system-ui, sans-serif;
            background-image: radial-gradient(circle at 90% 80%, rgba(6, 182, 212, 0.12) 0%, transparent 40%);
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
            border-color: #06b6d4;
            color: #fff;
            box-shadow: 0 0 10px rgba(6, 182, 212, 0.25);
        }
    </style>
</head>
<body class="min-h-screen d-flex align-items-center justify-content-center p-3">

    <div class="w-100 max-w-sm">
        <div class="text-center mb-4">
            <a href="index.php" class="h2 text-white font-bold tracking-wider text-decoration-none">
                BET<span class="text-cyan">ELITE</span>
            </a>
            <p class="text-sm text-white/50 mt-1">SaaS Football Forecast & Marketplace</p>
        </div>

        <div class="glass-card p-4">
            <h4 class="text-center fw-bold mb-4">Create Account</h4>

            <?php if (!empty($error)): ?>
                <div class="alert alert-danger bg-red-950/40 border-red-500/30 text-red-200 text-sm rounded-xl py-2 px-3 mb-3 d-flex align-items-center gap-2">
                    <i class="bi bi-exclamation-triangle"></i> <?= sanitize_input($error); ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($success)): ?>
                <div class="alert alert-success bg-green-950/40 border-green-500/30 text-green-200 text-sm rounded-xl py-2 px-3 mb-3 d-flex align-items-center gap-2">
                    <i class="bi bi-check-circle"></i> <?= sanitize_input($success); ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="register.php">
                <input type="hidden" name="csrf_token" value="<?= $csrf_token; ?>">

                <div class="mb-3">
                    <label class="form-label text-xs uppercase text-white/60 font-semibold mb-1">Select Account Type</label>
                    <div class="row g-2">
                        <div class="col-6">
                            <input type="radio" class="btn-check" name="role" id="role_user" value="user" checked>
                            <label class="btn btn-outline-light w-100 rounded-xl py-2 text-sm" for="role_user"><i class="bi bi-person"></i> Buyer</label>
                        </div>
                        <div class="col-6">
                            <input type="radio" class="btn-check" name="role" id="role_predictor" value="predictor">
                            <label class="btn btn-outline-light w-100 rounded-xl py-2 text-sm" for="role_predictor"><i class="bi bi-shield-check"></i> Predictor</label>
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label text-xs uppercase text-white/60 font-semibold mb-1">Username</label>
                    <input type="text" name="username" class="form-control glass-input py-2.5 rounded-xl text-sm" placeholder="e.g. janesoccer" required>
                </div>

                <div class="mb-3">
                    <label class="form-label text-xs uppercase text-white/60 font-semibold mb-1">Email Address</label>
                    <input type="email" name="email" class="form-control glass-input py-2.5 rounded-xl text-sm" placeholder="e.g. jane@mail.com" required>
                </div>

                <div class="mb-3">
                    <label class="form-label text-xs uppercase text-white/60 font-semibold mb-1">Secure Password</label>
                    <input type="password" name="password" class="form-control glass-input py-2.5 rounded-xl text-sm" placeholder="••••••••••••" required>
                </div>

                <div class="mb-3">
                    <label class="form-label text-xs uppercase text-white/60 font-semibold mb-1">Referral Code (Optional)</label>
                    <input type="text" name="referral" class="form-control glass-input py-2.5 rounded-xl text-sm" placeholder="e.g. BETELITETIPSTER">
                </div>

                <button type="submit" class="btn btn-primary w-100 rounded-xl py-2.5 font-bold tracking-wide border-0 shadow-lg bg-cyan hover:brightness-110 mt-3">
                    Join Platform
                </button>
            </form>

            <div class="text-center mt-4">
                <p class="text-xs text-white/40 mb-1">Already standard on BETELITE?</p>
                <a href="login.php" class="text-cyan text-xs font-bold text-decoration-none hover:underline">Log in to your account &rarr;</a>
            </div>
        </div>
    </div>

</body>
</html>
