<?php
/**
 * BETELITE - REST API Live Scores Poller
 */
header("Content-Type: application/json");
require_once __DIR__ . '/../config/database.php';

// Mock values for live triggers
echo json_encode([
    "status" => "success",
    "live_minute" => 74,
    "home_score" => 2,
    "away_score" => 1,
    "momentum" => 55
]);
