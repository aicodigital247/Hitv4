<?php
/**
 * BETELITE - Database Connection Config
 * Uses Object-Oriented MySQLi with Prepared Statements support
 */

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'betelite');

// Create MySQLi connection
$conn = @new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check connection
if ($conn->connect_error) {
    // In production, log error and show friendly msg. Do NOT expose credentials
    error_log("Database connection failed: " . $conn->connect_error);
    die(json_encode([
        'status' => 'error',
        'message' => 'Database connection failed. Please verify configurations.'
    ]));
}

// Set charset to utf8mb4 for universal emoji support (team names, logo strings)
$conn->set_charset("utf8mb4");
