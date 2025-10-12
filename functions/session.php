<?php 
// Improved session handling with security measures
if (session_status() == PHP_SESSION_NONE) {
    // Session configuration for security
    ini_set('session.cookie_httponly', 1);
    ini_set('session.cookie_secure', 1);
    ini_set('session.use_strict_mode', 1);
    
    session_start();
    
    // Regenerate session ID to prevent fixation attacks
    if (!isset($_SESSION['created'])) {
        session_regenerate_id(true);
        $_SESSION['created'] = time();
    } else if (time() - $_SESSION['created'] > 1800) {
        // Regenerate session ID every 30 minutes
        session_regenerate_id(true);
        $_SESSION['created'] = time();
    }
}

// Set error reporting based on environment
if (isset($_SERVER['HTTP_HOST']) && (strpos($_SERVER['HTTP_HOST'], 'localhost') !== false || strpos($_SERVER['HTTP_HOST'], '127.0.0.1') !== false)) {
    // Development environment
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    // Production environment - suppress errors from users
    error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
}

// Initialize session variables if not set to prevent undefined index notices
if (!isset($_SESSION['id'])) {
    $_SESSION['id'] = null;
}
if (!isset($_SESSION['email'])) {
    $_SESSION['email'] = null;
}
if (!isset($_SESSION['first_name'])) {
    $_SESSION['first_name'] = null;
}
if (!isset($_SESSION['last_name'])) {
    $_SESSION['last_name'] = null;
}
if (!isset($_SESSION['pending_verification'])) {
    $_SESSION['pending_verification'] = null;
}
?>