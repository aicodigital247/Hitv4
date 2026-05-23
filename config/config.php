<?php
/**
 * BETELITE - Platform Globals & Session Setups
 */

// Start session securely if not already running
if (session_status() == PHP_SESSION_NONE) {
    // Session security configurations
    ini_set('session.cookie_httponly', 1);
    ini_set('session.cookie_secure', isset($_SERVER['HTTPS']) ? 1 : 0);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.gc_maxlifetime', 3600); // 1 hour session time-to-live
    
    session_start();
}

// Global Consts
define('SITE_NAME', 'BETELITE');
define('SITE_URL', 'http://localhost/betelite'); // Change to dynamic URL or direct domain on production
define('CURRENCY_SYMBOL', '$');

// Lazy load platform settings from database
function get_platform_setting($conn, $key, $default = '') {
    $stmt = $conn->prepare("SELECT `value` FROM `platform_settings` WHERE `key` = ?");
    if ($stmt) {
        $stmt->bind_param("s", $key);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($res && $row = $res->fetch_assoc()) {
            return $row['value'];
        }
        $stmt->close();
    }
    return $default;
}
