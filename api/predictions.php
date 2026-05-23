<?php
/**
 * BETELITE - REST API Predictions
 */
header("Content-Type: application/json");
require_once __DIR__ . '/../config/database.php';

$res = $conn->query("SELECT * FROM `predictions` LIMIT 10");
$predictions = [];
while ($row = $res->fetch_assoc()) {
    $predictions[] = $row;
}
echo json_encode(["status" => "success", "data" => $predictions]);
