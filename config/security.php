<?php
/**
 * BETELITE - Security Middleware & Cryptographic Helpers
 * Handles: CSRF validation, XSS filtering, Session Regeneration, Admin Guards
 */

// Regenerate session ID periodically to prevent hijacking
if (!isset($_SESSION['last_regeneration']) || time() - $_SESSION['last_regeneration'] > 1800) {
    session_regenerate_id(true);
    $_SESSION['last_regeneration'] = time();
}

// CSRF Token Generation
function generate_csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// CSRF Token Validation
function validate_csrf_token($token) {
    if (!isset($_SESSION['csrf_token']) || empty($token)) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
}

// XSS Sanitization helper for HTML outputs
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
}

// Input sanitizer for database bindings (raw escape fallback)
function db_escape($conn, $data) {
    return mysqli_real_escape_string($conn, trim($data));
}

// Auth Middleware: Check logged in
function require_login() {
    if (!isset($_SESSION['user_id'])) {
        header("Location: " . SITE_URL . "/login.php");
        exit;
    }
}

// Role Middleware: Check Role
function require_role($allowed_roles) {
    require_login();
    if (!in_array($_SESSION['role'], (array)$allowed_roles)) {
        header("HTTP/1.1 403 Forbidden");
        die("403 Forbidden - Unauthorized Access");
    }
}
