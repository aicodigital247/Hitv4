<?php
/**
 * BETELITE - REST API Register
 */
header("Content-Type: application/json");
require_once __DIR__ . '/../config/database.php';

echo json_encode(["status" => "success", "message" => "Account signup endpoint ready. Integrate via registration views."]);
