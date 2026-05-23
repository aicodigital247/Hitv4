<?php
/**
 * BETELITE - Essential Helper Functions For Wallet, Referrals, Predictions
 * Implements transaction logs & clean prepared statements
 */

require_once __DIR__ . '/database.php';

// Log unified notification to users
function create_notification($conn, $user_id, $title, $message) {
    $stmt = $conn->prepare("INSERT INTO `notifications` (user_id, title, message) VALUES (?, ?, ?)");
    if ($stmt) {
        $stmt->bind_param("iss", $user_id, $title, $message);
        $stmt->execute();
        $stmt->close();
        return true;
    }
    return false;
}

// Safely modify user wallet and log transaction
function change_wallet_balance($conn, $user_id, $amount, $type, $description, $reference = null, $gateway = 'wallet') {
    // Generate unique reference if missing
    if (!$reference) {
        $reference = 'TRX-' . strtoupper(bin2hex(random_bytes(8)));
    }

    $conn->begin_transaction();

    try {
        // Fetch current wallet
        $stmt = $conn->prepare("SELECT balance FROM `wallets` WHERE user_id = ? FOR UPDATE");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $res = $stmt->get_result();
        
        if ($res->num_rows === 0) {
            // Initiate wallet if empty
            $init = $conn->prepare("INSERT INTO `wallets` (user_id, balance) VALUES (?, 0.00)");
            $init->bind_param("i", $user_id);
            $init->execute();
            $init->close();
            $balance = 0.0;
        } else {
            $balance = (double)$res->fetch_assoc()['balance'];
        }
        $stmt->close();

        // Calculate new balance
        $new_balance = $balance + (double)$amount;
        if ($new_balance < 0) {
            throw new Exception("Insufficient wallet funds.");
        }

        // Update Wallet
        $update = $conn->prepare("UPDATE `wallets` SET balance = ? WHERE user_id = ?");
        $update->bind_param("di", $new_balance, $user_id);
        $update->execute();
        $update->close();

        // Log Transaction
        $status = 'completed';
        $log = $conn->prepare("INSERT INTO `transactions` (user_id, amount, type, status, description, reference, gateway) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $log->bind_param("idsssss", $user_id, $amount, $type, $status, $description, $reference, $gateway);
        $log->execute();
        $log->close();

        $conn->commit();
        
        // Notify user of balance change
        $signed_val = ($amount >= 0 ? '+' : '') . CURRENCY_SYMBOL . number_format($amount, 2);
        create_notification($conn, $user_id, "Wallet Transaction Completed", "Your wallet was adjusted by $signed_val. Reason: $description. New balance: " . CURRENCY_SYMBOL . number_format($new_balance, 2));

        return [
            'success' => true,
            'new_balance' => $new_balance,
            'reference' => $reference
        ];

    } catch (Exception $e) {
        $conn->rollback();
        return [
            'success' => false,
            'error' => $e->getMessage()
        ];
    }
}

// Calculate Predictor Stats
function get_predictor_stats($conn, $predictor_id) {
    $stats = [
        'win_rate' => 0,
        'total_predictions' => 0,
        'won_count' => 0,
        'rating' => 5.0
    ];

    // Count won/total predictions
    $stmt = $conn->prepare("SELECT status, COUNT(*) as count FROM `predictions` WHERE predictor_id = ? GROUP BY status");
    if ($stmt) {
        $stmt->bind_param("i", $predictor_id);
        $stmt->execute();
        $res = $stmt->get_result();
        while ($row = $res->fetch_assoc()) {
            $stats['total_predictions'] += $row['count'];
            if ($row['status'] === 'won') {
                $stats['won_count'] = $row['count'];
            }
        }
        $stmt->close();
    }

    if ($stats['total_predictions'] > 0) {
        $stats['win_rate'] = round(($stats['won_count'] / $stats['total_predictions']) * 100);
    }

    // Average rating
    $rating_stmt = $conn->prepare("SELECT AVG(rating) as avg_rate FROM `ratings` WHERE predictor_id = ?");
    if ($rating_stmt) {
        $rating_stmt->bind_param("i", $predictor_id);
        $rating_stmt->execute();
        $res = $rating_stmt->get_result();
        if ($row = $res->fetch_assoc()) {
            $stats['rating'] = $row['avg_rate'] ? round($row['avg_rate'], 1) : 4.8;
        }
        $rating_stmt->close();
    }

    return $stats;
}
