<?php
/**
 * BETELITE - REST API Wallet balance checks
 */
header("Content-Type: application/json");
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/config.php';

$user_id = $_SESSION['user_id'] ?? 0;
$balance = 0.00;

if ($user_id) {
    $stmt = $conn->prepare("SELECT balance FROM `wallets` WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $res = $stmt->get_result();
    if($row = $res->fetch_assoc()) {
        $balance = (double)$row['balance'];
    }
    $stmt->close();
}

echo json_encode(["status" => "success", "balance" => $balance]);
