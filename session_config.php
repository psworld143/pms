<?php
/**
 * Session Configuration for VPS
 * Include this file at the top of all PHP files that use sessions
 */

// Set custom session save path (cross-platform compatible)
$sessionPath = __DIR__ . DIRECTORY_SEPARATOR . 'tmp_sessions';

// Create session directory if it doesn't exist
if (!is_dir($sessionPath)) {
    mkdir($sessionPath, 0755, true);
    // Create .htaccess to protect session files (Apache only, ignored on Windows/IIS)
    $htaccessPath = $sessionPath . DIRECTORY_SEPARATOR . '.htaccess';
    if (!file_exists($htaccessPath)) {
        @file_put_contents($htaccessPath, "Order deny,allow\nDeny from all\n");
    }
}

// Set session save path
ini_set('session.save_path', $sessionPath);

// Set session cookie parameters for better security
session_set_cookie_params([
    'lifetime' => 0,                    // Session cookie (expires when browser closes)
    'path' => '/',                      // Available across entire domain
    'domain' => '',                     // Set to your domain if needed (e.g., '.yourdomain.com')
    'secure' => isset($_SERVER['HTTPS']), // HTTPS only in production
    'httponly' => true,                 // Prevent JavaScript access
    'samesite' => 'Lax'                 // CSRF protection
]);

// Start session
session_start();

// Optional: Regenerate session ID periodically for security
if (!isset($_SESSION['session_created'])) {
    $_SESSION['session_created'] = time();
} elseif (time() - $_SESSION['session_created'] > 1800) { // Regenerate every 30 minutes
    session_regenerate_id(true);
    $_SESSION['session_created'] = time();
}

// Session debugging (remove in production)
// if (isset($_GET['debug_session'])) {
//     echo "Session Path: " . ini_get('session.save_path') . "<br>";
//     echo "Session ID: " . session_id() . "<br>";
//     echo "Session Status: Working<br>";
// }
?>
