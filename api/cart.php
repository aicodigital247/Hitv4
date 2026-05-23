<?php
/**
 * BETELITE - REST API Cart
 */
header("Content-Type: application/json");

echo json_encode(["status" => "success", "message" => "Cashout cart actions synced."]);
