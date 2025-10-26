<?php
/**
 * POS Dynamic Path Configuration
 * Automatically detects the correct base paths for both localhost and production
 */

// Detect if we're on localhost or production
$is_localhost = (
    $_SERVER['SERVER_NAME'] === 'localhost' || 
    $_SERVER['SERVER_ADDR'] === '127.0.0.1' ||
    strpos($_SERVER['SERVER_NAME'], 'localhost') !== false
);

// Get the base path dynamically
$script_path = $_SERVER['SCRIPT_NAME'];
$pos_position = strpos($script_path, '/pos/');

if ($pos_position !== false) {
    // Extract everything up to /pos/
    $base_path = substr($script_path, 0, $pos_position);
    define('POS_BASE_PATH', $base_path . '/pos');
} else {
    // Fallback to /pms/pos if we can't detect
    define('POS_BASE_PATH', '/pms/pos');
}

// Define common paths
define('POS_ROOT', POS_BASE_PATH);
define('POS_RESTAURANT', POS_BASE_PATH . '/restaurant');
define('POS_ROOM_SERVICE', POS_BASE_PATH . '/room-service');
define('POS_SPA', POS_BASE_PATH . '/spa');
define('POS_GIFT_SHOP', POS_BASE_PATH . '/gift-shop');
define('POS_EVENTS', POS_BASE_PATH . '/events');
define('POS_QUICK_SALES', POS_BASE_PATH . '/quick-sales');
define('POS_REPORTS', POS_BASE_PATH . '/reports');
define('POS_API', POS_BASE_PATH . '/api');

// For includes (go back to PMS root)
if ($pos_position !== false) {
    define('PMS_BASE_PATH', $base_path);
} else {
    define('PMS_BASE_PATH', '/pms');
}

define('BOOKING_PATH', PMS_BASE_PATH . '/booking');
define('INVENTORY_PATH', PMS_BASE_PATH . '/inventory');

// Helper function to generate POS URLs
function pos_url($path = '') {
    $path = ltrim($path, '/');
    return POS_BASE_PATH . ($path ? '/' . $path : '');
}

// Helper function to generate booking URLs
function booking_url($path = '') {
    $path = ltrim($path, '/');
    return BOOKING_PATH . ($path ? '/' . $path : '');
}

// Debug info (comment out in production)
if (false) { // Set to true for debugging
    error_log("POS Path Detection:");
    error_log("- Script Path: " . $script_path);
    error_log("- POS Base Path: " . POS_BASE_PATH);
    error_log("- Is Localhost: " . ($is_localhost ? 'Yes' : 'No'));
}

?>

