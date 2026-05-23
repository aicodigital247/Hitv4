<?php
/**
 * BETELITE - REST API Matches
 */
header("Content-Type: application/json");
require_once __DIR__ . '/../config/database.php';

$res = $conn->query("SELECT * FROM `matches` LIMIT 10");
$matches = [];
while ($row = $res->fetch_assoc()) {
    $matches[] = $row;
}
echo json_encode(["status" => "success", "data" => $matches]);
