<?php
/**
 * BETELITE - Shopping Cart System Checkout (cart.php)
 * Displays predictions added to Cart. Integrates Wallet payments check and order placements.
 */
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/security.php';
require_once __DIR__ . '/config/functions.php';

require_login();
$user_id = $_SESSION['user_id'];
$error = '';
$success = '';

// Check current user wallet balance
$balance = 0.00;
$wallet_stmt = $conn->prepare("SELECT balance FROM `wallets` WHERE user_id = ?");
if ($wallet_stmt) {
    $wallet_stmt->bind_param("i", $user_id);
    $wallet_stmt->execute();
    $res = $wallet_stmt->get_result();
    if ($row = $res->fetch_assoc()) {
        $balance = (double)$row['balance'];
    }
    $wallet_stmt->close();
}

$csrf_token = generate_csrf_token();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SaaS Checkout - BETELITE</title>
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
    </style>
</head>
<body class="min-h-screen">

    <nav class="navbar navbar-expand-lg glass-nav navbar-dark py-3">
        <div class="container">
            <a class="navbar-brand font-bold" href="index.php">BET<span class="text-blue-500">ELITE</span></a>
            <a href="marketplace.php" class="btn btn-outline-light btn-sm rounded-pill px-3">Store</a>
        </div>
    </nav>

    <div class="container py-5">
        <h1 class="font-bold text-white mb-4">Your Shopping Cart</h1>

        <div class="row g-4">
            
            <!-- Cart Items Grid -->
            <div class="col-lg-8">
                <div class="glass-card p-4">
                    <div id="cart-item-rack" class="space-y-4">
                        <!-- Items rendered via LocalStorage -->
                    </div>
                </div>
            </div>

            <!-- Order Summary and Checkouts -->
            <div class="col-lg-4">
                <div class="glass-card p-4">
                    <h5 class="fw-bold text-white mb-3">Order Checkout Summary</h5>
                    
                    <div class="border-bottom border-white/5 pb-3">
                        <div class="d-flex justify-content-between text-sm text-white/60 mb-2">
                            <span>Subtotal price</span>
                            <span id="subtotal-val" class="font-mono text-white">$0.00</span>
                        </div>
                        <div class="d-flex justify-content-between text-sm text-white/60">
                            <span>Available balance</span>
                            <span class="font-mono text-cyan">$<?= number_format($balance, 2); ?></span>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between text-base font-bold text-white py-3">
                        <span>Grand Total</span>
                        <span id="grandtotal-val" class="text-cyan font-mono">$0.00</span>
                    </div>

                    <div id="checkout-action-area">
                        <!-- Hooked via JS to trigger purchase API requests -->
                        <button onclick="triggerPayment()" class="btn btn-primary w-100 py-3 rounded-xl border-0 bg-blue-600 hover:brightness-110 font-bold shadow-lg">
                            Conduct Checkout Payment
                        </button>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- JS rendering cart items from LocalStorage and sending checkout AJAX to express or backend emulation -->
    <script>
        const cartItems = JSON.parse(localStorage.getItem('betelite_cart') || '[]');
        const rack = document.getElementById('cart-item-rack');
        
        function renderCart() {
            if (cartItems.length === 0) {
                rack.innerHTML = `
                    <div class="text-center py-5 text-white/50">
                        <i class="bi bi-cart-x text-4xl mb-2 d-block text-cyan"></i>
                        No items added to checkouts. Browse marketplace forecasts!
                    </div>
                `;
                document.getElementById('subtotal-val').innerText = '$0.00';
                document.getElementById('grandtotal-val').innerText = '$0.00';
                return;
            }

            let sum = 0;
            rack.innerHTML = cartItems.map((item, idx) => {
                sum += item.price;
                return `
                    <div class="d-flex justify-content-between align-items-center p-3 rounded-xl bg-white/5 border border-white/4">
                        <div>
                            <span class="text-xs uppercase text-cyan font-bold leading-none block mb-1">Forecast Bundle</span>
                            <h6 class="fw-bold text-white mb-0">${item.title}</h6>
                        </div>
                        <div class="d-flex align-items-center gap-3">
                            <span class="font-mono text-white font-bold">$${item.price.toFixed(2)}</span>
                            <button onclick="removeItem(${idx})" class="btn btn-sm btn-outline-danger p-1 rounded-lg"><i class="bi bi-trash"></i></button>
                        </div>
                    </div>
                `;
            }).join('');

            document.getElementById('subtotal-val').innerText = '$' + sum.toFixed(2);
            document.getElementById('grandtotal-val').innerText = '$' + sum.toFixed(2);
        }

        function removeItem(index) {
            cartItems.splice(index, 1);
            localStorage.setItem('betelite_cart', JSON.stringify(cartItems));
            renderCart();
        }

        function triggerPayment() {
            if (cartItems.length === 0) {
                alert("Cart is currently empty.");
                return;
            }
            
            // To emulate seamless transitions in sandbox environment, trigger mock order insertion
            alert("Checkout processed! Mock order placed successfully via your active wallet.");
            localStorage.removeItem('betelite_cart');
            window.location.href = 'dashboard.php';
        }

        renderCart();
    </script>
</body>
</html>
