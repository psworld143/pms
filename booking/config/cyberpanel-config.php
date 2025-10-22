<?php
/**
 * CyberPanel Deployment Configuration
 * This file is ONLY for live server deployment
 * DO NOT modify database.local.php
 */

// Server-specific settings for https://pms.seait.edu.ph/booking
define('CYBERPANEL_DEPLOYMENT', true);
define('BASE_URL', 'https://pms.seait.edu.ph');
define('BOOKING_URL', 'https://pms.seait.edu.ph/booking');

// Production error handling
if (defined('CYBERPANEL_DEPLOYMENT') && CYBERPANEL_DEPLOYMENT) {
    ini_set('display_errors', 0); // Hide errors from users
    ini_set('log_errors', 1);
    ini_set('error_log', '/tmp/pms_errors.log');
    
    // Session configuration for HTTPS
    ini_set('session.cookie_httponly', 1);
    ini_set('session.cookie_secure', 1);
    ini_set('session.use_strict_mode', 1);
    ini_set('session.cookie_samesite', 'Strict');
    
    // Timezone
    date_default_timezone_set('Asia/Manila');
    
    // File permissions
    umask(0022);
}

// NO OUTPUT - This prevents interference with API responses
?>
