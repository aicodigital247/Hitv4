<?php
/**
 * BETELITE - REST API Login
 */
header("Content-Type: application/json");
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/security.php';

$response = ["status" => "error", "message" => "Invalid requests direction."];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $response["message"] = "Missing auth attributes.";
    } else {
        $stmt = $conn->prepare("SELECT id, username, password, role FROM `users` WHERE username = ?");
        if ($stmt) {
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $res = $stmt->get_result();
            if ($row = $res->fetch_assoc()) {
                if (password_verify($password, $row['password'])) {
                    $_SESSION['user_id'] = $row['id'];
                    $_SESSION['username'] = $row['username'];
                    $_SESSION['role'] = $row['role'];
                    $response = ["status" => "success", "message" => "Authorized completed!"];
                } else {
                    $response["message"] = "Incorrect credentials.";
                }
            } else {
                $response["message"] = "User does not exist.";
            }
            $stmt->close();
        }
    }
}

echo json_encode($response);
